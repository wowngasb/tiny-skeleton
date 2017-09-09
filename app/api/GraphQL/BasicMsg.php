<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/8
 * Time: 13:52
 */

namespace app\api\GraphQL;


use app\api\Dao\BasicUserDao;
use app\api\Dao\MsgChatAndReViewDao;
use app\api\Dao\MsgDonateAndGiftDao;
use app\api\GraphQL_\AbstractBasicMsg;
use GraphQL\Type\Definition\ResolveInfo;
use Tiny\Func;

class BasicMsg extends AbstractBasicMsg
{

    /**
     * 互动消息 消息内容
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed MsgContentUnion
     */
    public function msgContent($rootValue, $args, $context, ResolveInfo $info)
    {
        $msg_type = Func::v($rootValue, 'msg_type');
        $msg_id = Func::v($rootValue, 'msg_id');
        if ($msg_type == 'chat_and_review') {
            return MsgChatAndReViewDao::getOneById($msg_id);
        } else {
            return MsgDonateAndGiftDao::getOneById($msg_id);
        }
    }

    /**
     * 当前用户信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicUser
     */
    public function user($rootValue, $args, $context, ResolveInfo $info)
    {
        $user_id = Func::v($rootValue, 'user_id');
        return BasicUserDao::getOneById($user_id);
    }
}