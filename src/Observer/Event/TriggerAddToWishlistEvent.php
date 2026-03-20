<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToWishlist as AddToWishlistEvent;

class TriggerAddToWishlistEvent implements ObserverInterface
{
    /**
     * @param SessionServiceInterface $sessionService
     * @param AddToWishlistEvent $addToWishlistEvent
     */
    public function __construct(
        private readonly SessionServiceInterface $sessionService,
        private readonly AddToWishlistEvent $addToWishlistEvent
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getData('product');
        $this->sessionService->add(
            'AddToWishlist',
            $this->addToWishlistEvent->setProduct($product)->get()
        );
    }
}
