<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2017-09
 */
namespace app\api\GraphQL_;

use app\api\GraphQL_\Type\BasicMsg;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AbstractBasicMsg
 * 互动消息模型 可扩展自定义类型
 * @package app\api\GraphQL_
 */
abstract class AbstractBasicMsg extends BasicMsg
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
    abstract public function msgContent($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 当前用户信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed BasicUser
     */
    abstract public function user($rootValue, $args, $context, ResolveInfo $info);
    
}