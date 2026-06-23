<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\ViewModel;

use Emico\CodeCept\Test\Unit;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\ViewModel\Event;

class EventTest extends Unit
{
    protected UnitTester $tester;

    private Config|MockInterface $config;

    private Data|MockInterface $dataHelper;

    private Session|MockInterface $checkoutSession;

    private ExportConfig|MockInterface $exportConfig;

    private Order|MockInterface $order;

    private Event $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->dataHelper = Mockery::mock(Data::class);
        $this->checkoutSession = Mockery::mock(Session::class);
        $this->exportConfig = Mockery::mock(ExportConfig::class);

        $this->order = Mockery::mock(Order::class);
        $this->checkoutSession->shouldReceive('getLastRealOrder')->andReturn($this->order);

        $this->subject = new Event(
            $this->config,
            $this->dataHelper,
            $this->checkoutSession,
            new Json(),
            $this->exportConfig,
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsPlainIdsWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(false);
        $this->order->shouldReceive('getStoreId')->andReturn(0);
        $this->order->shouldReceive('getAllVisibleItems')->andReturn(
            [
                $this->buildOrderItemMock(10, 1),
                $this->buildOrderItemMock(42, 2),
            ]
        );

        $this->dataHelper->shouldReceive('getTweakwiseId')->with(10, 0, null)->andReturn('1000110');
        $this->dataHelper->shouldReceive('getTweakwiseId')->with(42, 0, null)->andReturn('1000142');

        $this->assertEquals('["1000110","1000142"]', $this->subject->getOrderProductIds());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsFallbackIdOnExceptionWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(false);
        $this->order->shouldReceive('getStoreId')->andReturn(0);
        $this->order->shouldReceive('getAllVisibleItems')->andReturn(
            [
                $this->buildOrderItemMock(42, 1),
            ]
        );

        $this->dataHelper->shouldReceive('getTweakwiseId')->andThrow(new NoSuchEntityException());

        $this->assertEquals('["0"]', $this->subject->getOrderProductIds());
    }

    /**
     * Configurable order: parent item (productId=10, itemId=1) and child item (productId=99, itemId=2).
     * getAllItems() returns parent first, then child. Child's setData('groupCode', 99) overwrites the
     * parent's initial setData('groupCode', 10), yielding simpleProductId=99 and parentProductId=10.
     *
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsGroupedFormatForConfigurableOrderItem(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(true);
        $this->order->shouldReceive('getStoreId')->andReturn(0);

        $parentItem = $this->buildOrderItemMock(10, 1, null);
        $childItem = $this->buildOrderItemMock(99, 2, $parentItem);

        $this->order->shouldReceive('getAllItems')->andReturn([$parentItem, $childItem]);

        $this->dataHelper->shouldReceive('getTweakwiseId')->with(10, 0, null)->andReturn('1000110');
        $this->dataHelper->shouldReceive('getTweakwiseId')->with(99, 0, 1000110)->andReturn('1000199-1000110');

        $this->assertEquals('["1000199-1000110"]', $this->subject->getOrderProductIds());
    }

    /**
     * Simple order item with no parent: productId and groupCode are the same, so the key is
     * simpleId-simpleId (e.g. 1000142-1000142).
     *
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsItemWithItselfAsGroupCodeWhenNoParent(): void
    {
        $this->exportConfig->shouldReceive('isGroupedExport')->andReturn(true);
        $this->order->shouldReceive('getStoreId')->andReturn(0);

        $this->order->shouldReceive('getAllItems')->andReturn(
            [
                $this->buildOrderItemMock(42, 5, null),
            ]
        );

        $this->dataHelper->shouldReceive('getTweakwiseId')->with(42, 0, null)->andReturn('1000142');
        $this->dataHelper->shouldReceive('getTweakwiseId')->with(42, 0, 1000142)->andReturn('1000142-1000142');

        $this->assertEquals('["1000142-1000142"]', $this->subject->getOrderProductIds());
    }

    /**
     * @param int $productId
     * @param int $itemId
     * @param Item|null $parentItem
     *
     * @return Item&MockInterface
     */
    private function buildOrderItemMock(int $productId, int $itemId, ?Item $parentItem = null): Item&MockInterface
    {
        $mock = Mockery::mock(Item::class)->makePartial();
        $mock->shouldReceive('getProductId')->andReturn($productId);
        $mock->shouldReceive('getId')->andReturn($itemId);
        $mock->shouldReceive('getParentItem')->andReturn($parentItem);

        return $mock;
    }
}
