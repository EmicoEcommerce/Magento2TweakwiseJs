<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api\Response\Catalog;

use Tweakwise\TweakwiseJs\Model\Api\Response;

class LanguageResponse extends Response
{
    /**
     * Format response to a list of languages
     *
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->normalizeArray($this->_data, 'language');
    }
}
