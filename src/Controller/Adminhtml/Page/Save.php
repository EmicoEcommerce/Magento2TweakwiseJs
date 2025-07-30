<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Controller\Adminhtml\Page;

use Emico\AttributeLanding\Controller\Adminhtml\Page\Save as EmicoSave;

class Save extends EmicoSave
{
    /**
     * Also allow attribute_other to be saved
     * @param array $filterAttributes
     * @return array
     */
    protected function sanitizeFilterAttributes(array $filterAttributes): array
    {
        $sanitizedAttributes = parent::sanitizeFilterAttributes($filterAttributes);

        foreach ($filterAttributes as $key => $filterAttribute) {
            foreach (array_keys($filterAttribute) as $field) {
                if ($field !== 'attribute_other' || !$filterAttribute[$field]) {
                    continue;
                }

                $sanitizedAttributes[$key][$field] = $filterAttribute[$field];
            }
        }

        return $sanitizedAttributes;
    }
}
