<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Type;

interface FacetAttributeTypeInterface
{
    public const TITLE = 'title';

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;
}
