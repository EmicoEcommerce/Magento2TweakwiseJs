<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api;

class Request
{
    /**
     * @var string
     */
    protected string $path = '';

    /**
     * @var array
     */
    protected array $parameters = [];


    /**
     * @return string
     */
    public function getResponseType(): string
    {
        return Response::class;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return bool
     */
    public function isPostRequest(): bool
    {
        return false;
    }
}
