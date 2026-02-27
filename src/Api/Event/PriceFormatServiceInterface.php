<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Event;

interface PriceFormatServiceInterface
{
    /**
     * @param float $price
     * @return float
     */
    public function format(float $price): float;
}
