<?php

declare(strict_types=1);

namespace Tweakwise\Test\Functional\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Mockery;
use Tweakwise\Magento2TweakwiseExport\Model\Helper;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToCartEvent;
use Tweakwise\TweakwiseJs\ViewModel\Event;
use Tweakwise\Test\Support\FunctionalTester;

class GroupedEventTest extends Unit
{
    protected FunctionalTester $tester;

    /**
     * Mock ExportHelper::getTweakwiseId with a returnMap of [storeId, entityId, groupCode, return].
     *
     * @param array<array{int, int, int|null, string}> $returnMap
     */
    private function mockExportHelper(array $returnMap): void
    {
        $helper = Mockery::mock(Helper::class);
        foreach ($returnMap as [$storeId, $entityId, $groupCode, $return]) {
            $helper->shouldReceive('getTweakwiseId')
                ->with($storeId, $entityId, $groupCode)
                ->andReturn($return);
        }

        $this->tester->mockService(Helper::class, $helper);
    }

    /**
     * @param int $productId
     * @param int $itemId
     * @param Item|null $parentItem
     *
     * @return Item
     */
    private function buildOrderItem(int $productId, int $itemId, ?Item $parentItem = null): Item
    {
        /** @var Item $item */
        $item = $this->tester->getObjectManager()->create(Item::class);
        $item->setProductId($productId);
        $item->setItemId($itemId);
        if ($parentItem !== null) {
            $item->setParentItem($parentItem);
        }

        return $item;
    }

    /**
     * @param Order $order
     */
    private function mockCheckoutSession(Order $order): void
    {
        $session = Mockery::mock(Session::class)->makePartial();
        $session->shouldReceive('getLastRealOrder')->andReturn($order);
        $this->tester->mockService(Session::class, $session);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsPlainIdsWhenGroupedExportDisabled(): void
    {
        $this->mockExportHelper(
            [
                [1, 10, null, '1000110'],
                [1, 42, null, '1000142'],
            ]
        );

        $order = $this->tester->getObjectManager()->get(Order::class);
        $order->setItems([
            $this->buildOrderItem(10, 1),
            $this->buildOrderItem(42, 2),
        ]);

        $this->mockCheckoutSession($order);

        $this->tester->mockConfig('tweakwise/export/grouped_export_enabled', '0');

        /** @var Event $viewModel */
        $viewModel = $this->tester->grabService(Event::class);

        $this->tester->assertEquals('["1000110","1000142"]', $viewModel->getOrderProductIds());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsGroupedFormatForConfigurableOrderItem(): void
    {
        $this->mockExportHelper(
            [
            [1, 10, null, '1000110'],
            [1, 99, 1000110, '1000199-1000110'],
            ]
        );

        $parentItem = $this->buildOrderItem(10, 1);
        $childItem = $this->buildOrderItem(99, 2, $parentItem);

        $order = Mockery::mock(Order::class)->makePartial();
        $order->shouldReceive('getAllItems')->andReturn([$parentItem, $childItem]);
        $this->mockCheckoutSession($order);

        $this->tester->mockConfig('tweakwise/export/grouped_export_enabled', '1');

        /** @var Event $viewModel */
        $viewModel = $this->tester->grabService(Event::class);

        $this->tester->assertEquals('["1000199-1000110"]', $viewModel->getOrderProductIds());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsSimpleItemWithItselfAsGroupCodeWhenNoParent(): void
    {
        $this->mockExportHelper(
            [
            [1, 42, null, '1000142'],
            [1, 42, 1000142, '1000142-1000142'],
            ]
        );

        $order = Mockery::mock(Order::class)->makePartial();
        $order->shouldReceive('getAllItems')->andReturn(
            [
            $this->buildOrderItem(42, 5),
            ]
        );
        $this->mockCheckoutSession($order);

        $this->tester->mockConfig('tweakwise/export/grouped_export_enabled', '1');

        /** @var Event $viewModel */
        $viewModel = $this->tester->grabService(Event::class);

        $this->tester->assertEquals('["1000142-1000142"]', $viewModel->getOrderProductIds());
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

        /** @var TriggerAddToCartEvent $observerInstance */
        $observerInstance = $this->tester->getObjectManager()->create(
            TriggerAddToCartEvent::class,
            ['request' => $request, 'sessionService' => $sessionService]
        );

        $magentoObserver = new Observer();
        $magentoObserver->setData([
            'product' => Mockery::mock(Product::class),
            'quote_item' => Mockery::mock(QuoteItem::class),
        ]);
        $observerInstance->execute($magentoObserver);
    }
}
