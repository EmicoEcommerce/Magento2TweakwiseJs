<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api;

use InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;

class ResponseFactory
{
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * @param Request $request
     * @param array $data
     * @return Response
     * @throws InvalidArgumentException
     */
    public function create(Request $request, array $data): Response
    {
        $responseType = $request->getResponseType();
        $response = $this->objectManager->create($responseType, ['request' => $request, 'data' => $data]);
        if (!$response instanceof Response) {
            throw new InvalidArgumentException(sprintf('%s is not an instanceof %s', $responseType, Response::class));
        }

        return $response;
    }
}
