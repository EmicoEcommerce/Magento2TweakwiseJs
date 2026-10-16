<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Observer;

use Codeception\Attribute\DataProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use PHPUnit\Framework\TestCase;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Observer\AddPageAssets;

class AddPageAssetsTest extends TestCase
{
    #[DataProvider('configurations')]
    public function testAssetsRequireEnabledModuleAndInstanceKey(bool $enabled, ?string $key, int $assets): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn($enabled);
        $config->method('getInstanceKey')->willReturn($key);
        $pageConfig = $this->createMock(PageConfig::class);
        $pageConfig->expects(self::exactly($assets))->method('addRemotePageAsset')->willReturnSelf();

        $subject = new AddPageAssets($pageConfig, $config);
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
