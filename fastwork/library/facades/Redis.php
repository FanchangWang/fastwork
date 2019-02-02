<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/2
 * Time: 10:27
 */

namespace fastwork\facades;


use fastwork\Facade;

/**
 * Class Redis
 * @see \fastwork\cache\Redis
 * @method Redis push(object $redis) static
 * @method Redis pop(object $redis) static
 * @method Redis setDefer(bool $bool=true) static
 *
 * //base
 * @method Redis expire(string $key, int $ttl) static
 * @method Redis keys(string $key); static
 *
 *
 * //string
 * @method Redis get(string $key) static
 * @method Redis set(string $key, string $value, int $timeout = 0) static
 * @method Redis setex(string $key, int $ttl, string $value) static
 * @method Redis psetex(string $key, int $expire, string $value) static
 * @method Redis setnx(string $key, string $value) static
 * @method Redis del(string ... $key) static
 * @method Redis delete(string ... $key) static
 * @method Redis getSet(string $key, string $value) static
 * @method Redis exists(string $key) static
 * @method Redis incr(string $key) static
 * @method Redis incrBy(string $key, int $increment) static
 * @method Redis incrByFloat(string $key, float $increment) static
 * @method Redis decr(string $key) static
 * @method Redis decrBy(string $key, int $increment) static
 * @method Redis mget(array ... $keys) static
 * @method Redis append(string $key, string $value) static
 * @method Redis getRange(string $key, int $start, int $end) static
 * @method Redis setRange(string $key, int $offset, string $value) static
 * @method Redis strlen(string $key) static
 * @method Redis getBit(string $key, int $offset) static
 * @method Redis setBit(string $key, int $offset, bool $bool) static
 * @method Redis mset(array $keyValue) static
 *
 * //list
 * @method Redis lPush(string $key, string $value) static
 * @method Redis rPush(string $key, string $value) static
 * @method Redis lPushx(string $key, string $value) static
 * @method Redis rPushx(string $key, string $value) static
 * @method Redis lPop(string $key) static
 * @method Redis rPop(string $key) static
 * @method Redis blpop(array $keys, int $timeout) static
 * @method Redis brpop(array $keys, int $timeout) static
 * @method Redis lSize(string $key) static
 * @method Redis lGet(string $key, int $index) static
 * @method Redis lSet(string $key, int $index, string $value) static
 * @method Redis IRange(string $key, int $start, int $end) static
 * @method Redis lTrim(string $key, int $start, int $end) static
 * @method Redis lRem(string $key, string $value, int $count) static
 * @method Redis rpoplpush(string $srcKey, string $dstKey) static
 * @method Redis brpoplpush(string $srcKey, string $detKey, int $timeout) static
 *
 * //set
 * @method Redis sAdd(string $key, string $value)
 * @method Redis sRem(string $key, string $value)
 * @method Redis sMove(string $srcKey, string $dstKey, string $value)
 * @method Redis sIsMember(string $key, string $value)
 * @method Redis sCard(string $key)
 * @method Redis sPop(string $key)
 * @method Redis sRandMember(string $key)
 * @method Redis sInter(string ... $keys)
 * @method Redis sInterStore(string $dstKey, string ... $srcKey)
 * @method Redis sUnion(string ... $keys)
 * @method Redis sUnionStore(string $dstKey, string ... $srcKey)
 * @method Redis sDiff(string ... $keys)
 * @method Redis sDiffStore(string $dstKey, string ... $srcKey)
 * @method Redis sMembers(string $key)
 *
 * //zset
 * @method Redis zAdd(string $key, double $score, string $value)
 * @method Redis zRange(string $key, int $start, int $end)
 * @method Redis zDelete(string $key, string $value)
 * @method Redis zRevRange(string $key, int $start, int $end)
 * @method Redis zRangeByScore(string $key, int $start, int $end, array $options = [])
 * @method Redis zCount(string $key, int $start, int $end)
 * @method Redis zRemRangeByScore(string $key, int $start, int $end)
 * @method Redis zRemRangeByRank(string $key, int $start, int $end)
 * @method Redis zSize(string $key)
 * @method Redis zScore(string $key, string $value)
 * @method Redis zRank(string $key, string $value)
 * @method Redis zRevRank(string $key, string $value)
 * @method Redis zIncrBy(string $key, double $score, string $value)
 *
 * //hash
 * @method Redis hSet(string $key, string $hashKey, string $value)
 * @method Redis hSetNx(string $key, string $hashKey, string $value)
 * @method Redis hGet(string $key, string $hashKey)
 * @method Redis hLen(string $key)
 * @method Redis hDel(string $key, string $hashKey)
 * @method Redis hKeys(string $key)
 * @method Redis hVals(string $key)
 * @method Redis hGetAll(string $key)
 * @method Redis hExists(string $key, string $hashKey)
 * @method Redis hIncrBy(string $key, string $hashKey, int $value)
 * @method Redis hIncrByFloat(string $key, string $hashKey, float $value)
 * @method Redis hMset(string $key, array $keyValue)
 * @method Redis hMGet(string $key, array $hashKeys)
 */

class Redis extends Facade
{

    protected static function getFacadeClass()
    {
        return 'redis';
    }
}