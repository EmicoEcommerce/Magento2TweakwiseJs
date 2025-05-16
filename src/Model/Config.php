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

    private const XML_PATH_MERCHANDISING_ENABLED = 'tweakwise/tweakwisejs/merchandising/enabled';

    private const XML_PATH_SEARCH_TYPE = 'tweakwise/tweakwisejs/search/type';
    private const XML_PATH_EVENTS_ENABLED = 'tweakwise/tweakwisejs/events/enabled';
    private const XML_PATH_EVENTS_COOKIE_NAME = 'tweakwise/tweakwisejs/events/cookie_name';

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
     * @return bool
     */
    public function isMerchandisingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_MERCHANDISING_ENABLED, ScopeInterface::SCOPE_STORE);
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
    public function isEventsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_EVENTS_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getEventsCookieName(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EVENTS_COOKIE_NAME, ScopeInterface::SCOPE_STORE);
    }
}
