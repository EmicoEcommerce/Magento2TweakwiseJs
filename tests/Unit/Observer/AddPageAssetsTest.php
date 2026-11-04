<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Observer;

use Codeception\Attribute\DataProvider;
use Emico\CodeCept\Test\Unit;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Observer\AddPageAssets;

class AddPageAssetsTest extends Unit
{
    protected UnitTester $tester;

    private Config|MockInterface $config;

    private PageConfig|MockInterface $pageConfig;

    protected function _before(): void
    {
        $this->config = Mockery::mock(Config::class);
        $this->pageConfig = Mockery::mock(PageConfig::class);
    }

    protected function _after(): void
    {
        Mockery::close();
    }

    #[DataProvider('configurations')]
    public function testAssetsRequireEnabledModuleAndInstanceKey(bool $enabled, ?string $key, int $assets): void
    {
        $this->config->shouldReceive('isEnabled')->zeroOrMoreTimes()->andReturn($enabled);
        $this->config->shouldReceive('getInstanceKey')->zeroOrMoreTimes()->andReturn($key);
        $this->pageConfig->shouldReceive('addRemotePageAsset')->times($assets)->andReturnSelf();

        $subject = new AddPageAssets($this->pageConfig, $this->config);
        $subject->execute(new Observer());
    }

    public static function configurations(): iterable
    {
        yield 'disabled' => [false, 'key', 0];
        yield 'no key' => [true, null, 0];
        yield 'empty key' => [true, '', 0];
        yield 'configured' => [true, 'key', 2];
    }
}
