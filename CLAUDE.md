# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Module identity

Magento 2 module `Tweakwise_TweakwiseJs` (PHP namespace `Tweakwise\TweakwiseJs`), registered via `src/registration.php`. Composer package: `tweakwise/magento2-tweakwise-js`.

Key dependencies: `tweakwise/magento2-tweakwise-export` (provides Tweakwise ID helpers), `emico/m2-attributelanding`.

## Commands

```sh
# Install dependencies
composer install

# Code quality (runs phpcs, phpstan, phpmd, parallel-lint via GrumPHP)
vendor/bin/grumphp run --ansi

# Run individual tools
vendor/bin/phpcs --standard=phpcs.xml src/
vendor/bin/phpstan analyse --configuration phpstan.neon src/
vendor/bin/phpmd src/ text ruleset.xml

# Run tests (Codeception)
vendor/bin/codecept run
vendor/bin/codecept run Unit
vendor/bin/codecept run Functional
```

## Commit message format

Follow Conventional Commits / semantic-release format (`feat:`, `fix:`, `chore:`, etc.). Releases and changelogs are automated from commit messages.

## Architecture

### Configuration

All admin config lives under `tweakwise/tweakwisejs/` in `etc/adminhtml/system.xml`. `src/Model/Config.php` is the single entry point for reading config values — always use it instead of reading `ScopeConfigInterface` directly.

Config paths: `general/enabled`, `general/instance_key`, `general/uilanguage`, `general/language`, `merchandising/enabled`, `search/type`, `events/enabled`, `events/cookie_name`.

### How JS is delivered

1. `Observer/AddPageAssets` (triggered on `layout_generate_blocks_after`) — adds `<link rel="preload">` and `<script>` tags for the external Tweakwise JS bundle from `gateway.tweakwisenavigator.net` (with `.com` as failover via `window.tweakwiseFailover`).
2. Layout XML in `src/view/frontend/layout/default.xml` injects inline `<script>` blocks (rendered from `.phtml` templates) into `after.body.start` / `before.body.end` for initial config, search, event init, and add-to tracking. `default_hyva.xml` provides Hyvä-specific overrides.
3. `Observer/ManageLayoutBlocks` (triggered on `layout_load_before`) — dynamically adds the `tweakwisejs_merchandising` layout handle on category pages when merchandising is enabled.

### ViewModels

`ViewModel/Base` is the base class. Feature-specific ViewModels extend it:
- `ViewModel/Search` — search type config
- `ViewModel/Merchandising` — merchandising feature flags
- `ViewModel/AttributeLanding` — attribute landing page context
- `ViewModel/Event` — event cookie name, purchase revenue, and serialised order product IDs for the purchase event

All templates receive a ViewModel via `view_model` block argument.

### Event tracking

Cart and wishlist events are captured server-side via Magento observers (`Observer/Event/TriggerAddToCartEvent`, `TriggerAddToWishlistEvent`), stored in the Magento session via `SessionService` (one virtual type per session type: `CheckoutSessionService` / `CustomerSessionService`), and then flushed into Magento's CustomerData JSON response by plugins on `Magento\Checkout\CustomerData\Cart` and `Magento\Wishlist\CustomerData\Wishlist`. The frontend reads `tweakwise_events` from those sections and pushes them.

Concrete service implementations live under `Service/Event/`: `SessionService` (session storage) and `PriceFormatService` (price formatting for event payloads).

`Plugin/Event/AddEventDataToSection.php` is the shared base plugin class. `Plugin/Event/CustomerData/Cart/AddEventDataToCartSection` and `Plugin/Event/CustomerData/Wishlist/AddEventDataToWishlistSection` are the concrete subclasses wired in `etc/frontend/di.xml`.

### API client

`Model/Api/Client` uses Guzzle to call `gateway.tweakwisenavigator.net`, receives XML, and converts it to arrays via `xmlToArray`. API results for features are cached in Magento's cache under `tweakwisejs_features`. `Model/Api/RequestFactory` / `ResponseFactory` handle object construction; concrete request/response types live under `Model/Api/Request/` and `Model/Api/Response/`.

### Theme compatibility

Templates under `src/view/frontend/templates/js/` have Luma and Hyvä variants (e.g. `event/luma/`, `event/hyva/`, `category/hyva/`). The Luma add-to-cart template is `category/add-to.phtml`; the Hyvä variant is `category/hyva/add-to.phtml`. The Hyvä CSP helper (`$hyvaCsp->registerInlineScript()`) is called at the end of templates that emit inline scripts.

The Luma widget-driven add-to-cart flow is handled by `src/view/frontend/web/js/add-to.js` (a RequireJS module). This file sends add-to-cart requests via AJAX from Tweakwise category/search pages and sets `tweakwise_event_handled=1` on those requests to prevent double-firing of the server-side observer.

## Change workflow

Before making any code changes:

1. Analyze the requested change
2. Identify all affected files and Magento components
3. Present a clear plan of what will be changed and why
4. Wait for explicit approval from the user
5. Only then proceed with implementation

Never apply code changes immediately without first showing the intended changes and waiting for confirmation.
