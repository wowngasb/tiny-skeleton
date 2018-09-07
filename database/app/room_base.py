# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
###################		LiveRoom		######################
##############################################################

class LiveRoom(Base):
    u"""频道  列表 每个条目为一个频道  拥有唯一的 stream"""
    __tablename__ = 'live_room'

    room_id = Column(Integer, primary_key=True, doc=u"""频道room_id自增""", info=SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道所属管理员admin_id""", info=InitializeField | SortableField)
    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道所属管理员agent_id""", info=InitializeField | SortableField)
    room_title = Column(String(32), nullable=False, index=True, doc=u"""频道标题""", info=CustomField | SortableField)
    viewlimit = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道最大观看人数限制，0为无限制""", info=CustomField | SortableField)

    stream_id = Column(Integer, nullable=False, index=True, doc=u"""频道对应视频流stream_id""", info=CustomField | SortableField)
    v_stream_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""录制来源 stream_id 频道视频设置为点播时有效 """, info=InitializeField | SortableField)

    player_id = Column(Integer, nullable=False, index=True, doc=u"""频道对应播放器player_id""", info=CustomField | SortableField)

    live_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""直播状态 参见 LiveStateEnum""", info=BitMask(CustomField | SortableField, LiveStateEnum))
    viewer_count = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""累计观看人数，登录用户进入加1""", info=CustomField | SortableField)
    viewer_now = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""当前在线人数 实时变动""", info=CustomField | SortableField)
    viewer_max = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""同时最该在线人数，历史最高人数""", info=CustomField | SortableField)
    viewer_max_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""同时最该在线人数，历史最高人数 时刻""", info=SortableField)

    video_bg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""视频背景图片""", info=CustomField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class LiveRoom(SQLAlchemyObjectType):
            class Meta:
                model = cls

            admin = Field(lambda :AdminUser, description=u'parent 类型 后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

            agent = Field(lambda :AdminUser, description=u'agent 类型 后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.agent_id).first() \
                        if self.admin_id \
                            else None

            stream = Field(lambda :StreamBase, description=u'StreamBase 视频流 信息')
            def resolve_stream(self, args, context, info):
                return StreamBase.query.filter_by(stream_id=self.stream_id).first() \
                        if self.stream_id \
                            else None

            player = Field(lambda :PlayerBase, description=u'PlayerBase 播放器 信息')
            def resolve_player(self, args, context, info):
                return PlayerBase.query.filter_by(player_id=self.player_id).first() \
                        if self.player_id \
                            else None

        return LiveRoom

class LiveRoomPagination(g.ObjectType):
    u''' LiveRoom 分页查询 列表'''
    rows = List(LiveRoom, description=u'当前查询 LiveRoom 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


from admin_base import AdminUser
from player_base import PlayerBase
from stream_base import StreamBase