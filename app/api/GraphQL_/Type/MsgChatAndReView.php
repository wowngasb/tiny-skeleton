<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Type;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;/**
 * Class MsgChatAndReView
 * 聊天及审核消息
 * @package app\api\GraphQL_\Type
 */
class MsgChatAndReView extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "聊天及审核消息";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['msg_status'] = [
                    'type' => $type::nonNull($type::MsgStatusEnum()),
                    'description' => "聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add",
                ];
                $fields['msg_type'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "互动消息类型 固定为 chat_and_review",
                ];
                $fields['content_text'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "消息文本内容",
                ];
                $fields['target_user'] = [
                    'type' => $type::BasicUser([], $type),
                    'description' => "目标用户 信息",
                ];
                $fields['target_msg'] = [
                    'type' => $type::BasicMsg([], $type),
                    'description' => "目标消息 信息",
                ];
                $fields['operator'] = [
                    'type' => $type::BasicUser([], $type),
                    'description' => "当前操作者 信息",
                ];
                return array_merge($fields, $_fields);
            },
            'resolveField' => function($value, $args, $context, ResolveInfo $info) {
                if (method_exists($this, $info->fieldName)) {
                    return $this->{$info->fieldName}($value, $args, $context, $info);
                } else {
                    return is_array($value) ? $value[$info->fieldName] : $value->{$info->fieldName};
                }
            },
        ];
        parent::__construct($config);
    }

}