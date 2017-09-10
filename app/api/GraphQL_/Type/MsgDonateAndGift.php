<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2017-09
 */
namespace app\api\GraphQL_\Type;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;/**
 * Class MsgDonateAndGift
 * 打赏及赠送礼物消息
 * @package app\api\GraphQL_\Type
 */
class MsgDonateAndGift extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }

        $_description = !empty($_config['description']) ? $_config['description'] : "打赏及赠送礼物消息";
        $_fields = !empty($_config['fields']) ? $_config['fields'] : [];
        
        $config = [
            'description' => $_description,
            'fields' => function () use ($type, $_fields) {
                $fields = [];
                $fields['msg_type'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "互动消息类型 固定为 donate_and_gift",
                ];
                $fields['trade_type'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "打赏或礼物类型 ",
                ];
                $fields['trade_num'] = [
                    'type' => $type::nonNull($type::Float()),
                    'description' => "打赏或礼物数量 ",
                ];
                $fields['content_text'] = [
                    'type' => $type::nonNull($type::String()),
                    'description' => "消息文本内容",
                ];
                $fields['target_user'] = [
                    'type' => $type::BasicUser([], $type),
                    'description' => "目标用户 信息",
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