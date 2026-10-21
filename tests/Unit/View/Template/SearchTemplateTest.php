<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\View\Template;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleXMLElement;

class SearchTemplateTest extends TestCase
{
    public function testSuggestionsInputIsClaimedBeforeWaitingForTheSdk(): void
    {
        $searchTemplate = $this->readModuleFile('src/view/frontend/templates/js/search.phtml');
        $inputReset = strpos($searchTemplate, "window['twn-starter-config'].input = null;");
        $sdkWait = strpos($searchTemplate, 'window.tweakwiseJs.onReady');

        $this->assertNotFalse($inputReset);
        $this->assertNotFalse($sdkWait);
        $this->assertLessThan($sdkWait, $inputReset);
    }

    public function testSuggestionsAndNavigationUseTheSameHashHistoryDefault(): void
    {
        $defaultTemplate = $this->readModuleFile('src/view/frontend/templates/js/default.phtml');
        $searchTemplate = $this->readModuleFile('src/view/frontend/templates/js/search.phtml');

        $this->assertMatchesRegularExpression(
            "/getHistoryMode\\(\\)\\s*\\{\\s*return window\\['twn-starter-config'\\]\\.history \\|\\| 'hash';/s",
            $defaultTemplate
        );
        $this->assertStringContainsString('this.getHistoryMode()', $defaultTemplate);
        $this->assertStringContainsString('window.tweakwiseJs.getHistoryMode()', $searchTemplate);
    }

    public function testSearchListerIsRenderedInTheVisibleColumnsContainer(): void
    {
        $layoutPath = $this->moduleFile('src/view/frontend/layout/tweakwisejs_search.xml');
        $layout = simplexml_load_file($layoutPath);
        if (!$layout instanceof SimpleXMLElement) {
            $this->fail('Could not parse the Tweakwise search layout XML.');
        }

        $lister = $layout->xpath(
            '/page/body/referenceContainer[@name="columns"]/block[@name="tweakwise-js-search-lister-init"]'
        );

        $this->assertCount(1, $lister);
    }

    private function readModuleFile(string $path): string
    {
        $contents = file_get_contents($this->moduleFile($path));
        if ($contents === false) {
            throw new RuntimeException(sprintf('Could not read module file "%s".', $path));
        }

        return $contents;
    }

    private function moduleFile(string $path): string
    {
        return dirname(__DIR__, 4) . '/' . $path;
    }
}
