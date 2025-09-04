<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Type;

use Tweakwise\TweakwiseJs\Model\Api\Type\FacetType\SettingsType;

interface FacetTypeInterface
{
    public const FACET_SETTINGS = 'facetsettings';

    /**
     * @return SettingsType
     */
    public function getFacetSettings(): SettingsType;

    /**
     * @param SettingsType $facetSettings
     * @return self
     */
    public function setFacetSettings(SettingsType $facetSettings): self;
}
