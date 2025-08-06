<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog;

interface SortTemplateResponseInterface
{
    public const TEMPLATES = 'templates';
    public const SORT_TEMPLATE = 'sorttemplate';

    /**
     * @return array
     */
    public function getTemplates(): array;

    /**
     * @param array $templates
     * @return self
     */
    public function setTemplates(array $templates): self;
}
