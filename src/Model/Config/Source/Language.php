<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Tweakwise\TweakwiseJs\Exception\ApiException;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\LanguageResponse;
use Magento\Framework\Data\OptionSourceInterface;

class Language implements OptionSourceInterface
{
    /**
     * @param RequestFactory $requestFactory
     * @param Client $apiClient
     */
    public function __construct(
        private readonly Client $apiClient,
        private readonly RequestFactory $requestFactory,
    ) {
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Default language'),
                'value' => ''
            ]
        ];

        try {
            $request = $this->requestFactory->create();
            /** @var LanguageResponse $response */
            $response = $this->apiClient->request($request);

            $languages = $response->getLanguages();

            foreach ($languages as $language) {
                $options[] = [
                    'label' => $language['name'],
                    'value' => $language['key']
                ];
            }
        } catch (ApiException $e) {
            //do nothing
        }

        return $options;
    }
}
