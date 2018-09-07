# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
###################		RoomViewRecord		######################
##############################################################

class RoomViewRecord(Base):
    u"""频道观看记录表  存储每条观看记录"""
    __tablename__ = 'room_view_record'

    id = Column(Integer, primary_key=True, doc=u"""自增id""", info=SortableField)
    user_id = Column(String(32), nullable=False, index=True, doc=u"""用户user_id""", info=InitializeField | SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员id""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id""", info=InitializeField | SortableField)
    login_ip_addr = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""field login_ip_addr""", info=CustomField | SortableField)
    login_ip = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""登录ip""", info=CustomField | SortableField)
    record_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""记录状态 参见 RecordStateEnum""", info=BitMask(CustomField | SortableField, RecordStateEnum))
    agent = Column(String(16), nullable=False, index=True, server_default=text("''"), doc=u"""登陆设备""", info=CustomField | SortableField)
    in_time = Column(DateTime, nullable=False, index=True, doc=u"""field in_time""", info=CustomField | SortableField)
    out_time = Column(DateTime, nullable=False, index=True, doc=u"""field out_time""", info=CustomField | SortableField)
    interval_time = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""时间间隔""", info=CustomField | SortableField)
    client_id = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""用户client_id""", info=CustomField | SortableField)
    ref_host = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""用户访问来源网页域名""", info=CustomField | SortableField)
    ref_url = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""用户访问来源网页地址""", info=CustomField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomViewRecord(SQLAlchemyObjectType):
            class Meta:
                model = cls

            admin = Field(lambda :AdminUser, description=u'parent 类型 后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

            room = Field(lambda :LiveRoom, description=u'对应频道 信息')
            def resolve_room(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

        return RoomViewRecord

class RoomViewRecordPagination(g.ObjectType):
    u''' RoomViewRecord 分页查询 列表'''
    rows = List(RoomViewRecord, description=u'当前查询 RoomViewRecord 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


##############################################################
###################		RoomViewRecordDms		######################
##############################################################


class RoomViewRecordDms(Base):
    u"""频道观看记录表  存储每条观看记录"""
    __tablename__ = 'room_view_record_dms'

    id = Column(Integer, primary_key=True, doc=u"""自增id""", info=SortableField)
    user_id = Column(String(32), nullable=False, index=True, doc=u"""用户user_id""", info=InitializeField | SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员id""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id""", info=InitializeField | SortableField)
    login_ip_addr = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""field login_ip_addr""", info=CustomField | SortableField)
    login_ip = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""登录ip""", info=CustomField | SortableField)
    record_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""记录状态 参见 RecordStateEnum""", info=BitMask(CustomField | SortableField, RecordStateEnum))
    agent = Column(String(16), nullable=False, index=True, server_default=text("''"), doc=u"""登陆设备""", info=CustomField | SortableField)
    in_time = Column(DateTime, nullable=False, index=True, doc=u"""field in_time""", info=CustomField | SortableField)
    out_time = Column(DateTime, nullable=False, index=True, doc=u"""field out_time""", info=CustomField | SortableField)
    interval_time = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""时间间隔""", info=CustomField | SortableField)
    client_id = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""用户client_id""", info=CustomField | SortableField)
    ref_host = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""用户访问来源网页域名""", info=CustomField | SortableField)
    ref_url = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""用户访问来源网页地址""", info=CustomField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomViewRecordDms(SQLAlchemyObjectType):
            class Meta:
                model = cls

            admin = Field(lambda :AdminUser, description=u'parent 类型 后台用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

            room = Field(lambda :LiveRoom, description=u'对应频道 信息')
            def resolve_room(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

        return RoomViewRecordDms

class RoomViewRecordDmsPagination(g.ObjectType):
    u''' RoomViewRecordDms 分页查询 列表'''
    rows = List(RoomViewRecord, description=u'当前查询 RoomViewRecordDms 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


from admin_base import AdminUser
from room_base import LiveRoom