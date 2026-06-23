<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use stdClass;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Api\Event\PriceFormatServiceInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Mockery;
use Mockery\MockInterface;

class AddToCartTest extends Unit
{
    protected UnitTester $tester;

    private PriceFormatServiceInterface|MockInterface $priceFormatService;

    private Data|MockInterface $dataHelper;

    /**
     * @var AddToCartExposed
     */
    private AddToCartExposed $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->priceFormatService = Mockery::mock(PriceFormatServiceInterface::class);
        $this->dataHelper = Mockery::mock(Data::class);

        $this->subject = new AddToCartExposed(
            $this->priceFormatService,
            $this->dataHelper,
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @return void
     */
    public function testProductKeyIsPlainTweakwiseIdWhenGroupedExportDisabled(): void
    {
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')
            ->with(42, 'simple')
            ->andReturn('1000142');

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(42);
        $product->shouldReceive('getTypeId')->andReturn('simple');

        $quoteItem = $this->buildQuoteItemMock();
        $quoteItem->shouldReceive('getQty')->andReturn(1);

        $this->subject->setProduct($product)->setQuoteItem($quoteItem)->setQty(1);

        $this->assertEquals('1000142', $this->subject->resolveProductKey());
    }

    /**
     * @return void
     */
    public function testProductKeyUsesSimpleIdFromQtyOptionsWhenGroupedExportEnabled(): void
    {
        $this->dataHelper->shouldReceive('getTweakwiseId')
            ->with(10, null, null)
            ->andReturn('1000110');
        $this->dataHelper->shouldReceive('getTweakwiseId')
            ->with(99, null, 1000110)
            ->andReturn('1000199-1000110');

        $product = Mockery::mock(Product::class);

        $quoteItem = $this->buildQuoteItemMock();
        $quoteItem->shouldReceive('getProductId')->andReturn(10);
        $quoteItem->shouldReceive('getQtyOptions')->andReturn([99 => new stdClass()]);
        $quoteItem->shouldReceive('getQty')->andReturn(2);

        $this->subject->setProduct($product)->setQuoteItem($quoteItem)->setQty(2);

        $this->assertEquals('1000199-1000110', $this->subject->resolveProductKey());
    }

    /**
     * @return void
     */
    public function testProductKeyFallsBackToParentIdWhenQtyOptionsEmpty(): void
    {
        $this->dataHelper->shouldReceive('getTweakwiseId')
            ->with(10, null, null)
            ->andReturn('1000110');
        $this->dataHelper->shouldReceive('getTweakwiseId')
            ->with(10, null, 1000110)
            ->andReturn('1000110-1000110');

        $product = Mockery::mock(Product::class);

        $quoteItem = $this->buildQuoteItemMock();
        $quoteItem->shouldReceive('getProductId')->andReturn(10);
        $quoteItem->shouldReceive('getQtyOptions')->andReturn([]);
        $quoteItem->shouldReceive('getQty')->andReturn(1);

        $this->subject->setProduct($product)->setQuoteItem($quoteItem)->setQty(1);

        $this->assertEquals('1000110-1000110', $this->subject->resolveProductKey());
    }

    /**
     * @return void
     */
    public function testProductKeyIsPlainIdWhenQuoteItemIsNull(): void
    {
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')
            ->with(42, 'simple')
            ->andReturn('1000142-1000142');

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(42);
        $product->shouldReceive('getTypeId')->andReturn('simple');

        // No quoteItem set — fallback to resolveGroupedExportProductKey
        $this->subject->setProduct($product)->setQty(1);

        $this->assertEquals('1000142-1000142', $this->subject->resolveProductKey());
    }

    /**
     * @return QuoteItemWithProductId&MockInterface
     */
    private function buildQuoteItemMock(): QuoteItemWithProductId&MockInterface
    {
        return Mockery::mock(QuoteItemWithProductId::class);
    }
}
