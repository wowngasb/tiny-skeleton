<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/1/26 0026
 * Time: 15:18
 */

namespace app\Http\Controllers;


use app\api\AdminMgr;
use app\api\GraphQL_\Enum\AdminTypeEnum;
use app\api\GraphQL_\Enum\StateEnum;
use app\Controller;
use app\Model\AdminUser;

class DeployController extends Controller
{

    public function index()
    {
        $this->getResponse()->end('Welcome To This Site!');
    }

    //初始化 超级管理员
    public function getInit()
    {
        $tmp = AdminUser::firstItem([]);
        if (!empty($tmp)) {
            $html_str = 'Admin Exist!';
            $this->getResponse()->end($html_str);
        }

        $html_str = <<<EOT
<form action="" method="POST">
    登陆账号：<input type="text" value="" placeholder="登录名" name="name">
    登录密码：<input type="password" placeholder="密码" name="password">
    <button type="submit">创建管理员</button>
</form>
EOT;
        $this->getResponse()->end($html_str);

    }

    public function postInit($name = '', $password = '')
    {
        $tmp = AdminUser::firstItem([]);
        if (!empty($tmp)) {
            $html_str = 'Admin Exist!';
            $this->getResponse()->appendBody($html_str)->end();
        }

        if (empty($password) || empty($name)) {
            $this->getResponse()->end("Error Input!");
        }
        $adminInfo = AdminUser::newItem([
            'name' => $name,
            'title' => "超级管理员",
            'avator' => AdminMgr::$default_avator,
            'admin_type' => AdminTypeEnum::SUPER_VALUE,
            'admin_slug' => AdminUser::SLUG_SUPER,
            'state' => StateEnum::NORMAL_VALUE,
            'pasw' => $password,
        ]);

        if (!empty($adminInfo)) {
            $html_str = "init {$name} ok";
            $this->getResponse()->end($html_str);
        }
    }

}