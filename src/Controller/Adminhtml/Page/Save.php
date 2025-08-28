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
        $allowedFields = ['attribute_other', 'attribute_value_other'];

        foreach ($filterAttributes as $key => $filterAttribute) {
            foreach (array_keys($filterAttribute) as $field) {
                if (!in_array($field, $allowedFields, true) || !$filterAttribute[$field]) {
                    continue;
                }

                $sanitizedAttributes[$key][$field] = $filterAttribute[$field];
            }
        }

        return $sanitizedAttributes;
    }
}
