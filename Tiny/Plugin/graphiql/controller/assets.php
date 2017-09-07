<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/7
 * Time: 14:02
 */

namespace Tiny\Plugin\graphiql\controller;


use Tiny\Func;
use Tiny\Plugin\graphiql\base\BaseGraphiQLController;

class assets extends BaseGraphiQLController
{

    public function index()
    {
        $uri = $this->request->getRequestUri();
        $tmp_list = explode('/', $uri);
        $file_name = $tmp_list[count($tmp_list) - 1];
        $file_path = Func::joinNotEmpty(DIRECTORY_SEPARATOR, [static::$template_dir, $this->routeInfo[0], $this->routeInfo[1], $file_name]);
        if (Func::str_endwith($file_name, '.css')) {
            $this->response->addHeader('Content-Type:text/css', true);
        } else if (Func::str_endwith($file_name, '.js')) {
            $this->response->addHeader('Content-Type:application/javascript', true);
        }
        $file_str = file_get_contents($file_path);
        $this->response->appendBody($file_str);
    }

}