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
 * Class TabItemConfigDao_
 * 单个tab选项的配置
 * 数据表 tab_item_config
 * @package app\api\Dao_
 */
class TabItemConfigDao_ extends BaseDao
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
            $db_name = !empty($db_config['database']) ? !empty($db_config['database']) : 'test';
            static::$_orm_config = new OrmConfig($db_name, 'tab_item_config', 'content_tab_id', static::$cache_time, static::$max_select);
        }
        return static::$_orm_config;
    }
    /*
     * INTEGER content_tab_id 对应 content_tab_id
     */
    public static function content_tab_id($content_tab_id, $default = null)
    {
        return static::getFiledById('content_tab_id', $content_tab_id, $default);
    }
    /*
     * VARCHAR(16) title 标题
     */
    public static function title($content_tab_id, $default = null)
    {
        return static::getFiledById('title', $content_tab_id, $default);
    }
    /*
     * SMALLINT new_msg 提醒新消息数量
     */
    public static function new_msg($content_tab_id, $default = null)
    {
        return static::getFiledById('new_msg', $content_tab_id, $default);
    }
    /*
     * VARCHAR(16) component 对应区域内容类型
     */
    public static function component($content_tab_id, $default = null)
    {
        return static::getFiledById('component', $content_tab_id, $default);
    }
}