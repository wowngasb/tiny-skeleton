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
 * Class BasicUserDao_
 * 用户信息 不同的用户类型对应不同的权限
 * 数据表 basic_user
 * @package app\api\Dao_
 */
class BasicUserDao_ extends BaseDao
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
            static::$_orm_config_map[$class_name] = new OrmConfig($db_name, 'basic_user', 'user_id', static::$cache_time, static::$max_select, static::$debug);
        }
        return static::$_orm_config_map[$class_name];
    }
    /*
     * INTEGER user_id 用户 唯一id
     */
    public static function user_id($user_id, $default = null)
    {
        return static::getFiledById('user_id', $user_id, $default);
    }
    /*
     * VARCHAR(16) nick 用户昵称
     */
    public static function nick($user_id, $default = null)
    {
        return static::getFiledById('nick', $user_id, $default);
    }
    /*
     * VARCHAR(128) avatar 用户头像
     */
    public static function avatar($user_id, $default = null)
    {
        return static::getFiledById('avatar', $user_id, $default);
    }
    /*
     * VARCHAR(16) user_type 用户类型 游客 guest, 已认证 authorized, 管理者 manager, 发布者 publisher
     */
    public static function user_type($user_id, $default = null)
    {
        return static::getFiledById('user_type', $user_id, $default);
    }
}