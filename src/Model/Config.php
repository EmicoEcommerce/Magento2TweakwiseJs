<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Tweakwise\TweakwiseJs\Model\Enum\SearchType;

class Config
{
    private const XML_PATH_ENABLED = 'tweakwise/tweakwisejs/general/enabled';
    private const XML_PATH_INSTANCE_KEY = 'tweakwise/tweakwisejs/general/instance_key';

    public const XML_PATH_MERCHANDISING_ENABLED = 'tweakwise/tweakwisejs/merchandising/enabled';

    private const XML_PATH_SEARCH_TYPE = 'tweakwise/tweakwisejs/search/type';

    private const XML_PATH_GROUPED_EXPORT = 'tweakwise/export/grouped_export_enabled';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getInstanceKey(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INSTANCE_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $scopeType
     * @param null|int|string $scopeCode
     * @return bool
     */
    public function isMerchandisingEnabled(
        string $scopeType = ScopeInterface::SCOPE_STORE,
        mixed $scopeCode = null
    ): bool {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_MERCHANDISING_ENABLED, $scopeType, $scopeCode);
    }

    /**
     * @return SearchType
     */
    public function getSearchType(): SearchType
    {
        return SearchType::tryFrom(
            $this->scopeConfig->getValue(self::XML_PATH_SEARCH_TYPE, ScopeInterface::SCOPE_STORE)
        ) ?? SearchType::MAGENTO_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isGroupedExportEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GROUPED_EXPORT, ScopeInterface::SCOPE_STORE);
    }
}
