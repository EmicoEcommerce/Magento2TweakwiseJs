<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
use Tweakwise\TweakwiseJs\Block\Widget\FeaturedRecommendations;
use Tweakwise\TweakwiseJs\Model\FeaturedRecommendationsPageState;

class DetectFeaturedRecommendationsWidget implements ObserverInterface
{
    /**
     * @param FeaturedRecommendationsPageState $pageState
     */
    public function __construct(
        private readonly FeaturedRecommendationsPageState $pageState
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
     */
    public function execute(Observer $observer): void
    {
        /** @var Layout $layout */
        $layout = $observer->getLayout();

        foreach ($layout->getAllBlocks() as $block) {
            if ($block instanceof FeaturedRecommendations) {
                $this->pageState->setHasWidget(true);
                return;
            }
        }
    }
}
