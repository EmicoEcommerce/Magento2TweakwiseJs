<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type\FacetType;

use Magento\Framework\Model\AbstractModel;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\FacetType\SettingsTypeInterface;

class SettingsType extends AbstractModel implements SettingsTypeInterface
{
    /**
     * @return string
     */
    public function getUrlKey(): string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getData(self::TITLE);
    }
}
