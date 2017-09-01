<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/25 0025
 * Time: 14:52
 */

namespace Tiny\Controller;

use Tiny\Abstracts\AbstractController;
use Tiny\View\ViewFis;
use Tiny\Func;
use Tiny\Request;
use Tiny\Response;

class ControllerFis extends AbstractController
{
    final public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->setView(new ViewFis());
    }

    public function setFisReleasePath($config_dir, $template_dir)
    {
        ViewFis::setFis($config_dir, $template_dir);
    }

    /**
     * @param string $tpl_path
     */
    public function display($tpl_path = '')
    {
        $tpl_path = Func::trimlower($tpl_path);
        if (empty($tpl_path)) {
            $tpl_path = $this->routeInfo[2] . '.php';
        } else {
            $tpl_path = Func::stri_endwith($tpl_path, '.php') ? $tpl_path : "{$tpl_path}.php";
        }
        $file_path = "view/{$this->routeInfo[0]}/{$this->routeInfo[1]}/{$tpl_path}";
        $view = $this->getView();
        $params = $view->getAssign();
        $params['routeInfo'] = $this->routeInfo;
        $params['appname'] = $this->appname;
        $params['request'] = $this->request;

        if (!empty($this->_layout_tpl)) {
            $layout_tpl = Func::stri_endwith($this->_layout_tpl, '.php') ? $this->_layout_tpl : "{$this->_layout_tpl}.php";
            $layout_path = $file_path = "view/{$this->routeInfo[0]}/{$this->routeInfo[1]}/{$layout_tpl}";
            if (is_file($layout_path)) {
                static::fire('preDisplay', [$this, $file_path, $params]);
                ob_start();
                ob_implicit_flush(false);
                $view->display($file_path, $params);
                $action_content = ob_get_clean();

                $params['action_content'] = $action_content;

                static::fire('preDisplay', [$this, $layout_path, $params]);
                $view->display($layout_path, $params);
                return;
            }
        }
        static::fire('preDisplay', [$this, $file_path, $params]);
        $view->display($file_path, $params);
    }

    /**
     * @param string $tpl_path
     * @param array $params
     * @return string
     * @throws \Tiny\Exception\AppStartUpError
     */
    public function widget($tpl_path, array $params = [])
    {
        $tpl_path = Func::trimlower($tpl_path);
        if (empty($tpl_path)) {
            return '';
        }
        $tpl_path = Func::stri_endwith($tpl_path, '.php') ? $tpl_path : "{$tpl_path}.php";

        $params['routeInfo'] = $this->routeInfo;
        $params['appname'] = $this->appname;
        $params['request'] = $this->request;

        $file_path = "widget/{$tpl_path}";

        static::fire('preWidget', [$this, $file_path, $params]);
        $buffer = $this->getView()->widget($file_path, $params);
        return $buffer;
    }

}