<?php
declare(strict_types=1);

namespace App\Service\Number;

class NumberService
{
    public function getList(array $params) :array
    {
        if (!isset($params['limit'])) $params['limit'] = 10;
        if (!isset($params['size'])) $params['size'] = 0;
        $count = 0;
        $data = [];
        return ['count' => $count, 'data' => $data];
    }
}
