<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Request\Catalog;

use Tweakwise\TweakwiseJs\Model\Api\Request;
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\TemplateResponse;

class TemplateRequest extends Request
{
    /**
     * @var string
     */
    protected string $path = 'catalog/templates';

    /**
     * @return string
     */
    public function getResponseType(): string
    {
        return TemplateResponse::class;
    }
}
