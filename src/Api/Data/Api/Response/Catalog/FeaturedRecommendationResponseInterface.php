<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog;

interface FeaturedRecommendationResponseInterface
{
    public const RECOMMENDATIONS = 'recommendations';
    public const RECOMMENDATION = 'recommendation';

    /**
     * @return array
     */
    public function getRecommendations(): array;

    /**
     * @param array $recommendations
     * @return self
     */
    public function setRecommendations(array $recommendations): self;
}
