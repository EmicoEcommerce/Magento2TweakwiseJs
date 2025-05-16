<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;

class Event implements ArgumentInterface
{
    /**
     * @param Data $dataHelper
     * @param Config $config
     * @param Session $checkoutSession
     * @param Json $jsonSerializer
     */
    public function __construct(
        private readonly Data $dataHelper,
        private readonly Config $config,
        private readonly Session $checkoutSession,
        private readonly Json $jsonSerializer
    ) {
    }

    /**
     * @param int $productId
     * @return string
     */
    public function getTweakwiseProductId(int $productId): string
    {
        try {
            return $this->dataHelper->getTweakwiseId($productId);
        } catch (NoSuchEntityException $e) {
            return '0';
        }
    }

    /**
     * @return string|null
     */
    public function getInstanceKey(): ?string
    {
        return $this->config->getInstanceKey();
    }

    /**
     * @return string
     */
    public function getEventsCookieName(): string
    {
        return $this->config->getEventsCookieName();
    }

    /**
     * @return string
     */
    public function getOrderProductIds(): string
    {
        $order = $this->checkoutSession->getLastRealOrder();

        $productIds = array_map(function ($orderItem) {
            try {
                return $this->dataHelper->getTweakwiseId((int)$orderItem->getProductId());
            } catch (NoSuchEntityException $e) {
                return '0';
            }
        }, $order->getAllVisibleItems());

        return $this->jsonSerializer->serialize($productIds);
    }
}
