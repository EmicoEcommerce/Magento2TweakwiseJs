<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;

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
     */
    public function execute(Observer $observer): void
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

        $this->addLinkRemotePageAsset(sprintf('%s/js/starter.js', Data::GATEWAY_TWEAKWISE_NAVIGATOR_NET_URL));
        $this->addLinkRemotePageAsset(
            sprintf('%s/js/%s/tweakwise.js', Data::GATEWAY_TWEAKWISE_NAVIGATOR_NET_URL, $instanceKey)
        );
        $this->addJsRemotePageAsset(
            sprintf('%s/js/%s/tweakwise.js', Data::GATEWAY_TWEAKWISE_NAVIGATOR_NET_URL, $instanceKey),
            sprintf('%s/js/%s/tweakwise.js', Data::GATEWAY_TWEAKWISE_NAVIGATOR_COM_URL, $instanceKey)
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
