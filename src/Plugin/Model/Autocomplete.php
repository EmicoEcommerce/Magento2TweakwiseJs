<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Plugin\Model;

use Magento\Search\Model\Autocomplete as Subject;
use Tweakwise\TweakwiseJs\Model\Config;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;

class Autocomplete
{
    /**
     * @param Config $config
     */
    public function __construct(
        private readonly Config $config
    ) {
    }

    /**
     * If search type is not the default Magento search -> disable the Magento suggestions
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetItems(Subject $subject, array $result): array
    {
        if (
            !$this->config->isEnabled() ||
            $this->config->getSearchType()->value === SearchType::MAGENTO_DEFAULT->value
        ) {
            return $result;
        }

        return [];
    }
}
