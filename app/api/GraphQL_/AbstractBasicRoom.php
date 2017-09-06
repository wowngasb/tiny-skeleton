<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_;

use app\api\GraphQL_\Type\BasicRoom;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AbstractBasicRoom
 * 直播活动基本信息 每个条目对应一个活动
 * @package app\api\GraphQL_
 */
abstract class AbstractBasicRoom extends BasicRoom
{
    
    /**
     * 播放器配置
     * _param String $args['player_type'] = "mpsplayer" 播放器类型 (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed PlayerConfigUnion
     */
    abstract public function playerConfig($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 当前登录用户信息 dms参数
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed CurrentUser
     */
    abstract public function currentUser($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 分页查询话题用户列表
     * _param Int $args['num'] = 20 每页数量 (NonNull)
     * _param Int $args['page'] = 1 页数 (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed TopicUserPagination
     */
    abstract public function topicUser($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 分页查询房间历史消息
     * _param Int $args['num'] = 20 每页数量 (NonNull)
     * _param Int $args['page'] = 1 页数 (NonNull)
     * _param ID $args['user_id'] = "" 发送者用户id 
     * _param MsgTypeEnum $args['msg_type'] 消息类型 (NonNull)
     * _param MsgStatusEnum $args['msg_status'] 消息状态 (NonNull)
     * _param String $args['trade_type'] = "" 礼物消息 交易类型 TODO 
     * _param ID $args['msg_id_s'] = 0 消息id开始，默认为0 
     * _param ID $args['msg_id_e'] = 0 消息id结束，默认为0 
     * _param String $args['timestamp_s'] = "" 时间字符串 开始 格式为 2012-03-04 05:06:07 
     * _param String $args['timestamp_e'] = "" 时间字符串 结束 
     * _param String $args['direction'] = "asc" 排序顺序 asc 或 desc 
     * _param String $args['field'] = "msg_id" 排序依据字段 
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed RoomMsgPagination
     */
    abstract public function historyMsg($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 直播间切换tab栏配置
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ContentTabConfig
     */
    abstract public function contentTabConfig($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 聊天互动配置
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ChatConfig
     */
    abstract public function chatConfig($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 用户进出房间 话题
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed String
     */
    abstract public function present_topic($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 同步房间数据 话题
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed String
     */
    abstract public function sync_room_topic($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 同步用户数据 话题
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed String
     */
    abstract public function sync_user_topic($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 流媒体直播消息 话题
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed String
     */
    abstract public function sys_notify_lss_topic($rootValue, $args, $context, ResolveInfo $info);
    
}