<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/15 0015
 * Time: 18:33
 */

namespace app\Exception;


class ApiCheckLimitError extends ApiError
{
    protected static $errno = 526;
}