<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type;

use Magento\Framework\Model\AbstractModel;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\FacetAttributeTypeInterface;

class FacetAttributeType extends AbstractModel implements FacetAttributeTypeInterface
{
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param string $title
     * @return FacetAttributeTypeInterface
     */
    public function setTitle(string $title): FacetAttributeTypeInterface
    {
        return $this->setData(self::TITLE, $title);
    }
}
