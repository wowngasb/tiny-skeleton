<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Enum;

use GraphQL\Type\Definition\EnumType;

/**
 * Class ReviewTypeEnum
 * 房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat
 * @package app\api\GraphQL_\Enum
 */
class ReviewTypeEnum extends EnumType
{

    public function __construct(array $_config = [])
    {
        $config = [
            'description' => "房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat",
            'values' => []
        ];
        $config['values']['disable_chat'] = [
            'value' => 'disable_chat',
            'description' => "房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat",
        ];
        $config['values']['review_chat'] = [
            'value' => 'review_chat',
            'description' => "房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat",
        ];
        $config['values']['direct_pub'] = [
            'value' => 'direct_pub',
            'description' => "房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat",
        ];
        
        if (!empty($_config['values'])) {
            $config['values'] = array_merge($config['values'], $_config['values']);
        }
        parent::__construct($config);
    }

}