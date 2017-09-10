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
 * Class BasicRoomDao_
 * 直播活动基本信息 每个条目对应一个活动
 * 数据表 basic_room
 * @package app\api\Dao_
 */
class BasicRoomDao_ extends BaseDao
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
            $db_config = Application::app()->getEnv('ENV_DB');
            $db_name = !empty($db_config['database']) ? $db_config['database'] : 'test';
            static::$_orm_config_map[$class_name] = new OrmConfig($db_name, 'basic_room', 'room_id', static::$cache_time, static::$max_select);
        }
        return static::$_orm_config_map[$class_name];
    }
    /*
     * INTEGER room_id 直播活动 唯一id
     */
    public static function room_id($room_id, $default = null)
    {
        return static::getFiledById('room_id', $room_id, $default);
    }
    /*
     * VARCHAR(32) room_title 直播活动标题
     */
    public static function room_title($room_id, $default = null)
    {
        return static::getFiledById('room_title', $room_id, $default);
    }
    /*
     * VARCHAR(32) chat_topic DMS topic 互动消息话题
     */
    public static function chat_topic($room_id, $default = null)
    {
        return static::getFiledById('chat_topic', $room_id, $default);
    }
    /*
     * VARCHAR(64) dms_sub_key DMS sub_key 必须确保dms状态正常并且开启系统消息通知，
     */
    public static function dms_sub_key($room_id, $default = null)
    {
        return static::getFiledById('dms_sub_key', $room_id, $default);
    }
    /*
     * VARCHAR(64) dms_pub_key DMS pub_key
     */
    public static function dms_pub_key($room_id, $default = null)
    {
        return static::getFiledById('dms_pub_key', $room_id, $default);
    }
    /*
     * VARCHAR(64) dms_s_key DMS s_key
     */
    public static function dms_s_key($room_id, $default = null)
    {
        return static::getFiledById('dms_s_key', $room_id, $default);
    }
    /*
     * INTEGER aodian_uin 奥点云 uin
     */
    public static function aodian_uin($room_id, $default = null)
    {
        return static::getFiledById('aodian_uin', $room_id, $default);
    }
    /*
     * VARCHAR(32) lss_app 流媒体 app
     */
    public static function lss_app($room_id, $default = null)
    {
        return static::getFiledById('lss_app', $room_id, $default);
    }
    /*
     * VARCHAR(32) stream 流媒体 stream
     */
    public static function stream($room_id, $default = null)
    {
        return static::getFiledById('stream', $room_id, $default);
    }
    /*
     * SMALLINT room_status 直播活动状态 正常 normal, 冻结 frozen, 删除 deleted
     */
    public static function room_status($room_id, $default = null)
    {
        return static::getFiledById('room_status', $room_id, $default);
    }
    /*
     * TIMESTAMP updated_at 更新时间
     */
    public static function updated_at($room_id, $default = null)
    {
        return static::getFiledById('updated_at', $room_id, $default);
    }
    /*
     * DATETIME created_at 创建时间
     */
    public static function created_at($room_id, $default = null)
    {
        return static::getFiledById('created_at', $room_id, $default);
    }
}