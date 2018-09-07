<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/29 0029
 * Time: 1:05
 */

namespace app\Exception;

use Tiny\Exception\Error as _Error;

class Error extends _Error
{
    protected static $errno = 520;

}