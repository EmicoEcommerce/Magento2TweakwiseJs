<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Plugin\Model\Page;

use Emico\AttributeLanding\Model\Page\DataProvider as Subject;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\UrlInterface;

class DataProvider
{
    /**
     * @param FrontNameResolver $frontNameResolver
     * @param UrlInterface $backendUrl
     */
    public function __construct(
        private readonly FrontNameResolver $frontNameResolver,
        private readonly UrlInterface $backendUrl,
    ) {
    }

    /**
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfigData(Subject $subject, array $result): array
    {
        $result['admin_url'] = sprintf(
            '%s%s/',
            $this->backendUrl->getBaseUrl(),
            $this->frontNameResolver->getFrontName()
        );
        return $result;
    }
}
