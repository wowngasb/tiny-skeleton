<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/11 0011
 * Time: 17:29
 */

namespace app\Libs;


use app\AbstractClass;
use Tiny\Traits\EncryptTrait;

class SteelEncrypter extends AbstractClass
{

    use EncryptTrait;

    ######################################################
    ################ 重写 EncryptTrait 方法 ##############
    ######################################################

    protected static function _getSalt()
    {
        // 修改这个 key 会影响所有 临时性的加密 token 只会导致短时间内 token 加密解密失效
        return '_za@#_rEd_SaLT';
    }


}