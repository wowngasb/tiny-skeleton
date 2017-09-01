<?php

namespace app;

use Tiny\Abstracts\AbstractBootstrap;
use Tiny\Application;
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
            ->addRoute('develop', new RouteMap('/develop', 'develop'), new DevelopDispatch())
            ->addRoute('page', new RouteMap('/'), new PageDispatch());  // 添加默认简单路由

        Application::on('routerShutdown', function (Application $obj, Request $request, Response $response) {
            false && func_get_args();
        });

        return parent::bootstrap($appname, $app);
    }

}