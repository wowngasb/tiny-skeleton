<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/8
 * Time: 13:53
 */

namespace app\api\GraphQL;


use app\api\Dao\BasicUserDao;
use app\api\GraphQL_\AbstractMsgDonateAndGift;
use GraphQL\Type\Definition\ResolveInfo;
use Tiny\Func;

class MsgDonateAndGift extends AbstractMsgDonateAndGift
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
        return BasicUserDao::getDataById($target_user_id);
    }
}