<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type;

use Magento\Framework\Model\AbstractModel;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\RecommendationGroupTypeInterface;

class RecommendationGroupType extends AbstractModel implements RecommendationGroupTypeInterface
{
    /**
     * @return string
     */
    public function getGroupKey(): string
    {
        return (string) $this->getData(self::GROUP_KEY);
    }

    /**
     * @param string $groupKey
     * @return RecommendationGroupTypeInterface
     */
    public function setGroupKey(string $groupKey): RecommendationGroupTypeInterface
    {
        return $this->setData(self::GROUP_KEY, $groupKey);
    }
}
