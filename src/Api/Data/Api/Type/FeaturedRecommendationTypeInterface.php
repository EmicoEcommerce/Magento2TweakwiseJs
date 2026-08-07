<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Type;

interface FeaturedRecommendationTypeInterface
{
    public const ID = 'id';
    public const NAME = 'name';

    /**
     * @return string
     */
    public function getRecommendationId(): string;

    /**
     * @param string $recommendationId
     * @return self
     */
    public function setRecommendationId(string $recommendationId): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self;
}
