<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\View\Template;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use SimpleXMLElement;
use Tweakwise\TweakwiseJs\Observer\ManageLayoutBlocks;

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

    public function testFeaturedRecommendationsReceiveConfiguredLanguages(): void
    {
        $recommendationsTemplate = $this->readModuleFile(
            'src/view/frontend/templates/js/widget/featured-recommendations.phtml'
        );

        $this->assertStringContainsString('$language = (string) $viewModel->getLanguage();', $recommendationsTemplate);
        $this->assertStringContainsString('$uiLanguage = (string) $viewModel->getUiLanguage();', $recommendationsTemplate);
        $this->assertStringContainsString("lang: '<?= \$escaper->escapeJs(\$language) ?>',", $recommendationsTemplate);
        $this->assertStringContainsString(
            "lang: '<?= \$escaper->escapeJs(\$uiLanguage) ?>'",
            $recommendationsTemplate
        );
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
        $sourceFile = (new ReflectionClass(ManageLayoutBlocks::class))->getFileName();
        if ($sourceFile === false) {
            throw new RuntimeException('Could not locate the Tweakwise module source.');
        }

        return dirname($sourceFile, 3) . '/' . $path;
    }
}
