<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/7
 * Time: 13:20
 */

namespace app\api\GraphQL;


class Types extends \app\api\GraphQL_\Types
{


    private static $_mQuery = null;

    /**
     * 必须实现 AbstractQuery 中的虚方法 才可以使用完整的查询 此方法需要重写
     * @param array $config
     * @param mixed $type
     * @return Query
     */
    public static function Query(array $config = [], $type = null)
    {
        return self::$_mQuery ?: (self::$_mQuery = new Query($config, $type));
    }


    private static $_mMsgDonateAndGift = null;

    /**
     * 打赏及赠送礼物消息
     * @param array $config
     * @param mixed $type
     * @return MsgDonateAndGift
     */
    public static function MsgDonateAndGift(array $config = [], $type = null)
    {
        return self::$_mMsgDonateAndGift ?: (self::$_mMsgDonateAndGift = new MsgDonateAndGift($config, $type));
    }


    private static $_mMsgChatAndReView = null;

    /**
     * 聊天及审核消息
     * @param array $config
     * @param mixed $type
     * @return MsgChatAndReView
     */
    public static function MsgChatAndReView(array $config = [], $type = null)
    {
        return self::$_mMsgChatAndReView ?: (self::$_mMsgChatAndReView = new MsgChatAndReView($config, $type));
    }


    private static $_mContentTabConfig = null;

    /**
     * 手机切换菜单配置
     * @param array $config
     * @param mixed $type
     * @return ContentTabConfig
     */
    public static function ContentTabConfig(array $config = [], $type = null)
    {
        return self::$_mContentTabConfig ?: (self::$_mContentTabConfig = new ContentTabConfig($config, $type));
    }


    private static $_mBasicRoom = null;

    /**
     * 直播活动基本信息 每个条目对应一个活动
     * @param array $config
     * @param mixed $type
     * @return BasicRoom
     */
    public static function BasicRoom(array $config = [], $type = null)
    {
        return self::$_mBasicRoom ?: (self::$_mBasicRoom = new BasicRoom($config, $type));
    }


    private static $_mBasicMsg = null;

    /**
     * 互动消息模型 可扩展自定义类型
     * @param array $config
     * @param mixed $type
     * @return BasicMsg
     */
    public static function BasicMsg(array $config = [], $type = null)
    {
        return self::$_mBasicMsg ?: (self::$_mBasicMsg = new BasicMsg($config, $type));
    }

}