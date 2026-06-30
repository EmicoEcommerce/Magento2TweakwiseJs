<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Helper;

use Emico\CodeCept\Test\Unit;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Magento2TweakwiseExport\Model\Helper as ExportHelper;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;

class DataTest extends Unit
{
    protected UnitTester $tester;

    private ExportHelper|MockInterface $exportHelper;

    private ConfigurableResource|MockInterface $configurableResource;

    private Data $subject;

    /**
     * @return void
     * @throws \Exception
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _before(): void
    {
        $this->tester->mockConfig(ExportConfig::PATH_GROUPED_EXPORT_ENABLED, '1');

        $this->exportHelper = Mockery::mock(ExportHelper::class);
        $this->tester->mockService(ExportHelper::class, $this->exportHelper);

        $this->configurableResource = Mockery::mock(ConfigurableResource::class);
        $this->tester->mockService(ConfigurableResource::class, $this->configurableResource);

        $this->subject = $this->tester->getObjectManager()->create(Data::class);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsPlainIdWhenGroupedExportDisabled(): void
    {
        $this->tester->mockConfig(ExportConfig::PATH_GROUPED_EXPORT_ENABLED, '0');

        $exportConfig = Mockery::mock(ExportConfig::class);
        $exportConfig->shouldReceive('isGroupedExport')->andReturn(false);
        $this->tester->mockService(ExportConfig::class, $exportConfig);

        $subject = $this->tester->getObjectManager()->create(Data::class);

        $this->exportHelper->shouldReceive('getTweakwiseId')->andReturn('1000142');
        $this->configurableResource->shouldNotReceive('getParentIdsByChild');
        $this->configurableResource->shouldNotReceive('getChildrenIds');

        $result = $subject->resolveGroupedExportProductKey(42, 'simple');

        $this->assertEquals('1000142', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsPlainIdForConfigurableTypeWhenGroupedExportDisabled(): void
    {
        $this->tester->mockConfig(ExportConfig::PATH_GROUPED_EXPORT_ENABLED, '0');

        $exportConfig = Mockery::mock(ExportConfig::class);
        $exportConfig->shouldReceive('isGroupedExport')->andReturn(false);
        $this->tester->mockService(ExportConfig::class, $exportConfig);

        $subject = $this->tester->getObjectManager()->create(Data::class);

        $this->exportHelper->shouldReceive('getTweakwiseId')->andReturn('1000110');
        $this->configurableResource->shouldNotReceive('getParentIdsByChild');
        $this->configurableResource->shouldNotReceive('getChildrenIds');

        $result = $subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForConfigurableWhenGroupedExportEnabled(): void
    {
        $this->configurableResource->shouldReceive('getChildrenIds')
            ->with(10)
            ->andReturn([[99 => 99]]);

        $this->exportHelper->shouldReceive('getTweakwiseId')->withArgs(function ($_storeId, $entityId, $groupCode) {
            return $entityId === 10 && $groupCode === null;
        })->andReturn('1000110');

        $this->exportHelper->shouldReceive('getTweakwiseId')->withArgs(function ($_storeId, $entityId, $groupCode) {
            return $entityId === 99 && $groupCode === 1000110;
        })->andReturn('1000110-1000199');

        $result = $this->subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110-1000199', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForSimpleWithConfigurableParent(): void
    {
        $this->configurableResource->shouldReceive('getParentIdsByChild')
            ->with(99)
            ->andReturn([10]);

        $this->exportHelper->shouldReceive('getTweakwiseId')->withArgs(function ($_storeId, $entityId, $groupCode) {
            return $entityId === 10 && $groupCode === null;
        })->andReturn('1000110');

        $this->exportHelper->shouldReceive('getTweakwiseId')->withArgs(function ($_storeId, $entityId, $groupCode) {
            return $entityId === 99 && $groupCode === 1000110;
        })->andReturn('1000110-1000199');

        $result = $this->subject->resolveGroupedExportProductKey(99, 'simple');

        $this->assertEquals('1000110-1000199', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsSimpleSimpleFormatForSimpleWithNoConfigurableParent(): void
    {
        $this->configurableResource->shouldReceive('getParentIdsByChild')
            ->with(42)
            ->andReturn([]);

        $this->exportHelper->shouldReceive('getTweakwiseId')->withArgs(function ($_storeId, $entityId, $groupCode) {
            return $entityId === 42 && $groupCode === null;
        })->andReturn('1000142');

        $this->exportHelper->shouldReceive('getTweakwiseId')->withArgs(function ($_storeId, $entityId, $groupCode) {
            return $entityId === 42 && $groupCode === 1000142;
        })->andReturn('1000142-1000142');

        $result = $this->subject->resolveGroupedExportProductKey(42, 'simple');

        $this->assertEquals('1000142-1000142', $result);
    }

    /**
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsParentSimpleFormatForConfigurableWithNoChildren(): void
    {
        $this->configurableResource->shouldReceive('getChildrenIds')
            ->with(10)
            ->andReturn([]);

        $this->exportHelper->shouldReceive('getTweakwiseId')->withArgs(function ($_storeId, $entityId, $groupCode) {
            return $entityId === 10 && $groupCode === null;
        })->andReturn('1000110');

        $this->exportHelper->shouldReceive('getTweakwiseId')->withArgs(function ($_storeId, $entityId, $groupCode) {
            return $entityId === 10 && $groupCode === 1000110;
        })->andReturn('1000110-1000110');

        $result = $this->subject->resolveGroupedExportProductKey(10, Configurable::TYPE_CODE);

        $this->assertEquals('1000110-1000110', $result);
    }
}
