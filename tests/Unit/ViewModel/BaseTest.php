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

    private Config|MockInterface $config;

    private Data|MockInterface $dataHelper;

    private Base $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->dataHelper = Mockery::mock(Data::class);
        $this->subject = new Base($this->config, $this->dataHelper);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
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
