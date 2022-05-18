<?php
declare(strict_types=1);
namespace App\Service\Order;
use App\Amqp\Producer\ShoplineProducer;
use App\Model\Order;
use App\Model\OrderLog;
use Hyperf\Amqp\Producer;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
class OrderService
{
    /**
     * @Inject
     * @var Order
     */
    protected $orderModel;

    /**
     * @Inject
     * @var OrderLog
     */
    protected $orderLog;

    /**
     * @Inject
     * @var Producer
     */
    protected $producer;

    public function getShoplineOrder(string $handle) :bool
    {
        $url = 'https://'. $handle .'.myshopline.com/admin/openapi/v20210901/orders.json';

        return true;
    }

    /**
     * push queue
     * @param array $array
     * @return bool
     */
    public function pushQueue(array $array) :bool
    {
        /**
         * 1, 通过shopline webhook拿到订单数据后先保存一份,先标记未处理
         * 2, 在把数据push到队列,[ShoplineConsumer]
         */
        if (!$array){
            return false;
        }
        $ins = [
            'name' => $array['name'],
            'shopline_id' => $array['id'],
            'client_details' => @json_encode($array['client_details']),
            'cancel_reason' => $array['cancel_reason'] ?? '',
            'browser_ip' => $array['browser_ip'],
            'billing_address' => json_encode($array['billing_address']),
            'cancelled_at' => $array['cancelled_at'] ?? '',
            'currency' => $array['currency'],
            'current_total_price' => $array['current_total_price'],
            'current_total_tax' => $array['current_total_tax'],
            'customer' => json_encode($array['customer']),
            'customer_locale' => $array['customer_locale'],
            'email' => $array['email'],
            'financial_status' => $array['financial_status'],
            'fulfillment_status' => $array['fulfillment_status'],
            'note' => $array['note'] ?? '',
            'order_at' => $array['order_at'],
            'line_item' => $array['line_item'],
            'payment_details' => json_encode($array['payment_details']),
            'payment_gateway_names' => isset($array['payment_gateway_names']) ? json_encode($array['payment_gateway_names']) : '',
            'phone' => $array['phone'],
            'shipping_address' => json_encode($array['shipping_address']),
            'store_id' => $array['store_id'],
            'subtotal_price' => $array['subtotal_price'],
            'tags' => $array['tags'] ?? '',
            'tax_lines' => isset($array['tax_lines']) ? json_encode($array['tax_lines']) : '',
            'tax_number' => $array['tax_number'] ?? '',
            'tax_type' => $array['tax_type'] ?? '',
            'total_tax' => $array['total_tax'],
            'total_tip_received' => $array['total_tip_received'],
            'total_weight' => $array['total_weight'],
            'updated_at' => $array['updated_at'],
            'create_time' => date('Y-m-d H:i:s')
        ];
        echo "start = \r\n";
        print_r($ins);
        $insert = $this->orderModel->insert($ins);
        if (!$insert){
            throw new \Exception('添加webhook失败');
        }
        #. 此处将产品信息push到 amqp 等待推送到 easyparcel
        return $this->producer->produce(new ShoplineProducer($ins));
    }
}
