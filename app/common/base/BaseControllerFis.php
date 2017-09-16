<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/14
 * Time: 10:01
 */

namespace app\common\base;


use Tiny\Application;
use Tiny\Controller\ControllerFis;
use Tiny\Func;

class BaseControllerFis extends ControllerFis
{
    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);

        $appname = Application::app()->getAppName();
        $config_dir = ROOT_PATH . Func::joinNotEmpty(DIRECTORY_SEPARATOR, ["{$appname}-public", 'tpl']);
        $template_dir = ROOT_PATH . Func::joinNotEmpty(DIRECTORY_SEPARATOR, ["{$appname}-public", 'tpl']);
        $this->setFisReleasePath($config_dir, $template_dir);
        $env_web = Application::get_config('ENV_WEB', []);
        $this->assign('webname', !empty($env_web['name']) ? $env_web['name'] : '');

        return $params;
    }

}