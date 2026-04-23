<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type;

use Magento\Framework\Model\AbstractModel;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\LanguageTypeInterface;

class LanguageType extends AbstractModel implements LanguageTypeInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string $name
     * @return LanguageTypeInterface
     */
    public function setName(string $name): LanguageTypeInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string
     */
    public function getLanguageId(): string
    {
        return $this->getData(self::KEY);
    }

    /**
     * @param string $languageId
     * @return LanguageTypeInterface
     */
    public function setLanguageId(string $languageId): LanguageTypeInterface
    {
        return $this->setData(self::KEY, $languageId);
    }
}
