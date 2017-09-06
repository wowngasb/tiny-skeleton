<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Type;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class TabItemConfig
 * 单个tab选项的配置
 * @package app\api\GraphQL_\Type
 */
class TabItemConfig extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = new Types();
        }
        $config = [
            'description' => "单个tab选项的配置",
            'fields' => []
        ];
        $config['fields']['title'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "标题",
        ];
        $config['fields']['new_msg'] = [
            'type' => $type::nonNull($type::Int()),
            'description' => "提醒新消息数量",
        ];
        $config['fields']['component'] = [
            'type' => $type::nonNull($type::String()),
            'description' => "对应区域内容类型",
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