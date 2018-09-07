# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
###############		DailyRoomRunning		##################
##############################################################

class DailyRoomRunning(Base):
    u"""每日 频道 观看人数峰值表  由每日定时任务 查询出峰值收据后 写入"""
    __tablename__ = 'daily_room_running'
    __table_args__ = (
        Index('day_room_admin_udx', 'per_day', 'room_id', 'admin_id', unique=True),
    )

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示客户""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id，为0表示当前客户所有频道之和""", info=InitializeField | SortableField)
    num_max = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""全部频道 最高峰值在线人数""", info=CustomField | SortableField)
    num_max_time = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""全部频道 最高峰值在线人数 峰值时刻""", info=CustomField | SortableField)

    hw_num_max = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""海外频道 最高峰值在线人数""", info=CustomField | SortableField)
    hw_num_max_time = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""海外频道 最高峰值在线人数 峰值时刻""", info=CustomField | SortableField)

    package_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""当前生效套餐 套餐内并发数量""", info=CustomField | SortableField)
    over_price = Column(Numeric(20, 2), nullable=False, index=True, server_default=text("'0.00'"), doc=u"""超出并发套餐之后的 单价 单位为 元/每人每天""", info=CustomField | SortableField)


    per_day = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""每天 日期  格式为 20160801""", info=InitializeField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class DailyRoomRunning(SQLAlchemyObjectType):
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

        return DailyRoomRunning

class DailyRoomRunningPagination(g.ObjectType):
    u''' DailyRoomRunning 分页查询 列表'''
    rows = List(DailyRoomRunning, description=u'当前查询 DailyRoomRunning 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

############### 备用数据 DailyRoomRunningDms ##################

class DailyRoomRunningDms(Base):
    u"""每日 频道 观看人数峰值表  由每日定时任务 查询出峰值收据后 写入 未使用"""
    __tablename__ = 'daily_room_running_dms'
    __table_args__ = (
        Index('day_room_admin_udx', 'per_day', 'room_id', 'admin_id', unique=True),
    )

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""管理员admin_id，表示客户""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""频道room_id，为0表示当前客户所有频道之和""", info=InitializeField | SortableField)
    num_max = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""全部频道 最高峰值在线人数""", info=CustomField | SortableField)
    num_max_time = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""全部频道 最高峰值在线人数 峰值时刻""", info=CustomField | SortableField)

    hw_num_max = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""海外频道 最高峰值在线人数""", info=CustomField | SortableField)
    hw_num_max_time = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""海外频道 最高峰值在线人数 峰值时刻""", info=CustomField | SortableField)

    package_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""当前生效套餐 套餐内并发数量""", info=CustomField | SortableField)
    over_price = Column(Numeric(20, 2), nullable=False, index=True, server_default=text("'0.00'"), doc=u"""超出并发套餐之后的 单价 单位为 元/每人每天""", info=CustomField | SortableField)

    per_day = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""每天 日期  格式为 20160801""", info=InitializeField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class DailyRoomRunningDms(SQLAlchemyObjectType):
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

        return DailyRoomRunningDms

class DailyRoomRunningDmsPagination(g.ObjectType):
    u''' DailyRoomRunningDms 分页查询 列表'''
    rows = List(DailyRoomRunningDms, description=u'当前查询 DailyRoomRunningDms 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

##############################################################
#################		DailyViewCount		##################
##############################################################

class DailyViewCount(Base):
    u"""每日频道点击数 统计表  每打开一次频道页面  点击数+1"""
    __tablename__ = 'daily_view_count'
    __table_args__ = (
        Index('day_room_admin_udx', 'per_day', 'room_id', 'admin_id', unique=True),
    )

    id = Column(Integer, primary_key=True, doc=u"""自增主键""", info=SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""admin_id""", info=InitializeField | SortableField)
    room_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""room_id""", info=InitializeField | SortableField)
    view_count = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""每日观看计数""", info=CustomField | SortableField)
    per_day = Column(Integer, nullable=False, index=True, doc=u"""每天 日期  格式为 20160801""", info=InitializeField | SortableField)

    num_max = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""全部频道 最高峰值在线人数""", info=CustomField | SortableField)
    num_max_time = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""全部频道 最高峰值在线人数 峰值时刻""", info=CustomField | SortableField)

    dms_num_max = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""全部频道 最高峰值在线人数""", info=CustomField | SortableField)
    dms_num_max_time = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""dms 来源  全部频道 最高峰值在线人数 峰值时刻""", info=CustomField | SortableField)

    total_ip = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""每天 ip 去重 数量总和""", info=InitializeField | SortableField)
    total_view_record = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""每天 观看记录 数量总和 """, info=InitializeField | SortableField)
    total_unique_user = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""每天 uv 去重 总和""", info=InitializeField | SortableField)
    total_interval_time = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""每天 用户观看时长 总和""", info=InitializeField | SortableField)

    dms_total_ip = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""dms 来源 每天 ip 去重 数量总和""", info=InitializeField | SortableField)
    dms_total_view_record = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""dms 来源 每天 观看记录 数量总和 """, info=InitializeField | SortableField)
    dms_total_unique_user = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""dms 来源 每天 uv 去重 总和""", info=InitializeField | SortableField)
    dms_total_interval_time = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""dms 来源 每天 用户观看时长 总和""", info=InitializeField | SortableField)

    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class DailyViewCount(SQLAlchemyObjectType):
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

        return DailyViewCount

class DailyViewCountPagination(g.ObjectType):
    u''' DailyViewCount 分页查询 列表'''
    rows = List(DailyViewCount, description=u'当前查询 DailyViewCount 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


from admin_base import AdminUser
from room_base import LiveRoom