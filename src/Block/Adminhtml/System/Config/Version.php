<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends Field
{
    /**
     * Version constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setTemplate('Tweakwise_TweakwiseJs::system/config/version.phtml');
    }

    /**
     * Prepare the HTML for the version display
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }


    /**
     * Get the version of the Tweakwise JS package from composer.lock
     *
     * @return string
     */
    public function getComposerVersion(): string
    {
        $packageName = 'tweakwise/magento2-tweakwise-js';

        $lockFilePath = BP . '/composer.lock';
        if (!file_exists($lockFilePath)) {
            return 'composer.lock not found';
        }

        $content = file_get_contents($lockFilePath);
        $data = json_decode($content, true);

        foreach (['packages', 'packages-dev'] as $section) {
            if (empty($data[$section])) {
                continue;
            }

            foreach ($data[$section] as $package) {
                if ($package['name'] === $packageName) {
                    return $package['version'];
                }
            }
        }

        return 'Version not found';
    }
}
