<?php

declare(strict_types=1);

namespace Tweakwise\Test\Unit\View\Template;

use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class SearchLayoutTest extends TestCase
{
    public function testSearchListerIsRenderedInTheVisibleColumnsContainer(): void
    {
        $layoutPath = dirname(__DIR__, 4) . '/src/view/frontend/layout/tweakwisejs_search.xml';
        $layout = simplexml_load_file($layoutPath);
        if (!$layout instanceof SimpleXMLElement) {
            $this->fail('Could not parse the Tweakwise search layout XML.');
        }

        $lister = $layout->xpath(
            '/page/body/referenceContainer[@name="columns"]/block[@name="tweakwise-js-search-lister-init"]'
        );

        $this->assertCount(1, $lister);
    }
}
