<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL\Enum;

use GraphQL\Type\Definition\EnumType;

/**
 * Class MsgTypeEnum
 * 互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift
 * @package app\api\GraphQL\Enum
 */
class MsgTypeEnum extends EnumType
{

    public function __construct(array $_config = [])
    {
        $config = [
            'description' => "互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift",
            'values' => []
        ];
        $config['values']['chat_and_review'] = [
            'value' => 'chat_and_review',
            'description' => "互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift",
        ];
        $config['values']['donate_and_gift'] = [
            'value' => 'donate_and_gift',
            'description' => "互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift",
        ];
        
        if (!empty($_config['values'])) {
            $config['values'] = array_merge($_config['values'], $config['values']);
        }
        parent::__construct($config);
    }

}