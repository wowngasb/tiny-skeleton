<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/30
 * Time: 17:05
 */

namespace app\common\dispatch;


use Exception;
use Tiny\Abstracts\AbstractContext;
use Tiny\Application;
use Tiny\Func;
use Tiny\Interfaces\DispatchInterface;
use Tiny\Interfaces\RequestInterface;
use Tiny\Interfaces\ResponseInterface;


class PageDispatch implements DispatchInterface
{

    /**
     * 根据对象和方法名 获取 修复后的参数
     * @param AbstractContext $context
     * @param string $action
     * @param array $params
     * @return array
     */
    public static function initMethodParams(AbstractContext $context, $action, array $params)
    {
        return Application::initMethodParams($context, $action, $params);
    }

    /**
     * 修复并返回 真实需要调用对象的 方法名称
     * @param array $routeInfo
     * @return string
     */
    public static function initMethodName(array $routeInfo)
    {
        return 'action' . trim($routeInfo[2]);
    }

    /**
     * 修复并返回 真实需要调用对象的 命名空间
     * @param array $routeInfo
     * @return string
     */
    public static function initMethodNamespace(array $routeInfo)
    {
        $controller = (!empty($routeInfo[1]) ? Func::trimlower($routeInfo[1]) : 'index') . 'Controller';
        $module = !empty($routeInfo[0]) ? Func::trimlower($routeInfo[0]) : 'index';

        return "\\" . Func::joinNotEmpty("\\", [Application::app()->getAppName(), $module, $controller]);
    }

    /**
     * 创建需要调用的对象 并检查对象和方法的合法性
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string $namespace
     * @param string $action
     * @return AbstractContext 可返回实现此接口的 其他对象 方便做类型限制
     */
    public static function initMethodContext(RequestInterface $request, ResponseInterface $response, $namespace, $action)
    {
        $request->session_start($response);  // 开启 session
        return Application::initMethodContext($request, $response, $namespace, $action);
    }

    /**
     * 调用分发 渲染输出执行结果  请在方法开头加上 固定流程 调用自身接口  无任何返回值
     * @param AbstractContext $context
     * @param string $action
     * @param array $params
     */
    public static function dispatch(AbstractContext $context, $action, array $params)
    {
        Application::dispatch($context, $action, $params);
    }

    /**
     * 处理异常接口 用于捕获分发过程中的异常
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Exception $ex
     */
    public static function traceException(RequestInterface $request, ResponseInterface $response, Exception $ex)
    {
        Application::traceException($request, $response, $ex);
    }
}