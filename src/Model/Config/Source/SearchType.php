<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;
use Tweakwise\TweakwiseJs\Model\Enum\Feature;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType as SearchTypeEnum;

class SearchType implements OptionSourceInterface
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
        return array_map(function (SearchTypeEnum $searchTypeEnum) {
            // If suggestions feature is not enabled, don't add this option
            if (
                $searchTypeEnum->value === SearchTypeEnum::SUGGESTIONS->value &&
                !$this->isSuggestionsFeatureEnabled()
            ) {
                return ['value' => null, 'label' => null];
            }

            return ['value' => $searchTypeEnum->value, 'label' => $searchTypeEnum->label()];
        }, SearchTypeEnum::cases());
    }

    /**
     * @return bool
     */
    protected function isSuggestionsFeatureEnabled(): bool
    {
        $featureRequest = $this->requestFactory->create();
        return (bool)$this->apiClient->getFeatures($featureRequest)[Feature::SUGGESTIONS->value] ?? false;
    }
}
