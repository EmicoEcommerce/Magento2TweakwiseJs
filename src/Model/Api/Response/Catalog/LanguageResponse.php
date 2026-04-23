<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response\Catalog;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Tweakwise\TweakwiseJs\Api\Data\Api\Response\Catalog\LanguageResponseInterface;
use Tweakwise\TweakwiseJs\Model\Api\Response;
use Tweakwise\TweakwiseJs\Model\Api\Type\LanguageType;
use Tweakwise\TweakwiseJs\Model\Api\Type\LanguageTypeFactory;

class LanguageResponse extends Response implements LanguageResponseInterface
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param LanguageTypeFactory $languageTypeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly LanguageTypeFactory $languageTypeFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        $languages = $this->getData(self::LANGUAGES);
        if ($languages) {
            return $languages;
        }

        $language = $this->getData(self::LANGUAGE);
        if ($language) {
            if (isset($language['languageid']) && !isset($language[0])) {
                $language = [$language];
            }

            $values = [];
            foreach ($language as $value) {
                if (!$value instanceof LanguageType) {
                    $value = $this->languageTypeFactory->create(['data' => $value]);
                }

                $values[] = $value;
            }

            $this->setLanguages($values);
        }

        return $this->getData(self::LANGUAGES);
    }

    /**
     * @param array $languages
     * @return LanguageResponse
     */
    public function setLanguages(array $languages): LanguageResponseInterface
    {
        return $this->setData(self::LANGUAGES, $languages);
    }
}
