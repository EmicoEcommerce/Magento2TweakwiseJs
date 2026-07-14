<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Mockery;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Event\AddToWishlist;

class AddToWishlistTest extends Unit
{
    protected UnitTester $tester;

    private AddToWishlist $subject;

    /**
     * @return void
     * @throws \Exception
     */
    public function _before(): void
    {
        $this->tester->mockConfig(ExportConfig::PATH_GROUPED_EXPORT_ENABLED, '1');
        $this->subject = $this->tester->getObjectManager()->create(AddToWishlist::class);
    }

    /**
     * @return void
     */
    public function testProductKeyIsPlainIdWhenGroupedExportDisabled(): void
    {
        $this->tester->mockConfig(ExportConfig::PATH_GROUPED_EXPORT_ENABLED, '0');
        $subject = $this->tester->getObjectManager()->create(AddToWishlist::class);

        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(42);
        $product->shouldReceive('getTypeId')->andReturn('simple');

        $subject->setProduct($product);

        $result = $subject->get();

        $this->assertArrayHasKey('productKey', $result['data']);
        $this->assertEquals('addtowishlist', $result['event']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesGroupedFormatForConfigurableProduct(): void
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(10);
        $product->shouldReceive('getTypeId')->andReturn('configurable');

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertArrayHasKey('productKey', $result['data']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesGroupedFormatForSimpleProductWithConfigurableParent(): void
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(99);
        $product->shouldReceive('getTypeId')->andReturn('simple');

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertArrayHasKey('productKey', $result['data']);
    }

    /**
     * @return void
     */
    public function testProductKeyUsesPlainIdWhenTypeIdIsNotString(): void
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getId')->andReturn(42);
        $product->shouldReceive('getTypeId')->andReturn(null);

        $this->subject->setProduct($product);

        $result = $this->subject->get();

        $this->assertArrayHasKey('productKey', $result['data']);
    }
}
