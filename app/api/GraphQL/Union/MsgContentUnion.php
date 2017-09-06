<?php
/**
 * Created by table_graphQL.
 * User: Administrator
 * Date: 2017-09
 */
namespace app\api\GraphQL\Union;

use app\api\GraphQL\Types;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\UnionType;

/**
 * Class MsgContentUnion
 * 播放器配置
 * @package app\api\GraphQL\Union
 */
class MsgContentUnion extends UnionType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = new Types();
        }
        $config = [
            'types' => [
                $type::MsgChatAndReView([], $type),
                $type::MsgDonateAndGift([], $type),
            ],
            'resolveType' => function ($rootValue, $context, ResolveInfo $info) use ($type) {
                false && func_get_args();
                if ($rootValue['msg_type'] == 'chat_and_review') {
                    return $type::MsgChatAndReView([], $type);
                } else if ($rootValue['msg_type'] == 'donate_and_gift') {
                    return $type::MsgDonateAndGift([], $type);}
                return null;
            },
            'description' => "播放器配置"
        ];

        $config = array_merge($config, $_config);
        parent::__construct($config);
    }

}