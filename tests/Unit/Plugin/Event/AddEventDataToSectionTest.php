<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Plugin\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Customer\CustomerData\SectionSourceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Plugin\Event\AddEventDataToSection;

class AddEventDataToSectionTest extends Unit
{
    protected UnitTester $tester;

    private SessionServiceInterface|MockObject $sessionService;

    private AddEventDataToSection $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionService = $this->createMock(SessionServiceInterface::class);
        $this->subject = new AddEventDataToSection($this->sessionService);
    }

    /**
     * @return void
     */
    public function testEventDataMergedIntoSectionResult(): void
    {
        $events = [['event' => 'addtocart', 'data' => ['productKey' => '123', 'quantity' => 1, 'totalAmount' => 24.99]]];

        $this->sessionService->method('get')->willReturn($events);

        $subject = $this->createMock(SectionSourceInterface::class);
        $result = $this->subject->afterGetSectionData($subject, ['existing_key' => 'value']);

        $this->assertEquals($events, $result['tweakwise_events']);
        $this->assertEquals('value', $result['existing_key']);
    }

    /**
     * @return void
     */
    public function testSessionClearedAfterEventDataMerged(): void
    {
        $this->sessionService->method('get')->willReturn([]);
        $this->sessionService->expects($this->once())->method('clear');

        $subject = $this->createMock(SectionSourceInterface::class);
        $this->subject->afterGetSectionData($subject, []);
    }

    /**
     * @return void
     */
    public function testEmptyEventsWhenSessionEmpty(): void
    {
        $this->sessionService->method('get')->willReturn([]);

        $subject = $this->createMock(SectionSourceInterface::class);
        $result = $this->subject->afterGetSectionData($subject, []);

        $this->assertEquals([], $result['tweakwise_events']);
    }
}
