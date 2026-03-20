<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Service\Event;

use Tweakwise\TweakwiseJs\Api\Event\PriceFormatServiceInterface;

class PriceFormatService implements PriceFormatServiceInterface
{
    /**
     * @param float $price
     * @return float
     */
    public function format(float $price): float
    {
        return (float)number_format($price, 2, '.', '');
    }
}
