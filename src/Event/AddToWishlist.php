<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Event;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Tweakwise\TweakwiseJs\Api\Data\EventInterface;
use Tweakwise\TweakwiseJs\Helper\Data;

class AddToWishlist implements EventInterface
{
    /**
     * @var Product|null
     */
    private ?Product $product = null;

    /**
     * @param Data $dataHelper
     */
    public function __construct(
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
            'event' => 'addtowishlist',
            'data' => [
                'productKey' => $this->dataHelper->resolveGroupedExportProductKey(
                    (int)$this->product->getId(),
                    $this->product->getTypeId()
                )
            ]
        ];
    }

    /**
     * @param Product $product
     * @return AddToWishlist
     */
    public function setProduct(Product $product): AddToWishlist
    {
        $this->product = $product;
        return $this;
    }
}
