<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;

class AddPageAssets implements ObserverInterface
{
    /**
     * @param PageConfig $pageConfig
     * @param Config $config
     */
    public function __construct(
        private readonly PageConfig $pageConfig,
        private readonly Config $config
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $this->addDefaultPageAssets();
    }

    /**
     * @return void
     */
    private function addDefaultPageAssets(): void
    {
        $instanceKey = $this->config->getInstanceKey();

        $this->addLinkRemotePageAsset('https://gateway.tweakwisenavigator.net/js/starter.js');
        $this->addLinkRemotePageAsset(
            sprintf('https://gateway.tweakwisenavigator.net/js/%s/tweakwise.js', $instanceKey)
        );
        $this->addJsRemotePageAsset(
            sprintf('https://gateway.tweakwisenavigator.net/js/%s/tweakwise.js', $instanceKey),
            sprintf('https://gateway.tweakwisenavigator.com/js/%s/tweakwise.js', $instanceKey)
        );
    }

    /**
     * @param string $url
     * @return void
     */
    private function addLinkRemotePageAsset(string $url): void
    {
        $this->pageConfig->addRemotePageAsset(
            $url,
            'link',
            ['attributes' => ['rel' => 'preload', 'as' => 'script']]
        );
    }

    /**
     * @param string $url
     * @param string $failoverUrl
     * @return void
     */
    private function addJsRemotePageAsset(string $url, string $failoverUrl): void
    {
        $this->pageConfig->addRemotePageAsset(
            $url,
            'js',
            ['attributes' => [
                'data-failover' => $failoverUrl,
                'onerror' => 'window.tweakwiseFailover(this.dataset.failover)'
            ]]
        );
    }
}
