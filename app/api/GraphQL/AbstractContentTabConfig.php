<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL;

use app\api\GraphQL\Type\ContentTabConfig;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AbstractContentTabConfig
 * 手机切换菜单配置
 * @package app\api\GraphQL
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