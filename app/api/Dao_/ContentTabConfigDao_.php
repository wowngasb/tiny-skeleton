<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\Dao_;

use app\api\base\BaseDao;
use Tiny\Application;
use Tiny\OrmQuery\OrmConfig;


/**
 * Class ContentTabConfigDao_
 * 手机切换菜单配置
 * 数据表 content_tab_config
 * @package app\api\Dao_
 */
class ContentTabConfigDao_ extends BaseDao
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
        if (is_null(static::$_orm_config)) {
            $db_config = Application::app()->getEnv('ENV_DB');
            $db_name = !empty($db_config['database']) ? $db_config['database'] : 'test';
            static::$_orm_config = new OrmConfig($db_name, 'content_tab_config', 'room_id', static::$cache_time, static::$max_select);
        }
        return static::$_orm_config;
    }
    /*
     * INTEGER room_id 对应房间 id
     */
    public static function room_id($room_id, $default = null)
    {
        return static::getFiledById('room_id', $room_id, $default);
    }
    /*
     * INTEGER content_tab_id 对应 content_tab_id
     */
    public static function content_tab_id($room_id, $default = null)
    {
        return static::getFiledById('content_tab_id', $room_id, $default);
    }
    /*
     * VARCHAR(16) active 当前激活的tab栏标题
     */
    public static function active($room_id, $default = null)
    {
        return static::getFiledById('active', $room_id, $default);
    }
}