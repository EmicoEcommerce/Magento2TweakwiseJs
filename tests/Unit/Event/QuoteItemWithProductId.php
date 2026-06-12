<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Event;

use Magento\Quote\Model\Quote\Item;

class QuoteItemWithProductId extends Item
{
    /**
     * @return int|null
     */
    public function getProductId(): ?int
    {
        return null;
    }
}
