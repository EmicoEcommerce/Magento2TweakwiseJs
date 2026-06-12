<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Helper;

use Emico\CodeCept\Test\Unit;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Magento2TweakwiseExport\Model\Helper as ExportHelper;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;

class DataTest extends Unit
{
    protected UnitTester $tester;

    private StoreManagerInterface|MockObject $storeManager;

    private ExportHelper|MockObject $exportHelper;

    private ExportConfig|MockObject $exportConfig;

    private ConfigurableResource|MockObject $configurableResource;

    private Data $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeManager->method('getStore')->willReturn($store);

        $this->exportHelper = $this->createMock(ExportHelper::class);
        $this->exportConfig = $this->createMock(ExportConfig::class);
        $this->configurableResource = $this->createMock(ConfigurableResource::class);

        $context = $this->createMock(Context::class);

        $this->subject = new Data(
            $context,
            $this->storeManager,
            $this->exportHelper,
            $this->exportConfig,
            $this->configurableResource,
        );
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsPlainIdWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(false);
        $this->exportHelper->method('getTweakwiseId')->with(1, 42, null)->willReturn('1000142');

        $result = $this->subject->resolveGroupedExportProductKey(42, 'simple');

        $this->assertEquals('1000142', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsPlainIdForConfigurableTypeWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(false);
        $this->exportHelper->method('getTweakwiseId')->with(1, 10, null)->willReturn('1000110');

        $result = $this->subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForConfigurableWhenGroupedExportEnabled(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(true);

        // configurable has one child: product 99
        $this->configurableResource->method('getChildrenIds')
            ->with(10)
            ->willReturn([[99 => 99]]);

        $this->exportHelper->method('getTweakwiseId')->willReturnMap([
            [1, 10, null, '1000110'],
            [1, 99, 1000110, '1000110-1000199'],
        ]);

        $result = $this->subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110-1000199', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForSimpleWithConfigurableParent(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(true);

        $this->configurableResource->method('getParentIdsByChild')
            ->with(99)
            ->willReturn([10]);

        $this->exportHelper->method('getTweakwiseId')->willReturnMap([
            [1, 10, null, '1000110'],
            [1, 99, 1000110, '1000110-1000199'],
        ]);

        $result = $this->subject->resolveGroupedExportProductKey(99, 'simple');

        $this->assertEquals('1000110-1000199', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsSimpleSimpleFormatForSimpleWithNoConfigurableParent(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(true);

        $this->configurableResource->method('getParentIdsByChild')
            ->with(42)
            ->willReturn([]);

        $this->exportHelper->method('getTweakwiseId')->willReturnMap([
            [1, 42, null, '1000142'],
            [1, 42, 1000142, '1000142-1000142'],
        ]);

        $result = $this->subject->resolveGroupedExportProductKey(42, 'simple');

        $this->assertEquals('1000142-1000142', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForConfigurableWithNoChildren(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(true);

        $this->configurableResource->method('getChildrenIds')
            ->with(10)
            ->willReturn([]);

        $this->exportHelper->method('getTweakwiseId')->willReturnMap([
            [1, 10, null, '1000110'],
            [1, 10, 1000110, '1000110-1000110'],
        ]);

        $result = $this->subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110-1000110', $result);
    }
}
