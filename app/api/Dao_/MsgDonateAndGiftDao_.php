<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2017-09
 */
namespace app\api\Dao_;

use app\api\base\BaseDao;
use Tiny\Application;
use Tiny\OrmQuery\OrmConfig;


/**
 * Class MsgDonateAndGiftDao_
 * 打赏及赠送礼物消息
 * 数据表 msg_donate_and_gift
 * @package app\api\Dao_
 */
class MsgDonateAndGiftDao_ extends BaseDao
{


    ####################################
    ########### 自动生成代码 ############
    ####################################

    /**
     * 使用这个特性的子类必须 实现这个方法 返回特定格式的数组 表示数据表的配置
     * @return OrmConfig
     */
    protected static function getOrmConfig()
    {
        $class_name = get_called_class();
        if (!isset(static::$_orm_config_map[$class_name])) {
            $db_config = Application::config('ENV_DB');
            $db_name = !empty($db_config['database']) ? $db_config['database'] : 'test';
            static::$_orm_config_map[$class_name] = new OrmConfig($db_name, 'msg_donate_and_gift', 'msg_id', static::$cache_time, static::$max_select, static::$debug);
        }
        return static::$_orm_config_map[$class_name];
    }
    /*
     * INTEGER msg_id 互动消息 唯一id
     */
    public static function msg_id($msg_id, $default = null)
    {
        return static::getFiledById('msg_id', $msg_id, $default);
    }
    /*
     * VARCHAR(16) msg_type 互动消息类型 固定为 donate_and_gift
     */
    public static function msg_type($msg_id, $default = null)
    {
        return static::getFiledById('msg_type', $msg_id, $default);
    }
    /*
     * VARCHAR(16) target_user_id 消息目标用户 用户id 用于处理打赏给指定用户 
     */
    public static function target_user_id($msg_id, $default = null)
    {
        return static::getFiledById('target_user_id', $msg_id, $default);
    }
    /*
     * VARCHAR(16) trade_type 打赏或礼物类型 
     */
    public static function trade_type($msg_id, $default = null)
    {
        return static::getFiledById('trade_type', $msg_id, $default);
    }
    /*
     * FLOAT trade_num 打赏或礼物数量 
     */
    public static function trade_num($msg_id, $default = null)
    {
        return static::getFiledById('trade_num', $msg_id, $default);
    }
    /*
     * VARCHAR(512) content_text 消息文本内容
     */
    public static function content_text($msg_id, $default = null)
    {
        return static::getFiledById('content_text', $msg_id, $default);
    }
}