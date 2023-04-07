<?php

declare(strict_types=1);

namespace Chiliz\SymfonyFlex\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'flex:generate', description: 'Generate symfony flex configurations')]
class GenerateCommand extends Command
{
    private SymfonyStyle $io;
    private readonly string $configDir;
    private readonly string $assetsDir;
    private readonly string $recipesDir;
    private Filesystem $fs;
    private Finder $finder;
    private array $recipes;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->configDir = dirname(__DIR__, 2) . '/config';
        $this->assetsDir = dirname(__DIR__, 2) . '/assets';
        $this->recipesDir = dirname(__DIR__, 2) . '/recipes';

        $this->fs = new Filesystem();
        $this->finder = new Finder();

        $this->recipes = [];
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Generating symfony/flex files');

        $this->finder->files()->in(sprintf('%s/%s', $this->configDir, 'bundles'))->name(['*.yaml', '*.yml']);

        /** @var SplFileInfo $file */
        foreach($this->finder as $file){
            $bundleConfiguration = Yaml::parseFile($file->getPathname());
            foreach ($bundleConfiguration as $bundleName => $versions) {
                $this->recipes[$bundleName] = [];
                foreach ($versions as $version => $versionConfig) {
                    $this->recipes[$bundleName][] = str_ireplace('v', '', $version);
                    $this->processVersion($bundleName, $version, $versionConfig);
                }
            }
        }

        $baseConfiguration = Yaml::parseFile($this->configDir . '/base.yaml');
        $this->generateIndexJsonFile($baseConfiguration);

        $this->io->success('symfony/flex files generated, now you can use the new recipe\'s versions!');

        return Command::SUCCESS;
    }

    private function generateIndexJsonFile(array $configuration): void
    {
        $this->io->section('Generating index.json file');
        $branch = $configuration['branch'] ?? 'main';
        $data = [
            "recipes" => $this->recipes,
            "branch" => $branch,
            "is_contrib" => $configuration['is_contrib'] ?? true,
            "_links" => [
                "repository" => $configuration['repository'],
                "origin_template" => sprintf('{package}:{version}@%s:%s', $configuration['repository'], $branch),
                "recipe_template" => sprintf('https://%s/-/raw/%s/recipes/{package_dotted}.{version}.json', $configuration['repository'], $branch),
            ]
        ];

        $this->fs->dumpFile($this->recipesDir . '/index.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function processVersion(string $bundleName, string $version, ?array $versionConfig): void
    {
        $this->io->section(sprintf('Processing recipe %s %s', $bundleName, $version));
        $bundleNameSplitted = explode('/', $bundleName);
        $filename = sprintf('%s/%s.%s.%s.json', $this->recipesDir, $bundleNameSplitted[0], $bundleNameSplitted[1], str_ireplace('v', '', $version));

        $newContent = [
            "manifests" => [
                $bundleName => [
                    "manifest" => [],
                    "files" => [],
                ]
            ]
        ];

        $this->handleBundlesActivation($versionConfig, $newContent['manifests'][$bundleName]['manifest']);
        $this->handleGitignore($versionConfig, $newContent['manifests'][$bundleName]['manifest']);
        $this->handleFiles(
            $bundleName,
            $version,
            $versionConfig,
            $newContent['manifests'][$bundleName]['manifest'],
            $newContent['manifests'][$bundleName]['files']
        );
        $this->handleMakefile($versionConfig, $newContent['manifests'][$bundleName]['manifest']);
        $this->handlePostInstallOutput($bundleName, $version, $newContent['manifests'][$bundleName]['manifest']);
        $this->handleComposerScripts($versionConfig, $newContent['manifests'][$bundleName]['manifest']);

        if ($this->fs->exists($filename)) {
            $oldContent = json_decode(file_get_contents($filename), true);
            unset($oldContent['manifests'][$bundleName]['ref']);
            if ($newContent === $oldContent) {
                $this->io->writeln('Same content, nothing to do on this one');
                return;
            }
        }

        $newContent['manifests'][$bundleName]['ref'] = bin2hex(random_bytes(20));
        $this->fs->dumpFile($filename, json_encode($newContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function handleBundlesActivation(?array $versionConfig, array &$manifest): void
    {
        if (!isset($versionConfig['bundles']) || $versionConfig['bundles'] === []) {
            return;
        }

        $manifest['bundles'] = $versionConfig['bundles'];
    }

    private function handleGitignore(?array $versionConfig, array &$manifest): void
    {
        if (!isset($versionConfig['gitignore']) || $versionConfig['gitignore'] === []) {
            return;
        }

        $manifest['gitignore'] = $versionConfig['gitignore'];
    }

    private function handleFiles(string $bundleName, string $version, ?array $versionConfig, array &$manifest, array &$files): void
    {
        if (!isset($versionConfig['copyFromRecipe']) || $versionConfig['copyFromRecipe'] === []) {
            return;
        }

        $manifest['copy-from-recipe'] = [];
        foreach ($versionConfig['copyFromRecipe'] as $file) {
            $bundleNameSplitted = explode('/', $bundleName);
            $filename = sprintf('%s/%s/%s/%s/%s', $this->assetsDir, $bundleNameSplitted[0], $bundleNameSplitted[1], str_ireplace('v', '', $version), $file);
            if (!$this->fs->exists($filename)) {
                $this->io->error(sprintf('%s has not been found or is not readable', $filename));
                continue;
            }
            $manifest['copy-from-recipe'][$file] = $file;
            $files[$file] = [
                "contents" => explode(PHP_EOL, file_get_contents($filename)),
                "executable" => false,
            ];
        }
    }

    private function handleMakefile(?array $versionConfig, array &$manifest): void
    {
        if (!isset($versionConfig['makefile']) || $versionConfig['makefile'] === []) {
            return;
        }
        $manifest['makefile'] = $versionConfig['makefile'];
    }

    private function handlePostInstallOutput(string $bundleName, string $version, array &$manifest)
    {
        $bundleNameSplitted = explode('/', $bundleName);
        $filename = sprintf('%s/%s/%s/%s/after-install.txt', $this->assetsDir, $bundleNameSplitted[0], $bundleNameSplitted[1], str_ireplace('v', '', $version));
        if(!$this->fs->exists($filename)){
            return;
        }

        $manifest['post-install-output'] = explode(PHP_EOL, file_get_contents($filename));
    }

    private function handleComposerScripts(?array $versionConfig, array &$manifest)
    {
        if (!isset($versionConfig['composerScripts']) || $versionConfig['composerScripts'] === []) {
            return;
        }

        $manifest['composer-scripts'] = $versionConfig['composerScripts'];
    }

}
