# coding: utf-8
import app
from app import db, models, md5key
from app.models import *
from sqlalchemy.sql.expression import func

import datetime, json
import time

def pri_filter(dao, pri, item_list, churk=200):
    id_set = list(set([item[pri] for item in item_list]))
    tmp = []
    print '\npri_filter %r all:%s' % (dao, len(item_list)),
    while id_set:
        l_tmp = id_set[:churk]
        s_tmp = dao.query.filter( getattr(dao, pri).in_(l_tmp) ).all()
        tmp.extend(s_tmp)
        id_set = id_set[churk:]
        print '.',

    print '\n'
    has_set = set([getattr(i, pri, None) for i in tmp])
    return [t for t in item_list if t[pri] not in has_set]

def date(format_='%Y-%m-%d %H:%M:%S', time_=None):
    timestamp = time.time() if time_ is None else int(time_)
    timestruct = time.localtime(timestamp)
    return time.strftime(format_, timestruct)

def _AdminUser(idx, name_pre, admin_type, agent_id, parent_id):
    name = '{pre}_{idx}'.format(pre=name_pre, idx=idx)
    return dict(
        admin_id = idx,
        name = name,
        pasw = '1SYTwi6xwbprfY5V6GYSmIpg',
        pasw_time = date(),
        login_ip = '127.0.0.1',
        login_location = '本机地址',
        avator = '''https://ss1.bdstatic.com/70cFvXSh_Q1YnxGkpoWK1HF6hhy/it/u=3448484253,3685836170&fm=27&gp=0.jpg''',
        login_count = 1,
        login_time = date(),
        admin_note = 'note for ' + name,
        title = name,
        email = '{pre}_{idx}@xxx.com'.format(pre=name_pre, idx=idx),
        cellphone = idx,
        register_from = 'test',
        admin_type = admin_type,
        api_key = md5key(name),
        agent_id = agent_id,
        parent_id = parent_id,
        state = 1,
        admin_config = '',
    )

# 超级管理员账号
SuperUser_item = _AdminUser(1, AdminTypeEnum.SUPER.value, AdminTypeEnum.SUPER.value, 0, 0)

_list = pri_filter(AdminUser, 'admin_id', [SuperUser_item, ])
for idx, item in enumerate(_list):
    db.session.add(AdminUser(**item))
    if idx % 100 == 1:
        db.session.commit()
db.session.commit()
print 'Add All SuperUser_item =>', 1

# 代理账号 测试数据
AgentUser_list = [
    _AdminUser(idx, AdminTypeEnum.AGENT.value, AdminTypeEnum.AGENT.value, 0, 0) \
        for idx in range(100, 122)
]

_list = pri_filter(AdminUser, 'admin_id', AgentUser_list)
for idx, item in enumerate(_list):
    db.session.add(AdminUser(**item))
    if idx % 100 == 1:
        db.session.commit()
        print 'Add AgentUser_list =>', idx
db.session.commit()
print 'Add All AgentUser_list =>', len(AgentUser_list)


def _XdyProduct(idx, name_pre, admin_id=0, product_type='package', limit_type='onlinenum'):
    name = '{pre}_{idx}'.format(pre=name_pre, idx=idx)
    return dict(
        product_id = idx,
        admin_id = admin_id,
        product_title = name,
        product_price = idx * 1.00,
        product_num = idx + 900,
        product_type = product_type,
        product_note = 'note for ' + name,
        sell_count = idx * 10,
        limit_type = limit_type,
        limit_value = idx * 100,
        expired_days = (idx % 3) * 30,
        state = 1,
    )

# 产品 测试数据
XdyProduct_list = [
    _XdyProduct(idx, '产品_', 0) \
        for idx in range(10, 20)
] + [
    _XdyProduct(agent['admin_id'], '套餐_', agent['admin_id']) \
        for agent in AgentUser_list
]

_list = pri_filter(XdyProduct, 'product_id', XdyProduct_list)
for idx, item in enumerate(_list):
    db.session.add(XdyProduct(**item))
    if idx % 100 == 1:
        db.session.commit()
        print 'Add XdyProduct_list =>', idx
db.session.commit()
print 'Add All XdyProduct_list =>', len(XdyProduct_list)


def _XdyOrder(idx, name_pre, admin_id=0, operator_id=0, product_type='package', order_type='product'):
    name = '{pre}_{idx}'.format(pre=name_pre, idx=idx)
    return dict(
        order_id = idx,
        admin_id = admin_id,
        operator_id = operator_id,
        account_balance_after = idx * 0.9,
        account_balance_before = idx * 0.3,
        order_money = idx * 0.2,
        order_note = name,
        order_type = order_type,
        product_id = random.choice(XdyProduct_list)['product_id'],
        product_type = product_type,
        product_config = '',
        product_value = '',
        admin_email = '',
        admin_mobile = '',
        m_state = '1',
        error_msg = '',
        state = 1,
    )

# 产品 测试数据
XdyOrder_list = [
    _XdyOrder(idx, '订单_', random.choice(AgentUser_list)['admin_id'], random.choice(AgentUser_list)['admin_id']) \
        for idx in range(100, 200)
]

_list = pri_filter(XdyOrder, 'order_id', XdyOrder_list)
for idx, item in enumerate(_list):
    db.session.add(XdyOrder(**item))
    if idx % 100 == 1:
        db.session.commit()
        print 'Add XdyOrder_list =>', idx
db.session.commit()
print 'Add All XdyOrder_list =>', len(XdyOrder_list)

exit(0)

# 客户账号 测试数据
ParentUser_list = [
    _AdminUser(agent['admin_id'] * 100 + idx, AdminTypeEnum.PARENT.value, AdminTypeEnum.PARENT.value, agent['admin_id'], 0) \
        for idx in range(0, 13) \
            for agent in AgentUser_list
]

_list = pri_filter(AdminUser, 'admin_id', ParentUser_list)
for idx, item in enumerate(_list):
    db.session.add(AdminUser(**item))
    if idx % 100 == 1:
        db.session.commit()
        print 'Add ParentUser_list =>', idx
db.session.commit()
print 'Add All ParentUser_list =>', len(ParentUser_list)

# 子账号 测试数据
SubUser_List = [
    _AdminUser(parent['admin_id'] * 100 + idx, AdminTypeEnum.SUB.value, AdminTypeEnum.SUB.value, parent['agent_id'], parent['admin_id']) \
        for idx in range(0, 17) \
            for parent in ParentUser_list
]

_list = pri_filter(AdminUser, 'admin_id', SubUser_List)
for idx, item in enumerate(_list):
    db.session.add(AdminUser(**item))
    if idx % 100 == 1:
        print 'Add SubUser_List =>', idx
db.session.commit()
print 'Add All SubUser_List =>', len(SubUser_List)


def _StreamBase(idx, name_pre, admin_id, stream_type):
    name = '{pre}_{idx}'.format(pre=name_pre, idx=idx)
    return dict(
        stream_id = idx,
        stream_type = stream_type,
        stream_name = name,
        admin_id = admin_id,
        state = 1,
    )
# 播放器 测试数据
StreamBase_List = [
    _StreamBase(parent['admin_id'] * 10 + idx, 'player', parent['admin_id'], StreamTypeEnum.STREAM_MCS.value) \
        for idx in range(7) \
            for parent in ParentUser_list
]

_list = pri_filter(StreamBase, 'stream_id', StreamBase_List)
for idx, item in enumerate(_list):
    db.session.add(StreamBase(**item))
    if idx % 100 == 1:
        db.session.commit()
        print 'Add StreamBase =>', idx
db.session.commit()
print 'Add All StreamBase_List =>', len(StreamBase_List)

def _PlayerBase(idx, name_pre, admin_id, player_type):
    name = '{pre}_{idx}'.format(pre=name_pre, idx=idx)
    return dict(
        player_id = admin_id,
        player_type = player_type,
        player_name = name,
        admin_id = admin_id,
        state = 1,
    )

# 播放器 测试数据
PlayerBase_List = [
    _PlayerBase(parent['admin_id'] * 10, 'player', parent['admin_id'], PlayerTypeEnum.PLAYER_AODIAN.value) \
        for parent in ParentUser_list
]

_list = pri_filter(PlayerBase, 'player_id', PlayerBase_List)
for idx, item in enumerate(_list):
    db.session.add(PlayerBase(**item))
    if idx % 100 == 1:
        db.session.commit()
        print 'Add PlayerBase_List =>', idx
db.session.commit()
print 'Add All PlayerBase_List =>', len(PlayerBase_List)

def _PlayerAodian(idx):
    return dict(
        player_id = idx,
        width = '100%',
        height = '100%',
        autostart = 1,
        controlbardisplay = 'enable',
        isclickplay = 0,
        isfullscreen = 1,
        stretching = 1,
        defvolume = 80,
        bufferlength =3,
        maxbufferlength = 3,
        adveDeAddr = 'https://www.baidu.com/img/bd_logo1.png',
        adveWidth = '100%',
        adveHeight = '100%',
        adveReAddr = 'https://www.baidu.com/',
    )

# 写入 具体的 播放器
PlayerAodian_List = [
    _PlayerAodian(player['player_id']) for player in PlayerBase_List
]

_list = pri_filter(PlayerAodian, 'player_id', PlayerAodian_List)
for idx, item in enumerate(_list):
    db.session.add(PlayerAodian(**item))
    if idx % 100 == 1:
        db.session.commit()
        print 'Add PlayerAodian_List =>', idx
db.session.commit()
print 'Add All PlayerAodian_List =>', len(PlayerAodian_List)

def _LiveRoom(idx, name_pre, admin_id, stream_id, player_id):
    name = '{pre}_{idx}'.format(pre=name_pre, idx=idx)
    return dict(
        room_id = idx,
        admin_id = admin_id,
        room_title = name,
        viewlimit = 1000,
        stream_id = stream_id,
        player_id = player_id,
        state = 1,
    )

# 频道 测试数据
LiveRoom_List = [
    _LiveRoom(parent['admin_id'] * 100 + idx, 'room_', parent['admin_id'], 0, parent['admin_id'] * 10) \
        for idx in range(1, 19) \
            for parent in ParentUser_list \
]

_list = pri_filter(LiveRoom, 'room_id', LiveRoom_List)
for idx, item in enumerate(_list):
    db.session.add(LiveRoom(**item))
    if idx % 100 == 1:
        db.session.commit()
        print 'Add LiveRoom_List =>', idx
db.session.commit()
print 'Add All LiveRoom_List =>', len(LiveRoom_List)


