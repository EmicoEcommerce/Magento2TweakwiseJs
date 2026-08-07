<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\Exception\ApiException;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\FeaturedRecommendationResponse;

class FeaturedRecommendation implements OptionSourceInterface
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

        if (!$response instanceof FeaturedRecommendationResponse) {
            return [];
        }

        $result = [];
        foreach ($response->getRecommendations() as $recommendation) {
            $result[] = ['value' => $recommendation->getRecommendationId(), 'label' => $recommendation->getName()];
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

            $this->options = $options;
        }

        return $this->options;
    }
}
