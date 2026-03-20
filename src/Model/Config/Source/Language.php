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
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
     * phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
     * @SuppressWarnings("PHPMD.EmptyCatchBlock")
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
            $response = $this->client->request($request);

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
