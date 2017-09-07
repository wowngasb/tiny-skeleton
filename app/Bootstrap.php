<?php

namespace app;

use app\api\base\BaseDao;
use Tiny\Abstracts\AbstractBootstrap;
use Tiny\Application;
use Tiny\OrmQuery\OrmConfig;
use Tiny\Plugin\graphiql\dispatch\GraphiQLDispatch;
use Tiny\Request;
use Tiny\Response;
use Tiny\Route\RouteMap;
use Tiny\Dispatch\ApiDispatch;
use Tiny\Plugin\develop\dispatch\DevelopDispatch;
use app\common\dispatch\PageDispatch;

final class Bootstrap extends AbstractBootstrap
{

    /** 在app run 之前, 设置app 命名空间 并 注册路由
     *  #param Application $app
     *  #return Application
     * @param string $appname
     * @param Application $app
     * @return Application
     */
    public static function bootstrap($appname, Application $app)
    {
        if ($app->isBootstrapCompleted()) {
            return $app;
        }
        $app->addRoute('api', new RouteMap('/api', 'api', ['api', 'ApiHub', 'hello']), new ApiDispatch())
            ->addRoute('develop', new RouteMap('/develop', 'develop', ['develop', 'index', 'index']), new DevelopDispatch())
            ->addRoute('graphiql', new RouteMap('/graphiql', 'graphiql', ['graphiql', 'index', 'index']), new GraphiQLDispatch())
            ->addRoute('page', new RouteMap('/'), new PageDispatch());  // 添加默认简单路由

        OrmConfig::on('runSql', function($obj, $sql_str, $time, $_tag){
            false && func_get_args();
            $time_str = round($time, 3) * 1000;
            static::debugConsole("{$sql_str} <{$time_str}ms>", $_tag, 1);
        });
        return parent::bootstrap($appname, $app);
    }

}