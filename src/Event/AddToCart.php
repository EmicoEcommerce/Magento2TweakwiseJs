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
     */
    public function __construct(
        private readonly PriceFormatServiceInterface $priceFormatService,
        private readonly Data $dataHelper,
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
    public function resolveProductKey(): string
    {
        [$productId, $typeId] = $this->resolveProductIdAndType();

        if (!is_string($typeId)) {
            return $this->dataHelper->getTweakwiseId($productId);
        }

        return $this->dataHelper->resolveGroupedExportProductKey($productId, $typeId);
    }

    /**
     * Resolves the product ID and type from either the quote item or the product.
     *
     * When a quote item is available (add-to-cart via observer), the actual simple product ID
     * is extracted from qty_options so the correct child is resolved rather than the first child.
     *
     * @return array{int, string|null}
     */
    private function resolveProductIdAndType(): array
    {
        if ($this->quoteItem === null) {
            $typeId = $this->product->getTypeId();
            return [(int)$this->product->getId(), is_string($typeId) ? $typeId : null];
        }

        $hasQtyOptions = !empty($this->quoteItem->getQtyOptions());

        if ($hasQtyOptions) {
            $simpleProductId = (int)array_key_first($this->quoteItem->getQtyOptions());
            return [$simpleProductId, 'simple'];
        }

        $typeId = $this->product->getTypeId();
        return [(int)$this->quoteItem->getProductId(), is_string($typeId) ? $typeId : null];
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
