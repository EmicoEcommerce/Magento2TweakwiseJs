<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Request;

use Tweakwise\TweakwiseJs\Model\Api\Request;
use Tweakwise\TweakwiseJs\Model\Api\Response\FacetResponse;

class FacetRequest extends Request
{
    /**
     * @var string
     */
    protected string $path = 'facets';

    /**
     * @return string
     */
    public function getResponseType(): string
    {
        return FacetResponse::class;
    }
}
