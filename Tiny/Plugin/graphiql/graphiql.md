# 测试GraphQL

## 测试查询

查询语句

```
query TestQuery($num: Int!, $page: Int!, $player_type: String!, 
  $room_id: ID!, $user_id: ID!, $msg_id: ID!,
	$msg_type: MsgTypeEnum!, $msg_status: MsgStatusEnum!){
  user(user_id: $user_id, room_id: $room_id){
    user_id,
    nick,
    avatar,
    user_type
  }
  msg(msg_id: $msg_id){
    msg_id,
    msg_type,
    timestamp,
    user{
      user_id,
      user_type,
      avatar,
      nick,
    }
    msgContent{
        ... on MsgChatAndReview{
          content_text,
          msg_status,
          msg_type,
          operator_id,
          target_user_id
        }
        ... on MsgDonateAndGift{
          content_text,
          msg_type,
          target_user_id,
          trade_num,
          trade_type
        }
    }
  }
  room(room_id: $room_id){
    room_id,
    room_title,
    room_status,
    dms_s_key,
    dms_pub_key,
    dms_sub_key,
    lss_app,
    stream,
    aodian_uin,
    chat_topic,
    present_topic,
    sync_room_topic,
    sync_user_topic,
    sys_notify_lss_topic,
    chatConfig{
      review_type
    }
    currentUser{
      user{
        user_id,
        nick,
        avatar,
        user_type
      }
      user_agent,
      client_id
    }
    playerConfig(player_type: $player_type){
        ... on PlayerMpsConfig{
            appId,
            autostart,
            controlbardisplay,
            isclickplay,
            isfullscreen,
            mobilefullscreen,
            player_type,
            room_id,
            stretching,
            uin,
        }
        ... on PlayerAodianConfig{
            adveDeAddr,
            autostart,
            bufferlength,
            controlbardisplay,
            defvolume,
            hlsUrl,
            maxbufferlength,
            player_type,
            room_id,
            rtmpUrl,
            stretching,
        }
    }
    topicUser(num: $num, page: $page){
      userList{
        user{
          user_id,
          nick,
          avatar,
          user_type
        }
        user_agent,
        client_id
      }
      pageInfo{
        num,
        page,
        total,
        hasNextPage,
        hasPreviousPage
      }
    }
    historyMsg(num: 10, page: 1, msg_type: $msg_type, msg_status: $msg_status){
			msgList{
        msg_id,
        msg_type,
        timestamp,
        user{
          user_id,
          user_type,
          avatar,
          nick,
        }
        msgContent{
            ... on MsgChatAndReview{
              content_text,
              msg_status,
              msg_type,
              operator_id,
              target_user_id
            }
            ... on MsgDonateAndGift{
              content_text,
              msg_type,
              target_user_id,
              trade_num,
              trade_type
            }
        }
      },
      pageInfo{
        total,
        page,
        num,
        hasNextPage,
        hasPreviousPage
      }
    }
    envConfig{
      ENV_ROOM_AUTH_LINK,
      ENV_ROOM_VIEW_AUTH
    }
  }
}


{
  "msg_id": 351,
  "msg_type": "chat_and_review",
  "msg_status": "review_add",
  "room_id": 147,
  "user_id": 12345678902,
  "player_type": "mpsplayer",
  "num": 10,
  "page": 1
}

```