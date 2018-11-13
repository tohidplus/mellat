<?php
/**
 * Created by PhpStorm.
 * User: TD-PLUS
 * Date: 11/7/2018
 * Time: 7:05 PM
 */

namespace Tohidplus\Mellat\Facades;

use Illuminate\Support\Facades\Facade;

class Mellat extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'mellat';
    }
}
