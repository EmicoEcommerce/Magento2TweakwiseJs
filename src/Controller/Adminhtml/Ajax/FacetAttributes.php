<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Controller\Adminhtml\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Tweakwise\TweakwiseJs\Model\Api\Client;

class FacetAttributes implements HttpPostActionInterface
{
    /**
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param Client $client
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $resultJsonFactory,
        private readonly Client $client
    ) {
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $params = [];

        $categoryId = $this->request->getParam('category_id');
        $categoryId = 1; // TODO: REMOVE
        if ($categoryId) {
            $params['tn_cid'] = $categoryId;
        }

        $filterTemplate = (int) $this->request->getParam('filter_template');
        if ($filterTemplate) {
            $params['tn_ft'] = $filterTemplate;
        }

        // TODO: Why loop through all stores in old module?
        $facetKey = $this->request->getParam('facet_key');
        $response = $this->client->getFacetAttributes($facetKey, $params);

        // TODO: Build facet attribute request
        $attributes = [];
        if (isset($attributes['attributes']) && is_array($attributes['attributes'])) {
            foreach ($response['attributes'] as $attribute) {
                $attributes[] = [
                    'value' => $attribute['title'],
                    'label' => $attribute['title']
                ];
            }
        }

        $attributes[] = ['value' => 'tw_other', 'label' => 'Other (text field)'];

        if ($this->request->getParam('category_id') === '3') {
            $attributes = [
                [
                    'value' => 'categorie',
                    'label' => 'Categorie',
                ],
                [
                    'value' => 'material',
                    'label' => 'material',
                ],
                [
                    'value' => 'color',
                    'label' => 'color',
                ],
                [
                    'value' => 'tw_other',
                    'label' => 'Other (text field)',
                ],
            ];
        } else {
            $attributes = [
                [
                    'value' => 'onzin1',
                    'label' => 'onzin1',
                ],
                [
                    'value' => 'tw_other',
                    'label' => 'Other (text field)',
                ],
                [
                    'value' => 'onzin3',
                    'label' => 'onzin3',
                ],
                [
                    'value' => 'material',
                    'label' => 'material',
                ],
            ];
        }

        return $result->setData($attributes);
    }
}
