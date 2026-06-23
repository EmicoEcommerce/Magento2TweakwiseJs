<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Helper;

use Emico\CodeCept\Test\Unit;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Magento2TweakwiseExport\Model\Helper as ExportHelper;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;

class DataTest extends Unit
{
    protected UnitTester $tester;

    private StoreManagerInterface|MockInterface $storeManager;

    private ExportHelper|MockInterface $exportHelper;

    private ExportConfig|MockInterface $exportConfig;

    private ConfigurableResource|MockInterface $configurableResource;

    private Data $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $store = Mockery::mock(StoreInterface::class);
        $store->shouldReceive('getId')->andReturn(1);

        $this->storeManager = Mockery::mock(StoreManagerInterface::class);
        $this->storeManager->shouldReceive('getStore')->andReturn($store);

        $this->exportHelper = Mockery::mock(ExportHelper::class);
        $this->exportConfig = Mockery::mock(ExportConfig::class);
        $this->configurableResource = Mockery::mock(ConfigurableResource::class);

        $context = Mockery::mock(Context::class);

        $this->subject = new Data(
            $context,
            $this->storeManager,
            $this->exportHelper,
            $this->exportConfig,
            $this->configurableResource,
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
    public function testResolveGroupedExportProductKeyReturnsPlainIdWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(false);
        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 42, null)->andReturn('1000142');

        $result = $this->subject->resolveGroupedExportProductKey(42, 'simple');

        $this->assertEquals('1000142', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsPlainIdForConfigurableTypeWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(false);
        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 10, null)->andReturn('1000110');

        $result = $this->subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForConfigurableWhenGroupedExportEnabled(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(true);

        $this->configurableResource->shouldReceive('getChildrenIds')
            ->with(10)
            ->andReturn([[99 => 99]]);

        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 10, null)->andReturn('1000110');
        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 99, 1000110)->andReturn('1000110-1000199');

        $result = $this->subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110-1000199', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForSimpleWithConfigurableParent(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(true);

        $this->configurableResource->shouldReceive('getParentIdsByChild')
            ->with(99)
            ->andReturn([10]);

        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 10, null)->andReturn('1000110');
        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 99, 1000110)->andReturn('1000110-1000199');

        $result = $this->subject->resolveGroupedExportProductKey(99, 'simple');

        $this->assertEquals('1000110-1000199', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsSimpleSimpleFormatForSimpleWithNoConfigurableParent(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(true);

        $this->configurableResource->shouldReceive('getParentIdsByChild')
            ->with(42)
            ->andReturn([]);

        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 42, null)->andReturn('1000142');
        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 42, 1000142)->andReturn('1000142-1000142');

        $result = $this->subject->resolveGroupedExportProductKey(42, 'simple');

        $this->assertEquals('1000142-1000142', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForConfigurableWithNoChildren(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(true);

        $this->configurableResource->shouldReceive('getChildrenIds')
            ->with(10)
            ->andReturn([]);

        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 10, null)->andReturn('1000110');
        $this->exportHelper->shouldReceive('getTweakwiseId')->with(1, 10, 1000110)->andReturn('1000110-1000110');

        $result = $this->subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110-1000110', $result);
    }
}
