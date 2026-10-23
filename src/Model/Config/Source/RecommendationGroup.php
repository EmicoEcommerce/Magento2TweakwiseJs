<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\Exception\ApiException;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\RecommendationGroupResponse;

class RecommendationGroup implements OptionSourceInterface
{
    /**
     * @var array|null
     */
    protected ?array $options = null;

    /**
     * @param Client $client
     * @param RequestFactory $requestFactory
     */
    public function __construct(
        protected readonly Client $client,
        protected readonly RequestFactory $requestFactory
    ) {
    }

    /**
     * @return array
     */
    protected function buildOptions(): array
    {
        $request = $this->requestFactory->create();
        $response = $this->client->request($request);

        if (!$response instanceof RecommendationGroupResponse) {
            return [];
        }

        $result = [];
        foreach ($response->getGroups() as $group) {
            $result[] = ['value' => $group->getGroupKey(), 'label' => $group->getGroupKey()];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            try {
                $options = $this->buildOptions();
            } catch (ApiException $e) {
                $options = [];
            }

            $this->options = array_merge([['value' => '', 'label' => __('-- Select --')]], $options);
        }

        return $this->options;
    }
}
