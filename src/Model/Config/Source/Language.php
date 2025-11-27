<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\Exception\ApiException;
use Tweakwise\TweakwiseJs\Model\Client\Response\Catalog\LanguageResponse;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;

class Language implements OptionSourceInterface
{
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Language constructor.
     * @param RequestFactory $requestFactory
     * @param Client $client
     */
    public function __construct(
        RequestFactory $requestFactory,
        Client $client
    ) {
        $this->requestFactory = $requestFactory;
        $this->client = $client;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => 'Default language',
                'value' => ''
            ]
        ];

        try {
            $request = $this->requestFactory->create();
            /** @var LanguageResponse $response */
            $response = $this->client->request($request);

            $languages = $response->getLanguages();

            foreach ($languages as $language) {
                $options[] = [
                    'label' => $language['name'],
                    'value' => $language['key']
                ];
            }
        } catch (ApiException $e) {
            $options;
        }

        return $options;
    }
}
