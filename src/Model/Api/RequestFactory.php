<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api;

use InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;

class RequestFactory
{
    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $type
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager,
        protected readonly string $type = Request::class
    ) {
    }

    /**
     * @param array $parameters
     * @return Request
     * @throws InvalidArgumentException
     */
    public function create(array $parameters = []): Request
    {
        $request =  $this->objectManager->create($this->type, ['parameters' => $parameters]);
        if (!$request instanceof Request) {
            throw new InvalidArgumentException(sprintf('%s is not an instanceof %s', $this->type, Request::class));
        }

        return $request;
    }
}
