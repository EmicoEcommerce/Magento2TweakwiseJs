<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog;

interface RecommendationGroupResponseInterface
{
    /**
     * @return array
     */
    public function getGroups(): array;

    /**
     * @param array $groups
     * @return self
     */
    public function setGroups(array $groups): self;
}
