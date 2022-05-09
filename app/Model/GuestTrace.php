<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $user_id 
 * @property int $guest_id 
 * @property string $store_url 
 * @property int $platform 
 * @property int $is_chat 
 * @property string $total_date 
 * @property \Carbon\Carbon $create_time 
 * @property \Carbon\Carbon $update_time 
 */
class GuestTrace extends Model
{
    /**
     * The table associated with the model.
     * 游客轨迹表
     * @var string
     */
    protected $table = 'guest_trace';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["user_id", "guest_id", "store_url", "platform", "is_chat", "total_date", "create_time", "update_time"];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'guest_id' => 'integer', 'platform' => 'integer', 'is_chat' => 'integer', 'create_time' => 'datetime', 'update_time' => 'datetime'];
}