# chiliz/symfony-flex

This repository is the source of chiliz's bundles recipes for symfony/flex.

The documentation about the way it is working is [available here](https://symfony.com/doc/current/setup/flex_private_recipes.html).

## Use our recipes in your project 

In order to use our recipes, you need to have the following configuration in your `composer.json`:
```json
{
  "extra": {
    "symfony": {
      "allow-contrib": false,
      "require": "6.1.*",
      "endpoint": [
        "https://gitlab.mediarex.com/mediarex-public/symfony-flex/-/raw/dev/recipes/index.json",
        "flex://defaults"
      ]
    }
  }
}
```

Then all available recipes will be usable in your project.

You are now able to execute your `composer require|update` as usual, symfony flex will manage the configuration!

## Register a new symfony/flex recipe

In order to register a new recipe (a set of configuration to be installed while installing/upgrading a bundle), you need to follow the next steps :

### 1. Configuration
In the [config/bundles](./config/bundles), you just need to update a bundle file, or create a new one for your bundle.

The configuration file is simple : 
```yaml
"<your bundle name (with vendor name)>":
  "version number as a string":
    bundles: [] # The list of bundles to be enabled
    copyFromRecipe: [] # The list of files to copy for this recipe. Those must be present in the [assets](./assets) directory following the same structure (vendorName/bundleName/version)
    envs: [] # The list of environment variables to be defined
    gitignore: [] # The list of ignored files for this bundle
    makefile: [] # lines to add to the project makefile
    composerScripts: [] # lines to add to the scripts/auto-script section of composer.json
```

Last but not least, if in your bundle asset directory you've created a `after-install.txt` file, this will be automatically parsed and displayed after installing the recipe.

### 2. Generation
After configuring your bundle, you just need to run `./bin/sf flex:generate` and commit generated files
