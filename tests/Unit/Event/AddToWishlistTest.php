<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Event\AddToWishlist;
use Tweakwise\TweakwiseJs\Helper\Data;

class AddToWishlistTest extends Unit
{
    protected UnitTester $tester;

    private Data|MockInterface $dataHelper;

    private AddToWishlist $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataHelper = Mockery::mock(Data::class);
        $this->subject = new AddToWishlist($this->dataHelper);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @return void
     */
    public function testProductKeyIsPlainIdWhenGroupedExportDisabled(): void
    {
        // Data::resolveGroupedExportProductKey returns plain ID when grouped export is off
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')->andReturn('1000142');

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(42);
        $product->shouldReceive('getTypeId')->andReturn('simple');

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertEquals('1000142', $result['data']['productKey']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesGroupedFormatForConfigurableProduct(): void
    {
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')
            ->with(10, 'configurable')
            ->andReturn('1000199-1000110');

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(10);
        $product->shouldReceive('getTypeId')->andReturn('configurable');

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertEquals('1000199-1000110', $result['data']['productKey']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesGroupedFormatForSimpleProductWithConfigurableParent(): void
    {
        $this->dataHelper->shouldReceive('resolveGroupedExportProductKey')
            ->with(99, 'simple')
            ->andReturn('1000199-1000110');

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(99);
        $product->shouldReceive('getTypeId')->andReturn('simple');

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertEquals('1000199-1000110', $result['data']['productKey']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesPlainIdWhenTypeIdIsNotString(): void
    {
        $this->dataHelper->shouldReceive('getTweakwiseId')->with(42)->andReturn('1000142');

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(42);
        $product->shouldReceive('getTypeId')->andReturn(null);

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertEquals('1000142', $result['data']['productKey']);
    }
}
