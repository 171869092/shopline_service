<?php


namespace App\Collector;


use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

class SocketUserCollector
{
    protected static $prefix = "socket_io_user";

    /**
     * 绑定连接和用户
     * @param int $fd
     * @param array $user
     */
    public static function bindUser($fd, array $user) {
        $redis = static::getConnect();
        $redis->multi();
        $redis->hMSet(static::$prefix . ":fd:{$fd}", $user);
        $redis->sAdd(static::$prefix . ":user_id:{$user["symbol"]}_{$user["id"]}", $fd);
        // 游客在线
        if ($user["symbol"] == "2" && !empty($user["user_id"])) {
            $redis->zAdd(static::$prefix . ":guest_online", $user["user_id"], $user["id"]);
        }
        $redis->exec();
    }

    /**
     * 修改用户信息
     * @param $fd
     * @param array $user
     * @param bool $isDel
     * @return bool|int
     */
    public static function modifyUser($fd, array $user, $isDel = false) {
        $redis = static::getConnect();
        if ($isDel) {
            return $redis->hDel(static::$prefix . ":fd:{$fd}", ...$user);
        }
        return $redis->hMSet(static::$prefix . ":fd:{$fd}", $user);
    }

    /**
     * 是否已经有绑定指定用户
     * @param int $symbol
     * @param int $userId
     * @return bool
     */
    public static function hasUser($symbol, $userId) {
        $redis = static::getConnect();
        return $redis->sCard(static::$prefix . ":user_id:{$symbol}_{$userId}") >= 1;
    }

    /**
     * 通过连接获取用户
     * @param $fd
     * @param null|string|array $field
     * @return array|null
     */
    public static function getUser($fd, $field = null) {
        $redis = static::getConnect();
        if (is_null($field)) {
            $user = $redis->hGetAll(static::$prefix . ":fd:{$fd}");
        } elseif (is_array($field) || is_string($field)) {
            $user = $redis->hMGet(static::$prefix . ":fd:{$fd}", (array)$field);
        }
        return $user ?? null;
    }

    /**
     * 获取用户信息
     * @param int $symbol
     * @param int $userId
     * @return array
     */
    public static function getUserById($symbol, $userId) {
        $redis = static::getConnect();
        $prefix = static::$prefix;
        // 原子性处理
        $info = $redis->eval(<<<EOF
local fd = redis.call('sRandMember','{$prefix}:user_id:{$symbol}_{$userId}')
if fd == false then
    return {}
end
return redis.call('hGetAll','{$prefix}:fd:'..fd)
EOF);
        $result = [];
        $len = count($info);
        for($i = 0;$i < $len;$i+=2) {
            $result[$info[$i]] = $info[$i+1] ?? null;
        }
        return $result;
    }

    /**
     * 获取在线游客ID
     * @param : $userId 客服ID
     * @return array
     */
    public static function getOnlineGuestIds($userId) {
        $redis = static::getConnect();
        return $redis->zRangeByScore(static::$prefix . ":guest_online", (string)$userId, (string)$userId);
    }

    /**
     * 获取在线游客数量
     * @param : $userId 客服ID
     * @return int
     */
    public static function getOnlineGuestCount($userId) {
        $redis = static::getConnect();
        return $redis->zCount(static::$prefix . ":guest_online", (string)$userId, (string)$userId);
    }

    /**
     * 通过用户ID获取连接（一个账户多个连接）
     * @param int $symbol
     * @param int $userId
     * @return array
     */
    public static function getUserFd($symbol, $userId) {
        $redis = static::getConnect();
        return $redis->sMembers(static::$prefix . ":user_id:{$symbol}_{$userId}");
    }

    /**
     * 获取全部连接
     * @param int $cursor
     * @param int $count
     * @return array
     */
    public static function getAllFdToScan(?int &$cursor, $count = 10000) {
        $redis = static::getConnect();
        $keys = $redis->scan($cursor, static::$prefix . ":fd:*", $count);
        return array_map(function ($value) {
            return (int)substr($value, strlen(static::$prefix . ":fd:"));
        }, $keys);
    }

    /**
     * 清除连接信息
     * @param int|null $fd
     */
    public static function clear(?int $fd = null): void
    {
        $redis = static::getConnect();
        $prefix = static::$prefix;
        // 原子性处理
        $redis->eval(<<<EOF
if KEYS[1] == '' then
    local user_keys = redis.call('keys','{$prefix}:*')
    for i1, v1 in ipairs(user_keys) do
       redis.call('del',v1)
    end
    return 1
else
    local user_info = redis.call('hMGet','{$prefix}:fd:'..KEYS[1], 'id', 'symbol')
    if user_info[2] == false or user_info[1] == false then
        return 3
    end
    local user_socket_key = '{$prefix}:user_id:'..user_info[2]..'_'..user_info[1]
    local user_fds = redis.call('sCard',user_socket_key)
    if user_fds > 1 then
        redis.call('sRem',user_socket_key,KEYS[1])
    else
        redis.call('del',user_socket_key)
    end
    if user_info[2] == '2' then
        local guest_online = '{$prefix}:guest_online'
        redis.call('zRem',guest_online,user_info[1])
        if redis.call('zCard',guest_online) <= 0 then
            redis.call('del',guest_online)
        end
    end
    redis.call('del','{$prefix}:fd:'..KEYS[1])
    return 2
end
EOF
, [$fd], 1);
    }

    /**
     * @return Redis
     */
    private static function getConnect() :Redis {
        return ApplicationContext::getContainer()->get(Redis::class);
    }
}