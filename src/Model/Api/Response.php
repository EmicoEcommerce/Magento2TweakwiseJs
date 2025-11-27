<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Model\Api;

use Magento\Framework\Model\AbstractModel;

class Response extends AbstractModel
{
    protected function normalizeArray(array $data, $key)
    {
        if (isset($data[$key])) {
            $data = $data[$key];
        }

        if (empty($data)) {
            return [];
        }

        if (!is_array($data) || !isset($data[0])) {
            $data = [$data];
        }

        return $data;
    }
}
