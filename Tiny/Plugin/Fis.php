<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/1 0001
 * Time: 17:45
 */

namespace Tiny\Plugin;

class Fis
{

    public static function initFisResource($config_dir, $template_dir)
    {
        //设置配置和模板文件夹
        FisResource::setConfig([
            'config_dir' => $config_dir,
            'template_dir' => $template_dir,
        ]);
    }

    public static function scriptStart()
    {
        ob_start();
        ob_implicit_flush(false);
        return '';
    }

    private static function getScriptContent($_script)
    {
        $script = trim($_script);
        $s_idx = stripos($script, '<script');
        $e_idx = stripos($script, '>', $s_idx + 1);
        if ($e_idx === false || $s_idx === false) {
            return $script;
        }
        $end_idx = strripos($script, '</script>', $e_idx);
        $text = substr($script, $e_idx + 1, $end_idx - ($e_idx + 1));
        return $text;
    }

    private static function getStyleContent($_style)
    {
        $style = trim($_style);
        $s_idx = stripos($style, '<style');
        $e_idx = stripos($style, '>', $s_idx + 1);
        if ($e_idx === false || $s_idx === false) {
            return $style;
        }
        $end_idx = strripos($style, '</style>', $e_idx);
        $text = substr($style, $e_idx + 1, $end_idx - ($e_idx + 1));
        return $text;
    }

    public static function scriptEnd($priority = 1)
    {
        $script = ob_get_clean();
        $text = self::getScriptContent($script);
        if (!empty($text)) {
            FisResource::addScriptPool($text, $priority);
        }
        return '';
    }

    public static function styleStart()
    {
        ob_start();
        ob_implicit_flush(false);
        return '';
    }

    public static function styleEnd()
    {
        $style = ob_get_clean();
        $text = self::getStyleContent($style);
        if (!empty($text)) {
            FisResource::addStylePool($text);
        }
        return '';
    }

    /**
     * 设置前端加载器
     * @param string $id
     * @return string
     */
    public static function framework($id)
    {
        FisResource::setFramework(FisResource::getUri($id));
        return '';
    }

    /**
     * 加载某个资源及其依赖
     * @param  string $id
     * @param bool $async 是否为异步组件（only JS）
     * @return string
     */
    public static function import($id, $async = false)
    {
        FisResource::load($id, $async);
        return '';
    }

    /**
     * 添加标记位
     * @param  string $type
     * @return string
     */
    public static function placeholder($type)
    {
        return FisResource::placeholder($type);
    }

    /**
     * 加载组件
     * @param  string $id
     * @param  array $tpl_vars
     * @return string
     */
    public static function widget($id, array $tpl_vars = [])
    {
        $path = FISResource::getUri($id);
        if (is_file($path)) {
            extract($tpl_vars, EXTR_OVERWRITE);
            include($path);
            FisResource::load($id);
        }
        return '';
    }

    /**
     * 渲染页面
     * @param  string $id
     * @param  array $tpl_vars
     */
    public static function display($id, array $tpl_vars)
    {
        $path = FISResource::getUri($id);

        if (is_file($path)) {
            ob_start();
            ob_implicit_flush(false);
            extract($tpl_vars, EXTR_OVERWRITE);
            include($path);
            $html = ob_get_clean();
            FisResource::load($id); //注意模板资源也要分析依赖，否则可能加载不全
            echo FisResource::renderResponse($html);
        } else {
            trigger_error($id . ' file not found!');
        }
    }

}