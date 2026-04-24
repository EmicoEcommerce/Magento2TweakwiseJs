<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class UiLanguage implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Default language'),
                'value' => ''
            ],
            ['value' => 'en', 'label' => __('English')],
            ['value' => 'nl', 'label' => __('Dutch')],
            ['value' => 'de', 'label' => __('German')],
            ['value' => 'fr', 'label' => __('French')],
            ['value' => 'es', 'label' => __('Spanish')],
            ['value' => 'it', 'label' => __('Italian')],
        ];
    }
}
