# Tweakwise JS
Magento 2 module for Tweakwise JS

## Installation

Install package using composer
```sh
composer require tweakwise/magento2-tweakwise-js
```

Enable module and run installers
```sh
php bin/magento module:enable Emico_AttributeLanding Tweakwise_Magento2TweakwiseExport Tweakwise_TweakwiseJs
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## Configurations
All settings can be found under Stores -> Configuration -> Catalog -> Tweakwise -> Tweakwise JS.

## JavaScript Theme Compatibility
All JavaScript included in this module has been developed to ensure full compatibility with both the Luma and Hyvä themes.
The implementation avoids direct dependencies on theme-specific frontend frameworks or structures, making it flexible and reliable across different Magento 2 storefront environments.

## Contributors
If you want to create a pull request as a contributor, use the guidelines of semantic-release. semantic-release automates the whole package release workflow including: determining the next version number, generating the release notes, and publishing the package.
By adhering to the commit message format, a release is automatically created with the commit messages as release notes. Follow the guidelines as described in: https://github.com/semantic-release/semantic-release?tab=readme-ov-file#commit-message-format. 
