<?php

namespace App\Library\Proxy\Adapters;

use App\Library\Proxy;

abstract class AdapterAbstract implements Contracts\AdapterInterface
{
    public static function getAdapterName(): string
    {
        $path = explode('\\', get_called_class());

        return array_pop($path);
    }
}
