<?php
{%- set namespace = options.namespace(options.path) %}
{%- set classname = _classname %}
{%- set description = 'Acts as a registry and factory for types.' %}
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: {{ time.strftime('%Y-%m') }}
 */
namespace {{ namespace }};

//import table classes


//import state enum classes
use GraphQL\Enum\MsgTypeEnum;
use GraphQL\Enum\RoomStatusEnum;
use GraphQL\Enum\UserTypeEnum;
use GraphQL\Enum\ReviewTypeEnum;
use GraphQL\Enum\MsgStatusEnum;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;

/**
 * Class {{ classname }}
 * {{ description }}
 * @package {{ namespace }}
 */
class {{ classname }}
{

    ####################################
    ########  root query type  #########
    ####################################

    private static $_mQuery = null;

    /**
     * 必须实现 AbstractQueryType 中的虚方法 才可以使用查询 此方法需要重写
     * @return AbstractQueryType
     */
    public static function query()
    {
        return self::$_mQuery ?: (self::$_mQuery = null);
    }

    ####################################
    ##########  table types  ##########
    ####################################

    private static $_mBasicMsg = null;

    /**
     * @param array $config
     * @param mixed $type
     * @return BasicMsg
     */
    public static function BasicMsg(array $config = [], $type = null)
    {
        return self::$_mBasicMsg ?: (self::$_mBasicMsg = new BasicMsg($config, $type));
    }

    private static $_mBasicRoom = null;

    /**
     * @param array $config
     * @param mixed $type
     * @return BasicRoom
     */
    public static function BasicRoom(array $config = [], $type = null)
    {
        return self::$_mBasicRoom ?: (self::$_mBasicRoom = new BasicRoom($config, $type));
    }

    private static $_mBasicUser = null;

    /**
     * @param array $config
     * @param mixed $type
     * @return BasicUser
     */
    public static function BasicUser(array $config = [], $type = null)
    {
        return self::$_mBasicUser ?: (self::$_mBasicUser = new BasicUser($config, $type));
    }

    private static $_mChatConfig = null;

    /**
     * @param array $config
     * @param mixed $type
     * @return ChatConfig
     */
    public static function ChatConfig(array $config = [], $type = null)
    {
        return self::$_mChatConfig ?: (self::$_mChatConfig = new ChatConfig($config, $type));
    }

    private static $_mMsgChatAndReview = null;

    /**
     * @param array $config
     * @param mixed $type
     * @return MsgChatAndReview
     */
    public static function MsgChatAndReview(array $config = [], $type = null)
    {
        return self::$_mMsgChatAndReview ?: (self::$_mMsgChatAndReview = new MsgChatAndReview($config, $type));
    }

    private static $_mMsgDonateAndGift = null;

    /**
     * @param array $config
     * @param mixed $type
     * @return MsgDonateAndGift
     */
    public static function MsgDonateAndGift(array $config = [], $type = null)
    {
        return self::$_mMsgDonateAndGift ?: (self::$_mMsgDonateAndGift = new MsgDonateAndGift($config, $type));
    }

    private static $_mPlayerAodianConfig = null;

    /**
     * @param array $config
     * @param mixed $type
     * @return PlayerAodianConfig
     */
    public static function PlayerAodianConfig(array $config = [], $type = null)
    {
        return self::$_mPlayerAodianConfig ?: (self::$_mPlayerAodianConfig = new PlayerAodianConfig($config, $type));
    }

    private static $_mPlayerMpsConfig = null;

    /**
     * @param array $config
     * @param mixed $type
     * @return PlayerMpsConfig
     */
    public static function PlayerMpsConfig(array $config = [], $type = null)
    {
        return self::$_mPlayerMpsConfig ?: (self::$_mPlayerMpsConfig = new PlayerMpsConfig($config, $type));
    }

    ####################################
    ######### state enum types #########
    ####################################

    private static $_mMsgTypeEnum = null;

    /**
     * @return MsgTypeEnum
     */
    public static function MsgTypeEnum()
    {
        return self::$_mMsgTypeEnum ?: (self::$_mMsgTypeEnum = new MsgTypeEnum());
    }

    private static $_mRoomStatusEnum = null;

    /**
     * @return RoomStatusEnum
     */
    public static function RoomStatusEnum()
    {
        return self::$_mRoomStatusEnum ?: (self::$_mRoomStatusEnum = new RoomStatusEnum());
    }

    private static $_mUserTypeEnum = null;

    /**
     * @return UserTypeEnum
     */
    public static function UserTypeEnum()
    {
        return self::$_mUserTypeEnum ?: (self::$_mUserTypeEnum = new UserTypeEnum());
    }

    private static $_mReviewTypeEnum = null;

    /**
     * @return ReviewTypeEnum
     */
    public static function ReviewTypeEnum()
    {
        return self::$_mReviewTypeEnum ?: (self::$_mReviewTypeEnum = new ReviewTypeEnum());
    }

    private static $_mSysMsgTypeEnum = null;

    /**
     * @return SysMsgTypeEnum
     */
    public static function SysMsgTypeEnum()
    {
        return self::$_mSysMsgTypeEnum ?: (self::$_mSysMsgTypeEnum = new SysMsgTypeEnum());
    }

    private static $_mMsgStatusEnum = null;

    /**
     * @return MsgStatusEnum
     */
    public static function MsgStatusEnum()
    {
        return self::$_mMsgStatusEnum ?: (self::$_mMsgStatusEnum = new MsgStatusEnum());
    }

    ####################################
    ########## internal types ##########
    ####################################

    public static function boolean()
    {
        return Type::boolean();
    }

    /**
     * @return \GraphQL\Type\Definition\FloatType
     */
    public static function float()
    {
        return Type::float();
    }

    /**
     * @return \GraphQL\Type\Definition\IDType
     */
    public static function id()
    {
        return Type::id();
    }

    /**
     * @return \GraphQL\Type\Definition\IntType
     */
    public static function int()
    {
        return Type::int();
    }

    /**
     * @return \GraphQL\Type\Definition\StringType
     */
    public static function string()
    {
        return Type::string();
    }

    /**
     * @param Type $type
     * @return ListOfType
     */
    public static function listOf($type)
    {
        return new ListOfType($type);
    }

    /**
     * @param Type $type
     * @return NonNull
     */
    public static function nonNull($type)
    {
        return new NonNull($type);
    }
}