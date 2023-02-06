# chiliz/symfony-flex

This repository is the source of chiliz's bundles recipes for symfony/flex.

In order to use it, you need to have the following configuration in your `composer.json`:
```json
{
  "extra": {
    "symfony": {
      "allow-contrib": false,
      "require": "6.2.*",
      "endpoint": [
        "https://gitlab.mediarex.com/mediarex/shared/symfony-flex/-/raw/main/recipes/index.json",
        "flex://defaults"
      ]
    }
  }
}
```

Then all available recipes will be usable in your project.

## Contribution

The contribution is rather simple.

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
```

Last but not least, if in your bundle asset directory you've created a `after-install.txt` file, this will be automatically parsed and displayed after installing the recipe.

Once, that's done, you just need to run `./bin/sf flex:generate`
