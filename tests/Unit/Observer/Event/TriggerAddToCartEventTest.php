<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Observer\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToCart as AddToCartEvent;
use Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToCartEvent;

class TriggerAddToCartEventTest extends Unit
{
    protected UnitTester $tester;

    private SessionServiceInterface|MockObject $sessionService;

    private AddToCartEvent|MockObject $addToCartEvent;

    private RequestInterface|MockObject $request;

    private TriggerAddToCartEvent $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionService = $this->createMock(SessionServiceInterface::class);
        $this->addToCartEvent = $this->createMock(AddToCartEvent::class);
        $this->request = $this->createMock(RequestInterface::class);

        $this->subject = new TriggerAddToCartEvent(
            $this->sessionService,
            $this->addToCartEvent,
            $this->request,
        );
    }

    /**
     * @return void
     */
    public function testEventIsQueuedWhenProductAddedToCart(): void
    {
        $eventData = ['event' => 'addtocart', 'data' => ['productKey' => '123', 'quantity' => 2, 'totalAmount' => 49.98]];

        $product = $this->createMock(Product::class);
        $quoteItem = $this->createMock(Item::class);
        $quoteItem->method('getQty')->willReturn(2);

        $observer = $this->createMock(Observer::class);
        $observer->method('getData')->willReturnMap([
            ['product', null, $product],
            ['quote_item', null, $quoteItem],
        ]);

        $this->addToCartEvent->expects($this->once())->method('setProduct')->with($product)->willReturnSelf();
        $this->addToCartEvent->expects($this->once())->method('setQuoteItem')->with($quoteItem)->willReturnSelf();
        $this->addToCartEvent->expects($this->once())->method('setQty')->with(2)->willReturnSelf();
        $this->addToCartEvent->expects($this->once())->method('get')->willReturn($eventData);

        $this->sessionService->expects($this->once())->method('add')->with('AddToCart', $eventData);

        $this->subject->execute($observer);
    }

    /**
     * @return void
     */
    public function testQtyDefaultsToOneWhenQuoteItemQtyIsZero(): void
    {
        $eventData = ['event' => 'addtocart', 'data' => ['productKey' => '123', 'quantity' => 1, 'totalAmount' => 24.99]];

        $product = $this->createMock(Product::class);
        $quoteItem = $this->createMock(Item::class);
        $quoteItem->method('getQty')->willReturn(0);

        $observer = $this->createMock(Observer::class);
        $observer->method('getData')->willReturnMap([
            ['product', null, $product],
            ['quote_item', null, $quoteItem],
        ]);

        $this->addToCartEvent->method('setProduct')->willReturnSelf();
        $this->addToCartEvent->method('setQuoteItem')->willReturnSelf();
        $this->addToCartEvent->expects($this->once())->method('setQty')->with(1)->willReturnSelf();
        $this->addToCartEvent->method('get')->willReturn($eventData);

        $this->sessionService->expects($this->once())->method('add');

        $this->subject->execute($observer);
    }

    /**
     * @return void
     */
    public function testSkipsWhenAlreadyHandled(): void
    {
        $this->request->method('getParam')->with('tweakwise_event_handled')->willReturn('1');

        $observer = $this->createMock(Observer::class);
        $observer->expects($this->never())->method('getData');

        $this->sessionService->expects($this->never())->method('add');

        $this->subject->execute($observer);
    }
}
