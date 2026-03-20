<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\ViewModel;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use Tweakwise\TweakwiseJs\Helper\Data;
use Tweakwise\TweakwiseJs\Model\Config;

class AttributeLanding extends Base
{
    protected const FILTER_PREFIX = 'tn_fk_';

    /**
     * @param Config $config
     * @param Data $dataHelper
     * @param Serialize $serializer
     * @param Json $jsonSerializer
     */
    public function __construct(
        Config $config,
        Data $dataHelper,
        private readonly Serialize $serializer,
        private readonly Json $jsonSerializer,
    ) {
        parent::__construct($config, $dataHelper);
    }

    /**
     * @param string $filterAttributesSerialized
     * @return string
     */
    public function getParameters(string $filterAttributesSerialized): string
    {
        return http_build_query($this->getFilterAttributes($filterAttributesSerialized));
    }

    /**
     * @param string $filterAttributesSerialized
     * @return string
     */
    public function getFilters(string $filterAttributesSerialized): string
    {
        return $this->jsonSerializer->serialize($this->getFilterAttributes($filterAttributesSerialized, true));
    }

    /**
     * @param string $filterAttributesSerialized
     * @param bool $addPrefix
     * @return array
     */
    protected function getFilterAttributes(string $filterAttributesSerialized, bool $addPrefix = false): array
    {
        $filterAttributes = $this->serializer->unserialize($filterAttributesSerialized);
        $filters = [];
        // @phpstan-ignore-next-line
        foreach ($filterAttributes as $filterAttribute) {
            $filters[$this->getAttribute($filterAttribute, $addPrefix)] = $this->getValue($filterAttribute);
        }

        return $filters;
    }

    /**
     * @param array $filterAttribute
     * @param bool $addPrefix
     * @return string
     */
    protected function getAttribute(array $filterAttribute, bool $addPrefix): string
    {
        $attribute = $filterAttribute['attribute'] === Data::OTHER_ATTRIBUTE_VALUE
            ? $filterAttribute['attribute_other']
            : $filterAttribute['attribute'];

        return $addPrefix
            ? sprintf('%s%s', self::FILTER_PREFIX, $attribute)
            : $attribute;
    }

    /**
     * @param array $filterAttribute
     * @return string
     */
    protected function getValue(array $filterAttribute): string
    {
        return $filterAttribute['value'] === Data::OTHER_ATTRIBUTE_VALUE
            ? $filterAttribute['attribute_value_other']
            : $filterAttribute['value'];
    }
}
