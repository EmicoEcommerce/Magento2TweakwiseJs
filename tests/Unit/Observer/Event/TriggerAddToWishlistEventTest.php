<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Observer\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToWishlist as AddToWishlistEvent;
use Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToWishlistEvent;

class TriggerAddToWishlistEventTest extends Unit
{
    protected UnitTester $tester;

    private SessionServiceInterface|MockObject $sessionService;

    private AddToWishlistEvent|MockObject $addToWishlistEvent;

    private RequestInterface|MockObject $request;

    private TriggerAddToWishlistEvent $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionService = $this->createMock(SessionServiceInterface::class);
        $this->addToWishlistEvent = $this->createMock(AddToWishlistEvent::class);
        $this->request = $this->createMock(RequestInterface::class);

        $this->subject = new TriggerAddToWishlistEvent(
            $this->sessionService,
            $this->addToWishlistEvent,
            $this->request,
        );
    }

    /**
     * @return void
     */
    public function testEventIsQueuedWhenProductAddedToWishlist(): void
    {
        $eventData = ['event' => 'addtowishlist', 'data' => ['productKey' => '456']];

        $product = $this->createMock(Product::class);

        $observer = $this->createMock(Observer::class);
        $observer->method('getData')->with('product')->willReturn($product);

        $this->addToWishlistEvent->expects($this->once())->method('setProduct')->with($product)->willReturnSelf();
        $this->addToWishlistEvent->expects($this->once())->method('get')->willReturn($eventData);

        $this->sessionService->expects($this->once())->method('add')->with('AddToWishlist', $eventData);

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
