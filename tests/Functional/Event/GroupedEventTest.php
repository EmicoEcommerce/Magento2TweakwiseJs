<?php

declare(strict_types=1);

namespace Tweakwise\Test\Functional\Event;

use Magento\Sales\Model\Order\Item;
use Magento\Checkout\Model\Session;
use Emico\CodeCept\Test\Unit;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order;
use Mockery;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Observer\Event\TriggerAddToCartEvent;
use Tweakwise\TweakwiseJs\ViewModel\Event;
use Tweakwise\Test\Support\FunctionalTester;

class GroupedEventTest extends Unit
{
    protected FunctionalTester $tester;

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsPlainIdsWhenGroupedExportDisabled(): void
    {
        $itemOne = $this->createMock(Item::class);
        $itemOne->method('getProductId')->willReturn(10);

        $itemTwo = $this->createMock(Item::class);
        $itemTwo->method('getProductId')->willReturn(42);

        $order = $this->createMock(Order::class);
        $order->method('getAllVisibleItems')->willReturn([$itemOne, $itemTwo]);
        $order->method('getStoreId')->willReturn(1);

        $checkoutSession = $this->createMock(Session::class);
        $checkoutSession->method('getLastRealOrder')->willReturn($order);

        $exportConfig = $this->createMock(ExportConfig::class);
        $exportConfig->method('isGroupedExport')->willReturn(false);

        $dataHelper = $this->createMock(Data::class);
        $dataHelper->method('getTweakwiseId')->willReturnMap(
            [
                [10, 1, null, '1000110'],
                [42, 1, null, '1000142'],
            ]
        );

        $viewModel = new Event(
            $this->createMock(Config::class),
            $dataHelper,
            $checkoutSession,
            new Json(),
            $exportConfig,
        );

        $this->assertEquals('["1000110","1000142"]', $viewModel->getOrderProductIds());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsSimpleItemWithItselfAsGroupCodeWhenNoParent(): void
    {
        $item = $this->createMock(Item::class);
        $item->method('getProductId')->willReturn(10);
        $item->method('getData')->with('groupCode')->willReturn(10);

        $order = $this->createMock(Order::class);
        $order->method('getAllItems')->willReturn([$item]);
        $order->method('getStoreId')->willReturn(1);

        $checkoutSession = $this->createMock(Session::class);
        $checkoutSession->method('getLastRealOrder')->willReturn($order);

        $exportConfig = $this->createMock(ExportConfig::class);
        $exportConfig->method('isGroupedExport')->willReturn(true);

        $dataHelper = $this->createMock(Data::class);
        $dataHelper->method('getTweakwiseId')->willReturnMap(
            [
                [10, 1, null, '1000110'],
                [10, 1, 1000110, '1000110-1000110'],
            ]
        );

        $viewModel = new Event(
            $this->createMock(Config::class),
            $dataHelper,
            $checkoutSession,
            new Json(),
            $exportConfig,
        );

        $this->assertEquals('["1000110-1000110"]', $viewModel->getOrderProductIds());
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
