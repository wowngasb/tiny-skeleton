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
 * Class ChatConfigDao_
 * 直播活动 聊天配置信息
 * 数据表 chat_config
 * @package app\api\Dao_
 */
class ChatConfigDao_ extends BaseDao
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
            static::$_orm_config_map[$class_name] = new OrmConfig($db_name, 'chat_config', 'room_id', static::$cache_time, static::$max_select);
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
     * VARCHAR(16) review_type 房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat
     */
    public static function review_type($room_id, $default = null)
    {
        return static::getFiledById('review_type', $room_id, $default);
    }
    /*
     * VARCHAR(16) sysmsg_type 房间系统消息显示类型 全部显示 show_all, 全部隐藏 hide_all
     */
    public static function sysmsg_type($room_id, $default = null)
    {
        return static::getFiledById('sysmsg_type', $room_id, $default);
    }
}