<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Enum;

use GraphQL\Type\Definition\EnumType;

/**
 * Class MsgStatusEnum
 * 聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add
 * @package app\api\GraphQL_\Enum
 */
class MsgStatusEnum extends EnumType
{

    public function __construct(array $_config = [])
    {
        $config = [
            'description' => "聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add",
            'values' => []
        ];
        $config['values']['review_del'] = [
            'value' => 'review_del',
            'description' => "聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add",
        ];
        $config['values']['publish_chat'] = [
            'value' => 'publish_chat',
            'description' => "聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add",
        ];
        $config['values']['review_add'] = [
            'value' => 'review_add',
            'description' => "聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add",
        ];
        $config['values']['review_pub'] = [
            'value' => 'review_pub',
            'description' => "聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add",
        ];
        
        if (!empty($_config['values'])) {
            $config['values'] = array_merge($_config['values'], $config['values']);
        }
        parent::__construct($config);
    }

}