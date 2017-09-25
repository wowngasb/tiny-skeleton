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
 * Class PlayerAodianConfigDao_
 * 直播活动 奥点播放器
 * 数据表 player_aodian_config
 * @package app\api\Dao_
 */
class PlayerAodianConfigDao_ extends BaseDao
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
            static::$_orm_config_map[$class_name] = new OrmConfig($db_name, 'player_aodian_config', 'room_id', static::$cache_time, static::$max_select, static::$debug);
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
     * VARCHAR(16) player_type 播放器类型 固定为 aodianplayer
     */
    public static function player_type($room_id, $default = null)
    {
        return static::getFiledById('player_type', $room_id, $default);
    }
    /*
     * VARCHAR(128) rtmpUrl 控制台开通的APP rtmp地址 必要参数
     */
    public static function rtmpUrl($room_id, $default = null)
    {
        return static::getFiledById('rtmpUrl', $room_id, $default);
    }
    /*
     * VARCHAR(128) hlsUrl 控制台开通的APP hls地址 必要参数
     */
    public static function hlsUrl($room_id, $default = null)
    {
        return static::getFiledById('hlsUrl', $room_id, $default);
    }
    /*
     * SMALLINT autostart 是否自动播放
     */
    public static function autostart($room_id, $default = null)
    {
        return static::getFiledById('autostart', $room_id, $default);
    }
    /*
     * SMALLINT bufferlength 视频缓冲时间 默认为1秒
     */
    public static function bufferlength($room_id, $default = null)
    {
        return static::getFiledById('bufferlength', $room_id, $default);
    }
    /*
     * SMALLINT maxbufferlength 最大视频缓冲时间 默认为2秒
     */
    public static function maxbufferlength($room_id, $default = null)
    {
        return static::getFiledById('maxbufferlength', $room_id, $default);
    }
    /*
     * SMALLINT stretching 设置全屏模式 1代表按比例撑满至全屏 2代表铺满全屏 3代表视频原始大小
     */
    public static function stretching($room_id, $default = null)
    {
        return static::getFiledById('stretching', $room_id, $default);
    }
    /*
     * VARCHAR(16) controlbardisplay 是否显示控制栏 可取值 disable enable 默认为disable
     */
    public static function controlbardisplay($room_id, $default = null)
    {
        return static::getFiledById('controlbardisplay', $room_id, $default);
    }
    /*
     * SMALLINT defvolume 默认音量
     */
    public static function defvolume($room_id, $default = null)
    {
        return static::getFiledById('defvolume', $room_id, $default);
    }
    /*
     * VARCHAR(128) adveDeAddr 封面图地址
     */
    public static function adveDeAddr($room_id, $default = null)
    {
        return static::getFiledById('adveDeAddr', $room_id, $default);
    }
}