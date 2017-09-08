<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/8
 * Time: 13:52
 */

namespace app\api\GraphQL;


use app\api\Dao\TabItemConfigDao;
use app\api\GraphQL_\AbstractContentTabConfig;
use GraphQL\Type\Definition\ResolveInfo;
use Tiny\Func;

class ContentTabConfig extends AbstractContentTabConfig
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
    public function tabList($rootValue, $args, $context, ResolveInfo $info)
    {
        $content_tab_id = Func::v($rootValue, 'content_tab_id');
        return TabItemConfigDao::getDataById($content_tab_id);
    }
}