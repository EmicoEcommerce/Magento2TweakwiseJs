<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Observer;

use Codeception\Attribute\DataProvider;
use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Model\Layout\Merge;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;
use Tweakwise\TweakwiseJs\Observer\ManageLayoutBlocks;

class ManageLayoutBlocksTest extends Unit
{
    protected UnitTester $tester;

    private Config|MockInterface $config;

    private Http|MockInterface $request;

    private Resolver|MockInterface $layerResolver;

    private Layout|MockInterface $layout;

    private Merge|MockInterface $update;

    protected function _before(): void
    {
        $this->config = Mockery::mock(Config::class);
        $this->request = Mockery::mock(Http::class);
        $this->layerResolver = Mockery::mock(Resolver::class);
        $this->layout = Mockery::mock(Layout::class);
        $this->update = Mockery::mock(Merge::class);
    }

    protected function _after(): void
    {
        Mockery::close();
    }

    #[DataProvider('searchConfigurations')]
    public function testSearchLayoutIsOnlyReplacedWhenConfigured(
        string $action,
        bool $enabled,
        ?string $instanceKey,
        SearchType $searchType,
        bool $replace
    ): void {
        $this->config->shouldReceive('isEnabled')->zeroOrMoreTimes()->andReturn($enabled);
        $this->config->shouldReceive('getInstanceKey')->zeroOrMoreTimes()->andReturn($instanceKey);
        $this->config->shouldReceive('getSearchType')->zeroOrMoreTimes()->andReturn($searchType);
        $this->request->shouldReceive('getFullActionName')->zeroOrMoreTimes()->andReturn($action);
        $this->update->shouldReceive('addHandle')
            ->with('tweakwisejs_search')
            ->times($replace ? 1 : 0);
        $this->layerResolver->shouldNotReceive('get');
        $this->layout->shouldReceive('getUpdate')->times($replace ? 1 : 0)->andReturn($this->update);

        $subject = new ManageLayoutBlocks($this->request, $this->config, $this->layerResolver);
        $subject->execute(new Observer(['layout' => $this->layout]));
    }

    public static function searchConfigurations(): iterable
    {
        foreach (['catalogsearch_result_index', 'catalogsearch_results_index'] as $action) {
            yield $action . ' disabled' => [$action, false, 'key', SearchType::SUGGESTIONS, false];
            yield $action . ' no key' => [$action, true, null, SearchType::SUGGESTIONS, false];
            yield $action . ' empty key' => [$action, true, '', SearchType::SUGGESTIONS, false];
            yield $action . ' Magento default' => [$action, true, 'key', SearchType::MAGENTO_DEFAULT, false];
            yield $action . ' suggestions' => [$action, true, 'key', SearchType::SUGGESTIONS, true];
            yield $action . ' instant' => [$action, true, 'key', SearchType::INSTANT_SEARCH, true];
        }
        yield 'unrelated page' => ['cms_index_index', true, 'key', SearchType::SUGGESTIONS, false];
    }

    #[DataProvider('categoryConfigurations')]
    public function testCategoryLayoutStillRespectsMerchandising(
        bool $merchandising,
        int $categoryId,
        string $displayMode,
        bool $replace
    ): void {
        $this->config->shouldReceive('isEnabled')->zeroOrMoreTimes()->andReturn(true);
        $this->config->shouldReceive('getInstanceKey')->zeroOrMoreTimes()->andReturn('key');
        $this->config->shouldReceive('isMerchandisingEnabled')->zeroOrMoreTimes()->andReturn($merchandising);
        $this->request->shouldReceive('getFullActionName')->zeroOrMoreTimes()->andReturn('catalog_category_view');
        $this->update->shouldReceive('addHandle')
            ->with('tweakwisejs_merchandising')
            ->times($replace ? 1 : 0);
        $this->layout->shouldReceive('getUpdate')->times($replace ? 1 : 0)->andReturn($this->update);

        if ($merchandising) {
            $category = Mockery::mock(Category::class);
            $category->shouldReceive('getId')->zeroOrMoreTimes()->andReturn($categoryId);
            $category->shouldReceive('getDisplayMode')->zeroOrMoreTimes()->andReturn($displayMode);

            $layer = Mockery::mock(Layer::class);
            $layer->shouldReceive('getCurrentCategory')->zeroOrMoreTimes()->andReturn($category);
            $this->layerResolver->shouldReceive('get')->zeroOrMoreTimes()->andReturn($layer);
        } else {
            $this->layerResolver->shouldNotReceive('get');
        }

        $subject = new ManageLayoutBlocks($this->request, $this->config, $this->layerResolver);
        $subject->execute(new Observer(['layout' => $this->layout]));
    }

    public static function categoryConfigurations(): iterable
    {
        yield 'disabled' => [false, 2, Category::DM_PRODUCT, false];
        yield 'no category' => [true, 0, Category::DM_PRODUCT, false];
        yield 'CMS only' => [true, 2, Category::DM_PAGE, false];
        yield 'products' => [true, 2, Category::DM_PRODUCT, true];
        yield 'mixed' => [true, 2, Category::DM_MIXED, true];
    }
}
