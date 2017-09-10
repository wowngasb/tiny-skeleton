<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2017-09
 */
namespace app\api\GraphQL_;

use app\api\GraphQL_\Type\ContentTabConfig;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AbstractContentTabConfig
 * 手机切换菜单配置
 * @package app\api\GraphQL_
 */
abstract class AbstractContentTabConfig extends ContentTabConfig
{
    
    /**
     * tab栏列表
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed TabItemConfig
     */
    abstract public function tabList($rootValue, $args, $context, ResolveInfo $info);
    
}