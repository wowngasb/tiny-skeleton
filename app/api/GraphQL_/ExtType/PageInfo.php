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
 * Class PageInfo
 * 分页信息
 * @package app\api\GraphQL_\ExtType
 */
class PageInfo extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "分页信息";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['num'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "每页数量",
                ];
                $fields['total'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "总数",
                ];
                $fields['page'] = [
                    'type' => $type::nonNull($type::Int()),
                    'description' => "当前页数",
                ];
                $fields['hasNextPage'] = [
                    'type' => $type::nonNull($type::Boolean()),
                    'description' => "是否拥有下一页",
                ];
                $fields['hasPreviousPage'] = [
                    'type' => $type::nonNull($type::Boolean()),
                    'description' => "是否拥有上一页",
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