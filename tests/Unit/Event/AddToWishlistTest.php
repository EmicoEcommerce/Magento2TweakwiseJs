<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\MockObject\MockObject;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Event\AddToWishlist;
use Tweakwise\TweakwiseJs\Helper\Data;

class AddToWishlistTest extends Unit
{
    protected UnitTester $tester;

    private Data|MockObject $dataHelper;

    private AddToWishlist $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataHelper = $this->createMock(Data::class);
        $this->subject = new AddToWishlist($this->dataHelper);
    }

    /**
     * @return void
     */
    public function testProductKeyIsPlainIdWhenGroupedExportDisabled(): void
    {
        // Data::resolveGroupedExportProductKey returns plain ID when grouped export is off
        $this->dataHelper->method('resolveGroupedExportProductKey')->willReturn('1000142');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(42);
        $product->method('getTypeId')->willReturn('simple');

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertEquals('1000142', $result['data']['productKey']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesGroupedFormatForConfigurableProduct(): void
    {
        // Data::resolveGroupedExportProductKey returns grouped format when grouped export is on
        $this->dataHelper->method('resolveGroupedExportProductKey')
            ->with(10, 'configurable')
            ->willReturn('1000199-1000110');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(10);
        $product->method('getTypeId')->willReturn('configurable');

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertEquals('1000199-1000110', $result['data']['productKey']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesGroupedFormatForSimpleProductWithConfigurableParent(): void
    {
        $this->dataHelper->method('resolveGroupedExportProductKey')
            ->with(99, 'simple')
            ->willReturn('1000199-1000110');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(99);
        $product->method('getTypeId')->willReturn('simple');

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertEquals('1000199-1000110', $result['data']['productKey']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesPlainIdWhenTypeIdIsNotString(): void
    {
        $this->dataHelper->method('getTweakwiseId')->with(42)->willReturn('1000142');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(42);
        $product->method('getTypeId')->willReturn(null);

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertEquals('1000142', $result['data']['productKey']);
    }
}
