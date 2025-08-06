<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Controller\Adminhtml\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Tweakwise\TweakwiseJs\Api\Data\FacetResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;

class Facets implements HttpPostActionInterface
{
    /**
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param Client $client
     * @param RequestFactory $requestFactory
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $resultJsonFactory,
        private readonly Client $client,
        private readonly RequestFactory $requestFactory,
    ) {
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $facetRequest = $this->requestFactory->create();

        $categoryId = $this->request->getParam('category_id');
        $categoryId = 100015; // TODO: GET CORRECT TW CATEGORY ID
        if ($categoryId) {
            $facetRequest->addParameter('tn_cid', $categoryId);
        }

        $filterTemplate = (int) $this->request->getParam('filter_template');
        if ($filterTemplate) {
            $facetRequest->addParameter('tn_ft', $filterTemplate);
        }

        // TODO: LOOP THROUGH STORES
        /** @var FacetResponseInterface $response */
        $response = $this->client->request($facetRequest);

        $facets = [];
        foreach ($response->getFacets() as $facet) {
            $facets[] = [
                'value' => $facet->getFacetSettings()->getUrlKey(),
                'label' => $facet->getFacetSettings()->getTitle()
            ];
        }

        $facets[] = ['value' => 'tw_other', 'label' => 'Other (text field)'];

        return $result->setData($facets);
    }
}
