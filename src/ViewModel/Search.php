<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;

class Search extends Base
{
    /**
     * @param Config $config
     * @param Data $dataHelper
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param Http $request
     */
    public function __construct(
        Config $config,
        Data $dataHelper,
        private readonly StoreManagerInterface $storeManager,
        private readonly UrlInterface $urlBuilder,
        private readonly Http $request
    ) {
        parent::__construct($config, $dataHelper);
    }

    /**
     * @return SearchType
     */
    public function getSearchType(): SearchType
    {
        return $this->config->getSearchType();
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
}
