<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/1 0001
 * Time: 17:39
 */

namespace Tiny\View;


use Tiny\Plugin\Fis;

class ViewFis extends ViewSimple
{

    public static function setFis($config_dir, $template_dir)
    {
        Fis::initFisResource($config_dir, $template_dir);
    }

    /**
     * 渲染一个 widget 视图模板, 得到结果
     * @param string $widget_path 视图模板的文件, 绝对路径, 一般这个路径由Controller提供
     * @param array $tpl_vars 关联数组, 模板变量
     * @return string
     */
    public static function widget($widget_path, array $tpl_vars = [])
    {
        ob_start();
        ob_implicit_flush(false);
        Fis::widget($widget_path, $tpl_vars);

        return ob_get_clean();
    }

    /**
     * 渲染一个视图模板, 并直接输出给请求端
     * @param string $view_path 视图模板的文件, 绝对路径, 一般这个路径由Controller提供
     * @param array $tpl_vars 关联数组, 模板变量
     */
    public static function display($view_path, array $tpl_vars = [])
    {
        Fis::display($view_path, $tpl_vars);
    }

}