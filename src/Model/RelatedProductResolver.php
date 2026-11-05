<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config\Source\ProductSource;

class RelatedProductResolver
{
    private const CART_ACTION = 'checkout_cart_index';
    private const CONFIGURABLE_TYPE = 'configurable';

    /**
     * @param Registry $registry
     * @param Session $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param Http $request
     * @param Data $dataHelper
     * @param ExportConfig $exportConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Registry $registry,
        private readonly Session $checkoutSession,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly Http $request,
        private readonly Data $dataHelper,
        private readonly ExportConfig $exportConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string|null $source
     * @param string|null $sku
     * @return string|null
     */
    public function resolve(?string $source, ?string $sku): ?string
    {
        $product = $source === ProductSource::CUSTOM
            ? $this->getProductBySku($sku)
            : $this->getAutomaticProduct();

        return $product ? $this->getTweakwiseKey($product) : null;
    }

    /**
     * @param string|null $sku
     * @return ProductInterface|null
     */
    private function getProductBySku(?string $sku): ?ProductInterface
    {
        if ($sku === null || trim($sku) === '') {
            return null;
        }

        try {
            return $this->productRepository->get(trim($sku));
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return ProductInterface|null
     */
    private function getAutomaticProduct(): ?ProductInterface
    {
        $currentProduct = $this->registry->registry('current_product');
        if ($currentProduct instanceof ProductInterface) {
            return $currentProduct;
        }

        if ($this->request->getFullActionName() !== self::CART_ACTION) {
            return null;
        }

        return $this->getCartProduct();
    }

    /**
     * @return ProductInterface|null
     */
    private function getCartProduct(): ?ProductInterface
    {
        try {
            $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
            $lastAddedProductId = (int) $this->checkoutSession->getLastAddedProductId(false);
        } catch (LocalizedException $e) {
            return null;
        }

        $cartItem = $items ? reset($items) : null;
        foreach ($items as $item) {
            if ($lastAddedProductId && (int) $item->getProductId() === $lastAddedProductId) {
                $cartItem = $item;
                break;
            }
        }

        return $cartItem ? $this->getItemProduct($cartItem) : null;
    }

    /**
     * @param Item $item
     * @return ProductInterface
     */
    private function getItemProduct(Item $item): ProductInterface
    {
        if ($item->getProductType() === self::CONFIGURABLE_TYPE && $this->exportConfig->isGroupedExport()) {
            $children = $item->getChildren();
            $child = reset($children);
            if ($child) {
                return $child->getProduct();
            }
        }

        return $item->getProduct();
    }

    /**
     * @param ProductInterface $product
     * @return string|null
     */
    private function getTweakwiseKey(ProductInterface $product): ?string
    {
        try {
            $key = $this->dataHelper->resolveGroupedExportProductKey((int) $product->getId(), (string) $product->getTypeId());
        } catch (NoSuchEntityException $e) {
            $key = '';
        }

        if ($key === '' || $key === '0') {
            $this->logger->debug(
                sprintf('Tweakwise related recommendations: no export key for product "%s"', $product->getSku())
            );
            return null;
        }

        return $key;
    }
}
