<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Event;

use Tweakwise\TweakwiseJs\Event\AddToCart;

class AddToCartExposed extends AddToCart
{
    /**
     * @return string
     * @phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
     */
    public function resolveProductKey(): string
    {
        return parent::resolveProductKey();
    }
}
