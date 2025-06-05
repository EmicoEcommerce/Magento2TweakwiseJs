<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends Field
{
    protected $_template = 'Tweakwise_TweakwiseJs::system/config/version.phtml';

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getComposerVersion()
    {
        $packageName = 'tweakwise/magento2-tweakwise-js';

        $lockFilePath = BP . '/composer.lock';
        if (!file_exists($lockFilePath)) {
            return 'composer.lock not found';
        }

        $content = file_get_contents($lockFilePath);
        $data = json_decode($content, true);

        foreach (['packages', 'packages-dev'] as $section) {
            if (!empty($data[$section])) {
                foreach ($data[$section] as $package) {
                    if ($package['name'] === $packageName) {
                        return $package['version'];
                    }
                }
            }
        }

        return 'Version not found';
    }
}
