<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Request;

use Tweakwise\TweakwiseJs\Model\Api\Request;
use Tweakwise\TweakwiseJs\Model\Api\Response\FacetAttributeResponse;

class FacetAttributeRequest extends Request
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
        return FacetAttributeResponse::class;
    }

    /**
     * @param string $facetKey
     * @return void
     */
    public function addFacetKey(string $facetKey): void
    {
        $this->path = sprintf('%s/%s/attributes', $this->path, $facetKey);
    }
}
