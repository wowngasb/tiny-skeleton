# coding: utf-8
from app import db, models, md5key
import datetime, json

def _BasicRoom(idx):
    return {
        'room_id': idx,
        'room_title': 'live test %s' % (idx, ),
        'chat_topic': 'room_%s' % (idx, ),
        'dms_sub_key': 'sub_eae37e48dab5f305516d07788eaaea60',
        'dms_pub_key': 'pub_5bfb7a0ced7adb2ce454575747762679',
        'dms_s_key': 's_ceb80d29276f78653df081e5a9f0ac76',
        'aodian_uin': '13830',
        'lss_app': 'dyy_1736_133',
        'stream': 'a0c3d2dd3b4688f31da13991477980d9',
        'room_status': 1,
    }

BasicRoom_list = [_BasicRoom(idx) for idx in range(101, 106)]

[db.session.add(models.BasicRoom(**BasicRoom)) \
    for BasicRoom in BasicRoom_list \
        if not models.BasicRoom.query.filter_by(room_id = BasicRoom['room_id']).first()]
db.session.commit()


def _ChatConfig(idx):
    return {
        'room_id': idx,
        'review_type': 'direct_pub',
        'sysmsg_type': 'show_all',
    }

ChatConfig_list = [_ChatConfig(idx) for idx in range(101, 106)]

[db.session.add( models.ChatConfig(**ChatConfig) ) \
    for ChatConfig in ChatConfig_list \
        if not models.ChatConfig.query.filter_by(room_id = ChatConfig['room_id']).first()]
db.session.commit()


def _PlayerAodianConfig(idx):
    return {
        'room_id': idx,
        'player_type': 'aodianplayer',
        'rtmpUrl': 'rtmp://13830.lssplay.aodianyun.com/dyy_1736_133/a0c3d2dd3b4688f31da13991477980d9',
        'hlsUrl': 'http://13830.hlsplay.aodianyun.com/dyy_1736_133/a0c3d2dd3b4688f31da13991477980d9.m3u8',
        'autostart': 1,
        'bufferlength': 1,
        'maxbufferlength': 1,
        'stretching': 1,
        'controlbardisplay': 'enable',
        'defvolume': 80,
        'adveDeAddr': 'http://static.douyalive.com/aae/dyy/assets/img/play_bj.png',
    }

PlayerAodianConfig_list = [_PlayerAodianConfig(idx) for idx in range(101, 106)]

[db.session.add( models.PlayerAodianConfig(**PlayerAodianConfig) ) \
    for PlayerAodianConfig in PlayerAodianConfig_list \
        if not models.PlayerAodianConfig.query.filter_by(room_id = PlayerAodianConfig['room_id']).first()]
db.session.commit()


def _PlayerMpsConfig(idx):
    return {
        'room_id': idx,
        'player_type': 'mpsplayer',
        'uin': 13830,
        'appId': 'fHNNBuuB3BbUWJiP',
        'autostart': 1,
        'stretching': 1,
        'mobilefullscreen': 0,
        'controlbardisplay': 'enable',
        'isclickplay': 1,
        'isfullscreen': 1,
    }

PlayerMpsConfig_list = [_PlayerMpsConfig(idx) for idx in range(101, 106)]

[db.session.add( models.PlayerMpsConfig(**PlayerMpsConfig) ) \
    for PlayerMpsConfig in PlayerMpsConfig_list \
        if not models.PlayerMpsConfig.query.filter_by(room_id = PlayerMpsConfig['room_id']).first()]
db.session.commit()


def _BasicUser(idx):
    return {
        'user_id': idx,
        'nick': 'Nick%s' % (idx, ),
        'avatar': 'http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png',
        'user_type': 'authorized',
    }

BasicUser_list = [_BasicUser(idx) for idx in range(1000, 1010)]

[db.session.add( models.BasicUser(**BasicUser) ) \
    for BasicUser in BasicUser_list \
        if not models.BasicUser.query.filter_by(user_id = BasicUser['user_id']).first()]
db.session.commit()



