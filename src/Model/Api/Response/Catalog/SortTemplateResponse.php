<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response\Catalog;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog\SortTemplateResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;
use Tweakwise\TweakwiseJs\Model\Api\Type\TemplateType;
use Tweakwise\TweakwiseJs\Model\Api\Type\TemplateTypeFactory;

class SortTemplateResponse extends Response implements SortTemplateResponseInterface
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

        $sortTemplate = $this->getData(self::SORT_TEMPLATE);
        if ($sortTemplate) {
            if (isset($sortTemplate['sorttemplateid']) && !isset($sortTemplate[0])) {
                $sortTemplate = [$sortTemplate];
            }

            $values = [];
            foreach ($sortTemplate as $value) {
                if (!$value instanceof TemplateType) {
                    $value = $this->templateTypeFactory->create(['idField' => 'sorttemplateid', 'data' => $value]);
                }

                $values[] = $value;
            }

            $this->setTemplates($values);
        }

        return $this->getData(self::TEMPLATES);
    }

    /**
     * @param array $templates
     * @return SortTemplateResponseInterface
     */
    public function setTemplates(array $templates): SortTemplateResponseInterface
    {
        return $this->setData(self::TEMPLATES, $templates);
    }
}
