<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id
 * @property string $rate_id
 * @property string $service_detail
 * @property string $service_id
 * @property string $service_type
 * @property string $courier_id
 * @property string $courier_name
 * @property string $courier_logo
 * @property string $scheduled_start_date
 * @property string $pickup_date
 * @property string $delivery
 * @property string $price
 * @property string $addon_price
 * @property string $shipment_price
 * @property string $service_name
 * @property string $dropoff_point
 * @property string $pickup_point
 * @property string $conutry
 */
class Service extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rate_id','service_detail','service_id','service_type','courier_id','courier_name','courier_logo','scheduled_start_date','pickup_date','delivery','price','addon_price','shipment_price','service_name','dropoff_point','pickup_point','country'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int'];
}
