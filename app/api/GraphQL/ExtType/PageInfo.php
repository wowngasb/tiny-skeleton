<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/5 0005
 * Time: 18:13
 */

namespace app\api\GraphQL\ExtType;

use app\api\GraphQL_\ExtType\PageInfo as _PageInfo;

class PageInfo extends _PageInfo
{

    public static function buildPageInfoEx($list, array $offset, $sortOption = [], $allowSortField = [])
    {
        return [
            'rows' => $list,
            'pageInfo' => static::buildPageInfo($offset['page'], $offset['num'], $offset['total'], $sortOption, $allowSortField),
        ];
    }

    public static function buildPageInfo($page, $num, $total, $sortOption = [], $allowSortField = [])
    {
        $page = intval($page);
        $num = intval($num);
        $total = intval($total);
        $page = $page > 1 ? $page : 1;
        $num = $num > 1 ? $num : 1;
        $total = ($total >= 0 || $total == -1) ? $total : 0;

        return [
            'num' => $num,
            'page' => $page,
            'total' => $total,
            'hasPreviousPage' => $page > 1,
            'hasNextPage' => ($total > $page * $num || $total == -1),
            'sortOption' => [
                'field' => !empty($sortOption['field']) ? $sortOption['field'] : '',
                'direction' => !empty($sortOption['direction']) ? $sortOption['direction'] : 'asc',
            ],
            'allowSortField' => $allowSortField,
        ];
    }

}