<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Response;

interface FacetResponseInterface
{
    public const FACETS = 'facets';
    public const FACET = 'facet';

    /**
     * @return array
     */
    public function getFacets(): array;

    /**
     * @param array $facets
     * @return self
     */
    public function setFacets(array $facets): self;
}
