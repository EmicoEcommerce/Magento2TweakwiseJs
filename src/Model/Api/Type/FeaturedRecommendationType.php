<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type;

use Magento\Framework\Model\AbstractModel;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\FeaturedRecommendationTypeInterface;

class FeaturedRecommendationType extends AbstractModel implements FeaturedRecommendationTypeInterface
{
    /**
     * @return string
     */
    public function getRecommendationId(): string
    {
        return $this->getData(self::ID);
    }

    /**
     * @param string $recommendationId
     * @return FeaturedRecommendationTypeInterface
     */
    public function setRecommendationId(string $recommendationId): FeaturedRecommendationTypeInterface
    {
        return $this->setData(self::ID, $recommendationId);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string $name
     * @return FeaturedRecommendationTypeInterface
     */
    public function setName(string $name): FeaturedRecommendationTypeInterface
    {
        return $this->setData(self::NAME, $name);
    }
}
