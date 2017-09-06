<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Type;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class BasicMsg
 * 互动消息模型 可扩展自定义类型
 * @package app\api\GraphQL_\Type
 */
class BasicMsg extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = new Types();
        }
        $config = [
            'description' => "互动消息模型 可扩展自定义类型",
            'fields' => []
        ];
        $config['fields']['msg_type'] = [
            'type' => $type::nonNull($type::MsgTypeEnum()),
            'description' => "互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift",
        ];
        $config['fields']['msg_id'] = [
            'type' => $type::nonNull($type::ID()),
            'description' => "互动消息 唯一id",
        ];
        $config['fields']['room_id'] = [
            'type' => $type::nonNull($type::Int()),
            'description' => "对应房间id",
        ];
        $config['fields']['timestamp'] = [
            'type' => $type::nonNull($type::Int()),
            'description' => "消息创建时间戳",
        ];
        $config['fields']['msgContent'] = [
            'type' => $type::MsgContentUnion(),
            'description' => "互动消息 消息内容",
        ];
        $config['fields']['user'] = [
            'type' => $type::BasicUser([], $type),
            'description' => "当前用户信息",
        ];
        
        $config['resolveField'] = function($value, $args, $context, ResolveInfo $info) {
            if (method_exists($this, $info->fieldName)) {
                return $this->{$info->fieldName}($value, $args, $context, $info);
            } else {
                return is_array($value) ? $value[$info->fieldName] : $value->{$info->fieldName};
            }
        };
        if (!empty($_config['fields'])) {
            $config['fields'] = array_merge($_config['fields'], $config['fields']);
        }
        parent::__construct($config);
    }

}