<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends Field
{
    /**
     * @param Context $context
     * @param ComposerInformation $composerInformation
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly ComposerInformation $composerInformation,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $packageName = 'tweakwise/magento2-tweakwise-js';
        $installedMagentoPackages = $this->composerInformation->getInstalledMagentoPackages();
        $version = $installedMagentoPackages[$packageName]['version'] ?? null;

        return sprintf('<span>%s</span>', $version ?? __('Version not found'));
    }
}
