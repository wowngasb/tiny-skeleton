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
 * Class ContentTabConfig
 * 手机切换菜单配置
 * @package app\api\GraphQL_\Type
 */
class ContentTabConfig extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "手机切换菜单配置";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['active'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "当前激活的tab栏标题",
                ];
                $fields['tabList'] = [
                    'type' => $type::listOf($type::TabItemConfig([], $type)),
                    'description' => "tab栏列表",
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