<?php

declare(strict_types=1);

use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;
use Tweakwise\TweakwiseJs\Observer\AddPageAssets;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

function renderTemplate(string $template, SearchType $searchType = SearchType::SUGGESTIONS): string
{
    $escaper = new Escaper();
    $block = new class ($searchType) {
        public function __construct(private readonly SearchType $searchType)
        {
        }

        public function __call(string $method, array $arguments): mixed
        {
            return match ($method) {
                'getViewModel', 'getCurrentCategory', 'getLandingPage' => $this,
                'isEnabled', 'isEventsEnabled', 'isSearchResultsPage' => true,
                'getInstanceKey' => 'test-key',
                'getLanguage' => 'en',
                'getUiLanguage' => 'nl',
                'getSearchType' => $this->searchType,
                'getSearchUrl' => "https://shop.test/catalogsearch/results?existing=1&quote='",
                'getStoreRootCategory', 'getId', 'getCategoryId', 'getTweakwiseId' => 2,
                'getHideSelectedFilters' => false,
                'getFilterAttributes' => [],
                'getFilters' => '{}',
                'getRuleId' => 'rule-1',
                'getContainerId' => 'featured-recommendations',
                'getWidgetTitle' => '',
                'getTweakwiseFilterTemplate', 'getTweakwiseSortTemplate', 'getTweakwiseBuilderTemplate' => 0,
                default => throw new LogicException("Unexpected template call: $method"),
            };
        }
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
