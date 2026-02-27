<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Service\Event;

use Magento\Checkout\Model\Session as CheckoutSession;
use Tweakwise\TweakwiseJs\Api\Event\CheckoutSessionServiceInterface;

class CheckoutSessionService implements CheckoutSessionServiceInterface
{
    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
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
        $this->checkoutSession->setTweakwiseEventData($eventData);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $eventData = $this->checkoutSession->getTweakwiseEventData();
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
        $this->checkoutSession->setTweakwiseEventData([]);
    }
}
