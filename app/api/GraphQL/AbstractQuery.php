<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL;

use app\api\GraphQL\ExtType\Query;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AbstractQuery
 * AbstractQuery
 * @package app\api\GraphQL
 */
abstract class AbstractQuery extends Query
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
    abstract public function user($rootValue, $args, $context, ResolveInfo $info);
    
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
    abstract public function room($rootValue, $args, $context, ResolveInfo $info);
    
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
    abstract public function msg($rootValue, $args, $context, ResolveInfo $info);
    
}