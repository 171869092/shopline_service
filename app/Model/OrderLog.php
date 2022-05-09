<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $order_id 
 * @property int $user_id 
 * @property string $context 
 * @property int $status 
 * @property string $create_at 
 */
class OrderLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_log';
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
    protected $casts = ['id' => 'int', 'user_id' => 'integer', 'status' => 'integer'];
}