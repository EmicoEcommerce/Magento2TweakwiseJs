<?php

declare(strict_types=1);

namespace Tweakwise\Test\Functional\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Mockery;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\TweakwiseJs\Api\Event\PriceFormatServiceInterface;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToCartEvent;
use Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToWishlistEvent;
use Tweakwise\Test\Support\FunctionalTester;

class GroupedEventTest extends Unit
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

        $priceFormatService = Mockery::mock(PriceFormatServiceInterface::class);
        $priceFormatService->shouldReceive('format')->andReturnUsing(fn(float $price) => $price);
        $this->tester->mockService(PriceFormatServiceInterface::class, $priceFormatService);

        $configurableResource = Mockery::mock(ConfigurableResource::class);
        $configurableResource->shouldReceive('getParentIdsByChild')->andReturn([]);
        $configurableResource->shouldReceive('getChildrenIds')->andReturn([]);
        $this->tester->mockService(ConfigurableResource::class, $configurableResource);
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

        $sessionService = Mockery::mock(SessionServiceInterface::class);
        $sessionService->shouldReceive('add')
            ->once()
            ->withArgs(function (string $identifier, array $data) {
                return $identifier === 'AddToCart'
                    && $data['event'] === 'addtocart'
                    && isset($data['data']['productKey']);
            });
        $this->tester->mockService(SessionServiceInterface::class, $sessionService);

        /** @var Product $product */
        $product = $this->tester->getObjectManager()->create(Product::class);
        $product->setId(42);
        $product->setTypeId('simple');

        /** @var QuoteItem $quoteItem */
        $quoteItem = $this->tester->getObjectManager()->create(QuoteItem::class);
        $quoteItem->setProductId(42);
        $quoteItem->setQtyToAdd(1);

        $observer = new Observer([
            'product' => $product,
            'quote_item' => $quoteItem,
        ]);

        /** @var TriggerAddToCartEvent $triggerAddToCartEvent */
        $triggerAddToCartEvent = $this->tester->getObjectManager()->create(TriggerAddToCartEvent::class, [
            'sessionService' => $sessionService,
        ]);
        $triggerAddToCartEvent->execute($observer);
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

        $sessionService = Mockery::mock(SessionServiceInterface::class);
        $sessionService->shouldReceive('add')
            ->once()
            ->withArgs(function (string $identifier, array $data) {
                return $identifier === 'AddToWishlist'
                    && $data['event'] === 'addtowishlist'
                    && isset($data['data']['productKey']);
            });
        $this->tester->mockService(SessionServiceInterface::class, $sessionService);

        /** @var Product $product */
        $product = $this->tester->getObjectManager()->create(Product::class);
        $product->setId(42);
        $product->setTypeId('simple');

        $observer = new Observer([
            'product' => $product,
        ]);

        /** @var TriggerAddToWishlistEvent $triggerAddToWishlistEvent */
        $triggerAddToWishlistEvent = $this->tester->getObjectManager()->create(TriggerAddToWishlistEvent::class, [
            'sessionService' => $sessionService,
        ]);
        $triggerAddToWishlistEvent->execute($observer);
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

        $observer = new Observer([
            'product' => Mockery::mock(Product::class),
            'quote_item' => Mockery::mock(QuoteItem::class)->shouldIgnoreMissing(),
        ]);

        /** @var TriggerAddToCartEvent $triggerAddToCartEvent */
        $triggerAddToCartEvent = $this->tester->getObjectManager()->create(TriggerAddToCartEvent::class, [
            'request' => $request,
            'sessionService' => $sessionService,
        ]);
        $triggerAddToCartEvent->execute($observer);
    }
}
