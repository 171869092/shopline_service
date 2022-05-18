<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $order_id 
 * @property string $msg 
 * @property string $push_time 
 * @property int $type 
 * @property string $params 
 * @property string $return_value 
 */
class OrderPush extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_push';
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
    protected $casts = ['id' => 'int', 'type' => 'integer'];
}