<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Response;

interface FeatureResponseInterface
{
    public const FEATURES = 'features';
    public const FEATURE = 'feature';

    /**
     * @return array
     */
    public function getFeatures(): array;
}
