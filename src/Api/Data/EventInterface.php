<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data;

use Magento\Framework\View\Element\Block\ArgumentInterface;

interface EventInterface extends ArgumentInterface
{
    /**
     * @return array
     */
    public function get(): array;
}
