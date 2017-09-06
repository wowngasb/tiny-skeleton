<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/15
 * Time: 17:25
 */

namespace app\api\base;


use Tiny\Traits\CacheTrait;
use Tiny\Traits\LogTrait;

class BaseModel
{
    use LogTrait, CacheTrait;


}