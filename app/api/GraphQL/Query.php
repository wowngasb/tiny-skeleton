<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/8
 * Time: 13:53
 */

namespace app\api\GraphQL;


use app\api\Dao\BasicMsgDao;
use app\api\Dao\BasicRoomDao;
use app\api\Dao\BasicUserDao;
use app\api\GraphQL_\AbstractQuery;
use GraphQL\Type\Definition\ResolveInfo;
use Tiny\Func;

class Query extends AbstractQuery
{

    /**
     * 查询用户
     * _param ID $args['room_id'] 房间id (NonNull)
     * _param ID $args['user_id'] 用户id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicUser
     */
    public function user($rootValue, $args, $context, ResolveInfo $info)
    {
        // $room_id = Func::v($args, 'room_id');
        $user_id = Func::v($args, 'user_id');
        $tmp = BasicUserDao::getItem($user_id);
        error_log("user_id:{$user_id}" . json_encode($tmp));
        return $tmp;
    }

    /**
     * 查询房间
     * _param ID $args['room_id'] 房间id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicRoom
     */
    public function room($rootValue, $args, $context, ResolveInfo $info)
    {
        $room_id = Func::v($args, 'room_id');
        return BasicRoomDao::getDataById($room_id);
    }

    /**
     * 查询消息
     * _param ID $args['msg_id'] 消息id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicMsg
     */
    public function msg($rootValue, $args, $context, ResolveInfo $info)
    {
        $msg_id = Func::v($args, 'msg_id');
        return BasicMsgDao::getDataById($msg_id);
    }
}