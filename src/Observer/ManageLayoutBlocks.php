<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Layout;
use Tweakwise\TweakwiseJs\Model\Config;

class ManageLayoutBlocks implements ObserverInterface
{
    /**
     * @var Layout
     */
    private Layout $layout;

    /**
     * @param Http $request
     * @param Config $config
     * @param Resolver $layerResolver
     */
    public function __construct(
        private readonly Http $request,
        private readonly Config $config,
        private readonly Resolver $layerResolver,
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $this->layout = $observer->getLayout();

        if (!$this->isCategoryPage() || !$this->shouldShowTweakwiseJsCategoryViewBlock()) {
            return;
        }

        $this->addMerchandisingHandle();
    }

    /**
     * @return void
     */
    private function addMerchandisingHandle(): void
    {
        $this->layout->getUpdate()->addHandle('tweakwisejs_merchandising');
    }

    /**
     * @return bool
     */
    private function isCategoryPage(): bool
    {
        return $this->request->getFullActionName() === 'catalog_category_view';
    }

    /**
     * @return bool
     */
    private function shouldShowTweakwiseJsCategoryViewBlock(): bool
    {
        if (!$this->config->isMerchandisingEnabled()) {
            return false;
        }

        $currentCategory = $this->layerResolver->get()->getCurrentCategory();
        if (!$currentCategory) {
            return false;
        }

        $displayMode = $currentCategory->getDisplayMode();
        if ($displayMode && $displayMode === Category::DM_PAGE) {
            return false;
        }

        return true;
    }
}
