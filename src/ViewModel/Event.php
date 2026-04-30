<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Item;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;

class Event extends Base
{
    /**
     * @param Config $config
     * @param Data $dataHelper
     * @param Session $checkoutSession
     * @param Json $jsonSerializer
     * @param ExportConfig $exportConfig
     */
    public function __construct(
        Config $config,
        Data $dataHelper,
        private readonly Session $checkoutSession,
        private readonly Json $jsonSerializer,
        private readonly ExportConfig $exportConfig,
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

        if (!$this->exportConfig->isGroupedExport()) {
            $productIds = array_map(function (Item $orderItem) {
                try {
                    return $this->dataHelper->getTweakwiseId((int)$orderItem->getProductId());
                } catch (NoSuchEntityException $e) {
                    return '0';
                }
            }, $order->getAllVisibleItems());

            return $this->jsonSerializer->serialize(array_values($productIds));
        }

        // When grouped export is enabled, map each order item to simpleId-parentId format.
        $filteredItems = [];
        foreach ($order->getAllItems() as $originalItem) {
            $parentItem = $originalItem->getParentItem();
            $returnedItem = $parentItem instanceof Item ? $parentItem : $originalItem;
            $returnedItem->setData('groupCode', $originalItem->getProductId());
            $filteredItems[(int)$returnedItem->getId()] = $returnedItem;
        }

        $productIds = [];
        foreach ($filteredItems as $item) {
            try {
                $simpleProductId = (int)$item->getData('groupCode');
                $parentProductId = (int)$item->getProductId();
                // groupCode must be the full Tweakwise ID of the parent, cast to int, so it is appended as-is.
                $groupCode = (int)$this->dataHelper->getTweakwiseId($parentProductId);
                $productIds[] = $this->dataHelper->getTweakwiseId($simpleProductId, null, $groupCode);
            } catch (NoSuchEntityException $e) {
                $productIds[] = '0';
            }
        }

        return $this->jsonSerializer->serialize($productIds);
    }

    /**
     * @return float
     */
    public function getPurchaseRevenue(): float
    {
        $order = $this->checkoutSession->getLastRealOrder();
        return (float)$order->getSubtotal() + (float)$order->getDiscountAmount();
    }
}
