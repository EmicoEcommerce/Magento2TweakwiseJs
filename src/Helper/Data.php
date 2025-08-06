<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
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
     */
    public function __construct(
        Context $context,
        private readonly StoreManagerInterface $storeManager,
        private readonly Helper $exportHelper
    ) {
        parent::__construct($context);
    }

    /**
     * @param int $entityId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTweakwiseId(int $entityId): string
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        return $this->exportHelper->getTweakwiseId($storeId, $entityId);
    }
}
