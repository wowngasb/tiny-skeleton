<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL\ExtType;

use app\api\GraphQL\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class CurrentUser
 * 当前登录用户信息 及 用户连接dms配置
 * @package app\api\GraphQL\ExtType
 */
class CurrentUser extends ObjectType
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
        $config['fields']['user'] = [
            'type' => $type::nonNull($type::BasicUser([], $type)),
            'description' => "当前用户信息",
        ];
        $config['fields']['user_agent'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "用户设备信息 PC网页 WEB 手机网页 WAP 发布工具内嵌网页 PUB 后台管理页面 MGR",
        ];
        $config['fields']['client_id'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "用户连接DMS 唯一id",
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