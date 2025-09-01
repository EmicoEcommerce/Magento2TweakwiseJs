<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config as AppConfig;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Tweakwise\TweakwiseJs\Model\Api\Exception\ApiException;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\Feature;

class Client
{
    private const FEATURES_CACHE_KEY = 'tweakwisejs_features';

    /**
     * @param Config $config
     * @param Json $jsonSerializer
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly Config $config,
        private readonly Json $jsonSerializer,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @return bool
     */
    public function isNavigationFeatureEnabled(): bool
    {
        return $this->getFeatures()[Feature::NAVIGATION->value] ?? false;
    }

    /**
     * @return bool
     */
    public function isSuggestionsFeatureEnabled(): bool
    {
        return $this->getFeatures()[Feature::SUGGESTIONS->value] ?? false;
    }

    /**
     * @return array
     */
    private function getFeatures(): array
    {
        $cachedFeatures = $this->cache->load(self::FEATURES_CACHE_KEY);
        if ($cachedFeatures) {
            return (array)$this->jsonSerializer->unserialize($cachedFeatures);
        }

        $instanceKey = $this->config->getInstanceKey();
        if (!$instanceKey) {
            return $this->getFallbackValues();
        }

        $url = sprintf(
            '%s/instance/%s',
            Data::GATEWAY_TWEAKWISE_NAVIGATOR_COM_URL,
            $instanceKey
        );

        try {
            $response = $this->doRequest($url);
        } catch (ApiException $e) {
            $this->logger->critical(
                'Tweakwise API error: Unable to retrieve Tweakwise features',
                [
                    'url' => $url,
                    'exception' => $e->getMessage()
                ]
            );
            return $this->getFallbackValues();
        }

        $features = [];
        foreach ($response['features'] ?? [] as $feature) {
            $features[$feature['name']] = $feature['value'];
        }

        if ($features) {
            $this->cache->save(
                $this->jsonSerializer->serialize($features),
                self::FEATURES_CACHE_KEY,
                [AppConfig::CACHE_TAG]
            );
        }

        return $features;
    }

    /**
     * @param string $url
     * @param string $method
     * @return array
     * @throws ApiException
     */
    private function doRequest(string $url, string $method = 'GET'): array
    {
        $httpClient = new HttpClient(
            [
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]
        );

        try {
            $response = $httpClient->request($method, $url);
        } catch (GuzzleException $e) {
            throw new ApiException('An error occurred while retrieving data via the API', previous: $e);
        }

        $contents = $response->getBody()->getContents();
        return (array)$this->jsonSerializer->unserialize($contents);
    }

    /**
     * @return array
     */
    private function getFallbackValues(): array
    {
        return [
            Feature::NAVIGATION->value => false,
            Feature::SUGGESTIONS->value => false
        ];
    }
}
