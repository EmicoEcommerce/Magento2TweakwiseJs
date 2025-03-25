<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;

class Search implements ArgumentInterface
{
    /**
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     * @param UrlInterface $urlBuilder
     * @param Http $request
     */
    public function __construct(
        private readonly Config $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly Data $dataHelper,
        private readonly UrlInterface $urlBuilder,
        private readonly Http $request
    ) {
    }

    /**
     * @return SearchType
     */
    public function getSearchType(): SearchType
    {
        return $this->config->getSearchType();
    }

    /**
     * @return string|null
     */
    public function getInstanceKey(): ?string
    {
        return $this->config->getInstanceKey();
    }

    /**
     * @return int
     */
    public function getStoreRootCategory(): int
    {
        try {
            return (int)$this->dataHelper->getTweakwiseId(
                (int)$this->storeManager->getStore()->getRootCategoryId()
            );
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }

    /**
     * @return string
     */
    public function getSearchUrl(): string
    {
        return trim($this->urlBuilder->getUrl('catalogsearch/results#twn|'), '/');
    }

    /**
     * @return bool
     */
    public function isSearchResultsPage(): bool
    {
        return $this->request->getFullActionName() === 'catalogsearch_results_index';
    }

    /**
     * @return bool
     */
    public function isGrouped(): bool
    {
        return $this->config->isGroupedExportEnabled();
    }
}
