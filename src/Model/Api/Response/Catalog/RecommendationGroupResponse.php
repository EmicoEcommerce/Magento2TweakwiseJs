<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response\Catalog;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog\RecommendationGroupResponseInterface;
use Tweakwise\TweakwiseJs\Api\Data\Api\Type\RecommendationGroupTypeInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;
use Tweakwise\TweakwiseJs\Model\Api\Type\RecommendationGroupTypeFactory;

class RecommendationGroupResponse extends Response implements RecommendationGroupResponseInterface
{
    /**
     * @var array|null
     */
    private ?array $groups = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param RecommendationGroupTypeFactory $recommendationGroupTypeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly RecommendationGroupTypeFactory $recommendationGroupTypeFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array
     */
    public function getGroups(): array
    {
        if ($this->groups === null) {
            $this->setGroups($this->buildGroups($this->getData()));
        }

        return $this->groups;
    }

    /**
     * @param array $groups
     * @return RecommendationGroupResponseInterface
     */
    public function setGroups(array $groups): RecommendationGroupResponseInterface
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @param array $node
     * @return RecommendationGroupTypeInterface[]
     */
    private function buildGroups(array $node): array
    {
        if (isset($node[RecommendationGroupTypeInterface::GROUP_KEY])) {
            return [$this->recommendationGroupTypeFactory->create(['data' => $node])];
        }

        $groups = [];
        foreach ($node as $child) {
            if (!is_array($child)) {
                continue;
            }

            array_push($groups, ...$this->buildGroups($child));
        }

        return $groups;
    }
}
