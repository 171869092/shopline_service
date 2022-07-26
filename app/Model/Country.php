<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $code 
 * @property string $name 
 */
class Country extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'country';
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
    protected $casts = ['id' => 'int'];
}