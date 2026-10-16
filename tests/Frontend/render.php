<?php

declare(strict_types=1);

use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;
use Tweakwise\TweakwiseJs\Observer\AddPageAssets;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

/**
 * The included Magento templates consume $escaper and $block from this scope.
 *
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
function renderTemplate(string $template, SearchType $searchType = SearchType::SUGGESTIONS): string
{
    $escaper = new class extends Escaper {
        public function escapeHtmlAttr($string, $escapeSingleQuote = true)
        {
            return htmlspecialchars(
                (string) $string,
                $escapeSingleQuote ? ENT_QUOTES : ENT_COMPAT,
                'UTF-8',
                false
            );
        }
    };
    $block = new class ($searchType) {
        public function __construct(private readonly SearchType $searchType)
        {
        }

        /**
         * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
         */
        public function __call(string $method, array $arguments): mixed
        {
            $values = [
                'getViewModel' => $this,
                'getCurrentCategory' => $this,
                'getLandingPage' => $this,
                'isEnabled' => true,
                'isEventsEnabled' => true,
                'isSearchResultsPage' => true,
                'getInstanceKey' => 'test-key',
                'getLanguage' => 'en',
                'getUiLanguage' => 'nl',
                'getSearchType' => $this->searchType,
                'getSearchUrl' => "https://shop.test/catalogsearch/results?existing=1&quote='",
                'getStoreRootCategory' => 2,
                'getId' => 2,
                'getCategoryId' => 2,
                'getTweakwiseId' => 2,
                'getHideSelectedFilters' => false,
                'getFilterAttributes' => [],
                'getFilters' => '{}',
                'getRuleId' => 'rule-1',
                'getContainerId' => 'featured-recommendations',
                'getWidgetTitle' => '',
                'getTweakwiseFilterTemplate' => 0,
                'getTweakwiseSortTemplate' => 0,
                'getTweakwiseBuilderTemplate' => 0,
            ];

            if (!array_key_exists($method, $values)) {
                throw new LogicException('Unexpected template call: ' . $method);
            }

            return $values[$method];
        }
        // phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
    };

    ob_start();
    require dirname(__DIR__, 2) . '/src/view/frontend/templates/js/' . $template . '.phtml';
    return ob_get_clean();
}

$templates = [
    'bootstrap' => renderTemplate('default'),
    'suggestions' => renderTemplate('search'),
    'instant' => renderTemplate('search', SearchType::INSTANT_SEARCH),
    'search' => renderTemplate('search/lister-init'),
    'instantLister' => renderTemplate('search/lister-init', SearchType::INSTANT_SEARCH),
    'category' => renderTemplate('category/listing'),
    'landing' => renderTemplate('attribute-landing'),
    'instantCategory' => renderTemplate('category/listing', SearchType::INSTANT_SEARCH),
    'instantLanding' => renderTemplate('attribute-landing', SearchType::INSTANT_SEARCH),
    'magentoSearch' => renderTemplate('search', SearchType::MAGENTO_DEFAULT),
    'event' => renderTemplate('event/search-event'),
    'recommendations' => renderTemplate('widget/featured-recommendations'),
];
$pageConfig = new class extends PageConfig {
    public array $assets = [];

    public function __construct()
    {
    }

    public function addRemotePageAsset($url, $contentType, array $properties = [], $name = null)
    {
        $this->assets[$name ?? $url] = $properties['attributes'];
        $this->assets[$contentType] = $properties['attributes'];
        return $this;
    }
};
$config = new class extends Config {
    public function __construct()
    {
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function getInstanceKey(): ?string
    {
        return 'test-key';
    }
};
(new AddPageAssets($pageConfig, $config))->execute(new Observer());
?>
<!doctype html>
<title>Tweakwise JS initialization regression tests</title>
<pre id="result">Running</pre>
<script>
    const templates = <?= json_encode($templates, JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;
    const failoverHandler = <?= json_encode($pageConfig->assets['js']['onerror'], JSON_HEX_TAG | JSON_THROW_ON_ERROR) ?>;
    <?= file_get_contents(__DIR__ . '/initialization.js') ?>
</script>
