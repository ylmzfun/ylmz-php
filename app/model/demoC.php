<?php

namespace App\Model;

use Ylmz\Model;

class DemoC extends Model
{
    public function getList(): array
    {
        return self::db()->select('demo', '*');
    }
}
