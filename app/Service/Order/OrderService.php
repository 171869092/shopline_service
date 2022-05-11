<?php
declare(strict_types=1);
namespace App\Service\Order;
use App\Model\OrderLog;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
class OrderService
{
    /**
     */
    protected $model;

    /**
     * @Inject
     * @var OrderLog
     */
    protected $orderLog;

    public function webHook(array $params) :bool
    {
        return true;
    }
}
