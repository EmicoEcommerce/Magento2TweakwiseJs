<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\Exception\ApiException;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\TemplateResponse;

class Template implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected array $options = [];

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
        /** @var TemplateResponse $response */
        $response = $this->client->request($request);
        $result = [
            ['value' => null, 'label' => __('* Default template')],
        ];

        if (!is_array($response->getTemplates())) {
            return $result;
        }

        foreach ($response->getTemplates() as $template) {
            $result[] = ['value' => $template->getTemplateId(), 'label' => $template->getName()];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
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
