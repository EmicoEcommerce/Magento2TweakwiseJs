<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model;

class FeaturedRecommendationsPageState
{
    private bool $hasWidget = false;

    /**
     * @param bool $hasWidget
     * @return void
     */
    public function setHasWidget(bool $hasWidget): void
    {
        $this->hasWidget = $hasWidget;
    }

    /**
     * @return bool
     */
    public function hasWidget(): bool
    {
        return $this->hasWidget;
    }
}
