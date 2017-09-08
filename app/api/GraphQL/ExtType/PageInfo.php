<?php

/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/9/8
 * Time: 14:18
 */

namespace app\api\GraphQL\ExtType;


class PageInfo extends \app\api\GraphQL_\ExtType\PageInfo
{
    public static function buildPageInfo($total = 0, $num = 10, $page = 1)
    {
        if (empty($total) || $total < -1) {
            $total = 0;
        }
        if (empty($num) || $num <= 0) {
            $num = 10;
        }
        if (empty($page) || $page <= 0) {
            $page = 1;
        }
        return [
            'num' => $num,
            'page' => $page,
            'total' => $total,
            'hasPreviousPage' => !($page == 1),
            'hasNextPage' => ($total > $page * $num || $total < 0)
        ];
    }

}