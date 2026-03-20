<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Event;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\NoSuchEntityException;
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
                'productKey' => $this->dataHelper->getTweakwiseId((int)$this->product->getId()),
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
     * @param int $qty
     * @return $this
     */
    public function setQty(int $qty): AddToCart
    {
        $this->qty = $qty;
        return $this;
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
