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
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\FacetAttributeResponseInterface;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\FacetAttributeTypeInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\Request\FacetAttributeRequest;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;

class FacetAttributes implements HttpPostActionInterface
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
        $facetKey = $this->request->getParam('facet_key');
        $otherAttributeOption = ['value' => Data::OTHER_ATTRIBUTE_VALUE, 'label' => 'Other (text field)'];

        if ($facetKey === Data::OTHER_ATTRIBUTE_VALUE) {
            return $result->setData([$otherAttributeOption]);
        }

        $facetAttributeRequest = $this->requestFactory->create();

        $filterTemplate = (int) $this->request->getParam('filter_template');
        if ($filterTemplate) {
            $facetAttributeRequest->addParameter('tn_ft', $filterTemplate);
        }

        if ($facetKey && $facetAttributeRequest instanceof FacetAttributeRequest) {
            $facetAttributeRequest->addFacetKey($facetKey);
        }

        $allStores = $facetAttributeRequest->getStores();
        $attributes = [];
        foreach ($allStores as $store) {
            $categoryId = $this->dataHelper->getTweakwiseId(
                (int) $this->request->getParam('category_id'),
                (int)$store->getId()
            );
            if ($categoryId) {
                $facetAttributeRequest->addParameter('tn_cid', $categoryId);
            }

            /** @var FacetAttributeResponseInterface $response */
            $response = $this->client->request($facetAttributeRequest);

            // @phpstan-ignore-next-line
            if (!$response) {
                return $result->setData([$otherAttributeOption]);
            }

            /** @var FacetAttributeTypeInterface $attribute */
            foreach ($response->getAttributes() as $attribute) {
                $attributes[] = [
                    'value' => $attribute->getTitle(),
                    'label' => $attribute->getTitle()
                ];
            }
        }

        $attributes[] = $otherAttributeOption;

        $attributes = array_values(array_unique($attributes, SORT_REGULAR));

        return $result->setData($attributes);
    }
}
