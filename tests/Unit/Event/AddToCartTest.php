<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Api\Event\PriceFormatServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToCart;
use Tweakwise\TweakwiseJs\Helper\Data;

class AddToCartTest extends Unit
{
    protected UnitTester $tester;

    private PriceFormatServiceInterface|MockObject $priceFormatService;

    private Data|MockObject $dataHelper;

    private ExportConfig|MockObject $exportConfig;

    /**
     * @var AddToCartExposed
     */
    private AddToCartExposed $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->priceFormatService = $this->createMock(PriceFormatServiceInterface::class);
        $this->dataHelper = $this->createMock(Data::class);
        $this->exportConfig = $this->createMock(ExportConfig::class);

        $this->subject = new AddToCartExposed(
            $this->priceFormatService,
            $this->dataHelper,
            $this->exportConfig,
        );
    }

    /**
     * @return void
     */
    public function testProductKeyIsPlainTweakwiseIdWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(false);
        $this->dataHelper->method('getTweakwiseId')->with(42)->willReturn('1000142');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(42);

        $quoteItem = $this->buildQuoteItemMock();
        $quoteItem->method('getQty')->willReturn(1);

        $this->subject->setProduct($product)->setQuoteItem($quoteItem)->setQty(1);

        $this->assertEquals('1000142', $this->subject->resolveProductKey());
    }

    /**
     * @return void
     */
    public function testProductKeyUsesSimpleIdFromQtyOptionsWhenGroupedExportEnabled(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(true);
        $this->dataHelper->method('getTweakwiseId')->willReturnMap([
            [10, '1000110'],
            [99, null, 1000110, '1000199-1000110'],
        ]);

        $product = $this->createMock(Product::class);

        $quoteItem = $this->buildQuoteItemMock();
        $quoteItem->method('getProductId')->willReturn(10);
        $quoteItem->method('getQtyOptions')->willReturn([99 => new stdClass()]);
        $quoteItem->method('getQty')->willReturn(2);

        $this->subject->setProduct($product)->setQuoteItem($quoteItem)->setQty(2);

        $this->assertEquals('1000199-1000110', $this->subject->resolveProductKey());
    }

    /**
     * @return void
     */
    public function testProductKeyFallsBackToParentIdWhenQtyOptionsEmpty(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(true);
        $this->dataHelper->method('getTweakwiseId')->willReturnMap([
            [10, '1000110'],
            [10, null, 1000110, '1000110-1000110'],
        ]);

        $product = $this->createMock(Product::class);

        $quoteItem = $this->buildQuoteItemMock();
        $quoteItem->method('getProductId')->willReturn(10);
        $quoteItem->method('getQtyOptions')->willReturn([]);
        $quoteItem->method('getQty')->willReturn(1);

        $this->subject->setProduct($product)->setQuoteItem($quoteItem)->setQty(1);

        $this->assertEquals('1000110-1000110', $this->subject->resolveProductKey());
    }

    /**
     * @return void
     */
    public function testProductKeyIsPlainIdWhenQuoteItemIsNull(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(true);
        $this->dataHelper->method('getTweakwiseId')->with(42)->willReturn('1000142');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(42);

        // No quoteItem set — grouped export on but no item context
        $this->subject->setProduct($product)->setQty(1);

        $this->assertEquals('1000142', $this->subject->resolveProductKey());
    }

    /**
     * @return QuoteItemWithProductId&MockObject
     */
    private function buildQuoteItemMock(): QuoteItemWithProductId&MockObject
    {
        return $this->createMock(QuoteItemWithProductId::class);
    }
}

