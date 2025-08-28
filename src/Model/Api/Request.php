<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class Request
{
    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager,
    ) {
    }
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
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @return bool
     */
    public function isPostRequest(): bool
    {
        return false;
    }

    /**
     * @return StoreInterface[]
     */
    public function getStores(): array
    {
        return $this->storeManager->getStores();
    }
}
