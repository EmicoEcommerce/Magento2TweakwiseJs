<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Model\Api\Response\Catalog;

use Emico\CodeCept\Test\Unit;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\RecommendationGroupResponse;

class RecommendationGroupResponseTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @param array $data
     * @return RecommendationGroupResponse
     */
    private function createResponse(array $data): RecommendationGroupResponse
    {
        return $this->tester->getObjectManager()->create(RecommendationGroupResponse::class, ['data' => $data]);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\RecommendationGroupResponse::getGroups
     * @return void
     */
    public function testGetGroupsReadsListOfGroups(): void
    {
        $response = $this->createResponse([
            'groups' => ['group' => [['groupkey' => 'upsell'], ['groupkey' => 'crosssell']]],
            'headers' => ['Content-Type' => ['text/xml']],
        ]);

        $keys = array_map(fn ($group) => $group->getGroupKey(), $response->getGroups());

        $this->assertSame(['upsell', 'crosssell'], $keys);
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\RecommendationGroupResponse::getGroups
     * @return void
     */
    public function testGetGroupsReadsSingleGroup(): void
    {
        $response = $this->createResponse(['groups' => ['group' => ['groupkey' => 'upsell']]]);

        $this->assertCount(1, $response->getGroups());
        $this->assertSame('upsell', $response->getGroups()[0]->getGroupKey());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Model\Api\Response\Catalog\RecommendationGroupResponse::getGroups
     * @return void
     */
    public function testGetGroupsIsEmptyForEmptyResponse(): void
    {
        $this->assertSame([], $this->createResponse(['headers' => []])->getGroups());
    }
}
