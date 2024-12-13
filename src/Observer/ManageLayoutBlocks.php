<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Observer;

use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\ViewModel\Merchandising;
use Tweakwise\TweakwiseJs\ViewModel\Search;

class ManageLayoutBlocks implements ObserverInterface
{
    /**
     * @param Http $request
     * @param Config $config
     * @param Merchandising $merchandisingViewModel
     * @param Search $searchViewModel
     * @param Resolver $layerResolver
     */
    public function __construct(
        private readonly Http $request,
        private readonly Config $config,
        private readonly Merchandising $merchandisingViewModel,
        private readonly Search $searchViewModel,
        private readonly Resolver $layerResolver
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        // TODO: CAN WE SPECIFY ON WHICH PAGE TYPES THE BLOCKS MUST BE LOADED?
        if (!$this->config->isEnabled()) {
            return;
        }

        $layout = $observer->getLayout();

        $this->addDefaultBlock($layout);
        $this->addSearchBlock($layout);

        if (!$this->isCategoryPage() || !$this->showTweakwiseJsCategoryViewBlock()) {
            return;
        }

        $this->addTweakwiseJsCategoryViewBlock($layout);
    }

    /**
     * @param Layout $layout
     * @return void
     */
    private function addDefaultBlock(Layout $layout): void
    {
        $blockName = 'tweakwise-js-default';
        $layout->createBlock(Template::class, $blockName)
            ->setTemplate('Tweakwise_TweakwiseJs::default.phtml');
        $layout->setChild('after.body.start', $blockName, $blockName);
    }

    /**
     * @return bool
     */
    private function isCategoryPage(): bool
    {
        return $this->request->getFullActionName() === 'catalog_category_view';
    }

    /**
     * @param Layout $layout
     * @return void
     */
    private function addTweakwiseJsCategoryViewBlock(Layout $layout): void
    {
        $blockName = 'tweakwise-js-lister';
        $layout->createBlock(
            View::class,
            $blockName,
            [
                'data' => [
                    'view_model' => $this->merchandisingViewModel
                ]
            ]
        )->setTemplate('Tweakwise_TweakwiseJs::category/listing.phtml');
        $layout->setChild('page.wrapper', $blockName, $blockName);
    }

    /**
     * @return bool
     */
    private function showTweakwiseJsCategoryViewBlock(): bool
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

    /**
     * @param Layout $layout
     * @return void
     */
    private function addSearchBlock(Layout $layout): void
    {
        $blockName = 'tweakwise-js-search';
        $layout->createBlock(
            Template::class,
            $blockName,
            [
                'data' => [
                    'view_model' => $this->searchViewModel
                ]
            ]
        )->setTemplate('Tweakwise_TweakwiseJs::search.phtml');
        $layout->setChild('after.body.start', $blockName, $blockName);
    }
}
