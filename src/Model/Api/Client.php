<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config as AppConfig;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Api\Exception\ApiException;
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
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        private readonly Config $config,
        private readonly Json $jsonSerializer,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
        private readonly ResponseFactory $responseFactory,
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
     * @param array $params
     * @return array
     */
    public function getFacets(array $params = []): array
    {
        $url = sprintf(
            '%s/facets/%s',
            Data::GATEWAY_TWEAKWISE_NAVIGATOR_NET_URL,
            $this->config->getInstanceKey() ?? ''
        );

        try {
            return $this->doRequest(url: $url, params: $params);
        } catch (ApiException $e) {
            $this->logger->critical(
                'Tweakwise API error: Unable to retrieve Tweakwise facets',
                [
                    'url' => $url,
                    'exception' => $e->getMessage()
                ]
            );
            return [];
        }
    }

    /**
     * @param string $facetKey
     * @param array $params
     * @return array
     */
    public function getFacetAttributes(string $facetKey, array $params = []): array
    {
        $url = sprintf(
            '%s/facets/%s/attributes/%s',
            Data::GATEWAY_TWEAKWISE_NAVIGATOR_NET_URL,
            $facetKey,
            $this->config->getInstanceKey() ?? ''
        );

        try {
            return $this->doRequest(url: $url, params: $params);
        } catch (ApiException $e) {
            $this->logger->critical(
                'Tweakwise API error: Unable to retrieve Tweakwise facet attributes',
                [
                    'url' => $url,
                    'exception' => $e->getMessage()
                ]
            );
            return [];
        }
    }

    /**
     * @return array
     */
    private function getFeatures(): array
    {
        $cachedFeatures = $this->cache->load(self::FEATURES_CACHE_KEY);
        if ($cachedFeatures) {
            return $this->jsonSerializer->unserialize($cachedFeatures);
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
     * @param Request $request
     * @return Response|void
     */
    public function request(Request $request)
    {
        try {
            return $this->doRequestNew($request);
        } catch (ApiException $e) {
            $this->logger->critical(
                'Tweakwise API error: Unable to do Tweakwise request',
                [
                    'url' => $request->getPath(),
                    'exception' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @return array
     * @throws ApiException
     */
    private function doRequest(string $url, string $method = 'GET', array $params = []): array
    {
        $httpClient = new HttpClient(
            [
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]
        );

        try {
            $response = $httpClient->request($method, $url, [
                'query' => $params
            ]);
        } catch (GuzzleException $e) {
            throw new ApiException('An error occurred while retrieving data via the API', previous: $e);
        }

        $contents = $response->getBody()->getContents();
        return $this->jsonSerializer->unserialize($contents);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ApiException
     */
    private function doRequestNew(Request $request): Response
    {
        $url = sprintf(
            '%s/%s/%s',
            rtrim(Data::GATEWAY_TWEAKWISE_NAVIGATOR_NET_URL, '/'),
            trim($request->getPath(), '/'),
            $this->config->getInstanceKey()
        );
        $httpClient = new HttpClient(
            [
                'headers' => [
                    'Accept' => 'application/xml',
                ]
            ]
        );

        try {
            $response = $httpClient->request($request->isPostRequest() ? 'POST' : 'GET', $url, [
                'query' => $request->getParameters()
            ]);
        } catch (GuzzleException $e) {
            throw new ApiException('An error occurred while retrieving data via the API', previous: $e);
        }

        $xmlPreviousErrors = libxml_use_internal_errors(true);
        try {
            $xmlElement = simplexml_load_string(
                $response->getBody()->__toString(),
                SimpleXMLElement::class,
                LIBXML_NOCDATA
            );
            if ($xmlElement === false) {
                $errors = libxml_get_errors();
                throw new ApiException(
                    sprintf(
                        'Invalid response received by Tweakwise server, xml load fails. Request "%s", XML Errors: %s',
                        $url,
                        implode(PHP_EOL, $errors)
                    )
                );
            }
        } finally {
            libxml_use_internal_errors($xmlPreviousErrors);
        }

        $result = $this->xmlToArray($xmlElement);
        $result['headers'] = $response->getHeaders();
        return $this->responseFactory->create($request, $result);
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

    /**
     * @param SimpleXMLElement $element
     * @return array
     */
    protected function xmlToArray(SimpleXMLElement $element): array
    {
        $result = [];
        foreach ($element->attributes() as $attribute => $value) {
            $result['@' . $attribute] = (string)$value;
        }

        /** @var SimpleXMLElement $node */
        foreach ((array)$element as $index => $node) {
            if ($index === '@attributes') {
                continue;
            }

            $result[$index] = $this->xmlToArrayValue($node);
        }

        return $result;
    }

    /**
     * @param SimpleXMLElement|array|string $value
     * @return string|array
     */
    protected function xmlToArrayValue(SimpleXMLElement|array|string $value): string|array
    {
        if ($value instanceof SimpleXMLElement) {
            return $this->xmlToArray($value);
        }

        if (is_array($value)) {
            $values = [];
            foreach ($value as $element) {
                $values[] = $this->xmlToArrayValue($element);
            }

            return $values;
        }

        return (string)$value;
    }
}
