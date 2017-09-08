<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/8
 * Time: 13:51
 */

namespace app\api\GraphQL;


use app\api\Dao\BasicMsgDao;
use app\api\Dao\BasicUserDao;
use app\api\Dao\ChatConfigDao;
use app\api\Dao\PlayerAodianConfigDao;
use app\api\Dao\PlayerMpsConfigDao;
use app\api\GraphQL\ExtType\PageInfo;
use app\api\GraphQL_\AbstractBasicRoom;
use GraphQL\Type\Definition\ResolveInfo;
use Tiny\Func;

class BasicRoom extends AbstractBasicRoom
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
    public function playerConfig($rootValue, $args, $context, ResolveInfo $info)
    {
        $room_id = Func::v($rootValue, 'room_id');
        $player_type = Func::v($rootValue, 'player_type');
        if ($player_type == 'aodianplayer') {
            PlayerAodianConfigDao::getDataById($room_id);
        } else {
            PlayerMpsConfigDao::getDataById($room_id);
        }
    }

    /**
     * 当前登录用户信息 dms参数
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed CurrentUser
     */
    public function currentUser($rootValue, $args, $context, ResolveInfo $info)
    {
        $user_id = 1000;
        $user = BasicUserDao::getDataById($user_id);
        $agent = 'WEB';
        $client_id = time() . "_{$agent}" . rand(100, 999) . "_{$user_id}";
        return [
            'user' => $user,
            'agent' => $agent,
            'client_id' => $client_id,
        ];
    }

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
    public function topicUser($rootValue, $args, $context, ResolveInfo $info)
    {
        $num = Func::v($args, 'num', 20);
        $page = Func::v($args, 'page', 1);
        $total = $num * ($page + 1);
        $userList = [];
        foreach (range(0, $num) as $idx) {
            $user_id = 1000 + $idx;
            $agent = 'WEB';
            $client_id = time() . "_{$agent}" . rand(100, 999) . "_{$user_id}";
            $userList[] = [
                'user' => BasicUserDao::getDataById($user_id),
                'agent' => $agent,
                'client_id' => $client_id,
            ];
        }
        $pageInfo = PageInfo::buildPageInfo($total, $num, $page);
        return [
            'pageInfo' => $pageInfo,
            'userList' => $userList,
        ];
    }

    /**
     * 分页查询房间历史消息
     * _param Int $args['num'] = 20 每页数量 (NonNull)
     * _param Int $args['page'] = 1 页数 (NonNull)
     * _param ID $args['user_id'] = "" 发送者用户id
     * _param MsgTypeEnum $args['msg_type'] 消息类型 (NonNull)
     * _param MsgStatusEnum $args['msg_status'] 消息状态 (NonNull)
     * _param String $args['trade_type'] = "" 礼物消息 交易类型
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
    public function historyMsg($rootValue, $args, $context, ResolveInfo $info)
    {
        $num = Func::v($args, 'num', 20);
        $page = Func::v($args, 'page', 1);
        $user_id = Func::v($args, 'user_id', null);
        $msg_type = Func::v($args, 'msg_type', null);
        $msg_status = Func::v($args, 'msg_status', null);
        $where = [
            'user_id' => $user_id,
            'msg_type' => $msg_type,
            'msg_status' => $msg_status,
        ];

        $start = ($page - 1) * $num;
        $limit = $num;
        $sort_option = [
            'field' => '',
            'direction' => 'desc'
        ];
        $msgList = BasicMsgDao::selectItem($start, $limit, $sort_option, $where);
        $total = BasicMsgDao::countItem($where);
        $pageInfo = PageInfo::buildPageInfo($total, $num, $page);
        return [
            'pageInfo' => $pageInfo,
            'msgList' => $msgList,
        ];
    }

    /**
     * 直播间切换tab栏配置
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ContentTabConfig
     */
    public function contentTabConfig($rootValue, $args, $context, ResolveInfo $info)
    {
        // TODO: Implement contentTabConfig() method.
    }

    /**
     * 聊天互动配置
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ChatConfig
     */
    public function chatConfig($rootValue, $args, $context, ResolveInfo $info)
    {
        $room_id = Func::v($rootValue, 'room_id');
        return ChatConfigDao::getDataById($room_id);
    }

    /**
     * 用户进出房间 话题
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed String
     */
    public function present_topic($rootValue, $args, $context, ResolveInfo $info)
    {
        $chat_topic = Func::v($rootValue, 'chat_topic');
        return '__present__' . $chat_topic;
    }

    /**
     * 同步房间数据 话题
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed String
     */
    public function sync_room_topic($rootValue, $args, $context, ResolveInfo $info)
    {
        return 'sync_room';
    }

    /**
     * 同步用户数据 话题
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed String
     */
    public function sync_user_topic($rootValue, $args, $context, ResolveInfo $info)
    {
        return 'sync_user';
    }

    /**
     * 流媒体直播消息 话题
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed String
     */
    public function sys_notify_lss_topic($rootValue, $args, $context, ResolveInfo $info)
    {
        return 'sys/notify/lss';
    }
}