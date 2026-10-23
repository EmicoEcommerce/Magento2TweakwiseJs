<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Tweakwise\TweakwiseJs\Model\FeaturedRecommendationsPageState;
use Tweakwise\TweakwiseJs\Model\RelatedProductResolver;
use Tweakwise\TweakwiseJs\ViewModel\Base;

class RelatedRecommendations extends Template implements BlockInterface
{
    /**
     * @param Context $context
     * @param Base $viewModel
     * @param FeaturedRecommendationsPageState $pageState
     * @param RelatedProductResolver $productResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Base $viewModel,
        FeaturedRecommendationsPageState $pageState,
        private readonly RelatedProductResolver $productResolver,
        array $data = []
    ) {
        $data['template'] ??= 'Tweakwise_TweakwiseJs::js/widget/related-recommendations.phtml';
        $data['view_model'] ??= $viewModel;
        parent::__construct($context, $data);
        $pageState->setHasWidget(true);
    }

    /**
     * @return string|null
     */
    public function getWidgetTitle(): ?string
    {
        $title = $this->getData('title');
        return $title ? $title : null;
    }

    /**
     * @return string|null
     */
    public function getGroupKey(): ?string
    {
        $groupKey = $this->getData('group_key');
        return $groupKey ? $groupKey : null;
    }

    /**
     * @return string|null
     */
    public function getProductId(): ?string
    {
        return $this->productResolver->resolve($this->getData('product_source'), $this->getData('product_sku'));
    }

    /**
     * @return string
     */
    public function getContainerId(): string
    {
        return 'tw-' . $this->getJsId('related-recommendations');
    }
}
