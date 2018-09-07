<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/25
 * Time: 11:19
 */

namespace app\api\GraphQL;

use app\api\GraphQL\ExtType\PageInfo;
use app\api\GraphQL\ExtType\PlayerAclInfo;
use app\api\GraphQL_\Types as _Types;

class Types extends _Types
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
    ######### exttypes types #########
    ####################################

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

    private static $_mPlayerAclInfo = null;

    /**
     * 分页信息
     * @param array $config
     * @param mixed $type
     * @return PlayerAclInfo
     */
    public static function PlayerAclInfo(array $config = [], $type = null)
    {
        return self::$_mPlayerAclInfo ?: (self::$_mPlayerAclInfo = new PlayerAclInfo($config, $type));
    }


    ####################################
    ##########  abstract types  ##########
    ####################################

    private static $_mXdyAdminProduct = null;

    /**
     * 自营账号下  客户 购买套餐 导播台 记录  每项记录为一个套餐 或者导播台
     * @param array $config
     * @param mixed $type
     * @return XdyAdminProduct
     */
    public static function XdyAdminProduct(array $config = [], $type = null)
    {
        return self::$_mXdyAdminProduct ?: (self::$_mXdyAdminProduct = new XdyAdminProduct($config, $type));
    }

    private static $_mRoomRunningDms = null;

    /**
     * 频道人数流水记录 dms
     * @param array $config
     * @param mixed $type
     * @return RoomRunningDms
     */
    public static function RoomRunningDms(array $config = [], $type = null)
    {
        return self::$_mRoomRunningDms ?: (self::$_mRoomRunningDms = new RoomRunningDms($config, $type));
    }

    private static $_mPlayerBase = null;

    /**
     * 播放器
     * @param array $config
     * @param mixed $type
     * @return PlayerBase
     */
    public static function PlayerBase(array $config = [], $type = null)
    {
        return self::$_mPlayerBase ?: (self::$_mPlayerBase = new PlayerBase($config, $type));
    }

    private static $_mSendLog = null;

    /**
     * 客户超出套餐 信息提醒记录
     * @param array $config
     * @param mixed $type
     * @return SendLog
     */
    public static function SendLog(array $config = [], $type = null)
    {
        return self::$_mSendLog ?: (self::$_mSendLog = new SendLog($config, $type));
    }

    private static $_mRoomViewRecord = null;

    /**
     * 频道观看记录表  存储每条观看记录
     * @param array $config
     * @param mixed $type
     * @return RoomViewRecord
     */
    public static function RoomViewRecord(array $config = [], $type = null)
    {
        return self::$_mRoomViewRecord ?: (self::$_mRoomViewRecord = new RoomViewRecord($config, $type));
    }

    private static $_mAdminUser = null;

    /**
     * 后台管理员 用户表 每条数据对应一个后台用户
     * @param array $config
     * @param mixed $type
     * @return AdminUser
     */
    public static function AdminUser(array $config = [], $type = null)
    {
        return self::$_mAdminUser ?: (self::$_mAdminUser = new AdminUser($config, $type));
    }

    private static $_mArticleClassify = null;

    /**
     * 首页  文章分类
     * @param array $config
     * @param mixed $type
     * @return ArticleClassify
     */
    public static function ArticleClassify(array $config = [], $type = null)
    {
        return self::$_mArticleClassify ?: (self::$_mArticleClassify = new ArticleClassify($config, $type));
    }

    private static $_mRoomRunningDmsSum = null;

    /**
     * 频道人数流水记录 dms
     * @param array $config
     * @param mixed $type
     * @return RoomRunningDmsSum
     */
    public static function RoomRunningDmsSum(array $config = [], $type = null)
    {
        return self::$_mRoomRunningDmsSum ?: (self::$_mRoomRunningDmsSum = new RoomRunningDmsSum($config, $type));
    }

    private static $_mRoomRunning = null;

    /**
     * 频道人数流水记录
     * @param array $config
     * @param mixed $type
     * @return RoomRunning
     */
    public static function RoomRunning(array $config = [], $type = null)
    {
        return self::$_mRoomRunning ?: (self::$_mRoomRunning = new RoomRunning($config, $type));
    }

    private static $_mRoomViewRecordDms = null;

    /**
     * 频道观看记录表  存储每条观看记录
     * @param array $config
     * @param mixed $type
     * @return RoomViewRecordDms
     */
    public static function RoomViewRecordDms(array $config = [], $type = null)
    {
        return self::$_mRoomViewRecordDms ?: (self::$_mRoomViewRecordDms = new RoomViewRecordDms($config, $type));
    }

    private static $_mXdyOrder = null;

    /**
     * 自营账号下  客户 购买套餐 导播台 订单记录  每项记录为一个订单
     * @param array $config
     * @param mixed $type
     * @return XdyOrder
     */
    public static function XdyOrder(array $config = [], $type = null)
    {
        return self::$_mXdyOrder ?: (self::$_mXdyOrder = new XdyOrder($config, $type));
    }

    private static $_mDailyRoomRunning = null;

    /**
     * 每日 频道 观看人数峰值表  由每日定时任务 查询出峰值收据后 写入
     * @param array $config
     * @param mixed $type
     * @return DailyRoomRunning
     */
    public static function DailyRoomRunning(array $config = [], $type = null)
    {
        return self::$_mDailyRoomRunning ?: (self::$_mDailyRoomRunning = new DailyRoomRunning($config, $type));
    }

    private static $_mRoomRunningDmsRef = null;

    /**
     * 频道人数流水记录 dms
     * @param array $config
     * @param mixed $type
     * @return RoomRunningDmsRef
     */
    public static function RoomRunningDmsRef(array $config = [], $type = null)
    {
        return self::$_mRoomRunningDmsRef ?: (self::$_mRoomRunningDmsRef = new RoomRunningDmsRef($config, $type));
    }

    private static $_mAdminRecord = null;

    /**
     * table admin_record
     * @param array $config
     * @param mixed $type
     * @return AdminRecord
     */
    public static function AdminRecord(array $config = [], $type = null)
    {
        return self::$_mAdminRecord ?: (self::$_mAdminRecord = new AdminRecord($config, $type));
    }

    private static $_mDailyRoomRunningDms = null;

    /**
     * 每日 频道 观看人数峰值表  由每日定时任务 查询出峰值收据后 写入 未使用
     * @param array $config
     * @param mixed $type
     * @return DailyRoomRunningDms
     */
    public static function DailyRoomRunningDms(array $config = [], $type = null)
    {
        return self::$_mDailyRoomRunningDms ?: (self::$_mDailyRoomRunningDms = new DailyRoomRunningDms($config, $type));
    }

    private static $_mAdminCountly = null;

    /**
     * 用户统计服务   总公司 Countly App 记录，每个总公司对应一个Countly App  用于统计详细信息（暂时未使用）  每当用户访问频道都会获取 Countly 配置  使用会话心跳机制 统计用户数据
     * @param array $config
     * @param mixed $type
     * @return AdminCountly
     */
    public static function AdminCountly(array $config = [], $type = null)
    {
        return self::$_mAdminCountly ?: (self::$_mAdminCountly = new AdminCountly($config, $type));
    }

    private static $_mRoomContentConfig = null;

    /**
     * 块配置 具体内容块 设置及配置信息表
     * @param array $config
     * @param mixed $type
     * @return RoomContentConfig
     */
    public static function RoomContentConfig(array $config = [], $type = null)
    {
        return self::$_mRoomContentConfig ?: (self::$_mRoomContentConfig = new RoomContentConfig($config, $type));
    }

    private static $_mXdyProduct = null;

    /**
     * 自营账号下  套餐 导播台 产品表  超级管理员创建产品之后才可以购买
     * @param array $config
     * @param mixed $type
     * @return XdyProduct
     */
    public static function XdyProduct(array $config = [], $type = null)
    {
        return self::$_mXdyProduct ?: (self::$_mXdyProduct = new XdyProduct($config, $type));
    }

    private static $_mDailyViewCount = null;

    /**
     * 每日频道点击数 统计表  每打开一次频道页面  点击数+1
     * @param array $config
     * @param mixed $type
     * @return DailyViewCount
     */
    public static function DailyViewCount(array $config = [], $type = null)
    {
        return self::$_mDailyViewCount ?: (self::$_mDailyViewCount = new DailyViewCount($config, $type));
    }

    private static $_mRoomPublishRecord = null;

    /**
     * 频道  视频发布记录 数据表
     * @param array $config
     * @param mixed $type
     * @return RoomPublishRecord
     */
    public static function RoomPublishRecord(array $config = [], $type = null)
    {
        return self::$_mRoomPublishRecord ?: (self::$_mRoomPublishRecord = new RoomPublishRecord($config, $type));
    }

    private static $_mStreamBase = null;

    /**
     * 视频流
     * @param array $config
     * @param mixed $type
     * @return StreamBase
     */
    public static function StreamBase(array $config = [], $type = null)
    {
        return self::$_mStreamBase ?: (self::$_mStreamBase = new StreamBase($config, $type));
    }

    private static $_mLiveRoom = null;

    /**
     * 频道  列表 每个条目为一个频道  拥有唯一的 stream
     * @param array $config
     * @param mixed $type
     * @return LiveRoom
     */
    public static function LiveRoom(array $config = [], $type = null)
    {
        return self::$_mLiveRoom ?: (self::$_mLiveRoom = new LiveRoom($config, $type));
    }

}