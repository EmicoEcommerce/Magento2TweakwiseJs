<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\ViewModel;

use Emico\CodeCept\Test\Unit;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;
use Tweakwise\TweakwiseJs\ViewModel\Merchandising;

class MerchandisingTest extends Unit
{
    protected UnitTester $tester;

    private Config|MockInterface $config;

    private Http|MockInterface $request;

    private StoreManagerInterface|MockInterface $storeManager;

    private FormKey|MockInterface $formKey;

    private Merchandising $subject;

    /**
     * @return void
     * @throws \Exception
     */
    public function _before(): void
    {
        $this->config = Mockery::mock(Config::class);
        $this->request = Mockery::mock(Http::class);
        $this->storeManager = Mockery::mock(StoreManagerInterface::class);
        $this->formKey = Mockery::mock(FormKey::class);

        $this->tester->mockService(Config::class, $this->config);
        $this->tester->mockService(Http::class, $this->request);
        $this->tester->mockService(StoreManagerInterface::class, $this->storeManager);
        $this->tester->mockService(FormKey::class, $this->formKey);

        $this->subject = $this->tester->getObjectManager()->create(Merchandising::class, [
            'config' => $this->config,
            'request' => $this->request,
            'storeManager' => $this->storeManager,
            'formKey' => $this->formKey,
        ]);
    }

    /**
     * @return void
     */
    public function _after(): void
    {
        Mockery::close();
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::shouldAddAddToCartWishlistFunctionalities
     * @return void
     */
    public function testShouldAddAddToCartWishlistFunctionalitiesReturnsTrueOnCategoryPage(): void
    {
        $this->request->shouldReceive('getFullActionName')->andReturn('catalog_category_view');
        $this->config->shouldReceive('getSearchType')->andReturn(SearchType::MAGENTO_DEFAULT)->zeroOrMoreTimes();

        $this->assertTrue($this->subject->shouldAddAddToCartWishlistFunctionalities());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::shouldAddAddToCartWishlistFunctionalities
     * @return void
     */
    public function testShouldAddAddToCartWishlistFunctionalitiesReturnsTrueOnTweakwiseSearchResultsPage(): void
    {
        $this->request->shouldReceive('getFullActionName')->andReturn('catalogsearch_results_index');
        $this->config->shouldReceive('getSearchType')->andReturn(SearchType::MAGENTO_DEFAULT)->zeroOrMoreTimes();

        $this->assertTrue($this->subject->shouldAddAddToCartWishlistFunctionalities());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::shouldAddAddToCartWishlistFunctionalities
     * @return void
     */
    public function testShouldAddAddToCartWishlistFunctionalitiesReturnsTrueOnNativeMagentoSearchPage(): void
    {
        $this->request->shouldReceive('getFullActionName')->andReturn('catalogsearch_result_index');
        $this->config->shouldReceive('getSearchType')->andReturn(SearchType::MAGENTO_DEFAULT)->zeroOrMoreTimes();

        $this->assertTrue($this->subject->shouldAddAddToCartWishlistFunctionalities());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::shouldAddAddToCartWishlistFunctionalities
     * @return void
     */
    public function testShouldAddAddToCartWishlistFunctionalitiesReturnsTrueOnAnyPageWhenInstantSearchConfigured(): void
    {
        $this->request->shouldReceive('getFullActionName')->andReturn('cms_index_index');
        $this->config->shouldReceive('getSearchType')->andReturn(SearchType::INSTANT_SEARCH);

        $this->assertTrue($this->subject->shouldAddAddToCartWishlistFunctionalities());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::shouldAddAddToCartWishlistFunctionalities
     * @return void
     */
    public function testShouldAddAddToCartWishlistFunctionalitiesReturnsTrueOnAnyPageWhenSuggestionsConfigured(): void
    {
        $this->request->shouldReceive('getFullActionName')->andReturn('cms_index_index');
        $this->config->shouldReceive('getSearchType')->andReturn(SearchType::SUGGESTIONS);

        $this->assertTrue($this->subject->shouldAddAddToCartWishlistFunctionalities());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::shouldAddAddToCartWishlistFunctionalities
     * @return void
     */
    public function testShouldAddAddToCartWishlistFunctionalitiesReturnsFalseOnUnrelatedPageWithMagentoDefaultSearch(): void
    {
        $this->request->shouldReceive('getFullActionName')->andReturn('cms_index_index');
        $this->config->shouldReceive('getSearchType')->andReturn(SearchType::MAGENTO_DEFAULT);

        $this->assertFalse($this->subject->shouldAddAddToCartWishlistFunctionalities());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::getStoreId
     * @return void
     */
    public function testGetStoreIdReturnsCurrentStoreIdAsString(): void
    {
        $store = Mockery::mock(StoreInterface::class);
        $store->shouldReceive('getId')->andReturn(7);
        $this->storeManager->shouldReceive('getStore')->andReturn($store);

        $this->assertSame('7', $this->subject->getStoreId());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::getStoreId
     * @return void
     */
    public function testGetStoreIdReturnsZeroWhenStoreCannotBeResolved(): void
    {
        $this->storeManager->shouldReceive('getStore')->andThrow(new NoSuchEntityException());

        $this->assertSame('0', $this->subject->getStoreId());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::getFormKey
     * @return void
     */
    public function testGetFormKeyReturnsFormKey(): void
    {
        $this->formKey->shouldReceive('getFormKey')->andReturn('abc123');

        $this->assertSame('abc123', $this->subject->getFormKey());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Merchandising::getFormKey
     * @return void
     */
    public function testGetFormKeyReturnsEmptyStringOnLocalizedException(): void
    {
        $this->formKey->shouldReceive('getFormKey')->andThrow(new LocalizedException(__('boom')));

        $this->assertSame('', $this->subject->getFormKey());
    }
}
