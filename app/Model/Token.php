<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $handle 
 * @property string $token 
 * @property string $expire_time 
 * @property string $scope 
 * @property \Carbon\Carbon $update_time 
 */
class Token extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'token';
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
    protected $casts = ['id' => 'int', 'update_time' => 'datetime'];
}