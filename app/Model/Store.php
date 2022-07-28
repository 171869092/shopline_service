<?php

declare (strict_types=1);
namespace App\Model;

use Qbhy\HyperfAuth\AuthAbility;
use Qbhy\HyperfAuth\Authenticatable;
/**
 * @property int $id 
 * @property string $shopline_id 
 * @property string $store_name 
 * @property string $token 
 * @property int $biz_store_status 
 * @property string $created_at 
 * @property string $currency 
 * @property string $customer_email 
 * @property string $domain 
 * @property string $email 
 * @property string $iana_timezone 
 * @property string $language 
 * @property string $location_country_code 
 * @property string $standard_logo 
 * @property string $updated_at 
 * @property \Carbon\Carbon $create_time 
 * @property \Carbon\Carbon $update_time 
 * @property string $easy_api 
 * @property string $easy_auth_key 
 * @property string $easy_service_id 
 * @property int $sync_status 
 * @property string $easy_send_first_name 
 * @property string $easy_send_last_name 
 * @property string $easy_send_phone 
 * @property string $easy_address 
 * @property string $easy_post_code 
 */
class Store extends Model implements Authenticatable
{
    use AuthAbility;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'store';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['store_url', 'user_id', 'platform', 'create_time', 'api_token'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'biz_store_status' => 'integer', 'create_time' => 'datetime', 'update_time' => 'datetime', 'sync_status' => 'integer'];
}