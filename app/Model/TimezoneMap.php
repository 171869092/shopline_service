<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $country_code 
 * @property string $country_name 
 * @property string $time_zone 
 */
class TimezoneMap extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'timezone_map';
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
    protected $casts = ['id' => 'integer'];
}