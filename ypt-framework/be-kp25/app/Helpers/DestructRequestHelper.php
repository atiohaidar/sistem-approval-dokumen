<?php

namespace App\Helpers;

class DestructRequestHelper
{
    public function getValueRequestByLabel($valueName, $request)
    {
        $index = array_search($valueName, array_column($request, 'label'));

        if ($index !== false) {
            $value = $request[$index]['value'];
            return $value;
        } else {
            return null;
        }
    }
}
