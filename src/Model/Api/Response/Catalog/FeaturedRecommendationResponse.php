<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response\Catalog;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog\FeaturedRecommendationResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;
use Tweakwise\TweakwiseJs\Model\Api\Type\FeaturedRecommendationTypeFactory;

class FeaturedRecommendationResponse extends Response implements FeaturedRecommendationResponseInterface
{
    /**
     * @var array|null
     */
    private ?array $recommendations = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FeaturedRecommendationTypeFactory $featuredRecommendationTypeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly FeaturedRecommendationTypeFactory $featuredRecommendationTypeFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * The gateway response nests the repeated <recommendation> elements inside a <recommendations> wrapper,
     * so the items are read from self::RECOMMENDATION within the self::RECOMMENDATIONS wrapper.
     *
     * @return array
     */
    public function getRecommendations(): array
    {
        if ($this->recommendations === null) {
            $wrapper = $this->getData(self::RECOMMENDATIONS) ?? [];
            $this->setRecommendations($this->buildRecommendations($wrapper[self::RECOMMENDATION] ?? []));
        }

        return $this->recommendations;
    }

    /**
     * @param array $recommendations
     * @return FeaturedRecommendationResponseInterface
     */
    public function setRecommendations(array $recommendations): FeaturedRecommendationResponseInterface
    {
        $this->recommendations = $recommendations;
        return $this;
    }

    /**
     * @param array $items
     * @return array
     */
    private function buildRecommendations(array $items): array
    {
        if (!$items) {
            return [];
        }

        if (!array_is_list($items)) {
            $items = [$items];
        }

        return array_map(fn (array $item) => $this->featuredRecommendationTypeFactory->create(['data' => $item]), $items);
    }
}
