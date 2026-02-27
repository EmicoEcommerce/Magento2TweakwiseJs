<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Api\Event;

interface CheckoutSessionServiceInterface
{
    /**
     * @param string $identifier
     * @param array $data
     * @return void
     */
    public function add(string $identifier, array $data): void;

    /**
     * @return array
     */
    public function get(): array;

    /**
     * @return void
     */
    public function clear(): void;
}
