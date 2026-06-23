<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
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
        $storeId = $this->getOrderStoreId($order);

        if (!$this->exportConfig->isGroupedExport()) {
            return $this->jsonSerializer->serialize(
                array_values($this->getPlainProductIds($order, $storeId))
            );
        }

        return $this->jsonSerializer->serialize(
            $this->getGroupedProductIds($order, $storeId)
        );
    }

    /**
     * @param Order $order
     * @param int $storeId
     * @return array
     */
    private function getPlainProductIds(Order $order, int $storeId): array
    {
        return array_map(function (Item $orderItem) use ($storeId) {
            try {
                return $this->dataHelper->getTweakwiseId((int)$orderItem->getProductId(), $storeId);
            } catch (NoSuchEntityException $e) {
                return '0';
            }
        }, $order->getAllVisibleItems());
    }

    /**
     * Maps order items to simpleId-parentId format when grouped export is enabled.
     *
     * @param Order $order
     * @param int $storeId
     * @return array
     */
    private function getGroupedProductIds(Order $order, int $storeId): array
    {
        $filteredItems = $this->resolveGroupedOrderItems($order);

        $productIds = [];
        foreach ($filteredItems as $item) {
            try {
                $simpleProductId = (int)$item->getData('groupCode');
                $parentProductId = (int)$item->getProductId();
                $groupCode = (int)$this->dataHelper->getTweakwiseId($parentProductId, $storeId);
                $productIds[] = $this->dataHelper->getTweakwiseId($simpleProductId, $storeId, $groupCode);
            } catch (NoSuchEntityException $e) {
                $productIds[] = '0';
            }
        }

        return $productIds;
    }

    /**
     * Deduplicates order items, pairing child items with their parent.
     * Sets 'groupCode' to the child product ID on the returned item.
     *
     * @param Order $order
     * @return Item[]
     */
    private function resolveGroupedOrderItems(Order $order): array
    {
        $filteredItems = [];
        foreach ($order->getAllItems() as $originalItem) {
            $parentItem = $originalItem->getParentItem();
            $returnedItem = $parentItem instanceof Item ? $parentItem : $originalItem;
            $returnedItem->setData('groupCode', $originalItem->getProductId());
            $filteredItems[(int)$returnedItem->getId()] = $returnedItem;
        }
        return $filteredItems;
    }

    /**
     * @param Order $order
     * @return int
     */
    private function getOrderStoreId(Order $order): int
    {
        return (int)$order->getStoreId();
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
