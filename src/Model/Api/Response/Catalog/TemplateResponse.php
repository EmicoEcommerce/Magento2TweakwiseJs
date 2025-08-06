<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response\Catalog;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog\TemplateResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;
use Tweakwise\TweakwiseJs\Model\Api\Type\TemplateType;
use Tweakwise\TweakwiseJs\Model\Api\Type\TemplateTypeFactory;

class TemplateResponse extends Response implements TemplateResponseInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param TemplateTypeFactory $templateTypeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly TemplateTypeFactory $templateTypeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array
     */
    public function getTemplates(): array
    {
        $templates = $this->getData(self::TEMPLATES);
        if ($templates) {
            return $templates;
        }

        $template = $this->getData(self::TEMPLATE);
        if ($template) {
            if (isset($template['templateid']) && !isset($template[0])) {
                $template = [$template];
            }

            $values = [];
            foreach ($template as $value) {
                if (!$value instanceof TemplateType) {
                    $value = $this->templateTypeFactory->create(['idField' => 'templateid', 'data' => $value]);
                }

                $values[] = $value;
            }

            $this->setTemplates($values);
        }

        return $this->getData(self::TEMPLATES);
    }

    /**
     * @param array $templates
     * @return TemplateResponse
     */
    public function setTemplates(array $templates): TemplateResponseInterface
    {
        return $this->setData(self::TEMPLATES, $templates);
    }
}
