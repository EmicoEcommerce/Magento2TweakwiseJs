<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;

class Merchandising extends Base
{
    /**
     * @param Config $config
     * @param Data $dataHelper
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     * @param FormKey $formKey
     */
    public function __construct(
        Config $config,
        Data $dataHelper,
        private readonly Http $request,
        private readonly StoreManagerInterface $storeManager,
        private readonly FormKey $formKey,
    ) {
        parent::__construct($config, $dataHelper);
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        try {
            return (string)$this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            return '0';
        }
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        try {
            return $this->formKey->getFormKey();
        } catch (LocalizedException $e) {
            return '';
        }
    }

    /**
     * @return bool
     */
    public function shouldAddAddToCartWishlistFunctionalities(): bool
    {
        return $this->isCategoryPage() ||
            $this->isSearchResultsPage() ||
            $this->config->getSearchType()->value === SearchType::INSTANT_SEARCH->value;
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
    private function isSearchResultsPage(): bool
    {
        return $this->request->getFullActionName() === 'catalogsearch_results_index';
    }
}
