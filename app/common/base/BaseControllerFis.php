<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/14
 * Time: 10:01
 */

namespace app\common\base;


use Tiny\Application;
use Tiny\Controller\FisController;
use Tiny\Util as Func;

class BaseControllerFis extends FisController
{
    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);

        $config_dir = ROOT_PATH . Func::joinNotEmpty(DIRECTORY_SEPARATOR, ["public", 'tpl']);
        $template_dir = ROOT_PATH . Func::joinNotEmpty(DIRECTORY_SEPARATOR, ["public", 'tpl']);
        self::setFisPath($config_dir, $template_dir);
        $env_web = Application::config('ENV_WEB', []);
        $this->assign('webname', !empty($env_web['name']) ? $env_web['name'] : '');

        return $params;
    }

}