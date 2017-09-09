<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/8
 * Time: 13:53
 */

namespace app\api\GraphQL;


use app\api\Dao\BasicMsgDao;
use app\api\Dao\BasicUserDao;
use app\api\GraphQL_\AbstractMsgChatAndReView;
use GraphQL\Type\Definition\ResolveInfo;
use Tiny\Func;

class MsgChatAndReView extends AbstractMsgChatAndReView
{

    /**
     * 目标用户 信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicUser
     */
    public function target_user($rootValue, $args, $context, ResolveInfo $info)
    {
        $target_user_id = Func::v($rootValue, 'target_user_id');
        return BasicUserDao::getOneById($target_user_id);
    }

    /**
     * 目标消息 信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicMsg
     */
    public function target_msg($rootValue, $args, $context, ResolveInfo $info)
    {
        $target_msg_id = Func::v($rootValue, 'target_msg_id');
        return BasicMsgDao::getOneById($target_msg_id);
    }

    /**
     * 当前操作者 信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicUser
     */
    public function operator($rootValue, $args, $context, ResolveInfo $info)
    {
        $operator_id = Func::v($rootValue, 'operator_id');
        return BasicUserDao::getOneById($operator_id);
    }
}