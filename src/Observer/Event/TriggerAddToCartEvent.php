<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToCart as AddToCartEvent;

class TriggerAddToCartEvent implements ObserverInterface
{
    /**
     * @param SessionServiceInterface $sessionService
     * @param AddToCartEvent $addToCartEvent
     */
    public function __construct(
        private readonly SessionServiceInterface $sessionService,
        private readonly AddToCartEvent $addToCartEvent
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
        $qty = (int)$observer->getData('request')->getParam('qty');
        if ($qty === 0) {
            $qty = 1;
        }

        $this->sessionService->add(
            'AddToCart',
            $this->addToCartEvent->setProduct($product)->setQty($qty)->get()
        );
    }
}
