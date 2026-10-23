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
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\RecommendationGroupResponse;
use Tweakwise\TweakwiseJs\Model\Api\Type\RecommendationGroupType;
use Tweakwise\TweakwiseJs\Model\Config\Source\RecommendationGroup;

class RecommendationGroupTest extends Unit
{
    protected UnitTester $tester;

    private Client|MockInterface $client;

    private RequestFactory|MockInterface $requestFactory;

    private RecommendationGroup $subject;

    /**
     * @return void
     * @throws \Exception
     */
    public function _before(): void
    {
        $this->client = Mockery::mock(Client::class);
        $this->requestFactory = Mockery::mock(RequestFactory::class);

        $this->subject = new RecommendationGroup($this->client, $this->requestFactory);
    }

    /**
     * @return void
     */
    public function _after(): void
    {
        Mockery::close();
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Config\Source\RecommendationGroup::toOptionArray
     * @return void
     */
    public function testToOptionArrayMapsGroupsAfterEmptyFirstOption(): void
    {
        $request = Mockery::mock(Request::class);
        $this->requestFactory->shouldReceive('create')->once()->andReturn($request);

        $upsell = Mockery::mock(RecommendationGroupType::class);
        $upsell->shouldReceive('getGroupKey')->andReturn('upsell');
        $crosssell = Mockery::mock(RecommendationGroupType::class);
        $crosssell->shouldReceive('getGroupKey')->andReturn('crosssell');

        $response = Mockery::mock(RecommendationGroupResponse::class);
        $response->shouldReceive('getGroups')->andReturn([$upsell, $crosssell]);

        $this->client->shouldReceive('request')->with($request)->once()->andReturn($response);

        $result = $this->subject->toOptionArray();

        $this->assertSame('', $result[0]['value']);
        $this->assertSame('-- Select --', (string) $result[0]['label']);
        $this->assertSame([
            ['value' => 'upsell', 'label' => 'upsell'],
            ['value' => 'crosssell', 'label' => 'crosssell'],
        ], array_slice($result, 1));
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Config\Source\RecommendationGroup::toOptionArray
     * @return void
     */
    public function testToOptionArrayWithSingleGroup(): void
    {
        $request = Mockery::mock(Request::class);
        $this->requestFactory->shouldReceive('create')->once()->andReturn($request);

        $upsell = Mockery::mock(RecommendationGroupType::class);
        $upsell->shouldReceive('getGroupKey')->andReturn('upsell');

        $response = Mockery::mock(RecommendationGroupResponse::class);
        $response->shouldReceive('getGroups')->andReturn([$upsell]);

        $this->client->shouldReceive('request')->with($request)->once()->andReturn($response);

        $this->assertCount(2, $this->subject->toOptionArray());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Config\Source\RecommendationGroup::toOptionArray
     * @return void
     */
    public function testToOptionArrayWithEmptyResponseOnlyHasEmptyOption(): void
    {
        $request = Mockery::mock(Request::class);
        $this->requestFactory->shouldReceive('create')->once()->andReturn($request);

        $response = Mockery::mock(RecommendationGroupResponse::class);
        $response->shouldReceive('getGroups')->andReturn([]);

        $this->client->shouldReceive('request')->with($request)->once()->andReturn($response);

        $result = $this->subject->toOptionArray();

        $this->assertCount(1, $result);
        $this->assertSame('', $result[0]['value']);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Config\Source\RecommendationGroup::toOptionArray
     * @return void
     */
    public function testToOptionArrayOnApiExceptionOnlyHasEmptyOptionAndIsCached(): void
    {
        $request = Mockery::mock(Request::class);
        $this->requestFactory->shouldReceive('create')->once()->andReturn($request);

        $this->client->shouldReceive('request')->with($request)->once()->andThrow(new ApiException('Unreachable'));

        $first = $this->subject->toOptionArray();
        $second = $this->subject->toOptionArray();

        $this->assertCount(1, $first);
        $this->assertSame('', $first[0]['value']);
        $this->assertSame($first, $second);
    }
}
