<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Config\Model\Config\Source\Yesno as MagentoYesno;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;
use Tweakwise\TweakwiseJs\Model\Enum\Feature;

class Yesno extends MagentoYesno
{
    /**
     * @param Client $apiClient
     * @param RequestFactory $requestFactory
     */
    public function __construct(
        private readonly Client $apiClient,
        private readonly RequestFactory $requestFactory,
    ) {
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->isNavigationFeatureEnabled()) {
            return parent::toOptionArray();
        }

        return [['value' => 0, 'label' => __('No')]];
    }

    /**
     * @return bool
     */
    protected function isNavigationFeatureEnabled(): bool
    {
        $featureRequest = $this->requestFactory->create();
        // @phpstan-ignore-next-line
        return (bool)$this->apiClient->getFeatures($featureRequest)[Feature::NAVIGATION->value] ?? false;
    }
}
