<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Observer\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote\Item;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToCart as AddToCartEvent;
use Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToCartEvent;

class TriggerAddToCartEventTest extends Unit
{
    protected UnitTester $tester;

    private SessionServiceInterface|MockInterface $sessionService;

    private AddToCartEvent|MockInterface $addToCartEvent;

    private RequestInterface|MockInterface $request;

    private TriggerAddToCartEvent $subject;

    /**
     * @return void
     * @throws \Exception
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _before(): void
    {
        $this->sessionService = Mockery::mock(SessionServiceInterface::class);
        $this->addToCartEvent = Mockery::mock(AddToCartEvent::class);
        $this->request = Mockery::mock(RequestInterface::class);

        $this->tester->mockService(SessionServiceInterface::class, $this->sessionService);
        $this->tester->mockService(AddToCartEvent::class, $this->addToCartEvent);
        $this->tester->mockService(RequestInterface::class, $this->request);

        $this->subject = $this->tester->getObjectManager()->create(TriggerAddToCartEvent::class);
    }

    /**
     * @return void
     */
    public function testEventIsQueuedWhenProductAddedToCart(): void
    {
        $eventData = ['event' => 'addtocart', 'data' => ['productKey' => '123', 'quantity' => 2, 'totalAmount' => 49.98]];

        $this->request->shouldReceive('getParam')->with('tweakwise_event_handled')->andReturn(null);

        $product = Mockery::mock(Product::class);
        $quoteItem = Mockery::mock(Item::class);
        $quoteItem->shouldReceive('getQtyToAdd')->andReturn(2);
        $quoteItem->shouldReceive('getQty')->andReturn(3);

        $observer = Mockery::mock(Observer::class);
        $observer->shouldReceive('getData')->with('product')->andReturn($product);
        $observer->shouldReceive('getData')->with('quote_item')->andReturn($quoteItem);

        $this->addToCartEvent->shouldReceive('setProduct')->with($product)->once()->andReturnSelf();
        $this->addToCartEvent->shouldReceive('setQuoteItem')->with($quoteItem)->once()->andReturnSelf();
        $this->addToCartEvent->shouldReceive('setQty')->with(2)->once()->andReturnSelf();
        $this->addToCartEvent->shouldReceive('get')->once()->andReturn($eventData);

        $this->sessionService->shouldReceive('add')->with('AddToCart', $eventData)->once();

        $this->subject->execute($observer);
    }

    /**
     * @return void
     */
    public function testQtyDefaultsToOneWhenQuoteItemQtyIsZero(): void
    {
        $eventData = ['event' => 'addtocart', 'data' => ['productKey' => '123', 'quantity' => 1, 'totalAmount' => 24.99]];

        $this->request->shouldReceive('getParam')->with('tweakwise_event_handled')->andReturn(null);

        $product = Mockery::mock(Product::class);
        $quoteItem = Mockery::mock(Item::class);
        $quoteItem->shouldReceive('getQtyToAdd')->andReturn(0);
        $quoteItem->shouldReceive('getQty')->andReturn(0);

        $observer = Mockery::mock(Observer::class);
        $observer->shouldReceive('getData')->with('product')->andReturn($product);
        $observer->shouldReceive('getData')->with('quote_item')->andReturn($quoteItem);

        $this->addToCartEvent->shouldReceive('setProduct')->andReturnSelf();
        $this->addToCartEvent->shouldReceive('setQuoteItem')->andReturnSelf();
        $this->addToCartEvent->shouldReceive('setQty')->with(1)->once()->andReturnSelf();
        $this->addToCartEvent->shouldReceive('get')->andReturn($eventData);

        $this->sessionService->shouldReceive('add')->once();

        $this->subject->execute($observer);
    }

    /**
     * @return void
     */
    public function testSkipsWhenAlreadyHandled(): void
    {
        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getParam')->with('tweakwise_event_handled')->andReturn('1');

        $sessionService = Mockery::mock(SessionServiceInterface::class);
        $sessionService->shouldNotReceive('add');

        $subject = new TriggerAddToCartEvent($sessionService, $this->addToCartEvent, $request);

        $observer = Mockery::mock(Observer::class);
        $observer->shouldNotReceive('getData');

        $subject->execute($observer);
    }
}
