<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/29 0029
 * Time: 10:15
 */

namespace Tiny\OrmQuery;


use Tiny\Func;

abstract class AbstractQuery
{

    protected static $_allow_operator = [
        '=' => 1,
        '>' => 1,
        '<' => 1,
        '>=' => 1,
        '<=' => 1,
        '<>' => 1,
        'like' => 1,
    ];

    protected $_filter = null;

    public function __construct(callable $filter = null)
    {
        $this->_filter = $filter;
    }

    final public function buildQuery($column)
    {
        $action = Func::class2name(get_class($this));
        $enable = !empty($this->_filter) ? call_user_func_array($this->_filter, [$this]) : true;

        $query = $this->_queryArgs();
        if (is_array($query)) {
            array_unshift($query, $column);  //把字段名插入到 参数的第一个
        } else {
            $query = [$column,];
        }
        return [$enable, $action, $query];
    }

    /**
     * @return array  返回 $query格式的数组  表示查询参数数组
     */
    abstract protected function _queryArgs();

}