<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;

class Base implements ArgumentInterface
{
    /**
     * @param Config $config
     * @param Data $dataHelper
     */
    public function __construct(
        protected readonly Config $config,
        protected readonly Data $dataHelper
    ) {
    }

    /**
     * @return string|null
     */
    public function getInstanceKey(): ?string
    {
        return $this->config->getInstanceKey();
    }

    /**
     * @param int $id
     * @return string
     */
    public function getTweakwiseId(int $id): string
    {
        try {
            return $this->dataHelper->getTweakwiseId($id);
        } catch (NoSuchEntityException $e) {
            return '0';
        }
    }

    /**
     * @return bool
     */
    public function isEventsEnabled(): bool
    {
        return $this->config->isEventsEnabled();
    }
}
