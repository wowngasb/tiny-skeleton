<?php

namespace app;

use Tiny\Abstracts\AbstractBoot;
use Tiny\Application;
use Tiny\Plugin\graphiql\dispatch\GraphiQLDispatch;

use Tiny\Dispatch\ApiDispatch;
use Tiny\Plugin\develop\dispatch\DevelopDispatch;
use app\common\dispatch\PageDispatch;
use Tiny\Route\MapRoute;

final class Bootstrap extends AbstractBoot
{

    /** 在app run 之前, 设置app 命名空间 并 注册路由
     *  #param Application $app
     *  #return Application
     * @param Application $app
     * @return Application
     */
    public static function bootstrap(Application $app)
    {
        if ($app->isBootstrapCompleted()) {
            return $app;
        }
        $app->addRoute('api', new MapRoute('/api', 'api', ['api', 'ApiHub', 'hello']), new ApiDispatch())
            ->addRoute('develop', new MapRoute('/develop', 'develop', ['develop', 'index', 'index']), new DevelopDispatch())
            ->addRoute('graphiql', new MapRoute('/graphiql', 'graphiql', ['graphiql', 'index', 'index']), new GraphiQLDispatch())
            ->addRoute('page', new MapRoute('/'), new PageDispatch());  // 添加默认简单路由


        
        return parent::bootstrap($app);
    }

}