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
    protected function resolveProductKey(): string
    {
        if ($this->quoteItem === null) {
            return $this->dataHelper->resolveGroupedExportProductKey(
                (int)$this->product->getId(),
                (string)$this->product->getTypeId()
            );
        }

        $parentProductId = (int)$this->quoteItem->getProductId();
        $simpleProductId = !empty($this->quoteItem->getQtyOptions())
            ? (int)array_key_first($this->quoteItem->getQtyOptions())
            : $parentProductId;

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
