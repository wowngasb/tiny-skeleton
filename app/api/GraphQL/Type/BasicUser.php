<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL\Type;

use app\api\GraphQL\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class BasicUser
 * 用户信息 不同的用户类型对应不同的权限
 * @package app\api\GraphQL\Type
 */
class BasicUser extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = new Types();
        }
        $config = [
            'description' => "用户信息 不同的用户类型对应不同的权限",
            'fields' => []
        ];
        $config['fields']['user_type'] = [
            'type' => $type::nonNull($type::UserTypeEnum()),
            'description' => "用户类型 游客 guest, 已认证 authorized, 管理者 manager, 发布者 publisher",
        ];
        $config['fields']['user_id'] = [
            'type' => $type::nonNull($type::ID()),
            'description' => "用户 唯一id",
        ];
        $config['fields']['nick'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "用户昵称",
        ];
        $config['fields']['avatar'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "用户头像",
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