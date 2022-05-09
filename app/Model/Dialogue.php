<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $user_id 
 * @property int $guest_id 
 * @property int $target 
 * @property string $user_name 
 * @property string $guest_name 
 * @property int $type 
 * @property string $content 
 * @property array $attachment 
 * @property array $extra 
 * @property int $guest_read 
 * @property int $user_read 
 * @property \Carbon\Carbon $create_time 
 * @property \Carbon\Carbon $update_time 
 */
class Dialogue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dialogue';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["user_id", "guest_id", "target", "user_name", "guest_name", "type", "content", "attachment", "extra", "guest_read", "user_read", "create_time", "update_time"];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'guest_id' => 'integer', 'target' => 'integer', 'type' => 'integer', 'guest_read' => 'integer', 'user_read' => 'integer', 'create_time' => 'datetime', 'update_time' => 'datetime', 'attachment' => 'array', 'extra' => 'array'];
}