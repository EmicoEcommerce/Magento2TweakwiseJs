<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Observer\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Event\AddToWishlist as AddToWishlistEvent;
use Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToWishlistEvent;

class TriggerAddToWishlistEventTest extends Unit
{
    private SessionServiceInterface|MockInterface $sessionService;

    private AddToWishlistEvent|MockInterface $addToWishlistEvent;

    private RequestInterface|MockInterface $request;

    private TriggerAddToWishlistEvent $subject;

    /**
     * @return void
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _before(): void
    {
        $this->sessionService = Mockery::mock(SessionServiceInterface::class);
        $this->addToWishlistEvent = Mockery::mock(AddToWishlistEvent::class);
        $this->request = Mockery::mock(RequestInterface::class);

        $this->subject = new TriggerAddToWishlistEvent(
            $this->sessionService,
            $this->addToWishlistEvent,
            $this->request
        );
    }

    /**
     * @return void
     */
    public function testEventIsQueuedWhenProductAddedToWishlist(): void
    {
        $eventData = ['event' => 'addtowishlist', 'data' => ['productKey' => '456']];

        $this->request->shouldReceive('getParam')->with('tweakwise_event_handled')->andReturn(null);

        $product = Mockery::mock(Product::class);

        $observer = Mockery::mock(Observer::class);
        $observer->shouldReceive('getData')->with('product')->andReturn($product);

        $this->addToWishlistEvent->shouldReceive('setProduct')->with($product)->once()->andReturnSelf();
        $this->addToWishlistEvent->shouldReceive('get')->once()->andReturn($eventData);

        $this->sessionService->shouldReceive('add')->with('AddToWishlist', $eventData)->once();

        $this->subject->execute($observer);
    }

    /**
     * @return void
     */
    public function testSkipsWhenAlreadyHandled(): void
    {
        $this->request->shouldReceive('getParam')->with('tweakwise_event_handled')->andReturn('1');

        $observer = Mockery::mock(Observer::class);
        $observer->shouldNotReceive('getData');

        $this->sessionService->shouldNotReceive('add');

        $this->subject->execute($observer);
    }
}
