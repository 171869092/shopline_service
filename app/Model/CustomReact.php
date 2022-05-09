<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $user_id 
 * @property string $content 
 * @property string $create_time 
 * @property string $update_time 
 */
class CustomReact extends Model
{
    /**
     * The table associated with the model.
     * 快捷回复表
     * @var string
     */
    protected $table = 'custom_react';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','content','create_time','update_time'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer'];
}