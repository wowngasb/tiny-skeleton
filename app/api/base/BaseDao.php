<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/30
 * Time: 16:03
 */

namespace app\api\base;


use Tiny\Abstracts\AbstractModel;
use Tiny\Traits\OrmTrait;

class BaseDao extends AbstractModel
{

    use OrmTrait;

    protected static $cache_time = 0;
    protected static $max_select = 5000;

    protected static $debug = false;
}