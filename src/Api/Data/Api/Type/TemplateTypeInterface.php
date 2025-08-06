<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Type;

interface TemplateTypeInterface
{
    public const NAME = 'name';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self;

    /**
     * @return string
     */
    public function getTemplateId(): string;

    /**
     * @param string $templateId
     * @return self
     */
    public function setTemplateId(string $templateId): self;
}
