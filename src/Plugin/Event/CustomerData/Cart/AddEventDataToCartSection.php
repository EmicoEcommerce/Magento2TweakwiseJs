<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Plugin\Event\CustomerData\Cart;

use Magento\Checkout\CustomerData\Cart as Subject;
use Tweakwise\TweakwiseJs\Api\Event\CheckoutSessionServiceInterface;

class AddEventDataToCartSection
{
    /**
     * @param CheckoutSessionServiceInterface $checkoutSessionService
     */
    public function __construct(
        private readonly CheckoutSessionServiceInterface $checkoutSessionService,
    ) {
    }

    /**
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Subject $subject, array $result): array
    {
        $events = $this->checkoutSessionService->get();
        $this->checkoutSessionService->clear();

        return array_merge($result, ['tweakwise_events' => $events]);
    }
}
