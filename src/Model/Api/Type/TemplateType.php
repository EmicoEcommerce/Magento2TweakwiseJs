<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\TemplateTypeInterface;

class TemplateType extends AbstractModel implements TemplateTypeInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param string $idField
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected string $idField = 'templateid',
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

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
        return $this->getData($this->idField);
    }

    /**
     * @param string $templateId
     * @return TemplateTypeInterface
     */
    public function setTemplateId(string $templateId): TemplateTypeInterface
    {
        return $this->setData($this->idField, $templateId);
    }
}
