<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type;

use Magento\Framework\Model\AbstractModel;
use Tweakwise\TweakwiseJs\Api\Data\TemplateTypeInterface;

class TemplateType extends AbstractModel implements TemplateTypeInterface
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
     * @return TemplateTypeInterface
     */
    public function setName(string $name): TemplateTypeInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string
     */
    public function getTemplateId(): string
    {
        return $this->getData(self::TEMPLATE_ID);
    }

    /**
     * @param string $templateId
     * @return TemplateTypeInterface
     */
    public function setTemplateId(string $templateId): TemplateTypeInterface
    {
        return $this->setData(self::TEMPLATE_ID, $templateId);
    }
}
