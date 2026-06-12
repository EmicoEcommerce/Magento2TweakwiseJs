<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\ViewModel;

use Emico\CodeCept\Test\Unit;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\ViewModel\Base;

class BaseTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @var Config&MockObject
     */
    private Config|MockObject $config;

    /**
     * @var Data&MockObject
     */
    private Data|MockObject $dataHelper;

    private Base $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(Config::class);
        $this->dataHelper = $this->createMock(Data::class);
        $this->subject = new Base($this->config, $this->dataHelper);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Base::resolveGroupedExportProductKey
     * @return void
     */
    public function testResolveGroupedExportProductKeyDelegatesToDataHelper(): void
    {
        $this->dataHelper
            ->method('resolveGroupedExportProductKey')
            ->with(42, 'configurable')
            ->willReturn('1000199-1000142');

        $this->assertEquals('1000199-1000142', $this->subject->resolveGroupedExportProductKey(42, 'configurable'));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Base::resolveGroupedExportProductKey
     * @return void
     */
    public function testResolveGroupedExportProductKeyReturnsFallbackIdOnException(): void
    {
        $this->dataHelper
            ->method('resolveGroupedExportProductKey')
            ->willThrowException(new NoSuchEntityException());

        $this->assertEquals('0', $this->subject->resolveGroupedExportProductKey(42, 'simple'));
    }
}
