<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Plugin\Event;

use Emico\CodeCept\Test\Unit;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;
use Tweakwise\TweakwiseJs\Plugin\Event\AddEventDataToSection;

class AddEventDataToSectionTest extends Unit
{
    protected UnitTester $tester;

    private SessionServiceInterface|MockInterface $sessionService;

    private AddEventDataToSection $subject;

    /**
     * @return void
     * @throws \Exception
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _before(): void
    {
        $this->sessionService = Mockery::mock(SessionServiceInterface::class);
        $this->tester->mockService(SessionServiceInterface::class, $this->sessionService);

        $this->subject = $this->tester->getObjectManager()->create(AddEventDataToSection::class, [
            'sessionService' => $this->sessionService,
        ]);
    }

    /**
     * @return void
     */
    public function testEventDataMergedIntoSectionResult(): void
    {
        $events = [['event' => 'addtocart', 'data' => ['productKey' => '123', 'quantity' => 1, 'totalAmount' => 24.99]]];

        $this->sessionService->shouldReceive('get')->andReturn($events);
        $this->sessionService->shouldReceive('clear');

        $subject = Mockery::mock(SectionSourceInterface::class);
        $result = $this->subject->afterGetSectionData($subject, ['existing_key' => 'value']);

        $this->assertEquals($events, $result['tweakwise_events']);
        $this->assertEquals('value', $result['existing_key']);
    }

    /**
     * @return void
     */
    public function testSessionClearedAfterEventDataMerged(): void
    {
        $this->sessionService->shouldReceive('get')->andReturn([]);
        $this->sessionService->shouldReceive('clear')->once();

        $subject = Mockery::mock(SectionSourceInterface::class);
        $this->subject->afterGetSectionData($subject, []);
    }

    /**
     * @return void
     */
    public function testEmptyEventsWhenSessionEmpty(): void
    {
        $this->sessionService->shouldReceive('get')->andReturn([]);
        $this->sessionService->shouldReceive('clear');

        $subject = Mockery::mock(SectionSourceInterface::class);
        $result = $this->subject->afterGetSectionData($subject, []);

        $this->assertEquals([], $result['tweakwise_events']);
    }
}
