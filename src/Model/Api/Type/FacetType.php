<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\FacetTypeInterface;
use Tweakwise\TweakwiseJs\Model\Api\Type\FacetType\SettingsType;
use Tweakwise\TweakwiseJs\Model\Api\Type\FacetType\SettingsTypeFactory;

class FacetType extends AbstractModel implements FacetTypeInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param SettingsTypeFactory $settingsTypeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly SettingsTypeFactory $settingsTypeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return SettingsType
     */
    public function getFacetSettings(): SettingsType
    {
        $facetSettings = $this->getData(self::FACET_SETTINGS);
        if ($facetSettings instanceof SettingsType) {
            return $facetSettings;
        }

        $facetSettings = $this->settingsTypeFactory->create(['data' => $facetSettings]);

        $this->setFacetSettings($facetSettings);
        return $facetSettings;
    }

    /**
     * @param SettingsType $facetSettings
     * @return FacetTypeInterface
     */
    public function setFacetSettings(SettingsType $facetSettings): FacetTypeInterface
    {
        return $this->setData(self::FACET_SETTINGS, $facetSettings);
    }
}
