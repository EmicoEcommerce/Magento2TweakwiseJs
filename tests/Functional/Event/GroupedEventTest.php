<?php

declare(strict_types=1);

namespace Tweakwise\Test\Functional\Event;

use Emico\CodeCept\Test\Functional;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Mockery;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\Test\Support\FunctionalTester;

class GroupedEventTest extends Functional
{
    protected FunctionalTester $tester;

    /**
     * @return void
     * @throws \Exception
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _before(): void
    {
        $this->tester->mockConfig(ExportConfig::PATH_GROUPED_EXPORT_ENABLED, '1');
    }

    /**
     * Dispatching checkout_cart_product_add_after adds the event data to the session.
     *
     * @return void
     */
    public function testAddToCartEventIsStoredInSession(): void
    {
        $request = Mockery::mock(RequestInterface::class)->makePartial();
        $request->shouldReceive('getParam')->with('tweakwise_event_handled')->andReturn(null);
        $this->tester->mockService(RequestInterface::class, $request);

        /** @var Product $product */
        $product = $this->tester->getObjectManager()->create(Product::class);
        $product->setId(42);
        $product->setTypeId('simple');

        /** @var QuoteItem $quoteItem */
        $quoteItem = $this->tester->getObjectManager()->create(QuoteItem::class);
        $quoteItem->setProductId(42);
        $quoteItem->setQtyToAdd(1);

        /** @var EventManager $eventManager */
        $eventManager = $this->tester->getObjectManager()->create(EventManager::class);
        $eventManager->dispatch('checkout_cart_product_add_after', [
            'product' => $product,
            'quote_item' => $quoteItem,
        ]);

        /** @var SessionServiceInterface $sessionService */
        $sessionService = $this->tester->getObjectManager()->create(SessionServiceInterface::class);
        $events = $sessionService->get();

        $this->assertArrayHasKey('AddToCart', $events);
        $this->assertEquals('addtocart', $events['AddToCart']['event']);
        $this->assertArrayHasKey('productKey', $events['AddToCart']['data']);
    }

    /**
     * Dispatching wishlist_add_product adds the event data to the session.
     *
     * @return void
     */
    public function testAddToWishlistEventIsStoredInSession(): void
    {
        $request = Mockery::mock(RequestInterface::class)->makePartial();
        $request->shouldReceive('getParam')->with('tweakwise_event_handled')->andReturn(null);
        $this->tester->mockService(RequestInterface::class, $request);

        /** @var Product $product */
        $product = $this->tester->getObjectManager()->create(Product::class);
        $product->setId(42);
        $product->setTypeId('simple');

        /** @var EventManager $eventManager */
        $eventManager = $this->tester->getObjectManager()->create(EventManager::class);
        $eventManager->dispatch('wishlist_add_product', [
            'product' => $product,
        ]);

        /** @var SessionServiceInterface $sessionService */
        $sessionService = $this->tester->getObjectManager()->create(SessionServiceInterface::class);
        $events = $sessionService->get();

        $this->assertArrayHasKey('AddToWishlist', $events);
        $this->assertEquals('addtowishlist', $events['AddToWishlist']['event']);
        $this->assertArrayHasKey('productKey', $events['AddToWishlist']['data']);
    }

    /**
     * When tweakwise_event_handled param is present in the request, the observer must skip
     * enqueueing the event to prevent double-firing from the JS-initiated add-to-cart.
     *
     * @covers \Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToCartEvent::execute
     * @return void
     */
    public function testObserverSkipsWhenTweakwiseEventHandledParamPresent(): void
    {
        $request = Mockery::mock(RequestInterface::class)->makePartial();
        $request->shouldReceive('getParam')->with('tweakwise_event_handled')->andReturn('1');
        $this->tester->mockService(RequestInterface::class, $request);

        $sessionService = Mockery::mock(SessionServiceInterface::class);
        $sessionService->shouldReceive('add')->never();
        $this->tester->mockService(SessionServiceInterface::class, $sessionService);

        $eventManager = $this->tester->getObjectManager()->create(EventManager::class);
        $eventManager->dispatch('checkout_cart_product_add_after', [
            'product' => Mockery::mock(Product::class),
            'quote_item' => Mockery::mock(QuoteItem::class),
        ]);
    }
}
