<?php
declare(strict_types=1);
namespace App\Service\Store;

use App\Model\Log;

class StoreService{
    public function ex(array $params) :bool
    {
        if ($params) {
            Log::insert(['log' => json_encode($params)]);
        }
        return true;
    }
}
