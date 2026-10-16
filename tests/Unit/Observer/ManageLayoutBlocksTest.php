<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Observer;

use Codeception\Attribute\DataProvider;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Model\Layout\Merge;
use PHPUnit\Framework\TestCase;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;
use Tweakwise\TweakwiseJs\Observer\ManageLayoutBlocks;

class ManageLayoutBlocksTest extends TestCase
{
    #[DataProvider('searchConfigurations')]
    public function testSearchLayoutIsOnlyReplacedWhenConfigured(
        string $action,
        bool $enabled,
        ?string $instanceKey,
        SearchType $searchType,
        bool $replace
    ): void {
        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        $config->method('getInstanceKey')->willReturn($instanceKey);
        $config->method('getSearchType')->willReturn($searchType);

        $request = $this->createMock(Http::class);
        $request->method('getFullActionName')->willReturn($action);
        $update = $this->createMock(Merge::class);
        $update->expects($replace ? self::once() : self::never())
            ->method('addHandle')->with('tweakwisejs_search');
        $layout = $this->createMock(Layout::class);
        $layout->method('getUpdate')->willReturn($update);
        $resolver = $this->createMock(Resolver::class);
        $resolver->expects(self::never())->method('get');

        $subject = new ManageLayoutBlocks($request, $config, $resolver);
        $subject->execute(new Observer(['layout' => $layout]));
    }

    public static function searchConfigurations(): iterable
    {
        foreach (['catalogsearch_result_index', 'catalogsearch_results_index'] as $action) {
            yield "$action disabled" => [$action, false, 'key', SearchType::SUGGESTIONS, false];
            yield "$action no key" => [$action, true, null, SearchType::SUGGESTIONS, false];
            yield "$action empty key" => [$action, true, '', SearchType::SUGGESTIONS, false];
            yield "$action Magento default" => [$action, true, 'key', SearchType::MAGENTO_DEFAULT, false];
            yield "$action suggestions" => [$action, true, 'key', SearchType::SUGGESTIONS, true];
            yield "$action instant" => [$action, true, 'key', SearchType::INSTANT_SEARCH, true];
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
        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn(true);
        $config->method('getInstanceKey')->willReturn('key');
        $config->method('isMerchandisingEnabled')->willReturn($merchandising);
        $request = $this->createMock(Http::class);
        $request->method('getFullActionName')->willReturn('catalog_category_view');
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $category->method('getId')->willReturn($categoryId);
        $category->setData('display_mode', $displayMode);
        $layer = $this->createMock(Layer::class);
        $layer->method('getCurrentCategory')->willReturn($category);
        $resolver = $this->createMock(Resolver::class);
        $resolver->method('get')->willReturn($layer);
        $update = $this->createMock(Merge::class);
        $update->expects($replace ? self::once() : self::never())
            ->method('addHandle')->with('tweakwisejs_merchandising');
        $layout = $this->createMock(Layout::class);
        $layout->method('getUpdate')->willReturn($update);

        $subject = new ManageLayoutBlocks($request, $config, $resolver);
        $subject->execute(new Observer(['layout' => $layout]));
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
