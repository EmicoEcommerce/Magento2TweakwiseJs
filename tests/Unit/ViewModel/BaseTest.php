<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\ViewModel;

use Emico\CodeCept\Test\Unit;
use Magento\Framework\Exception\NoSuchEntityException;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\ViewModel\Base;

class BaseTest extends Unit
{
    protected UnitTester $tester;

    private Data|MockInterface $dataHelper;

    private Base $subject;

    /**
     * @return void
     * @throws \Exception
     */
    public function _before(): void
    {
        $this->dataHelper = Mockery::mock(Data::class);
        $this->tester->mockService(Data::class, $this->dataHelper);

        $this->subject = $this->tester->getObjectManager()->create(Base::class);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Base::resolveGroupedExportProductKey
     * @return void
     */
    public function testResolveGroupedExportProductKeyDelegatesToDataHelper(): void
    {
        $this->dataHelper
            ->shouldReceive('resolveGroupedExportProductKey')
            ->with(42, 'configurable')
            ->andReturn('1000199-1000142');

        $this->assertEquals('1000199-1000142', $this->subject->resolveGroupedExportProductKey(42, 'configurable'));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Base::resolveGroupedExportProductKey
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsFallbackIdOnException(): void
    {
        $this->dataHelper
            ->shouldReceive('resolveGroupedExportProductKey')
            ->andThrow(new NoSuchEntityException());

        $this->assertEquals('0', $this->subject->resolveGroupedExportProductKey(42, 'simple'));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Base::isEnabled
     * @return void
     * @throws \Exception
     */
    public function testIsEnabledDelegatesToConfig(): void
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('isEnabled')->once()->andReturn(true);
        $this->tester->mockService(Config::class, $config);

        $subject = $this->tester->getObjectManager()->create(Base::class);

        $this->assertTrue($subject->isEnabled());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Base::isEnabled
     * @return void
     * @throws \Exception
     */
    public function testIsEnabledReturnsFalseWhenConfigDisabled(): void
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('isEnabled')->once()->andReturn(false);
        $this->tester->mockService(Config::class, $config);

        $subject = $this->tester->getObjectManager()->create(Base::class);

        $this->assertFalse($subject->isEnabled());
    }
}
