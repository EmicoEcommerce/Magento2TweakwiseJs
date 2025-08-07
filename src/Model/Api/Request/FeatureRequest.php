<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Request;

use Tweakwise\TweakwiseJs\Model\Api\Request;
use Tweakwise\TweakwiseJs\Model\Api\Response\FeatureResponse;

class FeatureRequest extends Request
{
    /**
     * @var string
     */
    protected string $path = 'instance';

    /**
     * @return string
     */
    public function getResponseType(): string
    {
        return FeatureResponse::class;
    }
}
