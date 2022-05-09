<?php

declare (strict_types=1);
namespace App\Model;

use Qbhy\HyperfAuth\AuthAbility;
use Qbhy\HyperfAuth\Authenticatable;
/**
 * @property int $id 
 * @property int $user_id 
 * @property string $guest_name 
 * @property string $email 
 * @property string $phone 
 * @property string $note 
 * @property string $referer_title 
 * @property string $referer 
 * @property string $location 
 * @property string $user_agent 
 * @property string $browser 
 * @property string $device 
 * @property string $ip 
 * @property string $time_zone 
 * @property string $create_time 
 * @property string $update_time 
 */
class GuestUser extends Model implements Authenticatable
{
    use AuthAbility;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guest_user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["is_contact","user_id", "guest_name", "email", "phone", "note", "referer_title", "referer", "location", "user_agent", "browser", "device", "ip", "time_zone", "create_time", "update_time"];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer'];
}