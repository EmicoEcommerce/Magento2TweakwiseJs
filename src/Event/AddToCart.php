<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Event;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Tweakwise\TweakwiseJs\Api\Data\EventInterface;
use Tweakwise\TweakwiseJs\Api\Event\PriceFormatServiceInterface;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\Magento2TweakwiseExport\Model\Config as ExportConfig;

class AddToCart implements EventInterface
{
    /**
     * @var Product|null
     */
    private ?Product $product = null;

    /**
     * @var Item|null
     */
    private ?Item $quoteItem = null;

    /**
     * @var int
     */
    private int $qty = 1;

    /**
     * @param PriceFormatServiceInterface $priceFormatService
     * @param Data $dataHelper
     * @param ExportConfig $exportConfig
     */
    public function __construct(
        private readonly PriceFormatServiceInterface $priceFormatService,
        private readonly Data $dataHelper,
        private readonly ExportConfig $exportConfig,
    ) {
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function get(): array
    {
        return [
            'event' => 'addtocart',
            'data' => [
                'productKey' => $this->resolveProductKey(),
                'quantity' => $this->qty,
                'totalAmount' => $this->getTotalAmount()
            ]
        ];
    }

    /**
     * @param Product $product
     * @return AddToCart
     */
    public function setProduct(Product $product): AddToCart
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @param Item $quoteItem
     * @return AddToCart
     */
    public function setQuoteItem(Item $quoteItem): AddToCart
    {
        $this->quoteItem = $quoteItem;
        return $this;
    }

    /**
     * @param int $qty
     * @return $this
     */
    public function setQty(int $qty): AddToCart
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function resolveProductKey(): string
    {
        if (!$this->exportConfig->isGroupedExport() || $this->quoteItem === null) {
            return $this->dataHelper->getTweakwiseId((int)$this->product->getId());
        }

        $parentProductId = (int)$this->quoteItem->getProductId();
        $simpleProductId = $parentProductId;

        if (!empty($this->quoteItem->getQtyOptions())) {
            $simpleProductId = (int)array_key_first($this->quoteItem->getQtyOptions());
        }

        // groupCode must be the full Tweakwise ID of the parent, cast to int, so it is appended as-is.
        $groupCode = (int)$this->dataHelper->getTweakwiseId($parentProductId);

        return $this->dataHelper->getTweakwiseId($simpleProductId, null, $groupCode);
    }

    /**
     * @return float
     */
    private function getTotalAmount(): float
    {
        $price = (float)$this->product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
        return $this->priceFormatService->format($price * $this->qty);
    }
}
