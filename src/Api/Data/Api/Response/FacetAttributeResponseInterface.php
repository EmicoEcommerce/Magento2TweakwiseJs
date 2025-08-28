<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Response;

interface FacetAttributeResponseInterface
{
    public const ATTRIBUTES = 'attributes';
    public const ATTRIBUTE = 'attribute';

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param array $attributes
     * @return self
     */
    public function setAttributes(array $attributes): self;
}
