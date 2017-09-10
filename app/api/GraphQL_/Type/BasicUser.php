<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Type;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;/**
 * Class BasicUser
 * 用户信息 不同的用户类型对应不同的权限
 * @package app\api\GraphQL_\Type
 */
class BasicUser extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "用户信息 不同的用户类型对应不同的权限";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['user_type'] = [
                    'type' => $type::nonNull($type::UserTypeEnum()),
                    'description' => "用户类型 游客 guest, 已认证 authorized, 管理者 manager, 发布者 publisher",
                ];
                $fields['user_id'] = [
                    'type' => $type::nonNull($type::ID()),
                    'description' => "用户 唯一id",
                ];
                $fields['nick'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "用户昵称",
                ];
                $fields['avatar'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "用户头像",
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