<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Model\Config\Source;

use Emico\CodeCept\Test\Unit;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Model\Api\Client;
use Tweakwise\TweakwiseJs\Model\Api\Exception\ApiException;
use Tweakwise\TweakwiseJs\Model\Api\Request;
use Tweakwise\TweakwiseJs\Model\Api\RequestFactory;
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\FeaturedRecommendationResponse;
use Tweakwise\TweakwiseJs\Model\Api\Type\FeaturedRecommendationType;
use Tweakwise\TweakwiseJs\Model\Config\Source\FeaturedRecommendation;

class FeaturedRecommendationTest extends Unit
{
    protected UnitTester $tester;

    private Client|MockInterface $client;

    private RequestFactory|MockInterface $requestFactory;

    private FeaturedRecommendation $subject;

    /**
     * @return void
     * @throws \Exception
     */
    public function _before(): void
    {
        $this->client = Mockery::mock(Client::class);
        $this->requestFactory = Mockery::mock(RequestFactory::class);

        $this->subject = new FeaturedRecommendation($this->client, $this->requestFactory);
    }

    /**
     * @return void
     */
    public function _after(): void
    {
        Mockery::close();
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Config\Source\FeaturedRecommendation::toOptionArray
     * @return void
     */
    public function testToOptionArrayMapsRecommendationsToValueLabelPairs(): void
    {
        $request = Mockery::mock(Request::class);
        $this->requestFactory->shouldReceive('create')->once()->andReturn($request);

        $bestsellers = Mockery::mock(FeaturedRecommendationType::class);
        $bestsellers->shouldReceive('getRecommendationId')->andReturn('12');
        $bestsellers->shouldReceive('getName')->andReturn('Bestsellers');

        $newArrivals = Mockery::mock(FeaturedRecommendationType::class);
        $newArrivals->shouldReceive('getRecommendationId')->andReturn('34');
        $newArrivals->shouldReceive('getName')->andReturn('New arrivals');

        $response = Mockery::mock(FeaturedRecommendationResponse::class);
        $response->shouldReceive('getRecommendations')->andReturn([$bestsellers, $newArrivals]);

        $this->client->shouldReceive('request')->with($request)->once()->andReturn($response);

        $result = $this->subject->toOptionArray();

        $this->assertEquals([
            ['value' => '12', 'label' => 'Bestsellers'],
            ['value' => '34', 'label' => 'New arrivals'],
        ], $result);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Config\Source\FeaturedRecommendation::toOptionArray
     * @return void
     */
    public function testToOptionArrayCachesResultAndOnlyRequestsOnce(): void
    {
        $request = Mockery::mock(Request::class);
        $this->requestFactory->shouldReceive('create')->once()->andReturn($request);

        $response = Mockery::mock(FeaturedRecommendationResponse::class);
        $response->shouldReceive('getRecommendations')->andReturn([]);

        $this->client->shouldReceive('request')->with($request)->once()->andReturn($response);

        $first = $this->subject->toOptionArray();
        $second = $this->subject->toOptionArray();

        $this->assertSame([], $first);
        $this->assertSame($first, $second);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Config\Source\FeaturedRecommendation::toOptionArray
     * @return void
     */
    public function testToOptionArrayReturnsEmptyArrayWhenApiExceptionIsThrown(): void
    {
        $request = Mockery::mock(Request::class);
        $this->requestFactory->shouldReceive('create')->once()->andReturn($request);

        $this->client->shouldReceive('request')->with($request)->once()->andThrow(new ApiException('Gateway unreachable'));

        $result = $this->subject->toOptionArray();

        $this->assertSame([], $result);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Config\Source\FeaturedRecommendation::toOptionArray
     * @return void
     */
    public function testToOptionArrayReturnsEmptyArrayWhenClientReturnsNoResponse(): void
    {
        $request = Mockery::mock(Request::class);
        $this->requestFactory->shouldReceive('create')->once()->andReturn($request);

        $this->client->shouldReceive('request')->with($request)->once()->andReturnNull();

        $result = $this->subject->toOptionArray();

        $this->assertSame([], $result);
    }
}
