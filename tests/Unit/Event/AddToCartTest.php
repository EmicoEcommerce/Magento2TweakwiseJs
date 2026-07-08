<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use stdClass;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Api\Event\PriceFormatServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToCart;
use Mockery;

class AddToCartTest extends Unit
{
    protected UnitTester $tester;

    private AddToCart $subject;

    /**
     * @return void
     * @throws \Exception
     */
    public function _before(): void
    {
        $this->tester->mockConfig(ExportConfig::PATH_GROUPED_EXPORT_ENABLED, '1');

        $priceFormatService = Mockery::mock(PriceFormatServiceInterface::class);
        $priceFormatService->shouldReceive('format')->andReturnUsing(fn(float $price) => $price);
        $this->tester->mockService(PriceFormatServiceInterface::class, $priceFormatService);

        $this->subject = $this->tester->getObjectManager()->create(AddToCart::class);
    }

    /**
     * @return void
     */
    public function testProductKeyDelegatesToResolveGroupedExportProductKeyWithoutQuoteItem(): void
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(42);
        $product->shouldReceive('getTypeId')->andReturn('simple');

        $this->subject->setProduct($product)->setQty(1);

        $result = $this->subject->resolveProductKey();

        $this->assertNotEmpty($result);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesSimpleIdFromQtyOptionsWhenQuoteItemPresent(): void
    {
        $product = Mockery::mock(Product::class);

        $quoteItem = Mockery::mock(Item::class);
        $quoteItem->shouldReceive('getQtyOptions')->andReturn([99 => new stdClass()]);

        $this->subject->setProduct($product)->setQuoteItem($quoteItem)->setQty(2);

        $result = $this->subject->resolveProductKey();

        $this->assertNotEmpty($result);
    }

    /**
     * @return void
     */
    public function testProductKeyFallsBackToQuoteItemProductIdWhenQtyOptionsEmpty(): void
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getTypeId')->andReturn('simple');

        $quoteItem = Mockery::mock(Item::class);
        $quoteItem->shouldReceive('getQtyOptions')->andReturn([]);
        $quoteItem->shouldReceive('getProductId')->andReturn(10);

        $this->subject->setProduct($product)->setQuoteItem($quoteItem)->setQty(1);

        $result = $this->subject->resolveProductKey();

        $this->assertNotEmpty($result);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesPlainIdWhenTypeIdIsNotString(): void
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(42);
        $product->shouldReceive('getTypeId')->andReturn(null);

        $this->subject->setProduct($product)->setQty(1);

        $result = $this->subject->resolveProductKey();

        $this->assertNotEmpty($result);
    }
}
