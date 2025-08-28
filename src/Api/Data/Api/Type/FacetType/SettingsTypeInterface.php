<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Data\Api\Type\FacetType;

interface SettingsTypeInterface
{
    public const URL_KEY = 'urlkey';
    public const TITLE = 'title';

    /**
     * @return string
     */
    public function getUrlKey(): string;

    /**
     * @return string
     */
    public function getTitle(): string;
}
