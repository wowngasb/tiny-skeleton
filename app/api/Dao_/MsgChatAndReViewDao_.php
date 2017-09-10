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
 * Class MsgChatAndReViewDao_
 * 聊天及审核消息
 * 数据表 msg_chat_and_review
 * @package app\api\Dao_
 */
class MsgChatAndReViewDao_ extends BaseDao
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
            static::$_orm_config_map[$class_name] = new OrmConfig($db_name, 'msg_chat_and_review', 'msg_id', static::$cache_time, static::$max_select);
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
     * VARCHAR(16) msg_type 互动消息类型 固定为 chat_and_review
     */
    public static function msg_type($msg_id, $default = null)
    {
        return static::getFiledById('msg_type', $msg_id, $default);
    }
    /*
     * VARCHAR(16) target_user_id 消息目标用户 用户id 用于处理私聊
     */
    public static function target_user_id($msg_id, $default = null)
    {
        return static::getFiledById('target_user_id', $msg_id, $default);
    }
    /*
     * VARCHAR(16) target_msg_id 目标消息id
     */
    public static function target_msg_id($msg_id, $default = null)
    {
        return static::getFiledById('target_msg_id', $msg_id, $default);
    }
    /*
     * VARCHAR(512) content_text 消息文本内容
     */
    public static function content_text($msg_id, $default = null)
    {
        return static::getFiledById('content_text', $msg_id, $default);
    }
    /*
     * VARCHAR(16) msg_status 聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add
     */
    public static function msg_status($msg_id, $default = null)
    {
        return static::getFiledById('msg_status', $msg_id, $default);
    }
    /*
     * VARCHAR(16) operator_id 当前操作者 用户id
     */
    public static function operator_id($msg_id, $default = null)
    {
        return static::getFiledById('operator_id', $msg_id, $default);
    }
}