<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Service\Event;

use Magento\Framework\Session\SessionManagerInterface;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;

class SessionService implements SessionServiceInterface
{
    /**
     * @param SessionManagerInterface $session
     */
    public function __construct(
        private readonly SessionManagerInterface $session,
    ) {
    }

    /**
     * @param string $identifier
     * @param array $data
     * @return void
     */
    public function add(string $identifier, array $data): void
    {
        $eventData = $this->get();
        $eventData[$identifier] = $data;
        /** @phpstan-ignore-next-line */
        $this->session->setTweakwiseEventData($eventData);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        /** @phpstan-ignore-next-line */
        $eventData = $this->session->getTweakwiseEventData();
        if (is_array($eventData)) {
            return $eventData;
        }

        return [];
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        /** @phpstan-ignore-next-line */
        $this->session->setTweakwiseEventData([]);
    }
}
