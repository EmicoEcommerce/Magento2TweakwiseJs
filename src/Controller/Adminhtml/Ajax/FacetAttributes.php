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
        $otherAttribute = 'tw_other';
        $otherAttributeOption = ['value' => $otherAttribute, 'label' => 'Other (text field)'];

        if ($facetKey === $otherAttribute) {
            return $result->setData([$otherAttributeOption]);
        }

        $facetAttributeRequest = $this->requestFactory->create();

        // TODO: GET IN STORES LOOP BELOW
        $categoryId = $this->dataHelper->getTweakwiseId((int) $this->request->getParam('category_id'));
        if ($categoryId) {
            $facetAttributeRequest->addParameter('tn_cid', $categoryId);
        }

        $filterTemplate = (int) $this->request->getParam('filter_template');
        if ($filterTemplate) {
            $facetAttributeRequest->addParameter('tn_ft', $filterTemplate);
        }

        if ($facetKey && $facetAttributeRequest instanceof FacetAttributeRequest) {
            $facetAttributeRequest->addFacetKey($facetKey);
        }

        // TODO: LOOP THROUGH STORES
        /** @var FacetAttributeResponseInterface $response */
        $response = $this->client->request($facetAttributeRequest);

        $attributes = [];
        /** @var FacetAttributeTypeInterface $attribute */
        foreach ($response->getAttributes() as $attribute) {
            $attributes[] = [
                'value' => $attribute->getTitle(),
                'label' => $attribute->getTitle()
            ];
        }

        $attributes[] = $otherAttributeOption;

        return $result->setData($attributes);
    }
}
