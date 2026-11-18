<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;

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
     * @return int
     */
    public function getStoreRootCategory(): int
    {
        try {
            /** @var Store $store */
            $store = $this->storeManager->getStore();
            return (int)$this->dataHelper->getTweakwiseId(
                (int)$store->getRootCategoryId()
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
        return trim($this->urlBuilder->getUrl('catalogsearch/results'), '/');
    }

    /**
     * @return bool
     */
    public function isSearchResultsPage(): bool
    {
        return in_array(
            $this->request->getFullActionName(),
            Data::SEARCH_RESULT_ACTIONS,
            true
        );
    }
}
