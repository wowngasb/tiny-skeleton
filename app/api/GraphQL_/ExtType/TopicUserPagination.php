<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_\ExtType;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class TopicUserPagination
 * 当前登录用户信息 及 用户连接dms配置
 * @package app\api\GraphQL_\ExtType
 */
class TopicUserPagination extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = new Types();
        }
        $config = [
            'description' => "当前登录用户信息 及 用户连接dms配置",
            'fields' => []
        ];
        $config['fields']['userList'] = [
            'type' => $type::listOf($type::CurrentUser([], $type)),
            'description' => "当前查询用户列表",
        ];
        $config['fields']['pageInfo'] = [
            'type' => $type::PageInfo([], $type),
            'description' => "分页信息",
        ];
        
        $config['resolveField'] = function($value, $args, $context, ResolveInfo $info) {
            if (method_exists($this, $info->fieldName)) {
                return $this->{$info->fieldName}($value, $args, $context, $info);
            } else {
                return is_array($value) ? $value[$info->fieldName] : $value->{$info->fieldName};
            }
        };
        if (!empty($_config['fields'])) {
            $config['fields'] = array_merge($_config['fields'], $config['fields']);
        }
        parent::__construct($config);
    }

}