<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Tweakwise\TweakwiseJs\ViewModel\Base;

class FeaturedRecommendations extends Template implements BlockInterface
{
    /**
     * @param Context $context
     * @param Base $viewModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        Base $viewModel,
        array $data = []
    ) {
        $data['template'] ??= 'Tweakwise_TweakwiseJs::js/widget/featured-recommendations.phtml';
        $data['view_model'] ??= $viewModel;
        parent::__construct($context, $data);
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
    public function getRuleId(): ?string
    {
        $ruleId = $this->getData('rule_id');
        return $ruleId ? $ruleId : null;
    }

    /**
     * @return string
     */
    public function getContainerId(): string
    {
        return $this->getJsId('featured-recommendations');
    }
}
