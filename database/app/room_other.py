# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
###################		RoomContentConfig		######################
##############################################################


class RoomContentConfig(Base):
    u'''块配置 具体内容块 设置及配置信息表'''
    __tablename__ = 'room_content_config'

    content_id = Column(Integer, primary_key=True, doc=u"""id自增""", info=SortableField)
    admin_id = Column(Integer, nullable=False, doc=u"""管理员id""", info=CustomField | SortableField)
    room_id = Column(Integer, nullable=False, doc=u"""频道id""", info=CustomField | SortableField)
    content_slug = Column(String(32), nullable=False, doc=u"""分块内容 类型 slug""", info=CustomField | SortableField)
    content_key = Column(String(32), nullable=False, doc=u"""分块内容 类型 key""", info=CustomField | SortableField)
    content_title = Column(String(64), nullable=False, server_default=text("''"), doc=u"""配置 标题 字符串""", info=CustomField | SortableField)
    content_doc = Column(Text, nullable=False, doc=u"""配置 帮助信息 字符串""", info=CustomField)
    content_text = Column(Text, nullable=False, doc=u"""配置值 文本 字符串""", info=CustomField)
    content_config = Column(Text, nullable=False, doc=u"""配置值 json 字符串""", info=CustomField)
    content_group = Column(String(32), nullable=False, server_default=text("'group'"), doc=u"""区块类型  item 为单个条目  list 为一组设置 """, info=CustomField | SortableField)
    content_rank = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""排序依据""", info=CustomField | SortableField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomContentConfig(SQLAlchemyObjectType):
            class Meta:
                model = cls

            room = Field(lambda :LiveRoom, description=u'频道 信息')
            def resolve_room(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

            admin = Field(lambda :AdminUser, description=u'后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

        return RoomContentConfig

class RoomContentConfigPagination(g.ObjectType):
    u''' RoomContentConfig 分页查询 列表'''
    rows = List(RoomContentConfig, description=u'当前查询 RoomContentConfig 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


######################################################################
###################		RoomPublishRecord		######################
######################################################################

class RoomPublishRecord(Base):
    u"""频道  视频发布记录 数据表"""
    __tablename__ = 'room_publish_record'

    id = Column(Integer, primary_key=True, doc=u"""自增id""", info=SortableField)

    stream_type = Column(String(32), nullable=False, index=True, doc=u"""视频流类型 参见 StreamTypeEnum""", info=BitMask(InitializeField | SortableField, StreamTypeEnum))
    stream_id = Column(Integer, nullable=False, index=True, doc=u"""频道对应视频流stream_id""", info=InitializeField | SortableField)

    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道所属管理员 admin_id""", info=InitializeField | SortableField)
    live_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""直播状态 参见 LiveStateEnum""", info=BitMask(CustomField | SortableField, LiveStateEnum))
    start_time = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""直播开始时间""", info=CustomField | SortableField)
    end_time = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""直播结束时间""", info=CustomField | SortableField)
    interval_time = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""时长""", info=CustomField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomPublishRecord(SQLAlchemyObjectType):
            class Meta:
                model = cls

            admin = Field(lambda :AdminUser, description=u'parent 类型 后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

            stream = Field(lambda :StreamBase, description=u'对应视频流 信息')
            def resolve_room(self, args, context, info):
                return StreamBase.query.filter_by(stream_id=self.stream_id).first() \
                        if self.stream_id \
                            else None

        return RoomPublishRecord


class RoomPublishRecordPagination(g.ObjectType):
    u''' RoomPublishRecord 分页查询 列表'''
    rows = List(RoomPublishRecord, description=u'当前查询 RoomPublishRecord 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


from admin_base import AdminUser
from room_base import LiveRoom
from stream_base import StreamBase