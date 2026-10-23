<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ProductSource implements OptionSourceInterface
{
    public const AUTOMATIC = 'automatic';
    public const CUSTOM = 'custom';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::AUTOMATIC, 'label' => __('Automatic')],
            ['value' => self::CUSTOM, 'label' => __('Custom product (SKU)')],
        ];
    }
}
