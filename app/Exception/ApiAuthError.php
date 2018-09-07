<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/29 0029
 * Time: 1:05
 */

namespace app\Exception;


use Tiny\Exception\AuthError;

class ApiAuthError extends AuthError
{
    protected static $errno = 531;
}