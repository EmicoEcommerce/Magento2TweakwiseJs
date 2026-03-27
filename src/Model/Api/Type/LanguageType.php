<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Type;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\LanguageTypeInterface;

class LanguageType extends AbstractModel implements LanguageTypeInterface
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
        protected string $idField = 'key',
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
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
        return $this->getData($this->idField);
    }

    /**
     * @param string $languageId
     * @return LanguageTypeInterface
     */
    public function setLanguageId(string $languageId): LanguageTypeInterface
    {
        return $this->setData($this->idField, $languageId);
    }
}
