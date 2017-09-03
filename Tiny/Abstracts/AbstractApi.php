<?php

namespace Tiny\Abstracts;

use Tiny\Traits\CacheTrait;
use Tiny\Traits\LogTrait;
use Tiny\Traits\RpcTrait;

abstract class AbstractApi extends AbstractContext
{

    protected static $_API_LIMIT_KET = 'BaseApiRateLimit';

    /**
     * 过滤常见的 API参数  子类按照顺序依次调用父类此方法
     * @param array $params
     * @return array 处理后的 API 执行参数 将用于调用方法
     */
    public function beforeApi(array $params)
    {
        return (array)$params;
    }

    /*
     * 不同的API会有不同的调用次数限制, 请检查返回 header 中的如下字段
     * header 字段	描述
     */
    public static function _apiLimitByTimeRange($api_key, $range_sec = 300, $max_num = 100, $tag = 'all')
    {
        $testRst = self::_apiLimitByTimeRangeTest($api_key, $range_sec, $max_num, $tag);
        foreach ($testRst as $key => $val) {
            header("X-Rate-{$key}: {$val}");
        }

        if (!empty($testRst['Remaining']) && $testRst['Remaining'] < 0) {
            header("http/1.1 403 Forbidden");
            exit();
        }
        /*
        header("X-Rate-LimitTag: {$tag}");  //限制规则分类 all 代表总数限制
        header("X-Rate-LimitNum: {$max_num}");  //限制调用次数，超过后服务器会返回 403 错误
        header("X-Rate-Remaining: {$remaining}");  //当时间段中还剩下的调用次数
        header("X-Rate-TimeRange: {$range_sec}");  //限制时间范围长度 单位 秒
        header("X-Rate-TimeReset: {$reset_date}");  //限制重置时间 unix time
        */
    }

    /**
     * API 调用次数限制
     * @param $api_key
     * @param int $range_sec
     * @param int $max_num
     * @param string $tag
     * @return array
     */
    public static function _apiLimitByTimeRangeTest($api_key, $range_sec = 300, $max_num = 100, $tag = 'all')
    {
        $max_num = intval($max_num);
        $range_sec = intval($range_sec);
        $range_sec = $range_sec > 0 ? $range_sec : 1;
        $time_count = intval(time() / $range_sec);
        $max_num = $max_num > 0 ? $max_num : 1;
        $rKey = self::$_API_LIMIT_KET . ":{$api_key}:num_{$tag}_{$time_count}_{$range_sec}";

        $mCache = self::getCacheInstance();
        $tmp = $mCache->getItem($rKey)->get();
        $count = intval($tmp) > 0 ? intval($tmp) + 1 : 1;
        $itemObj = $mCache->getItem($rKey)->set($count)->expiresAfter(2 * $range_sec);  // 多保留一段时间
        $mCache->save($itemObj);

        return [
            'LimitTag' => $tag,
            'LimitNum' => $max_num,
            'Remaining' => $max_num - $count,
            'TimeRange' => $range_sec,
            'TimeReset' => gmdate('D, d M Y H:i:s T', ($time_count + 1) * $range_sec),
        ];
    }

    /**
     *  注册回调函数  回调参数为 callback($this, $action, $params, $result or $ex, $callback)
     *  1、apiResult    api执行完毕返回结果时触发
     *  2、apiException    api执行发生异常时触发
     * @param string $event
     * @return bool
     */
    protected static function isAllowedEvent($event)
    {
        static $allow_event = ['apiResult', 'apiException'];
        return in_array($event, $allow_event);
    }

    public function doneApi($action, $params, $result, $callback){
        static::fire('apiResult', [$this, $action, $params, $result, $callback]);
    }

    public function exceptApi($action, $params, $ex, $callback){
        static::fire('apiException', [$this, $action, $params, $ex, $callback]);
    }

}