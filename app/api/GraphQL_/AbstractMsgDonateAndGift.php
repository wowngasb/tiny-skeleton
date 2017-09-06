<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_;

use app\api\GraphQL_\Type\MsgDonateAndGift;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AbstractMsgDonateAndGift
 * 打赏及赠送礼物消息
 * @package app\api\GraphQL_
 */
abstract class AbstractMsgDonateAndGift extends MsgDonateAndGift
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
    abstract public function target_user($rootValue, $args, $context, ResolveInfo $info);
    
}