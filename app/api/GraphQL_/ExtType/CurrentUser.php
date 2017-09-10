<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2017-09
 */
namespace app\api\GraphQL_\ExtType;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;/**
 * Class CurrentUser
 * 当前登录用户信息 及 用户连接dms配置
 * @package app\api\GraphQL_\ExtType
 */
class CurrentUser extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "当前登录用户信息 及 用户连接dms配置";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['user'] = [
                    'type' => $type::nonNull($type::BasicUser([], $type)),
                    'description' => "当前用户信息",
                ];
                $fields['user_agent'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "用户设备信息 PC网页 WEB 手机网页 WAP 发布工具内嵌网页 PUB 后台管理页面 MGR",
                ];
                $fields['client_id'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "用户连接DMS 唯一id",
                ];
                return array_merge($fields, $_fields);
            },
            'resolveField' => function($value, $args, $context, ResolveInfo $info) {
                if (method_exists($this, $info->fieldName)) {
                    return $this->{$info->fieldName}($value, $args, $context, $info);
                } else {
                    return is_array($value) ? $value[$info->fieldName] : $value->{$info->fieldName};
                }
            },
        ];
        parent::__construct($config);
    }

}