<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response\Catalog;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog\BuilderTemplateResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;
use Tweakwise\TweakwiseJs\Model\Api\Type\TemplateType;
use Tweakwise\TweakwiseJs\Model\Api\Type\TemplateTypeFactory;

class BuilderTemplateResponse extends Response implements BuilderTemplateResponseInterface
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
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
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

        $template = $this->getData(self::BUILDER);
        if ($template) {
            if (isset($template['id']) && !isset($template[0])) {
                $template = [$template];
            }

            $values = [];
            foreach ($template as $value) {
                if (!$value instanceof TemplateType) {
                    $value = $this->templateTypeFactory->create(['idField' => 'id', 'data' => $value]);
                }

                $values[] = $value;
            }

            $this->setTemplates($values);
        }

        return $this->getData(self::TEMPLATES);
    }

    /**
     * @param array $templates
     * @return BuilderTemplateResponseInterface
     */
    public function setTemplates(array $templates): BuilderTemplateResponseInterface
    {
        return $this->setData(self::TEMPLATES, $templates);
    }
}
