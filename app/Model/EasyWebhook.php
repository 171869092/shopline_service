<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $topic 
 * @property string $payload 
 * @property string $event_id 
 * @property \Carbon\Carbon $create_time 
 */
class EasyWebhook extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'easy_webhook';
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
    protected $casts = ['id' => 'int', 'create_time' => 'datetime'];
}