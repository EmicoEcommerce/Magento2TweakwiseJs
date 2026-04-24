<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Request\Catalog;

use Tweakwise\TweakwiseJs\Model\Api\Request;
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\LanguageResponse;

class LanguageRequest extends Request
{
    /**
     * @var string
     */
    protected string $path = 'catalog/languages';

    /**
     * @return string
     */
    public function getResponseType(): string
    {
        return LanguageResponse::class;
    }
}
