<?php

declare (strict_types=1);
namespace App\Model;

use Qbhy\HyperfAuth\AuthAbility;
use Qbhy\HyperfAuth\Authenticatable;
/**
 * @property int $id
 * @property int $group_id
 * @property int $applet_id
 * @property string $user_id
 * @property string $openid
 * @property string $unionid
 * @property string $username
 * @property string $nickname
 * @property string $password
 * @property string $salt
 * @property string $email
 * @property string $wechat_numerous
 * @property string $mobile
 * @property string $avatar
 * @property string $level
 * @property int $gender
 * @property string $province
 * @property string $city
 * @property string $county
 * @property string $country
 * @property string $language
 * @property string $birthday
 * @property string $bio
 * @property string $invitecode
 * @property int $invite_num
 * @property int $score
 * @property int $successions
 * @property int $maxsuccessions
 * @property int $prevtime
 * @property int $logintime
 * @property string $loginip
 * @property int $loginfailure
 * @property string $joinip
 * @property int $jointime
 * @property string $token
 * @property string $status
 * @property string $verification
 * @property int $coin_used
 * @property int $coin_blocked
 * @property int $coin_to_be
 * @property string $coin_be
 * @property int $partner_id
 * @property int $store_id
 * @property string $starttime
 * @property string $keeptime
 * @property int $count
 * @property string $expiretime
 * @property int $expire_msg
 * @property int $vest
 * @property string $registertime
 * @property string $createtime
 * @property string $updatetime
 * @property int $parent_id
 * @property int $agency_gold_to_uid
 * @property string $source
 * @property int $is_app
 * @property string $user_source
 * @property string $amount_jc
 * @property string $volume
 * @property string $trans_code
 * @property int $zeroa
 * @property int $bid
 * @property string $dlg_prop_hash
 * @property int $mid
 * @property string $payment_pas
 * @property string $payment_mode
 * @property int $has_star
 * @property int $has_read_lpk_treaty
 * @property int $level_type
 */
class User extends Model implements Authenticatable
{
    use AuthAbility;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['applet_id', 'openid','unionid','user_id','username','nickname','avatar','city','province','country','gender','language','prevtime','logintime','jointime','loginip','joinip','status','invitecode','partner_id','parent_id','createtime','source','is_app','has_star'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'create_time' => 'datetime', 'update_time' => 'datetime', 'group_id' => 'integer', 'applet_id' => 'integer', 'user_id' => 'integer', 'gender' => 'integer', 'invite_num' => 'integer', 'score' => 'integer', 'successions' => 'integer', 'maxsuccessions' => 'integer', 'prevtime' => 'integer', 'logintime' => 'integer', 'loginfailure' => 'integer', 'jointime' => 'integer', 'coin_used' => 'integer', 'coin_blocked' => 'integer', 'coin_to_be' => 'integer', 'partner_id' => 'integer', 'store_id' => 'integer', 'count' => 'integer', 'expire_msg' => 'integer', 'vest' => 'integer', 'parent_id' => 'integer', 'agency_gold_to_uid' => 'integer', 'is_app' => 'integer', 'zeroa' => 'integer', 'bid' => 'integer', 'mid' => 'integer', 'has_star' => 'integer', 'has_read_lpk_treaty' => 'integer', 'level_type' => 'integer'];
}
