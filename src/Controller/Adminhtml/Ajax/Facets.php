<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Controller\Adminhtml\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Tweakwise\TweakwiseJs\Model\Api\Client;

class Facets implements HttpPostActionInterface
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
        if ($categoryId) {
            $params['tn_cid'] = $categoryId;
        }

        // TODO: MAAK FILTER TEMPLATES IN XML
        $filterTemplate = (int) $this->request->getParam('filter_template');
        if ($filterTemplate) {
            $params['tn_ft'] = $filterTemplate;
        }

        // TODO: Why loop through all stores in old module?
        $response = $this->client->getFacets($params);

        // TODO: Build facet request
        $facets = [];
        foreach ($response['facets'] as $facet) {
            $facets[] = [
                'value' => $facet['facetsettings']['urlkey'],
                'label' => $facet['facetsettings']['title']
            ];
        }

        $facets[] = ['value' => 'tw_other', 'label' => 'Other (text field)'];

        return $result->setData($facets);
    }
}
