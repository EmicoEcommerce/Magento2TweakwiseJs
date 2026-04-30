<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Magento2TweakwiseExport\Model\Helper;

class Data extends AbstractHelper
{
    public const GATEWAY_TWEAKWISE_NAVIGATOR_COM_URL = 'https://gateway.tweakwisenavigator.com';
    public const GATEWAY_TWEAKWISE_NAVIGATOR_NET_URL = 'https://gateway.tweakwisenavigator.net';
    public const OTHER_ATTRIBUTE_VALUE = 'tw_other';

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Helper $exportHelper
     * @param ExportConfig $exportConfig
     * @param ConfigurableResource $configurableResource
     */
    public function __construct(
        Context $context,
        private readonly StoreManagerInterface $storeManager,
        private readonly Helper $exportHelper,
        private readonly ExportConfig $exportConfig,
        private readonly ConfigurableResource $configurableResource,
    ) {
        parent::__construct($context);
    }

    /**
     * @param int $entityId
     * @param int|null $storeId
     * @param int|null $groupCode
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTweakwiseId(int $entityId, ?int $storeId = null, ?int $groupCode = null): string
    {
        $storeId = $storeId ?? (int) $this->storeManager->getStore()->getId();
        return $this->exportHelper->getTweakwiseId($storeId, $entityId, $groupCode);
    }

    /**
     * Resolves the correct Tweakwise product key for a given product ID, taking grouped export into account.
     *
     * When grouped export is enabled:
     * - If the product is a configurable, returns simpleId-configurableId using the first child.
     * - If the product is a simple with a configurable parent, returns simpleId-configurableId.
     * - Otherwise returns the plain Tweakwise ID.
     *
     * @param int $productId
     * @param string $productTypeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function resolveGroupedExportProductKey(int $productId, string $productTypeId): string
    {
        if (!$this->exportConfig->isGroupedExport()) {
            return $this->getTweakwiseId($productId);
        }

        if ($productTypeId === Configurable::TYPE_CODE) {
            $childIds = $this->configurableResource->getChildrenIds($productId);
            $simpleProductId = (int)array_key_first(array_key_first($childIds) !== null ? reset($childIds) : []);
            if (!$simpleProductId) {
                return $this->getTweakwiseId($productId);
            }
            $groupCode = (int)$this->getTweakwiseId($productId);
            return $this->getTweakwiseId($simpleProductId, null, $groupCode);
        }

        // Simple product — look up its configurable parent.
        $parentIds = $this->configurableResource->getParentIdsByChild($productId);
        if (empty($parentIds)) {
            return $this->getTweakwiseId($productId);
        }

        $parentProductId = (int)reset($parentIds);
        $groupCode = (int)$this->getTweakwiseId($parentProductId);
        return $this->getTweakwiseId($productId, null, $groupCode);
    }
}
