<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\ViewModel;

use Emico\CodeCept\Test\Unit;
use Magento\Framework\Exception\NoSuchEntityException;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\ViewModel\Base;

class BaseTest extends Unit
{
    protected UnitTester $tester;

    private Data|MockInterface $dataHelper;

    private Base $subject;

    /**
     * @return void
     * @throws \Exception
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
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
}
