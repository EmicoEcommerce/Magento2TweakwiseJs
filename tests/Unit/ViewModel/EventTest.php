<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\ViewModel;

use Emico\CodeCept\Test\Unit;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\ViewModel\Event;

class EventTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @var Config&MockObject
     */
    private Config|MockObject $config;

    /**
     * @var Data&MockObject
     */
    private Data|MockObject $dataHelper;

    /**
     * @var Session&MockObject
     */
    private Session|MockObject $checkoutSession;

    /**
     * @var ExportConfig&MockObject
     */
    private ExportConfig|MockObject $exportConfig;

    /**
     * @var Order&MockObject
     */
    private Order|MockObject $order;

    private Event $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(Config::class);
        $this->dataHelper = $this->createMock(Data::class);
        $this->checkoutSession = $this->createMock(Session::class);
        $this->exportConfig = $this->createMock(ExportConfig::class);

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllItems', 'getAllVisibleItems'])
            ->getMock();

        $this->checkoutSession->method('getLastRealOrder')->willReturn($this->order);

        $this->subject = new Event(
            $this->config,
            $this->dataHelper,
            $this->checkoutSession,
            new Json(),
            $this->exportConfig,
        );
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsPlainIdsWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(false);

        $this->order->method('getAllVisibleItems')->willReturn(
            [
            $this->buildOrderItemMock(10, 1),
            $this->buildOrderItemMock(42, 2),
            ]
        );

        $this->dataHelper->method('getTweakwiseId')->willReturnMap(
            [
            [10, null, null, '1000110'],
            [42, null, null, '1000142'],
            ]
        );

        $this->assertEquals('["1000110","1000142"]', $this->subject->getOrderProductIds());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\ViewModel\Event::getOrderProductIds
     * @return void
     */
    public function testGetOrderProductIdsReturnsFallbackIdOnExceptionWhenGroupedExportDisabled(): void
    {
        $this->exportConfig->method('isGroupedExport')->willReturn(false);

        $this->order->method('getAllVisibleItems')->willReturn(
            [
            $this->buildOrderItemMock(42, 1),
            ]
        );

        $this->dataHelper->method('getTweakwiseId')->willThrowException(new NoSuchEntityException());

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
        $this->exportConfig->method('isGroupedExport')->willReturn(true);

        $parentItem = $this->buildOrderItemMock(10, 1, null);
        $childItem = $this->buildOrderItemMock(99, 2, $parentItem);

        $this->order->method('getAllItems')->willReturn([$parentItem, $childItem]);

        $this->dataHelper->method('getTweakwiseId')->willReturnMap(
            [
            [10, null, null, '1000110'],
            [99, null, 1000110, '1000199-1000110'],
            ]
        );

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
        $this->exportConfig->method('isGroupedExport')->willReturn(true);

        $this->order->method('getAllItems')->willReturn(
            [
            $this->buildOrderItemMock(42, 5, null),
            ]
        );

        $this->dataHelper->method('getTweakwiseId')->willReturnMap(
            [
            [42, null, null, '1000142'],
            [42, null, 1000142, '1000142-1000142'],
            ]
        );

        $this->assertEquals('["1000142-1000142"]', $this->subject->getOrderProductIds());
    }

    /**
     * @param int $productId
     * @param int $itemId
     * @param Item|null $parentItem
     *
     * @return Item&MockObject
     */
    private function buildOrderItemMock(int $productId, int $itemId, ?Item $parentItem = null): Item&MockObject
    {
        $mock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProductId', 'getId', 'getParentItem'])
            ->getMock();

        $mock->method('getProductId')->willReturn($productId);
        $mock->method('getId')->willReturn($itemId);
        $mock->method('getParentItem')->willReturn($parentItem);

        return $mock;
    }
}
