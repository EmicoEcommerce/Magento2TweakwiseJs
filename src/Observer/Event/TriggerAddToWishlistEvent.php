<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToWishlist as AddToWishlistEvent;

class TriggerAddToWishlistEvent implements ObserverInterface
{
    /**
     * @param SessionServiceInterface $sessionService
     * @param AddToWishlistEvent $addToWishlistEvent
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly SessionServiceInterface $sessionService,
        private readonly AddToWishlistEvent $addToWishlistEvent,
        private readonly RequestInterface $request,
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if ($this->request->getParam('tweakwise_event_handled')) {
            return;
        }

        /** @var Product $product */
        $product = $observer->getData('product');
        $this->sessionService->add(
            'AddToWishlist',
            $this->addToWishlistEvent->setProduct($product)->get()
        );
    }
}
