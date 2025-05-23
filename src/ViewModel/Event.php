<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;

class Event extends Base
{
    /**
     * @param Config $config
     * @param Data $dataHelper
     * @param Session $checkoutSession
     * @param Json $jsonSerializer
     */
    public function __construct(
        Config $config,
        Data $dataHelper,
        private readonly Session $checkoutSession,
        private readonly Json $jsonSerializer
    ) {
        parent::__construct($config, $dataHelper);
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
