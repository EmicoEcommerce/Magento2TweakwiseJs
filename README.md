# Tweakwise JS
Magento 2 module for Tweakwise JS

## Installation

Install package using composer
```sh
composer require tweakwise/magento2-tweakwise-js
```

If installed and enabled, disable the alternative server-side Tweakwise integration and its compatibility modules first. Keep the base attribute-landing and Tweakwise export modules enabled:
```sh
php bin/magento module:disable Tweakwise_AttributeLandingTweakwise Tweakwise_TweakwiseHyva Tweakwise_Magento2Tweakwise
```

Enable module and run installers:
```sh
php bin/magento module:enable Emico_AttributeLanding Tweakwise_Magento2TweakwiseExport Tweakwise_TweakwiseJs
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## Configurations
All settings can be found under `Stores -> Configuration -> Catalog -> Tweakwise -> Tweakwise JS`.

### General
- **Module Version**: Displays the currently installed module version (read-only).
- **Enabled**: Enable or disable the Tweakwise JS module.
- **Instance Key**: Your Tweakwise instance key. Save this first so that all dynamic options (UI language, language, merchandising, search type) load their available values from your Tweakwise instance.
- **UI Language**: The language used for the Tweakwise JS user interface elements.
- **Language**: The language used for search queries and result processing (word conjugations, spelling corrections).

### Merchandising
- **Enabled**: Enable or disable Tweakwise merchandising functionality. Only visible when the module is enabled.

### Search
- **Type**: The type of search integration to use (e.g. instant search, autocomplete). Only visible when the module is enabled.

### Events
- **Enabled**: Enable event tracking. Captures key e-commerce events (product views, searches, purchases) to power Tweakwise insights reports and personalisation.
- **Cookie Name**: Name of the cookie that holds the Tweakwise profile id. Required when events are enabled.

## JavaScript Theme Compatibility
All JavaScript included in this module has been developed to ensure full compatibility with both the Luma and Hyvä themes.
The implementation avoids direct dependencies on theme-specific frontend frameworks or structures, making it flexible and reliable across different Magento 2 storefront environments.

## Search initialization

Native Magento search is replaced only when Tweakwise JS is enabled, an instance key is configured, and the search type is not **Magento Default**. Instant Search keeps the SDK's auto-initialization; the search results template only provides its output container, preventing a second lister initialization.

Category and attribute-landing pages also use the SDK's automatic navigation context when **Instant Search** is selected, rather than creating an additional explicit lister. With **Suggestions** or **Magento Default**, these pages initialize their lister explicitly. Landing-page filters and template settings are passed to the selected initialization path.
Landing-page filters provide the initial selection; an existing navigation selection in the configured query or hash history takes precedence when reloading the page.

Suggestions and Enter-key submissions respect the configured query or hash history mode; hash history is used when no mode is configured. Search analytics fires on the destination results page and when the search term changes without a page reload, including hash navigation and SDK selection changes. Repeated notifications for the same term are deduplicated. The initial term uses the same precedence as the lister (hash `tn_q`, query `tn_q`, then query `q`).

Shared initialization helpers wait for both the DOM and the SDK constructor. This also handles a cached SDK or the `.com` failover gateway becoming ready before the page templates register their callbacks.

## Contributors 
If you want to create a pull request as a contributor, use the guidelines of semantic-release. semantic-release automates the whole package release workflow including: determining the next version number, generating the release notes, and publishing the package.
By adhering to the commit message format, a release is automatically created with the commit messages as release notes. Follow the guidelines as described in: https://github.com/semantic-release/semantic-release?tab=readme-ov-file#commit-message-format. 
