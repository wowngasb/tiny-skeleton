# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
###################		RoomRunning		######################
##############################################################

class RoomRunning(Base):
    u"""频道人数流水记录"""
    __tablename__ = 'room_running'

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示代理""", info=InitializeField | SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示客户""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id，为0表示当前客户所有频道之和""", info=InitializeField | SortableField)
    num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道在间隔时间内最高人数""", info=CustomField | SortableField)
    live_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'1'"), doc=u"""直播状态 参见 LiveStateEnum""", info=BitMask(CustomField | SortableField, LiveStateEnum))
    timer_count = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""计时器序号，为时间戳除以时间间隔""", info=InitializeField | SortableField)
    timer_type = Column(String(8), nullable=False, index=True, server_default=text("'minute'"), doc=u"""间隔 minute 10minute  hour""", info=InitializeField | SortableField)
    ref_host = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""来源域名 ref_host 根据域名统计来源信息""", info=InitializeField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomRunning(SQLAlchemyObjectType):
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
                        if self.agent_id \
                            else None

            room = Field(lambda :LiveRoom, description=u'对应频道 信息')
            def resolve_room(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

        return RoomRunning


class RoomRunningPagination(g.ObjectType):
    u''' RoomRunning 分页查询 列表'''
    rows = List(RoomRunning, description=u'当前查询 RoomRunning 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

############### 汇总数据 RoomRunningSum ##################

class RoomRunningSum(Base):
    u"""频道人数流水记录 sum"""
    __tablename__ = 'room_running_sum'

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示代理""", info=InitializeField | SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示客户""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id，为0表示当前客户所有频道之和""", info=InitializeField | SortableField)
    num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道在间隔时间内最高人数""", info=CustomField | SortableField)
    live_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'1'"), doc=u"""直播状态 参见 LiveStateEnum""", info=BitMask(CustomField | SortableField, LiveStateEnum))
    timer_count = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""计时器序号，为时间戳除以时间间隔""", info=InitializeField | SortableField)
    timer_type = Column(String(8), nullable=False, index=True, server_default=text("'minute'"), doc=u"""间隔 minute 10minute  hour""", info=InitializeField | SortableField)
    ref_host = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""来源域名 ref_host 根据域名统计来源信息""", info=InitializeField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomRunningSum(SQLAlchemyObjectType):
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
                        if self.agent_id \
                            else None

            room = Field(lambda :LiveRoom, description=u'对应频道 信息')
            def resolve_room(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

        return RoomRunningSum


class RoomRunningSumPagination(g.ObjectType):
    u''' RoomRunningSum 分页查询 列表'''
    rows = List(RoomRunningSum, description=u'当前查询 RoomRunningSum 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')
    
############### 备用数据 RoomRunningDms ##################

class RoomRunningDms(Base):
    u"""频道人数流水记录 dms"""
    __tablename__ = 'room_running_dms'

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示代理""", info=InitializeField | SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示客户""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id，为0表示当前客户所有频道之和""", info=InitializeField | SortableField)
    num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道在间隔时间内最高人数""", info=CustomField | SortableField)
    live_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'1'"), doc=u"""直播状态 参见 LiveStateEnum""", info=BitMask(CustomField | SortableField, LiveStateEnum))
    timer_count = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""计时器序号，为时间戳除以时间间隔""", info=InitializeField | SortableField)
    timer_type = Column(String(8), nullable=False, index=True, server_default=text("'minute'"), doc=u"""间隔 minute 10minute  hour""", info=InitializeField | SortableField)
    ref_host = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""来源域名 ref_host 根据域名统计来源信息""", info=InitializeField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomRunningDms(SQLAlchemyObjectType):
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
                        if self.agent_id \
                            else None

            room = Field(lambda :LiveRoom, description=u'对应频道 信息')
            def resolve_room(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

        return RoomRunningDms


class RoomRunningDmsPagination(g.ObjectType):
    u''' RoomRunningDms 分页查询 列表'''
    rows = List(RoomRunningDms, description=u'当前查询 RoomRunningDms 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

############### 备用数据 RoomRunningDmsRef ##################

class RoomRunningDmsRef(Base):
    u"""频道人数流水记录 dms"""
    __tablename__ = 'room_running_dms_ref'

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示代理""", info=InitializeField | SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示客户""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id，为0表示当前客户所有频道之和""", info=InitializeField | SortableField)
    num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道在间隔时间内最高人数""", info=CustomField | SortableField)
    live_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'1'"), doc=u"""直播状态 参见 LiveStateEnum""", info=BitMask(CustomField | SortableField, LiveStateEnum))
    timer_count = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""计时器序号，为时间戳除以时间间隔""", info=InitializeField | SortableField)
    timer_type = Column(String(8), nullable=False, index=True, server_default=text("'minute'"), doc=u"""间隔 minute 10minute  hour""", info=InitializeField | SortableField)
    ref_host = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""来源域名 ref_host 根据域名统计来源信息""", info=InitializeField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomRunningDmsRef(SQLAlchemyObjectType):
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
                        if self.agent_id \
                            else None

            room = Field(lambda :LiveRoom, description=u'对应频道 信息')
            def resolve_room(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

        return RoomRunningDmsRef


class RoomRunningDmsRefPagination(g.ObjectType):
    u''' RoomRunningDmsRef 分页查询 列表'''
    rows = List(RoomRunningDmsRef, description=u'当前查询 RoomRunningDmsRef 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

############### 备用数据 RoomRunningDmsSum ##################

class RoomRunningDmsSum(Base):
    u"""频道人数流水记录 dms"""
    __tablename__ = 'room_running_dms_sum'

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示代理""", info=InitializeField | SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示客户""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id，为0表示当前客户所有频道之和""", info=InitializeField | SortableField)
    num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道在间隔时间内最高人数""", info=CustomField | SortableField)
    live_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'1'"), doc=u"""直播状态 参见 LiveStateEnum""", info=BitMask(CustomField | SortableField, LiveStateEnum))
    timer_count = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""计时器序号，为时间戳除以时间间隔""", info=InitializeField | SortableField)
    timer_type = Column(String(8), nullable=False, index=True, server_default=text("'minute'"), doc=u"""间隔 minute 10minute  hour""", info=InitializeField | SortableField)
    ref_host = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""来源域名 ref_host 根据域名统计来源信息""", info=InitializeField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class RoomRunningDmsSum(SQLAlchemyObjectType):
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
                        if self.agent_id \
                            else None

            room = Field(lambda :LiveRoom, description=u'对应频道 信息')
            def resolve_room(self, args, context, info):
                return LiveRoom.query.filter_by(room_id=self.room_id).first() \
                        if self.room_id \
                            else None

        return RoomRunningDmsSum


class RoomRunningDmsSumPagination(g.ObjectType):
    u''' RoomRunningDmsSum 分页查询 列表'''
    rows = List(RoomRunningDmsSum, description=u'当前查询 RoomRunningDmsSum 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


from admin_base import AdminUser
from room_base import LiveRoom