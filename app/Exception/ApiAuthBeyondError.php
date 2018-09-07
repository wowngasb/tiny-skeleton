<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/5 0005
 * Time: 11:49
 */

namespace app\Exception;


class ApiAuthBeyondError extends ApiAuthError
{

    protected static $errno = 535;
}