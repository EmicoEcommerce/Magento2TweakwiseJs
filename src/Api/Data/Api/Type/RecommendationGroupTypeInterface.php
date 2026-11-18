<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Type;

interface RecommendationGroupTypeInterface
{
    public const GROUP_KEY = 'groupkey';

    /**
     * @return string
     */
    public function getGroupKey(): string;

    /**
     * @param string $groupKey
     * @return self
     */
    public function setGroupKey(string $groupKey): self;
}
