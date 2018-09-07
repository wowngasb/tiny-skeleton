<?php

namespace app;

use app\Libs\NewPaginationPresenter;
use FastRoute\RouteCollector;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\Paginator;
use PhpConsole\Connector;
use PhpConsole\Storage\File;
use Tiny\Abstracts\AbstractBoot;
use Tiny\Application;
use Tiny\Dispatch\ApiDispatch;
use Tiny\Event\ApplicationEvent;
use Tiny\Event\CacheEvent;
use Tiny\Interfaces\RequestInterface;
use Tiny\Plugin\DbHelper;
use Tiny\Plugin\develop\dispatch\DevelopDispatch;
use Tiny\Plugin\EmptyMock;
use Tiny\Plugin\graphiql\dispatch\GraphiQLDispatch;
use Tiny\Plugin\LogHelper;
use Tiny\Plugin\RedisSession;
use Tiny\Route\DefaultRoute;
use Tiny\Route\FastRoute;
use Tiny\Route\MapRoute;
use Tiny\Traits\CacheConfig;


final class Boot extends AbstractBoot
{

    /**
     * 在app run 之前, 设置app 命名空间 并 注册路由
     * @param Application $app
     * @return Application
     * @throws \Tiny\Exception\AppStartUpError
     */
    public static function bootstrap(Application $app)
    {
        if ($app->isBootstrapCompleted()) {
            return $app;
        }
        # ApiDispatch 会保持 目录名 类名 原大小写状态 不作处理
        # 默认分发器 会强制把 所有目录转为小写
        $app->addRoute('r-api', new MapRoute('/api', 'api', ['api', 'ApiHub', 'hello']), new ApiDispatch())// 默认 API 路由
        ->addRoute('r-develop', new MapRoute('/develop', 'develop', ['develop', 'index', 'index']), new DevelopDispatch())// 开发工具 插件
        ->addRoute('r-graphiql', new MapRoute('/graphiql', 'graphiql', ['graphiql', 'index', 'index']), new GraphiQLDispatch())// graphql 插件
        ->addRoute('r-fast-web', self::_webHostRoute(), new PageDispatch())
            ->addRoute('r-default', new DefaultRoute('/', ['Http', 'IndexController', 'index']), new PageDispatch());  // 添加默认简单路由 处理异常情况

        self::initCacheConfig();
        self::registerGlobalEvent();

        return parent::bootstrap($app);
    }

    protected static function _tryGetUsedMilliSecond()
    {
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            return $request->usedMilliSecond();
        }
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            return $request->usedMilliSecond();
        }
        return -1;
    }

    public static function getRouteCachePath()
    {
        return App::cache_path(['fast_route']);
    }

    /**
     * @return Connector
     * @throws \Exception
     */
    protected static function getConsoleInstance()
    {
        //开启 辅助调试模式 注册对应事件
        if (empty(static::$_consoleInstance)) {
            $console_path = self::getConsoleStorageFile();
            Connector::setPostponeStorage(new File($console_path));
            Connector::getInstance()->setPassword(Application::config('ENV_DEVELOP_KEY'), true);
            static::$_consoleInstance = Connector::getInstance();
        }
        return static::$_consoleInstance;
    }

    public static function getConsoleStorageFile()
    {
        return Application::cache_path(['console.data'], false);
    }

    private static function _webHostRoute()
    {
        return new FastRoute(function (RouteCollector $r) {
            $r->get('/', ['Http', 'Front\\IndexController', 'index']);
            $r->get('/index', ['Http', 'Front\\IndexController', 'index']);

            $r->get('/article/{article_id:\d+}', ['Http', 'Front\\IndexController', 'article']);
            $r->get('/classify/{classify_id:\d+}', ['Http', 'Front\\IndexController', 'classify']);

            $r->get('/user/{uid:\d+}', ['Http', "Front\\UserController", "userInfo"]);

            $r->get('/errorpage[/{code}]', ['Http', 'ErrorController', 'page']);
            $r->get('/deploy/', ['Http', 'DeployController', 'index']);
            $r->addRoute(['GET', 'POST'], '/deploy[/{_action}]', ['Http', 'DeployController']);

            $r->get('/front/', ['Http', 'Front\\IndexController', 'index']);
            $r->addRoute(['GET', 'POST'], '/front[/{_action}]', ['Http', 'Front\\IndexController']);

            $r->addRoute(['GET', 'POST'], '/front/faq/{doc_id:\d+}', ['Http', 'Front\\IndexController', 'faq']);

            $r->get('/register/', ['Http', 'Front\\Auth\\RegisterController', 'index']);
            $r->addRoute(['GET', 'POST'], '/register[/{_action}]', ['Http', 'Front\\Auth\\RegisterController']);

            $r->get('/auth/', ['Http', 'Front\\Auth\\AuthController', 'index']);
            $r->addRoute(['GET', 'POST'], '/auth[/{_action}]', ['Http', 'Front\\Auth\\AuthController']);

            $r->addRoute(['GET', 'POST'], '/mgr/auth[/{_action}]', ['Http', "Front\\Auth\\SiteMgrAuthController"]);

            $r->get('/mgr', ['Http', 'Article\\IndexController', 'index']);
            $r->addGroup('/mgr', function (RouteCollector $r) {
                $r->get('/', ['Http', "Article\\IndexController", "index"]);

                $r->addRoute(['GET', 'POST'], '/artmgr[/{_action}]', ['Http', "Article\\ArtMgrController"]);

                $r->addRoute(['GET', 'POST'], '/seo[/{_action}]', ['Http', "Article\\SeoController"]);

            });

            $r->get('/cash', ['Http', 'Cash\\IndexController', 'index']);
            $r->addGroup('/cash', function (RouteCollector $r) {
                $r->get('/', ['Http', "Cash\\IndexController", "index"]);

                $r->addRoute(['GET', 'POST'], '/custom[/{_action}]', ['Http', "Cash\\CustomController"]);

                $r->addRoute(['GET', 'POST'], '/notify[/{_action}]', ['Http', "Cash\\NotifyController"]);

                $r->addRoute(['GET', 'POST'], '/oprate[/{_action}]', ['Http', "Cash\\OprateController"]);
            });


            $r->addGroup('/admin', function (RouteCollector $r) {
                $r->post('/ajaxUpload', ['Http', 'UploadController', 'ajaxUpload']);
                $r->post('/csvDownload', ['Http', 'AdminDownloadController', 'csvDownload']);
                $r->post('/homeAnalysisDataCsv', ['Http', 'AdminDownloadController', 'homeAnalysisDataCsv']);
                $r->post('/roomVodDataCsv', ['Http', 'AdminDownloadController', 'roomVodDataCsv']);
            });

            $r->post('/ajaxUpload', ['Http', "UploadController", "ajaxUpload"]);
            $r->addRoute(['GET', 'POST'],'/ajaxUploadVss', ['Http', "UploadController", "ajaxUploadVss"]);

            $r->get('/super/', ['Http', 'Super\\IndexController', 'index']);
            $r->addRoute(['GET', 'POST'], '/super[/{_action}]', ['Http', 'Super\\IndexController']);

            $r->get('/agent/', ['Http', 'Super\\IndexController', 'index']);
            $r->addRoute(['GET', 'POST'], '/agent[/{_action}]', ['Http', 'Agent\\IndexController']);

            $r->get('/parent/', ['Http', 'Parent\\IndexController', 'index']);
            $r->addRoute(['GET', 'POST'], '/parent[/{_action}]', ['Http', 'Parent\\IndexController']);

            $r->get('/sub/', ['Http', 'Sub\\IndexController', 'index']);
            $r->addRoute(['GET', 'POST'], '/sub[/{_action}]', ['Http', 'Sub\\IndexController']);

        }, ['Http', 'Front\\IndexController', 'index'], Util::path_join(self::getRouteCachePath(), ['route.r-fast-web.cache'], false), function (RequestInterface $request, $controller, $action) {
            false && func_get_args();
            return $action;
        });
    }

    public static function _getSessionPreKey()
    {
        $countly_pre = App::config('ENV_WEB.countly_pre', 'steel');
        return !empty($countly_pre) ? "_S_{$countly_pre}" : "_S_unknown";
    }

    public static function _getSessionMapKey()
    {
        $countly_pre = App::config('ENV_WEB.countly_pre', 'steel');
        return !empty($countly_pre) ? "_U_{$countly_pre}" : "_U_unknown";
    }

    public static function tryStartSession(RequestInterface $request)
    {
        $countly_pre = App::config('ENV_WEB.countly_pre', 'steel');
        $request->session_name(App::config('session.name', "S_" . Util::short_md5($countly_pre)));
        $mRedis = AbstractClass::_getRedisInstance();
        if (empty($mRedis) || $mRedis instanceof EmptyMock) {
            error_log(__METHOD__ . ' can not session_set_save_handler RedisSession  mRedis by _getRedisInstance ');
        } else {
            $request->session_set_save_handler(new RedisSession(self::_getSessionPreKey(), 0, 3600 * 100), true);
        }

        $request->session_start();
        $sid = $request->session_id();
        if (empty($sid)) {
            $request->session_id(Util::rand_str(24));
        }
    }

    private static function registerGlobalEvent()
    {
        App::on('routerStartup', function (ApplicationEvent $event) {
            $request = $event->getRequest();

            $_environ = $request->_request('ENVIRON', '');
            if (!empty($_environ)) {
                $_config = App::app()->getConfig();
                $_config['ENVIRON'] = $_environ;
                App::app()->setConfig($_config);
            }

            self::tryStartSession($request);

            if (App::dev()) {
                self::registerDebugEvent();
                self::actionRouterStartup($event);
            }
            return null;
        });

        // dispatchLoopStartup 分发开始  此时 response 才准备好 可以开启 session 把 sid 写入 cookie
        App::on('dispatchLoopStartup', function (ApplicationEvent $event) {
            $request = $event->getRequest();

            //   注册 分页类
            Paginator::currentPageResolver(function () use ($request) {
                return $request->_request('page', 1);
            });
            Paginator::currentPathResolver(function () use ($request) {
                $path = $request->path();
                if (!Util::str_startwith($path, '/')) {
                    $path = "/{$path}";
                }
                return $path;
            });
            Paginator::presenter(function (AbstractPaginator $_paginator) use ($request) {
                $_paginator->appends($request->all_get());
                /** @var \Illuminate\Contracts\Pagination\Paginator $paginator */
                $paginator = $_paginator;
                return new NewPaginationPresenter($paginator, $request);
            });
        });


    }

    private static function initCacheConfig()
    {
        $cache = new CacheConfig();
        $cache->setEncodeResolver(function ($val) {
            return serialize($val);
        });
        $cache->setDecodeResolver(function ($str_val) {
            return unserialize($str_val);
        });
        $cache->setMethodResolver(function ($method) {
            return str_replace([
                'app\\Http\\Controllers\\',
                'app\\Console\\Commands\\',
                'app\\Libs\\',
                'app\\api\\',
            ], [
                'Ctrl:',
                'Cmd:',
                'Lib:',
                'Api:',
            ], $method);
        });
        $cache->setPreFixResolver(function ($pre_fix = null) {
            if (empty($pre_fix)) {
                $countly_pre = App::config('ENV_WEB.countly_pre', 'steel');
                $pre_fix = !empty($countly_pre) ? "R_{$countly_pre}" : "R_unknown";
            }
            return $pre_fix;
        });

        $cache->setBaseConfig(true, true);

        CacheConfig::setConfig(function () use ($cache) {
            return $cache;
        });
    }

    protected static function debugStrap($routerShutdown = true, $dispatchLoopStartup = true, $dispatchLoopShutdown = false, $preDispatch = false, $postDispatch = false, $preDisplay = true, $preWidget = true, $apiResult = true, $apiException = true, $runSql = true)
    {
        parent::debugStrap($routerShutdown, $dispatchLoopStartup, $dispatchLoopShutdown, $preDispatch, $postDispatch, $preDisplay, $preWidget, $apiResult, $apiException, $runSql);
    }

    private static function registerDebugEvent($debugSql = true, $debugMdel = true, $debugMhit = false, $debugDelKey = true, $debugDelTag = true, $debugHit = false, $debugCache = true, $debugSkip = true, $logSql = false, $logCache = false)
    {
        if ($debugSql) {
            DbHelper::setOrmEventCallback(function ($type, $event) use ($logSql) {
                if ($type == 'QueryExecuted') {
                    /** @var \Illuminate\Database\Events\QueryExecuted $event */
                    $sql_str = Util::prepare_query($event->sql, $event->bindings);
                    $_tag = "Orm::sql {$sql_str} ({$event->time}ms)";
                    $tag = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->debugTag($_tag) : $_tag;
                    if ($logSql || App::config('app.dev_log_sql', false)) {
                        $log = LogHelper::create("debug_sql");
                        $url = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->getRequestUri() : '';
                        $bindings_str = !empty($event->bindings) ? ", bindings:" . json_encode($event->bindings) : '';

                        $t = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->usedMilliSecond() : 0;
                        $t_str = ($t > 0 && $t < 1000) ? "{$t}ms" : ($t >= 1000 ? ($t / 1000) . "s" : '');
                        $log->debug("Orm::sql {$sql_str} ({$event->time}ms){$bindings_str}  [url:{$url}@{$t_str}]");
                    }
                    App::_D(['bindings' => $event->bindings], $tag);
                } else {
                    /** @var \Illuminate\Database\Events\QueryExecuted $event */
                    $tag = Controller::_getRequestByCtx()->debugTag('DbHelper');
                    App::_D(['type' => $type, 'event' => $event], $tag);
                }
            });
        }

        foreach (['mdel' => $debugMdel, 'mhit' => $debugMhit, 'delkey' => $debugDelKey, 'deltag' => $debugDelTag, 'hit' => $debugHit, 'cache' => $debugCache, 'skip' => $debugSkip] as $type => $enable) {
            $enable && CacheConfig::on($type, function (CacheEvent $ev) use ($logCache) {
                list($key, $method, $now, $tags, $timeCache, $update, $type) = [$ev->getKey(), $ev->getMethod(), $ev->getNow(), $ev->getTags(), $ev->getTimeCache(), $ev->getUpdate(), $ev->getType()];
                $cache_time = $now - $update;
                $sTag = $ev->isUseStatic() ? '*' : '-';
                $_tag = "Cache::{$type} {$method}?{$key} <{$timeCache}, {$cache_time}> {$sTag}";
                $tag = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->debugTag($_tag) : $_tag;
                if ($logCache || App::config('app.dev_log_cache', false)) {
                    $log = LogHelper::create("debug_cache");
                    $tags_str = !empty($tags) ? ", tags:" . join(',', $tags) : '';
                    $url = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->getRequestUri() : '';

                    $t = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->usedMilliSecond() : 0;
                    $t_str = ($t > 0 && $t < 1000) ? "{$t}ms" : ($t >= 1000 ? ($t / 1000) . "s" : '');
                    $log->debug("Cache::{$type} {$method}?{$key} <{$timeCache}, {$cache_time}> {$sTag}  update:{$update}, now:{$now}{$tags_str}  [url:{$url}@{$t_str}]");
                }
                App::_D(['tags' => $tags, 'update' => $update, 'now' => $now], $tag);
            });
        }

        static::debugStrap();
    }

}