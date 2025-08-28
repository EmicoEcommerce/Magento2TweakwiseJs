<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response;

use Tweakwise\TweakwiseJs\Api\Data\Api\Response\FeatureResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;

class FeatureResponse extends Response implements FeatureResponseInterface
{
    /**
     * @return array
     */
    public function getFeatures(): array
    {
        $features = [];
        foreach ($this->getData(self::FEATURES)[self::FEATURE] as $feature) {
            $features[$feature['name']] = $feature['value'];
        }

        return $features;
    }
}
