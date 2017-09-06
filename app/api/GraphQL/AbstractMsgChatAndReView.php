<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL;

use app\api\GraphQL\Type\MsgChatAndReView;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AbstractMsgChatAndReView
 * 聊天及审核消息
 * @package app\api\GraphQL
 */
abstract class AbstractMsgChatAndReView extends MsgChatAndReView
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
    
    /**
     * 目标消息 信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicMsg
     */
    abstract public function target_msg($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 当前操作者 信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicUser
     */
    abstract public function operator($rootValue, $args, $context, ResolveInfo $info);
    
}