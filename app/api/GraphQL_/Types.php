<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_;

//import query classes
use app\api\GraphQL_\ExtType\Query;

//import Type tables classes
use app\api\GraphQL_\Type\MsgDonateAndGift;
use app\api\GraphQL_\Type\PlayerAodianConfig;
use app\api\GraphQL_\Type\BasicMsg;
use app\api\GraphQL_\Type\PlayerMpsConfig;
use app\api\GraphQL_\Type\TabItemConfig;
use app\api\GraphQL_\Type\MsgChatAndReView;
use app\api\GraphQL_\Type\BasicRoom;
use app\api\GraphQL_\Type\BasicUser;
use app\api\GraphQL_\Type\ChatConfig;
use app\api\GraphQL_\Type\ContentTabConfig;

//import Type exttypes classes
use app\api\GraphQL_\ExtType\RoomMsgPagination;
use app\api\GraphQL_\ExtType\PageInfo;
use app\api\GraphQL_\ExtType\CurrentUser;
use app\api\GraphQL_\ExtType\TopicUserPagination;

//import Type enums classes
use app\api\GraphQL_\Enum\MsgStatusEnum;
use app\api\GraphQL_\Enum\UserTypeEnum;
use app\api\GraphQL_\Enum\MsgTypeEnum;
use app\api\GraphQL_\Enum\SysMsgTypeEnum;
use app\api\GraphQL_\Enum\ReviewTypeEnum;
use app\api\GraphQL_\Enum\RoomStatusEnum;

//import Type unions classes
use app\api\GraphQL_\Union\MsgContentUnion;
use app\api\GraphQL_\Union\PlayerConfigUnion;


//import Type Definition classes
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;

/**
 * Class Types
 * Acts as a registry and factory for types.
 * @package app\api\GraphQL_
 */
class Types
{

    ####################################
    ########  root query type  #########
    ####################################

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

    ####################################
    ##########  table types  ##########
    ####################################

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

    private static $_mPlayerAodianConfig = null;

    /**
     * 直播活动 奥点播放器
     * @param array $config
     * @param mixed $type
     * @return PlayerAodianConfig
     */
    public static function PlayerAodianConfig(array $config = [], $type = null)
    {
        return self::$_mPlayerAodianConfig ?: (self::$_mPlayerAodianConfig = new PlayerAodianConfig($config, $type));
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

    private static $_mPlayerMpsConfig = null;

    /**
     * 直播活动 Mps播放器
     * @param array $config
     * @param mixed $type
     * @return PlayerMpsConfig
     */
    public static function PlayerMpsConfig(array $config = [], $type = null)
    {
        return self::$_mPlayerMpsConfig ?: (self::$_mPlayerMpsConfig = new PlayerMpsConfig($config, $type));
    }

    private static $_mTabItemConfig = null;

    /**
     * 单个tab选项的配置
     * @param array $config
     * @param mixed $type
     * @return TabItemConfig
     */
    public static function TabItemConfig(array $config = [], $type = null)
    {
        return self::$_mTabItemConfig ?: (self::$_mTabItemConfig = new TabItemConfig($config, $type));
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

    private static $_mBasicUser = null;

    /**
     * 用户信息 不同的用户类型对应不同的权限
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
     * 直播活动 聊天配置信息
     * @param array $config
     * @param mixed $type
     * @return ChatConfig
     */
    public static function ChatConfig(array $config = [], $type = null)
    {
        return self::$_mChatConfig ?: (self::$_mChatConfig = new ChatConfig($config, $type));
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

    ####################################
    ######### exttypes types #########
    ####################################

    private static $_mRoomMsgPagination = null;

    /**
     * 房间历史消息
     * @param array $config
     * @param mixed $type
     * @return RoomMsgPagination
     */
    public static function RoomMsgPagination(array $config = [], $type = null)
    {
        return self::$_mRoomMsgPagination ?: (self::$_mRoomMsgPagination = new RoomMsgPagination($config, $type));
    }

    private static $_mPageInfo = null;

    /**
     * 分页信息
     * @param array $config
     * @param mixed $type
     * @return PageInfo
     */
    public static function PageInfo(array $config = [], $type = null)
    {
        return self::$_mPageInfo ?: (self::$_mPageInfo = new PageInfo($config, $type));
    }

    private static $_mCurrentUser = null;

    /**
     * 当前登录用户信息 及 用户连接dms配置
     * @param array $config
     * @param mixed $type
     * @return CurrentUser
     */
    public static function CurrentUser(array $config = [], $type = null)
    {
        return self::$_mCurrentUser ?: (self::$_mCurrentUser = new CurrentUser($config, $type));
    }

    private static $_mTopicUserPagination = null;

    /**
     * 当前登录用户信息 及 用户连接dms配置
     * @param array $config
     * @param mixed $type
     * @return TopicUserPagination
     */
    public static function TopicUserPagination(array $config = [], $type = null)
    {
        return self::$_mTopicUserPagination ?: (self::$_mTopicUserPagination = new TopicUserPagination($config, $type));
    }


    ####################################
    ######### enums types #########
    ####################################

    private static $_mMsgStatusEnum = null;

    /**
     * 聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add
     * @param array $config
     * @param mixed $type
     * @return MsgStatusEnum
     */
    public static function MsgStatusEnum(array $config = [], $type = null)
    {
        return self::$_mMsgStatusEnum ?: (self::$_mMsgStatusEnum = new MsgStatusEnum($config, $type));
    }

    private static $_mUserTypeEnum = null;

    /**
     * 用户类型 游客 guest, 已认证 authorized, 管理者 manager, 发布者 publisher
     * @param array $config
     * @param mixed $type
     * @return UserTypeEnum
     */
    public static function UserTypeEnum(array $config = [], $type = null)
    {
        return self::$_mUserTypeEnum ?: (self::$_mUserTypeEnum = new UserTypeEnum($config, $type));
    }

    private static $_mMsgTypeEnum = null;

    /**
     * 互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift
     * @param array $config
     * @param mixed $type
     * @return MsgTypeEnum
     */
    public static function MsgTypeEnum(array $config = [], $type = null)
    {
        return self::$_mMsgTypeEnum ?: (self::$_mMsgTypeEnum = new MsgTypeEnum($config, $type));
    }

    private static $_mSysMsgTypeEnum = null;

    /**
     * 房间系统消息显示类型 全部显示 show_all, 全部隐藏 hide_all
     * @param array $config
     * @param mixed $type
     * @return SysMsgTypeEnum
     */
    public static function SysMsgTypeEnum(array $config = [], $type = null)
    {
        return self::$_mSysMsgTypeEnum ?: (self::$_mSysMsgTypeEnum = new SysMsgTypeEnum($config, $type));
    }

    private static $_mReviewTypeEnum = null;

    /**
     * 房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat
     * @param array $config
     * @param mixed $type
     * @return ReviewTypeEnum
     */
    public static function ReviewTypeEnum(array $config = [], $type = null)
    {
        return self::$_mReviewTypeEnum ?: (self::$_mReviewTypeEnum = new ReviewTypeEnum($config, $type));
    }

    private static $_mRoomStatusEnum = null;

    /**
     * 直播活动状态 正常 normal, 冻结 frozen, 删除 deleted
     * @param array $config
     * @param mixed $type
     * @return RoomStatusEnum
     */
    public static function RoomStatusEnum(array $config = [], $type = null)
    {
        return self::$_mRoomStatusEnum ?: (self::$_mRoomStatusEnum = new RoomStatusEnum($config, $type));
    }

    ####################################
    ######### unions types #########
    ####################################

    private static $_mMsgContentUnion = null;

    /**
     * 播放器配置
     * @param array $config
     * @param mixed $type
     * @return MsgContentUnion
     */
    public static function MsgContentUnion(array $config = [], $type = null)
    {
        return self::$_mMsgContentUnion ?: (self::$_mMsgContentUnion = new MsgContentUnion($config, $type));
    }

    private static $_mPlayerConfigUnion = null;

    /**
     * 播放器配置
     * @param array $config
     * @param mixed $type
     * @return PlayerConfigUnion
     */
    public static function PlayerConfigUnion(array $config = [], $type = null)
    {
        return self::$_mPlayerConfigUnion ?: (self::$_mPlayerConfigUnion = new PlayerConfigUnion($config, $type));
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