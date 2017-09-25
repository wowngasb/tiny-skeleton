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
 * Class PlayerMpsConfigDao_
 * 直播活动 Mps播放器
 * 数据表 player_mps_config
 * @package app\api\Dao_
 */
class PlayerMpsConfigDao_ extends BaseDao
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
            static::$_orm_config_map[$class_name] = new OrmConfig($db_name, 'player_mps_config', 'room_id', static::$cache_time, static::$max_select, static::$debug);
        }
        return static::$_orm_config_map[$class_name];
    }
    /*
     * INTEGER room_id 对应房间 id
     */
    public static function room_id($room_id, $default = null)
    {
        return static::getFiledById('room_id', $room_id, $default);
    }
    /*
     * VARCHAR(16) player_type 播放器类型 固定为 mpsplayer
     */
    public static function player_type($room_id, $default = null)
    {
        return static::getFiledById('player_type', $room_id, $default);
    }
    /*
     * INTEGER uin 用户奥点uin
     */
    public static function uin($room_id, $default = null)
    {
        return static::getFiledById('uin', $room_id, $default);
    }
    /*
     * VARCHAR(32) appId mps实例id 需要静态实例
     */
    public static function appId($room_id, $default = null)
    {
        return static::getFiledById('appId', $room_id, $default);
    }
    /*
     * SMALLINT autostart 是否自动播放
     */
    public static function autostart($room_id, $default = null)
    {
        return static::getFiledById('autostart', $room_id, $default);
    }
    /*
     * SMALLINT stretching 设置全屏模式 1代表按比例撑满至全屏 2代表铺满全屏 3代表视频原始大小
     */
    public static function stretching($room_id, $default = null)
    {
        return static::getFiledById('stretching', $room_id, $default);
    }
    /*
     * SMALLINT mobilefullscreen 移动端是否全屏
     */
    public static function mobilefullscreen($room_id, $default = null)
    {
        return static::getFiledById('mobilefullscreen', $room_id, $default);
    }
    /*
     * VARCHAR(16) controlbardisplay 是否显示控制栏 可取值 disable enable 默认为disable
     */
    public static function controlbardisplay($room_id, $default = null)
    {
        return static::getFiledById('controlbardisplay', $room_id, $default);
    }
    /*
     * SMALLINT isclickplay 是否单击播放，默认为false
     */
    public static function isclickplay($room_id, $default = null)
    {
        return static::getFiledById('isclickplay', $room_id, $default);
    }
    /*
     * SMALLINT isfullscreen 是否双击全屏，默认为true
     */
    public static function isfullscreen($room_id, $default = null)
    {
        return static::getFiledById('isfullscreen', $room_id, $default);
    }
}