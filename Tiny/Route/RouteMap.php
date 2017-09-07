<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/28 0028
 * Time: 17:40
 */

namespace Tiny\Route;


use Tiny\Func;
use Tiny\Request;
use Tiny\RouteInterface;


/**
 * Class RouteMap
 * RouteMap 是一种简单的路由协议, 它将REQUEST_URI中以'/'分割的节, 组合在一起, 形成一个分层的控制器或者动作的路由结果.
 * 对于请求request_uri为"/ap/foo/bar"
 * $base_uri  '/ap'  $default_module 为  'ap'
 * 则最后得到的路由信息为 ['ap', 'foo', 'bar']
 * $base_uri  不为空时  前缀不匹配的时候, RouteMap 会返回失败, 将路由权交给下一个路由协议.
 * $base_uri  为 '/' 时   RouteMap 会尽可能补全路由信息
 */
class RouteMap implements RouteInterface
{

    private $_base_uri = '';
    private $_default_module = '';
    private $_default_route_info = ['index', 'index', 'index'];

    public function __construct($base_uri = '/', $default_module = '', array $default_route_info = [])
    {
        $base_uri = Func::trimlower($base_uri);
        $base_uri = Func::str_startwith($base_uri, '/') ? $base_uri : "/{$base_uri}";
        $base_uri = Func::str_endwith($base_uri, '/') ? $base_uri : "{$base_uri}/";
        $this->_base_uri = $base_uri;
        $this->_default_module = Func::trimlower($default_module);
        $this->_default_route_info = Func::mergeNotEmpty($this->_default_route_info, $default_route_info);
    }

    /**
     * 根据请求的 $_method $_request_uri $_language 得出 路由信息 及 参数
     * 匹配成功后 获得 路由信息 及 参数  总是可以成功
     * 一般参数应设置到 php 原始 $_GET, $_POST $_REQUEST 中， 保持一致性
     * @param Request $request 请求对象
     * @return array 匹配成功 [$routeInfo, $params]  失败 [null, null]
     */
    public function route(Request $request)
    {
        $uri_origin = $request->getRequestPath();
        if (!Func::stri_startwith($uri_origin, $this->_base_uri)) {
            return [null, null];
        }

        list($default_module, $default_controller, $default_action) = $this->getDefaultRouteInfo();
        $uri = str_replace($this->_base_uri, '/', $uri_origin);
        $split_list = Func::splitNotEmpty('/', $uri);
        if (!empty($this->_default_module)) {
            $split_list = [$this->_default_module, isset($split_list[0]) ? $split_list[0] : $default_controller, isset($split_list[1]) ? $split_list[1] : $default_action];
        } else {
            $split_list = [isset($split_list[0]) ? $split_list[0] : $default_module, isset($split_list[1]) ? $split_list[1] : $default_controller, isset($split_list[2]) ? $split_list[2] : $default_action];
        }

        $_uri = "/{$split_list[0]}/{$split_list[1]}/{$split_list[2]}";
        $reg_str = "^\/([A-Za-z0-9_]+)\/([A-Za-z0-9_]+)\/([A-Za-z0-9_]+)";
        $matches = [];
        preg_match("/{$reg_str}/i", $_uri, $matches);

        if (!empty($matches[1]) && !empty($matches[2]) && !empty($matches[3])) {
            $routeInfo = [Func::trimlower($matches[1]), Func::trimlower($matches[2]), Func::trimlower($matches[3])];
            return [$routeInfo, $request->_request()];
        } else {
            return [null, null];
        }
    }

    /**
     * 根据 路由信息 及 参数 生成反路由 得到 url
     * @param array $routeInfo 路由信息数组
     * @param array $params 参数数组
     * @return string
     */
    public function url(array $routeInfo, array $params = [])
    {
        list($default_module, $default_controller, $default_action) = $this->getDefaultRouteInfo();

        $module = !empty($routeInfo[0]) ? Func::trimlower($routeInfo[0]) : $default_module;
        $controller = !empty($routeInfo[1]) ? Func::trimlower($routeInfo[1]) : $default_controller;
        $action = !empty($routeInfo[2]) ? Func::trimlower($routeInfo[2]) : $default_action;

        $url = SYSTEM_HOST . "{$module}/{$controller}/{$action}";
        $args_list = [];
        foreach ($params as $key => $val) {
            $args_list[] = trim($key) . '=' . urlencode($val);
        }
        return !empty($args_list) ? $url . '?' . join('&', $args_list) : $url;
    }

    /**
     * 获取路由 默认参数 用于url参数不齐全时 补全
     * @return array  $routeInfo [$module, $controller, $action]
     */
    public function getDefaultRouteInfo()
    {
        return $this->_default_route_info;  // 默认 $routeInfo
    }
}