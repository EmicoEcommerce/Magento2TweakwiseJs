<?php

declare(strict_types=1);

namespace Tweakwise\Test\Functional\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Test\Support\FunctionalTester;

class GroupedEventTest extends Unit
{
    protected FunctionalTester $tester;

    private CheckoutSession $checkoutSession;

    private CustomerSession $customerSession;

    private EventManagerInterface $eventManager;

    private HttpRequest $request;

    private AppState $appState;

    /**
     * @return void
     * @throws \Exception
     */
    public function _before(): void
    {
        $this->tester->mockConfig(ExportConfig::PATH_GROUPED_EXPORT_ENABLED, '1');

        $objectManager = $this->tester->getObjectManager();
        $this->checkoutSession = $objectManager->get(CheckoutSession::class);
        $this->customerSession = $objectManager->get(CustomerSession::class);
        $this->eventManager = $objectManager->get(EventManagerInterface::class);
        $this->request = $objectManager->get(HttpRequest::class);
        $this->appState = $objectManager->get(AppState::class);

        $this->checkoutSession->setTweakwiseEventData([]);
        $this->customerSession->setTweakwiseEventData([]);
        $this->request->setParam('tweakwise_event_handled', null);
    }

    /**
     * @return void
     */
    public function testCheckoutCartProductAddAfterStoresEventDataInCheckoutSession(): void
    {
        $product = $this->tester->getObjectManager()->create(Product::class);
        $product->setId(42);
        $product->setTypeId('simple');

        $quoteItem = $this->tester->getObjectManager()->create(QuoteItem::class);
        $quoteItem->setProductId(42);
        $quoteItem->setQtyToAdd(1);

        $this->dispatchFrontendEvent('checkout_cart_product_add_after', [
            'product' => $product,
            'quote_item' => $quoteItem,
        ]);

        $eventData = $this->checkoutSession->getTweakwiseEventData();
        $this->assertArrayHasKey('AddToCart', $eventData);
        $this->assertEquals('addtocart', $eventData['AddToCart']['event'] ?? null);
        $this->assertArrayHasKey('productKey', $eventData['AddToCart']['data'] ?? []);
    }

    /**
     * @return void
     */
    public function testWishlistAddProductStoresEventDataInCustomerSession(): void
    {
        $product = $this->tester->getObjectManager()->create(Product::class);
        $product->setId(42);
        $product->setTypeId('simple');

        $this->dispatchFrontendEvent('wishlist_add_product', [
            'product' => $product,
        ]);

        $eventData = $this->customerSession->getTweakwiseEventData();
        $this->assertArrayHasKey('AddToWishlist', $eventData);
        $this->assertEquals('addtowishlist', $eventData['AddToWishlist']['event'] ?? null);
        $this->assertArrayHasKey('productKey', $eventData['AddToWishlist']['data'] ?? []);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToCartEvent::execute
     * @return void
     */
    public function testCheckoutCartEventSkipsWhenHandledParamPresent(): void
    {
        $this->request->setParam('tweakwise_event_handled', '1');

        $product = $this->tester->getObjectManager()->create(Product::class);
        $product->setId(42);
        $product->setTypeId('simple');

        $quoteItem = $this->tester->getObjectManager()->create(QuoteItem::class);
        $quoteItem->setProductId(42);
        $quoteItem->setQtyToAdd(1);

        $this->dispatchFrontendEvent('checkout_cart_product_add_after', [
            'product' => $product,
            'quote_item' => $quoteItem,
        ]);

        $this->assertSame([], $this->checkoutSession->getTweakwiseEventData());
        $this->request->setParam('tweakwise_event_handled', null);
    }

    /**
     * @param string $eventName
     * @param array $eventData
     * @return void
     */
    private function dispatchFrontendEvent(string $eventName, array $eventData): void
    {
        $this->appState->emulateAreaCode('frontend', function () use ($eventName, $eventData): void {
            $this->eventManager->dispatch($eventName, $eventData);
        });
    }
}
