<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\FacetAttributeResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;
use Tweakwise\TweakwiseJs\Model\Api\Type\FacetAttributeType;
use Tweakwise\TweakwiseJs\Model\Api\Type\FacetAttributeTypeFactory;

class FacetAttributeResponse extends Response implements FacetAttributeResponseInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param FacetAttributeTypeFactory $facetAttributeTypeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly FacetAttributeTypeFactory $facetAttributeTypeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        $facets = $this->getData(self::ATTRIBUTES);
        if (!isset($facets[self::ATTRIBUTE])) {
            return $facets;
        }

        $facets = $facets[self::ATTRIBUTE];
        $values = [];
        foreach ($facets as $value) {
            if (!$value instanceof FacetAttributeType) {
                $value = $this->facetAttributeTypeFactory->create(['data' => $value]);
            }

            $values[] = $value;
        }

        $this->setAttributes($values);

        return $this->getData(self::ATTRIBUTES);
    }

    /**
     * @param array $attributes
     * @return FacetAttributeResponseInterface
     */
    public function setAttributes(array $attributes): FacetAttributeResponseInterface
    {
        return $this->setData(self::ATTRIBUTES, $attributes);
    }
}
