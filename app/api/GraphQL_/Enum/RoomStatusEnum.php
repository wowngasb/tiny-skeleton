<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Enum;

use GraphQL\Type\Definition\EnumType;

/**
 * Class RoomStatusEnum
 * 直播活动状态 正常 normal, 冻结 frozen, 删除 deleted
 * @package app\api\GraphQL_\Enum
 */
class RoomStatusEnum extends EnumType
{

    public function __construct(array $_config = [])
    {
        $config = [
            'description' => "直播活动状态 正常 normal, 冻结 frozen, 删除 deleted",
            'values' => []
        ];
        $config['values']['frozen'] = [
            'value' => '2',
            'description' => "直播活动状态 正常 normal, 冻结 frozen, 删除 deleted",
        ];
        $config['values']['deleted'] = [
            'value' => '9',
            'description' => "直播活动状态 正常 normal, 冻结 frozen, 删除 deleted",
        ];
        $config['values']['normal'] = [
            'value' => '1',
            'description' => "直播活动状态 正常 normal, 冻结 frozen, 删除 deleted",
        ];
        
        if (!empty($_config['values'])) {
            $config['values'] = array_merge($config['values'], $_config['values']);
        }
        parent::__construct($config);
    }

}