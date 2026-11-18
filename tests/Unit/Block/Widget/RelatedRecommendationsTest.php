<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\Block\Widget;

use Emico\CodeCept\Test\Unit;
use Magento\Framework\View\Element\Template\Context;
use Mockery;
use Mockery\MockInterface;
use Tweakwise\Test\Support\UnitTester;
use Tweakwise\TweakwiseJs\Block\Widget\RelatedRecommendations;
use Tweakwise\TweakwiseJs\Model\FeaturedRecommendationsPageState;
use Tweakwise\TweakwiseJs\Model\RelatedProductResolver;
use Tweakwise\TweakwiseJs\ViewModel\Base;

class RelatedRecommendationsTest extends Unit
{
    protected UnitTester $tester;

    private RelatedProductResolver|MockInterface $productResolver;

    private FeaturedRecommendationsPageState $pageState;

    /**
     * @param array $data
     * @return RelatedRecommendations
     */
    private function createBlock(array $data = []): RelatedRecommendations
    {
        $this->productResolver = Mockery::mock(RelatedProductResolver::class);
        $this->pageState = new FeaturedRecommendationsPageState();

        return new RelatedRecommendations(
            $this->tester->getObjectManager()->get(Context::class),
            Mockery::mock(Base::class),
            $this->pageState,
            $this->productResolver,
            $data
        );
    }

    /**
     * @return void
     */
    public function _after(): void
    {
        Mockery::close();
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Block\Widget\RelatedRecommendations::__construct
     * @return void
     */
    public function testConstructorFlagsPageStateWithWidget(): void
    {
        $this->createBlock();

        $this->assertTrue($this->pageState->hasWidget());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Block\Widget\RelatedRecommendations::getGroupKey
     * @covers \Tweakwise\TweakwiseJs\Block\Widget\RelatedRecommendations::getWidgetTitle
     * @return void
     */
    public function testGroupKeyAndTitleAreNullWhenMissing(): void
    {
        $block = $this->createBlock(['group_key' => '', 'title' => '']);

        $this->assertNull($block->getGroupKey());
        $this->assertNull($block->getWidgetTitle());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Block\Widget\RelatedRecommendations::getProductId
     * @return void
     */
    public function testGetProductIdDelegatesToResolverWithWidgetParameters(): void
    {
        $block = $this->createBlock(['product_source' => 'custom', 'product_sku' => 'ABC-1']);
        $this->productResolver->shouldReceive('resolve')->with('custom', 'ABC-1')->once()->andReturn('9');

        $this->assertSame('9', $block->getProductId());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Block\Widget\RelatedRecommendations::getProductId
     * @return void
     */
    public function testGetProductIdIsNullWithoutProduct(): void
    {
        $block = $this->createBlock();
        $this->productResolver->shouldReceive('resolve')->with(null, null)->andReturnNull();

        $this->assertNull($block->getProductId());
    }

    /**
     * @covers \Tweakwise\TweakwiseJs\Block\Widget\RelatedRecommendations::getContainerId
     * @return void
     */
    public function testContainerIdIsUniquePerInstance(): void
    {
        $first = $this->createBlock();
        $first->setNameInLayout('related.first');
        $second = $this->createBlock();
        $second->setNameInLayout('related.second');

        $this->assertNotSame($first->getContainerId(), $second->getContainerId());
    }
}
