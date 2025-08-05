<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data;

interface TemplateResponseInterface
{
    public const TEMPLATES = 'templates';
    public const TEMPLATE = 'template';

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
