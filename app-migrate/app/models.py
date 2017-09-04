# coding: utf-8
import random, time

from inspect import isclass
from sqlalchemy import Index, types, Column, BigInteger, Integer, SmallInteger, String, Text, DateTime, Float, Numeric, text, TIMESTAMP
from sqlalchemy.inspection import inspect as sqlalchemyinspect

import graphene as g

from grapheneinfo import BuildType, SQLAlchemyObjectType, List, NonNull, Field, _is_graphql, _is_graphql_cls

from grapheneinfo.utils import (
    HiddenField,
    InitializeField,
    EditableField,
    CustomField
)

from app import db, app
Base = db.Model

class BasicRoom(Base):
    u"""直播活动基本信息 每个条目对应一个活动"""

    __tablename__ = 'basic_room'

    room_id = Column(Integer, primary_key = True, doc = u"""直播活动 唯一id""")
    room_title = Column(String(32), nullable = False, doc = u"""直播活动标题""")

    chat_topic = Column(String(32), nullable = False, doc = u"""DMS topic 互动消息话题""")
    dms_sub_key = Column(String(64), nullable = False, doc = u"""DMS sub_key 必须确保dms状态正常并且开启系统消息通知，""")
    dms_pub_key = Column(String(64), nullable = False, doc = u"""DMS pub_key""")
    dms_s_key = Column(String(64), nullable = False, doc = u"""DMS s_key""", info = HiddenField)

    aodian_uin = Column(Integer, nullable = False, server_default=text("'0'"), doc = u"""奥点云 uin""")
    lss_app = Column(String(32), nullable = False, doc = u"""流媒体 app""")
    stream = Column(String(32), nullable = False, doc = u"""流媒体 stream""")

    room_status = Column(SmallInteger, nullable = False, doc = u"""直播活动状态 正常 normal, 冻结 frozen, 删除 deleted""", \
        info = g.Enum('RoomStatusEnum', [('normal', 1), ('frozen', 2), ('deleted', 9)]))

    updated_at = Column(TIMESTAMP, nullable=False, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""更新时间""")
    created_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""创建时间""")

    @classmethod
    def info(cls):
        class BasicRoom(SQLAlchemyObjectType):
            class Meta:
                model = cls

            playerConfig = Field(PlayerConfigUnion, description = u'播放器配置', \
                player_type=g.Argument(g.String, default_value='mpsplayer', required=True, description = u'播放器类型'), \
            )
            def resolve_playerConfig(self, args, context, info):
                return PlayerAodianConfig.query.filter_by(room_id = self.room_id).first() \
                        if args['player_type']=='aodianplayer' else \
                        PlayerMpsConfig.query.filter_by(room_id = self.room_id).first()

            currentUser = Field(CurrentUser, description = u'当前登录用户信息 dms参数')
            def resolve_currentUser(self, args, context, info):
                user = BasicUser.query.filter_by(user_id=10000).first()
                agent = 'WEB'
                client_id = '%s_%s%d_%s' % (int(time.time()), agent, random.randint(100, 999), user.user_id)
                return CurrentUser(user=user, user_agent=agent, client_id=client_id)

            topicUser = Field(TopicUserPagination, description = u'分页查询话题用户列表', \
                num=g.Argument(g.Int, default_value=20, required=True, description = u'每页数量'), \
                page=g.Argument(g.Int, default_value=1, required=True, description = u'页数'), \
            )
            def resolve_contentTabConfig(self, args, context, info):
                raise NotImplementedError('use dms topic user API TODO')

            historyMsg = Field(RoomMsgPagination, description = u'分页查询房间历史消息', \
                num=g.Argument(g.Int, default_value=20, required=True, description = u'每页数量'), \
                page=g.Argument(g.Int, default_value=1, required=True, description = u'页数'), \
                user_id=g.Argument(g.ID, default_value='', description = u'发送者用户id'), \
                msg_type=g.Argument(MsgTypeEnum, required=True, description = u'消息类型'), \
                msg_status=g.Argument(MsgStatusEnum, required=True, description = u'消息状态'), \
                trade_type=g.Argument(g.String, default_value='', description = u'礼物消息 交易类型 TODO'), \
                msg_id_s=g.Argument(g.ID, default_value=0, description = u'消息id开始，默认为0'), \
                msg_id_e=g.Argument(g.ID, default_value=0, description = u'消息id结束，默认为0'), \
                timestamp_s=g.Argument(g.String, default_value='', description = u'时间字符串 开始 格式为 2012-03-04 05:06:07'), \
                timestamp_e=g.Argument(g.String, default_value='', description = u'时间字符串 结束'), \
                direction=g.Argument(g.String, default_value='asc', description = u'排序顺序 asc 或 desc'), \
                field=g.Argument(g.String, default_value='msg_id', description = u'排序依据字段'), \
            )
            def resolve_historyMsg(self, args, context, info):
                raise NotImplementedError('select data from table BasicMsg TODO')

            contentTabConfig = Field(ContentTabConfig, description = u'直播间切换tab栏配置')
            def resolve_contentTabConfig(self, args, context, info):
                return ContentTabConfig.query.filter_by(room_id = self.room_id).first()

            chatConfig = Field(ChatConfig, description = u'聊天互动配置')
            def resolve_chatConfig(self, args, context, info):
                return ChatConfig.query.filter_by(room_id = self.room_id).first()

            present_topic = g.String(description = u'用户进出房间 话题', required=True)
            def resolve_present_topic(self, args, context, info):
                return '__present__' + self.chat_topic

            sync_room_topic = g.String(description = u'同步房间数据 话题', required=True)
            def resolve_sync_room_topic(self, args, context, info):
                return 'sync_room'

            sync_user_topic = g.String(description = u'同步用户数据 话题', required=True)
            def resolve_sync_user_topic(self, args, context, info):
                return 'sync_user'

            sys_notify_lss_topic = g.String(description = u'流媒体直播消息 话题', required=True)
            def resolve_sys_notify_lss_topic(self, args, context, info):
                return 'sys/notify/lss'

        return BasicRoom

class ContentTabConfig(Base):
    u"""手机切换菜单配置"""
    __tablename__ = 'content_tab_config'

    room_id = Column(Integer, primary_key = True, doc = u"""对应房间 id""", info = HiddenField)
    content_tab_id = Column(Integer, nullable = False, doc = u"""对应 content_tab_id""", info = HiddenField)
    active = Column(String(16), nullable = False, doc = u"""当前激活的tab栏标题""")

    @classmethod
    def info(cls):
        class ContentTabConfig(SQLAlchemyObjectType):
            class Meta:
                model = cls

            tabList = List(TabItemConfig, description = u'tab栏列表')

            def resolve_tabList(self, args, context, info):
                return TabItemConfig.query.filter_by(content_tab_id = self.content_tab_id).all()

        return ContentTabConfig

class TabItemConfig(Base):
    u"""单个tab选项的配置"""
    __tablename__ = 'tab_item_config'

    content_tab_id = Column(Integer, primary_key = True, doc = u"""对应 content_tab_id""", info = HiddenField)
    title = Column(String(16), nullable = False, doc = u"""标题""")
    new_msg = Column(SmallInteger, nullable = False, doc = u"""提醒新消息数量""")
    component = Column(String(16), nullable = False, doc = u"""对应区域内容类型""")

class ChatConfig(Base):
    u"""直播活动 聊天配置信息"""
    __tablename__ = 'chat_config'

    room_id = Column(Integer, primary_key = True, doc = u"""对应房间 id""", info = HiddenField)
    review_type = Column(String(16), nullable = False, doc = u"""房间聊天审核类型 禁止聊天 disable_chat, 关闭聊天审核，聊天直接发布 direct_pub, 开启聊天审核 review_chat""", \
        info = g.Enum('ReviewTypeEnum', [('disable_chat', 'disable_chat'), ('direct_pub', 'direct_pub'), ('review_chat', 'review_chat')]))

    sysmsg_type = Column(String(16), nullable = False, doc = u"""房间系统消息显示类型 全部显示 show_all, 全部隐藏 hide_all""", \
        info = g.Enum('SysMsgTypeEnum', [('show_all', 'show_all'), ('hide_all', 'hide_all')]))


class PlayerAodianConfig(Base):
    u"""直播活动 奥点播放器"""
    __tablename__ = 'player_aodian_config'

    room_id = Column(Integer, primary_key = True, doc = u"""对应房间 id""", info=HiddenField)
    player_type = Column(String(16), nullable = False, doc = u"""播放器类型 固定为 aodianplayer""")

    rtmpUrl = Column(String(128), nullable = False, doc = u"""控制台开通的APP rtmp地址 必要参数""")
    hlsUrl = Column(String(128), nullable = False, doc = u"""控制台开通的APP hls地址 必要参数""")
    autostart = Column(SmallInteger, nullable = False, doc = u"""是否自动播放""")
    bufferlength = Column(SmallInteger, nullable = False, doc = u"""视频缓冲时间 默认为1秒""")
    maxbufferlength = Column(SmallInteger, nullable = False, doc = u"""最大视频缓冲时间 默认为2秒""")
    stretching = Column(SmallInteger, nullable = False, doc = u"""设置全屏模式 1代表按比例撑满至全屏 2代表铺满全屏 3代表视频原始大小""")
    controlbardisplay = Column(String(16), nullable = False, doc = u"""是否显示控制栏 可取值 disable enable 默认为disable""")
    defvolume = Column(SmallInteger, nullable = False, doc = u"""默认音量""")
    adveDeAddr = Column(String(128), nullable = False, doc = u"""封面图地址""")

class PlayerMpsConfig(Base):
    u"""直播活动 Mps播放器"""
    __tablename__ = 'player_mps_config'

    room_id = Column(Integer, primary_key = True, doc = u"""对应房间 id""", info=HiddenField)
    player_type = Column(String(16), nullable = False, doc = u"""播放器类型 固定为 mpsplayer""")

    uin = Column(Integer, nullable = False, doc = u"""用户奥点uin""")
    appId = Column(String(32), nullable = False, doc = u"""mps实例id 需要静态实例""")

    autostart = Column(SmallInteger, nullable = False, doc = u"""是否自动播放""")
    stretching = Column(SmallInteger, nullable = False, doc = u"""设置全屏模式 1代表按比例撑满至全屏 2代表铺满全屏 3代表视频原始大小""")
    mobilefullscreen = Column(SmallInteger, nullable = False, doc = u"""移动端是否全屏""")
    controlbardisplay = Column(String(16), nullable = False, doc = u"""是否显示控制栏 可取值 disable enable 默认为disable""")

    isclickplay = Column(SmallInteger, nullable = False, doc = u"""是否单击播放，默认为false""")
    isfullscreen = Column(SmallInteger, nullable = False, doc = u"""是否双击全屏，默认为true""")

class PlayerConfigUnion(g.Union):
    u"""播放器配置"""

    _type_key = ('player_type', {
        'aodianplayer': BuildType(PlayerAodianConfig),
        'mpsplayer': BuildType(PlayerMpsConfig),
    })

    class Meta:
        types = [BuildType(PlayerAodianConfig), BuildType(PlayerMpsConfig)]

UserTypeEnum = g.Enum('UserTypeEnum', [ \
    ('guest', 'guest'), ('authorized', 'authorized'), ('manager', 'manager'), ('publisher', 'publisher') \
], description = u'用户类型 游客 guest, 已认证 authorized, 管理者 manager, 发布者 publisher')

class BasicUser(Base):
    u"""用户信息 不同的用户类型对应不同的权限"""
    __tablename__ = 'basic_user'

    user_id = Column(Integer, primary_key = True, doc = u"""用户 唯一id""")
    nick = Column(String(16), nullable = False, doc = u"""用户昵称""")
    avatar = Column(String(128), nullable = False, doc = u"""用户头像""")

    user_type = Column(String(16), nullable = False, doc = u"""用户类型 游客 guest, 已认证 authorized, 管理者 manager, 发布者 publisher""",
        info = UserTypeEnum)


MsgTypeEnum = g.Enum('MsgTypeEnum', [ \
    ('chat_and_review', 'chat_and_review'), ('donate_and_gift', 'donate_and_gift') \
], description = u'互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift')

class BasicMsg(Base):
    u"""互动消息模型 可扩展自定义类型"""
    __tablename__ = 'basic_msg'

    msg_id = Column(Integer, primary_key = True, doc = u"""互动消息 唯一id""")

    room_id = Column(Integer, nullable = False, doc = u"""对应房间id""")
    user_id = Column(String(16), nullable = False, doc = u"""对应消息发起者用户id""", info=HiddenField)
    msg_type = Column(String(16), nullable = False, doc = u"""互动消息类型 聊天及审核消息 chat_and_review, 打赏及赠送礼物消息 donate_and_gift""",
        info = MsgTypeEnum)

    timestamp= Column(Integer, nullable = False, doc = u"""消息创建时间戳""")

    @classmethod
    def info(cls):
        class BasicMsg(SQLAlchemyObjectType):

            class Meta:
                model = cls

            msgContent = Field(MsgContentUnion, description = u'互动消息 消息内容')
            user = Field(BasicUser, description = u'当前用户信息')

            def resolve_msgContent(self, args, context, info):
                return MsgChatAndReView.query.filter_by(msg_id = self.msg_id).first() \
                    if self.msg_type=='chat_and_review' else \
                        MsgDonateAndGift.query.filter_by(msg_id = self.msg_id).first()

            def resolve_user(self, args, context, info):
                return BasicUser.query.filter_by(user_id = self.user_id).first()

        return BasicMsg

MsgStatusEnum = g.Enum('MsgStatusEnum', [ \
    ('publish_chat', 'publish_chat'), ('review_pub', 'review_pub'), ('review_del', 'review_del'), ('review_add', 'review_add') \
], description = u'聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add')

class MsgChatAndReView(Base):
    u"""聊天及审核消息"""
    __tablename__ = 'msg_chat_and_review'

    msg_id = Column(Integer, primary_key = True, doc = u"""互动消息 唯一id""", info=HiddenField)
    msg_type = Column(String(16), nullable = False, doc = u"""互动消息类型 固定为 chat_and_review""")
    target_user_id = Column(String(16), nullable = False, doc = u"""消息目标用户 用户id 用于处理私聊""", info = HiddenField)
    target_msg_id = Column(String(16), nullable = False, doc = u"""目标消息id""", info = HiddenField)

    content_text = Column(String(512), nullable = False, doc = u"""消息文本内容""")
    msg_status = Column(String(16), nullable = False, doc = u"""聊天及审核消息状态 用户发布聊天 publish_chat, 审核发布消息 review_pub, 审核删除消息 review_del, 添加到审核列表 review_add""",
        info = MsgStatusEnum)

    operator_id = Column(String(16), nullable = False, doc = u"""当前操作者 用户id""", info = HiddenField)

    @classmethod
    def info(cls):
        class MsgChatAndReView(SQLAlchemyObjectType):
            class Meta:
                model = cls

            target_user = Field(BasicUser, description = u'目标用户 信息')
            target_msg = Field(lambda: BasicMsg, description = u'目标消息 信息')
            operator = Field(BasicUser, description = u'当前操作者 信息')

            def resolve_target_user(self, args, context, info):
                return BasicUser.query.filter_by(user_id = self.target_user_id).first() if self.target_user_id else None

            def resolve_target_msg(self, args, context, info):
                return BasicMsg.query.filter_by(msg_id = self.target_msg_id).first() if self.target_msg_id else None


            def resolve_operator(self, args, context, info):
                return BasicUser.query.filter_by(user_id = self.operator_id).first() if self.operator_id else None

        return MsgChatAndReView

    # 用户发布消息 页面生成数据 msg_status 为 publish_chat  存入消息到数据库
    # a. ChatConfig review_type 配置为 direct_pub 时 在房间 topic 广播消息 msg_status 为 publish_chat  所有用户页面直接显示该消息
    # b. ChatConfig review_type 配置为 disable_chat 时 返回发布失败 禁止聊天
    # c. ChatConfig review_type 配置为 review_chat 时
    #   在房间 topic 并修改数据库中消息的状态广播消息 并修改数据库中消息的状态 msg_status 为 review_add 所有审核页面把该消息添加到待审核列表
    #   管理员 发布该消息 广播消息 msg_status 为 review_pub 消息 并修改数据库中消息的状态 所有审核页面把该消息去除  所有用户页面显示该消息
    #   管理员 删除该消息 广播消息 msg_status 为 review_del 消息 并修改数据库中消息的状态 所有审核页面把该消息去除  所有用户页面尝试删除该消息（根据 msg_id）

class MsgDonateAndGift(Base):
    u"""打赏及赠送礼物消息"""
    __tablename__ = 'msg_donate_and_gift'

    msg_id = Column(Integer, primary_key = True, doc = u"""互动消息 唯一id""", info = HiddenField)
    msg_type = Column(String(16), nullable = False, doc = u"""互动消息类型 固定为 donate_and_gift""")
    target_user_id = Column(String(16), nullable = False, doc = u"""消息目标用户 用户id 用于处理打赏给指定用户 """, info = HiddenField)

    trade_type = Column(String(16), nullable = False, doc = u"""打赏或礼物类型 """)
    trade_num = Column(Float, nullable = False, doc = u"""打赏或礼物数量 """)

    content_text = Column(String(512), nullable = False, doc = u"""消息文本内容""")

    @classmethod
    def info(cls):
        class MsgDonateAndGift(SQLAlchemyObjectType):
            class Meta:
                model = cls

            target_user = Field(BasicUser, description = u'目标用户 信息')

            def resolve_target_user(self, args, context, info):
                return BasicUser.query.filter_by(user_id = self.target_user_id).first() if self.target_user_id else None

        return MsgDonateAndGift

class MsgContentUnion(g.Union):
    u"""播放器配置"""

    _type_key = ('msg_type', {
        'chat_and_review': BuildType(MsgChatAndReView),
        'donate_and_gift': BuildType(MsgDonateAndGift)
    })

    class Meta:
        types = [BuildType(MsgChatAndReView), BuildType(MsgDonateAndGift)]


class PageInfo(g.ObjectType):
    u'''分页信息'''
    num = g.Int(description = u'每页数量', required=True)
    total = g.Int(description = u'总数', required=True)
    page = g.Int(description = u'当前页数', required=True)
    hasNextPage = g.Boolean(description = u'是否拥有下一页', required=True)
    hasPreviousPage = g.Boolean(description = u'是否拥有上一页', required=True)

    @classmethod
    def buildPageInfo(cls, total=0, num=0, page=1):
        total = 0 if (not total or total < -1) else num
        num = 10 if not num or num <= 0 else num
        page = 1 if not page or page <= 0 else page

        return PageInfo(
            num=num,
            page=page,
            total=total,
            hasPreviousPage=not page==1,
            hasNextPage= (total > page * num or total == -1)
        )

class CurrentUser(g.ObjectType):
    u'''当前登录用户信息 及 用户连接dms配置'''
    user = Field(BasicUser, description = u'当前用户信息', required=True)
    user_agent = g.String(description = u'用户设备信息 PC网页 WEB 手机网页 WAP 发布工具内嵌网页 PUB 后台管理页面 MGR', required=True)
    client_id = g.String(description = u'用户连接DMS 唯一id', required=True)

class TopicUserPagination(g.ObjectType):
    u'''当前登录用户信息 及 用户连接dms配置'''
    userList = List(CurrentUser, description = u'当前查询用户列表')
    pageInfo = Field(PageInfo, description = u'分页信息')

class RoomMsgPagination(g.ObjectType):
    u'''房间历史消息'''
    msgList = List(BasicMsg, description = u'消息列表')
    pageInfo = Field(PageInfo, description = u'分页信息')


class Query(g.ObjectType):
    user = Field(BasicUser, description = u'查询用户', \
        room_id=g.Argument(g.ID, required=True, description = u'房间id'), \
        user_id=g.Argument(g.ID, required=True, description = u'用户id'), \
    )
    room = Field(BasicRoom, description = u'查询房间', \
        room_id=g.Argument(g.ID, required=True, description = u'房间id'), \
    )
    msg = Field(BasicMsg, description = u'查询消息', \
        msg_id=g.Argument(g.ID, required=True, description = u'消息id'), \
    )
    def resolve_user(self, args, context, info):
        return BasicUser.query.filter_by(user_id = args['user_id']).first()

    def resolve_room(self, args, context, info):
        return BasicRoom.query.filter_by(room_id = args['room_id']).first()

    def resolve_msg(self, args, context, info):
        return BasicMsg.query.filter_by(msg_id = args['msg_id']).first()

    hello = g.String(name=g.Argument(g.String, default_value="world", description = u'input you name'))
    deprecatedField = Field(g.String, deprecation_reason = 'This field is deprecated!')
    fieldWithException = g.String()

    def resolve_hello(self, args, context, info):
        return 'Hello, %s!' % (args.get('name', ''), )

    def resolve_deprecatedField(self, args, context, info):
        return 'You can request deprecated field, but it is not displayed in auto-generated documentation by default.'

    def resolve_fieldWithException(self, args, context, info):
        raise ValueError('Exception message thrown in field resolver')

tables = [tbl if BuildType(tbl) else tbl for _, tbl in globals().items() if isclass(tbl) and issubclass(tbl, Base) and tbl != Base]
schema = g.Schema(query=Query, types=[BuildType(tbl) for tbl in tables] + [cls for _, cls in globals().items() if _is_graphql_cls(cls)], auto_camelcase = False)
