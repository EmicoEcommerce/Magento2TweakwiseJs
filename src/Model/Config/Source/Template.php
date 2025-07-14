<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Template implements OptionSourceInterface
{
    /**
     * @return array[]
     * TODO: FIX THIS
     */
    public function toOptionArray()
    {
        return [
            ['value' => null, 'label' => __('* Default template')],
        ];
    }
}
