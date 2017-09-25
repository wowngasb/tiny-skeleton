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
 * Class BasicMsgDao_
 * 互动消息模型 可扩展自定义类型
 * 数据表 basic_msg
 * @package app\api\Dao_
 */
class BasicMsgDao_ extends BaseDao
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
            static::$_orm_config_map[$class_name] = new OrmConfig($db_name, 'basic_msg', 'msg_id', static::$cache_time, static::$max_select, static::$debug);
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
     * INTEGER room_id 对应房间id
     */
    public static function room_id($msg_id, $default = null)
    {
        return static::getFiledById('room_id', $msg_id, $default);
    }
    /*
     * VARCHAR(16) user_id 对应消息发起者用户id
     */
    public static function user_id($msg_id, $default = null)
    {
        return static::getFiledById('user_id', $msg_id, $default);
    }
    /*
     * VARCHAR(16) msg_type 互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift
     */
    public static function msg_type($msg_id, $default = null)
    {
        return static::getFiledById('msg_type', $msg_id, $default);
    }
    /*
     * INTEGER timestamp 消息创建时间戳
     */
    public static function timestamp($msg_id, $default = null)
    {
        return static::getFiledById('timestamp', $msg_id, $default);
    }
}