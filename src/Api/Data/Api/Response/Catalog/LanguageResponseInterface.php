<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog;

interface LanguageResponseInterface
{
    public const LANGUAGES = 'languages';
    public const LANGUAGE = 'language';

    /**
     * @return array
     */
    public function getLanguages(): array;

    /**
     * @param array $languages
     * @return self
     */
    public function setLanguages(array $languages): self;
}
