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
 * Class PageInfo
 * 分页信息
 * @package app\api\GraphQL\ExtType
 */
class PageInfo extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = new Types();
        }
        $config = [
            'description' => "分页信息",
            'fields' => []
        ];
        $config['fields']['num'] = [
            'type' => $type::nonNull($type::Int()),
            'description' => "每页数量",
        ];
        $config['fields']['total'] = [
            'type' => $type::nonNull($type::Int()),
            'description' => "总数",
        ];
        $config['fields']['page'] = [
            'type' => $type::nonNull($type::Int()),
            'description' => "当前页数",
        ];
        $config['fields']['hasNextPage'] = [
            'type' => $type::nonNull($type::Boolean()),
            'description' => "是否拥有下一页",
        ];
        $config['fields']['hasPreviousPage'] = [
            'type' => $type::nonNull($type::Boolean()),
            'description' => "是否拥有上一页",
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