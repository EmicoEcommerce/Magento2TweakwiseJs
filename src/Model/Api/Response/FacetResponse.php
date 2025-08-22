<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\FacetResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;
use Tweakwise\TweakwiseJs\Model\Api\Type\FacetType;
use Tweakwise\TweakwiseJs\Model\Api\Type\FacetTypeFactory;

class FacetResponse extends Response implements FacetResponseInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param FacetTypeFactory $facetTypeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly FacetTypeFactory $facetTypeFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array
     */
    public function getFacets(): array
    {
        $facets = $this->getData(self::FACETS);
        if (!isset($facets[self::FACET])) {
            return $facets;
        }

        $facets = $facets[self::FACET];
        if (!isset($facets[0])) {
            $facets = [$facets];
        }

        $values = [];
        foreach ($facets as $value) {
            if (!$value instanceof FacetType) {
                $value = $this->facetTypeFactory->create(['data' => $value]);
            }

            $values[] = $value;
        }

        $this->setFacets($values);

        return $this->getData(self::FACETS);
    }

    /**
     * @param array $facets
     * @return FacetResponseInterface
     */
    public function setFacets(array $facets): FacetResponseInterface
    {
        return $this->setData(self::FACETS, $facets);
    }
}
