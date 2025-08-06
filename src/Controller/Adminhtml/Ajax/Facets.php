<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Controller\Adminhtml\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\FacetResponseInterface;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\FacetTypeInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;

class Facets implements HttpPostActionInterface
{
    /**
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param Client $client
     * @param RequestFactory $requestFactory
     * @param Data $dataHelper
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $resultJsonFactory,
        private readonly Client $client,
        private readonly RequestFactory $requestFactory,
        private readonly Data $dataHelper,
    ) {
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $facetRequest = $this->requestFactory->create();

        $filterTemplate = (int) $this->request->getParam('filter_template');
        if ($filterTemplate) {
            $facetRequest->addParameter('tn_ft', $filterTemplate);
        }

        $allStores = $facetRequest->getStores();
        $facets = [];
        foreach ($allStores as $store) {
            $categoryId = $this->dataHelper->getTweakwiseId(
                (int) $this->request->getParam('category_id'),
                (int)$store->getId()
            );
            if ($categoryId) {
                $facetRequest->addParameter('tn_cid', $categoryId);
            }

            /** @var FacetResponseInterface $response */
            $response = $this->client->request($facetRequest);

            /** @var FacetTypeInterface $facet */
            foreach ($response->getFacets() as $facet) {
                $facets[] = [
                    'value' => $facet->getFacetSettings()->getUrlKey(),
                    'label' => $facet->getFacetSettings()->getTitle()
                ];
            }
        }

        $facets[] = ['value' => Data::OTHER_ATTRIBUTE_VALUE, 'label' => 'Other (text field)'];

        $facets = array_values(array_unique($facets, SORT_REGULAR));

        return $result->setData($facets);
    }
}
