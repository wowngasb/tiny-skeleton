<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/14
 * Time: 9:40
 */

namespace app\index;

use app\common\BaseIndexController;

class IndexController extends BaseIndexController
{

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);

        $this->assign('xxx', 0);

        return $params;
    }


    public function actionIndex()
    {
        $this->display();
    }

    public function actionTest()
    {
        $a = $this->_get('a', 123);
        $b = $this->_get('b', 456);
        $sum = intval($a) + intval($b);

        $this->assign('a', $a);
        $this->assign('b', $b);
        $this->assign('sum', $sum);
        $this->display();
    }

}