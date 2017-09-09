<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/14
 * Time: 12:03
 */

namespace app\api;


use app\api\Dao\BasicRoomDao;
use Tiny\Abstracts\AbstractApi;
use Tiny\Exception\ApiParamsError;
use Tiny\Func;

class ApiHub extends AbstractApi
{

    public function beforeApi(array $params)
    {
        $params = parent::beforeApi($params); // TODO: Change the autogenerated stub
        if (isset($params['name'])) {
            $params['name'] = trim(strval($params['name']));
        }

        if (Func::stri_cmp('testSum', $this->getActionName())) {
            $params['a'] = intval($params['a']);
            $params['b'] = intval($params['b']);
        }

        if (isset($params['id'])) {
            $params['id'] = intval($params['id']);
        }

        return $params;
    }

    /**
     * api hello
     * @param string $name
     * @return array
     */
    public function hello($name = 'world')
    {
        $msg = "test log name={$name}";
        self::debug($msg, __METHOD__, __CLASS__, __LINE__);
        self::info($msg, __METHOD__, __CLASS__, __LINE__);
        self::warn($msg, __METHOD__, __CLASS__, __LINE__);
        self::error($msg, __METHOD__, __CLASS__, __LINE__);
        self::fatal($msg, __METHOD__, __CLASS__, __LINE__);

        return ['info' => "Hello, {$name}!"];
    }

    public function testError($id)
    {
        if ($id <= 0) {
            throw new ApiParamsError('id must gt 0');
        }
        return ['id' => $id, 'info' => 'some info'];
    }

    /**
     * test sum
     * @param int $a
     * @param int $b
     * @return array
     */
    public function testSum($a, $b)
    {
        $sum = $a + $b;
        $msg = "test log a={$a} b={$b}, sum={$sum}";
        self::debug($msg, __METHOD__, __CLASS__, __LINE__);
        self::info($msg, __METHOD__, __CLASS__, __LINE__);
        self::warn($msg, __METHOD__, __CLASS__, __LINE__);
        self::error($msg, __METHOD__, __CLASS__, __LINE__);
        self::fatal($msg, __METHOD__, __CLASS__, __LINE__);
        return ['data' => $sum];
    }


    public function testOrm($room_id = 101)
    {
        $roomInfo = BasicRoomDao::getOneById($room_id, 0);
        return ['room' => $roomInfo];
    }

}