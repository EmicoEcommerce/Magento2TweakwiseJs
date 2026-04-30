<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote\Item;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToCart as AddToCartEvent;

class TriggerAddToCartEvent implements ObserverInterface
{
    /**
     * @param SessionServiceInterface $sessionService
     * @param AddToCartEvent $addToCartEvent
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly SessionServiceInterface $sessionService,
        private readonly AddToCartEvent $addToCartEvent,
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
        /** @var Item $quoteItem */
        $quoteItem = $observer->getData('quote_item');
        $qty = (int)$quoteItem->getQty();
        if ($qty === 0) {
            $qty = 1;
        }

        $this->sessionService->add(
            'AddToCart',
            $this->addToCartEvent->setProduct($product)->setQuoteItem($quoteItem)->setQty($qty)->get()
        );
    }
}
