<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $name 
 * @property string $shopline_id 
 * @property string $client_details 
 * @property string $cancel_reason 
 * @property string $browser_ip 
 * @property string $billing_address 
 * @property string $cancelled_at 
 * @property string $currency 
 * @property string $current_total_price 
 * @property string $current_total_tax 
 * @property string $line_item 
 * @property string $customer 
 * @property string $customer_locale 
 * @property string $email 
 * @property string $financial_status 
 * @property string $fulfillment_status 
 * @property string $note 
 * @property string $order_at 
 * @property string $payment_details 
 * @property string $payment_gateway_names 
 * @property string $phone 
 * @property string $shipping_address 
 * @property string $store_id 
 * @property string $subtotal_price 
 * @property string $tags 
 * @property string $tax_lines 
 * @property string $tax_number 
 * @property string $tax_type 
 * @property string $total_tax 
 * @property string $total_tip_received 
 * @property string $total_weight 
 * @property string $updated_at 
 * @property \Carbon\Carbon $create_time 
 * @property \Carbon\Carbon $update_time 
 * @property int $is_exec 
 */
class Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'create_time' => 'datetime', 'update_time' => 'datetime', 'is_exec' => 'integer'];
}