<?php

namespace Tiny\Plugin\develop\base;

use Tiny\Plugin\DevAuthControllerSimple;

class BaseDevelopController extends DevAuthControllerSimple
{

    public function beforeAction()
    {
        parent::beforeAction();
        $template_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
        $this->setTemplatePath($template_dir, $template_dir);
        $this->assign('tool_title', 'Tiny开发者工具');
    }


}