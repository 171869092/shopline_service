<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id
 * @property int $script_id
 * @property string $src
 * @property string $store_url
 * @property \Carbon\Carbon $create_time
 * @property \Carbon\Carbon $update_time
 * @property string $cache
 * @property string $display_scope
 */
class ScriptTag extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'script_tag';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['script_id','src','store_url','create_time','cache','display_scope'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'script_id' => 'integer', 'create_time' => 'datetime', 'update_time' => 'datetime'];
}

