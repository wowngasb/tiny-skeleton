# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model


##############################################################
###################		AdminUser		######################
##############################################################


class AdminUser(Base):
    u"""后台管理员 用户表 每条数据对应一个后台用户"""
    __tablename__ = 'admin_user'

    admin_id = Column(Integer, primary_key=True, doc=u"""管理员id自增""", info=SortableField)
    name = Column(String(32), nullable=False, index=True, unique=True, server_default=text("''"), doc=u"""管理员登陆用户名，可修改""", info=CustomField | SortableField)
    pasw = Column(String(32), nullable=False, server_default=text("''"), doc=u"""管理员登录密码，存储加盐md5""", info=HiddenField)
    pasw_time = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""上一次修改密码的时间""", info=SortableField)
    login_ip = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""上次登录ip""", info= SortableField)
    login_location = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""field op_location""", info= SortableField)

    vlimit_per_parent = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对下属客户 最大并发数限制  一般用于代理，0为无限制""", info=CustomField | SortableField)
    vlimit_per_room = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对下属每个频道 最大并发数限制  一般用于代理，0为无限制""", info=CustomField | SortableField)
    vlimit_all_room = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对下属所有频道 最大并发数限制 之和 一般用于客户，0为无限制""", info=CustomField | SortableField)

    vlimit_online_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""账号  最大并发数限制 一般用于客户 和 代理，0为无限制""", info=CustomField | SortableField)

    nlimit_count_parent = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对下属 客户 总数限制  一般用于代理，0为无限制""", info=CustomField | SortableField)
    nlimit_count_room = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对下属 频道 总数限制  一般用于客户，0为无限制""", info=CustomField | SortableField)
    nlimit_count_sub = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对下属 子账号 总数限制 一般用于客户，0为无限制""", info=CustomField | SortableField)
    nlimit_count_player = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对下属 播放器 总数限制 一般用于客户，0为无限制""", info=CustomField | SortableField)
    nlimit_count_stream = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""对下属 视频流 总数限制 一般用于客户，0为无限制""", info=CustomField | SortableField)


    login_count = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""登录次数""", info= SortableField)
    login_time = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""上次登陆时间""", info= SortableField)
    admin_note = Column(String(255), nullable=False, server_default=text("''"), doc=u"""客户信息备注  用于商务添加备注信息""", info=CustomField)
    title = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""管理员标题，用于显示""", info=CustomField | SortableField)
    avator = Column(String(128), nullable=False, server_default=text("''"), doc=u"""管理员头像，用于显示""", info=CustomField)
    email = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""邮箱地址 未使用""", info=CustomField | SortableField)
    cellphone = Column(String(64), nullable=False, index=True, server_default=text("''"), doc=u"""手机号码 未使用""", info=CustomField | SortableField)

    company = Column(String(32), nullable=False, server_default=text("''"), doc=u"""用户公司""", info=CustomField | SortableField)
    industry = Column(String(32), nullable=False, server_default=text("''"), doc=u"""用户行业""", info=CustomField | SortableField)
    register_from = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""用户注册 来源 只用于标注 可选 super""", info= SortableField)
    admin_type = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""管理员类型 用于区分控制台 参见 AdminTypeEnum""", info=BitMask(InitializeField | SortableField, AdminTypeEnum))
    api_key = Column(String(64), nullable=False, unique=True, index=True, server_default=text("''"), doc=u"""api 认证key 用于调用管理api""", info=HiddenField)

    agent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""所属代理admin_id 类型为 parent 的管理员有此字段""", info=InitializeField | SortableField)
    parent_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""所属父级admin_id 类型为 sub 的管理员有此字段""", info=InitializeField | SortableField)

    business_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""推广商务 admin_id """, info=InitializeField | SortableField)
    business_belong = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""所属商务 admin_id """, info=InitializeField | SortableField)
    admin_slug = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""用户类型 标注 """, info= SortableField | CustomField)


    agent_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""下属代理数量 类型为 super 的管理员有此字段""", info= SortableField)
    parent_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""下属客户数量 类型为 super agent 的管理员有此字段""", info= SortableField)
    sub_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""下属子账号数量 类型为 super agent parent 的管理员有此字段""", info= SortableField)

    room_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""下属频道数量 类型为 super agent parent 的管理员有此字段""", info= SortableField)
    player_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""下属播放器数量 类型为 super agent parent 的管理员有此字段""", info= SortableField)
    stream_num = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""下属视频流数量 类型为 super agent parent 的管理员有此字段""", info= SortableField)

    viewer_now = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""当前在线人数 实时变动""", info=CustomField | SortableField)
    viewer_max = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""同时最该在线人数，历史最高人数""", info=CustomField | SortableField)
    viewer_max_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""同时最该在线人数，历史最高人数 时刻""", info=SortableField)

    expiration_date = Column(DateTime, nullable=False, index=True, server_default=text("'0000-00-00 00:00:00'"), doc=u"""管理员有效期 定时任务到达这一天自动冻结""", info= SortableField | CustomField)
    view_auth_url = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""用户自定义认证url  防盗链  进入频道之前验证""", info=CustomField)
    mcs_vhost = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""客户 mcs 直播帐号 vhost 域名 默认为空""", info=CustomField)

    cname_host = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""客户 切换域名 CNAME 域名 默认为空""", info=CustomField)
    cname_cdn = Column(String(128), nullable=False, index=True, server_default=text("''"), doc=u"""客户 CDN CNAME 域名 默认为空""", info=CustomField)
    admin_config = Column(Text, nullable=False, doc=u"""客户 相关配置 json 文本存储 默认为空""", info=CustomField)

    account_credit = Column(Numeric(20, 2), nullable=False, index=True, server_default=text("'0.00'"), doc=u"""用户信用额度(用户欠费大于信用额度会被冻结)""", info=CustomField | SortableField)
    account_balance = Column(Numeric(20, 2), nullable=False, index=True, server_default=text("'0.00'"), doc=u"""用户账户余额  默认为0  小于0表示欠费""", info=CustomField | SortableField)
    limit_onlinenumber_over_price = Column(Numeric(20, 2), nullable=False, index=True, server_default=text("'2.00'"), doc=u"""超出并发套餐之后的 单价 单位为 元/每人每天""", info=CustomField | SortableField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class AdminUser(SQLAlchemyObjectType):
            class Meta:
                model = cls

            countly = Field(lambda :AdminCountly, description=u'Conutly配置 parent 类型 用户帐号有此字段')
            def resolve_countly(self, args, context, info):
                return AdminCountly.query.filter_by(admin_id = self.admin_id).first() \
                        if self.admin_type=='parent' else None


            agent = Field(lambda :AdminUser, description=u'上级代理信息 parent、sub 类型 用户帐号有此字段')
            def resolve_agent(self, args, context, info):
                return AdminUser.query.filter_by(admin_id = self.agent_id).first() \
                        if (self.admin_type=='parent' or self.admin_type=='sub') and self.agent_id else None

            parent = Field(lambda :AdminUser, description=u'上级父帐号信息 sub 类型 用户帐号有此字段')
            def resolve_parent(self, args, context, info):
                return AdminUser.query.filter_by(admin_id = self.parent_id).first() \
                        if self.admin_type=='sub' and self.parent_id else None

            contentList = Field(lambda: RoomContentConfigPagination, description=u'下属频道列表 super、agent、parent、sub 类型 用户帐号有此字段',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                content_id=g.Argument(g.Int, default_value=0, description=u'检索 content_id'),
                name=g.Argument(g.String, default_value='', description=u'精确检索 content 名称'),
                content_group=g.Argument(g.String, default_value='', description=u'精确检索 content_group 可选值 item list'),

                content_slug=g.Argument(g.String, default_value='', description=u'模糊检索 content_slug'),
                content_key=g.Argument(g.String, default_value='', description=u'模糊检索 content_key'),
                content_title=g.Argument(g.String, default_value='', description=u'模糊检索 content_title'),
                content_doc=g.Argument(g.String, default_value='', description=u'模糊检索 content_doc'),
                content_text=g.Argument(g.String, default_value='', description=u'模糊检索 content_text'),
                content_config=g.Argument(g.String, default_value='', description=u'模糊检索 content_config'),

                room_id=g.Argument(g.Int, default_value=0, description=u'对应频道id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_contentList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = RoomContentConfig.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(RoomContentConfig, SortableField))
                return RoomContentConfigPagination(rows=rows, pageInfo=pageInfo)

            roomList = Field(lambda :LiveRoomPagination, description=u'下属频道列表 super、agent、parent、sub 类型 用户帐号有此字段',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),
                room_id=g.Argument(g.Int, default_value=0, description=u'检索 频道id'),
                room_title=g.Argument(g.String, default_value='', description=u'模糊检索 频道标题'),
                viewlimit=g.Argument(IntRange, default_value=None, description=u'范围检索 频道最大观看人数限制'),
                viewer_count=g.Argument(IntRange, default_value=None, description=u'范围检索 累计观看人数'),
                viewer_max=g.Argument(IntRange, default_value=None, description=u'范围检索 历史最高人数'),

                player_id=g.Argument(g.Int, default_value=0, description=u'检索 播放器id 0表示检索全部'),

                stream_id=g.Argument(g.Int, default_value=0, description=u'检索 视频流id 0表示检索全部'),
                mcs_account=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_account'),
                mcs_password=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_password'),
                mcs_vhost=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_vhost'),
                mcs_app=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_app'),
                mcs_stream=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_stream'),

                live_state=g.Argument(LiveStateEnum, default_value=0, description=u'检索 直播状态'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_roomList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = LiveRoom.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(LiveRoom, SortableField))
                return LiveRoomPagination(rows=rows, pageInfo=pageInfo)

            playerList = Field(lambda :PlayerBasePagination, description=u'下属播放器列表 super、agent、parent、sub 类型 用户帐号有此字段',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),
                player_name=g.Argument(g.String, default_value='', description=u'模糊检索 播放器名称'),
                player_type=g.Argument(PlayerTypeEnum, default_value=None, description=u'检索 播放器类型'),
                player_id=g.Argument(g.Int, default_value=0, description=u'检索 播放器id 0表示检索全部'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_playerList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = PlayerBase.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(PlayerBase, SortableField))
                return PlayerBasePagination(rows=rows, pageInfo=pageInfo)

            vodList = Field(lambda :StreamVodPagination, description=u'下属视频 vod 列表 super、agent、parent、sub 类型 用户帐号有此字段',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                vod_id=g.Argument(g.String, default_value='', description=u'模糊检索 vod_id'),
                v_streamname=g.Argument(g.String, default_value='', description=u'模糊检索 v_streamname'),
                v_ossbucket=g.Argument(g.String, default_value='', description=u'模糊检索 v_ossbucket'),
                v_domainname=g.Argument(g.String, default_value='', description=u'模糊检索 v_domainname'),
                v_appname=g.Argument(g.String, default_value='', description=u'模糊检索 v_appname'),
                v_ossendpoint=g.Argument(g.String, default_value='', description=u'模糊检索 v_ossendpoint'),

                m3u8_url=g.Argument(g.String, default_value='', description=u'模糊检索 m3u8_url'),
                mp4_url=g.Argument(g.String, default_value='', description=u'模糊检索 mp4_url'),
                flv_url=g.Argument(g.String, default_value='', description=u'模糊检索 flv_url'),

                v_stream_id=g.Argument(g.Int, default_value=0, description=u'录制来源 stream_id'),
                v_room_id=g.Argument(g.Int, default_value=0, description=u'录制来源 room_id'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),

                v_createtime=g.Argument(DateRange, default_value=None, description=u'范围检索 录制 CreateTime'),
                v_starttime=g.Argument(DateRange, default_value=None, description=u'范围检索 录制 StartTime'),
                v_endtime=g.Argument(DateRange, default_value=None, description=u'范围检索 录制 EndTime'),
                v_duration=g.Argument(FloatRange, default_value=None, description=u'范围检索 录制 长度'),

                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_vodList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = StreamVod.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(StreamVod, SortableField))
                return StreamVodPagination(rows=rows, pageInfo=pageInfo)


            streamList = Field(lambda :StreamBasePagination, description=u'下属视频流列表 super、agent、parent、sub 类型 用户帐号有此字段',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),
                stream_name=g.Argument(g.String, default_value='', description=u'模糊检索 视频流名称'),
                stream_type=g.Argument(PlayerTypeEnum, default_value=None, description=u'检索 视频流类型'),
                stream_id=g.Argument(g.Int, default_value=0, description=u'检索 视频流id 0表示检索全部'),

                mcs_account=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_account'),
                mcs_password=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_password'),
                mcs_vhost=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_vhost'),
                mcs_app=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_app'),
                mcs_stream=g.Argument(g.String, default_value='', description=u'模糊检索 mcs_stream'),

                live_state=g.Argument(LiveStateEnum, default_value=0, description=u'检索 直播状态'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_streamList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = StreamBase.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(StreamBase, SortableField))
                return StreamBasePagination(rows=rows, pageInfo=pageInfo)

            agentSub = List(lambda :AdminUser, description=u'下属 所有代理 正常或冻结 状态 super 类型 用户帐号有此字段')
            def resolve_streamList(self, args, context, info):
                return AdminUser.query.filter_by(state=[1, 2], admin_type='agent').all()

            xdyParentSub = List(lambda :AdminUser, description=u'下属 所有 有套餐 客户 正常或冻结 状态 super 类型 用户帐号有此字段')
            def resolve_streamList(self, args, context, info):
                return AdminUser.query.filter_by(agent_id=self.admin_id, state=[1, 2], admin_type='parent').all()


            xdyAgentSub = List(lambda :AdminUser, description=u'下属 所有 有套餐 代理 正常或冻结 状态 super 类型 用户帐号有此字段')
            def resolve_streamList(self, args, context, info):
                return AdminUser.query.filter_by(state=[1, 2], admin_type='agent').all()

            parentSub = List(lambda :AdminUser, description=u'下属 所有客户 正常或冻结 状态 agent 类型 用户帐号有此字段')
            def resolve_streamList(self, args, context, info):
                return AdminUser.query.filter_by(agent_id=self.admin_id, state=[1, 2], admin_type='parent').all()

            subSub = List(lambda :AdminUser, description=u'下属 所有客户子账号 正常或冻结 状态 parent类型 用户帐号有此字段')
            def resolve_streamList(self, args, context, info):
                return AdminUser.query.filter_by(parent_id=self.admin_id, state=[1, 2], admin_type='sub').all()

            roomSub = List(lambda :LiveRoom, description=u'下属 所有频道 正常或冻结 状态 parent类型 用户帐号有此字段')
            def resolve_roomSub(self, args, context, info):
                return LiveRoom.query.filter_by(admin_id=self.admin_id, state=[1, 2]).all()

            streamSub = List(lambda :StreamBase, description=u'下属 所有视频流 正常或冻结 状态 parent类型 用户帐号有此字段 限定为mcs')
            def resolve_streamSub(self, args, context, info):
                return StreamBase.query.filter_by(admin_id=self.admin_id, state=[1, 2]).all()

            playerSub = List(lambda :PlayerBase, description=u'下属 所有播放器 正常或冻结 状态 parent类型 用户帐号有此字段')
            def resolve_playerSub(self, args, context, info):
                return PlayerBase.query.filter_by(admin_id=self.admin_id, state=[1, 2]).all()

            agentList = Field(lambda :AdminUserPagination, description=u'下属代理列表 super 类型 用户帐号有此字段',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'检索 管理员id'),
                name=g.Argument(g.String, default_value='', description=u'模糊检索 登陆用户名'),
                login_ip=g.Argument(g.String, default_value='', description=u'模糊检索 上次登录ip'),
                login_time=g.Argument(DateRange, default_value=None, description=u'范围检索 上次登陆时间'),
                admin_note=g.Argument(g.String, default_value='', description=u'模糊检索 备注信息'),
                admin_slug=g.Argument(g.String, default_value='', description=u'模糊检索 slug 信息'),
                title=g.Argument(g.String, default_value='', description=u'模糊检索 管理员标题'),
                register_from=g.Argument(g.String, default_value='', description=u'检索 用户注册 来源'),
                expiration_date=g.Argument(DateRange, default_value=None, description=u'范围检索 客户有效期'),
                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_agentList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = AdminUser.query.filter_by(admin_type='agent') # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(AdminUser, SortableField))
                return AdminUserPagination(rows=rows, pageInfo=pageInfo)

            parentList = Field(lambda :AdminUserPagination, description=u'下属客户列表 super agent 类型 用户帐号有此字段',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'检索 管理员id'),
                name=g.Argument(g.String, default_value='', description=u'模糊检索 登陆用户名'),
                login_ip=g.Argument(g.String, default_value='', description=u'模糊检索 上次登录ip'),
                login_time=g.Argument(DateRange, default_value=None, description=u'范围检索 上次登陆时间'),
                admin_note=g.Argument(g.String, default_value='', description=u'模糊检索 备注信息'),
                admin_slug=g.Argument(g.String, default_value='', description=u'模糊检索 slug 信息'),
                title=g.Argument(g.String, default_value='', description=u'模糊检索 管理员标题'),
                register_from=g.Argument(g.String, default_value='', description=u'检索 用户注册 来源'),
                expiration_date=g.Argument(DateRange, default_value=None, description=u'范围检索 客户有效期'),
                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id  0表示检索全部  '),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_parentList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = AdminUser.query.filter_by(admin_type='parent') # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(AdminUser, SortableField))
                return AdminUserPagination(rows=rows, pageInfo=pageInfo)

            subList = Field(lambda :AdminUserPagination, description=u'下属客户子账号列表 super agent parent类型 用户帐号有此字段',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),
                sub_id=g.Argument(g.Int, default_value=0, description=u'检索 管理员id'),
                name=g.Argument(g.String, default_value='', description=u'模糊检索 登陆用户名'),
                login_ip=g.Argument(g.String, default_value='', description=u'模糊检索 上次登录ip'),
                login_time=g.Argument(DateRange, default_value=None, description=u'范围检索 上次登陆时间'),
                admin_note=g.Argument(g.String, default_value='', description=u'模糊检索 备注信息'),
                admin_slug=g.Argument(g.String, default_value='', description=u'模糊检索 slug 信息'),
                title=g.Argument(g.String, default_value='', description=u'模糊检索 管理员标题'),
                register_from=g.Argument(g.String, default_value='', description=u'检索 用户注册 来源'),
                expiration_date=g.Argument(DateRange, default_value=None, description=u'范围检索 客户有效期'),
                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id  0表示检索全部  '),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属父帐号id 0表示检索全部'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_subList(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = AdminUser.query.filter_by(admin_type='sub') # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(AdminUser, SortableField))
                return AdminUserPagination(rows=rows, pageInfo=pageInfo)

            dailyRoomRunning = Field(lambda :DailyRoomRunningPagination, description=u'每日峰值 流水数据 当个频道每日最大值',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),
                room_id=g.Argument(g.Int, default_value=0, description=u'频道room_id'),

                per_day=g.Argument(IntRange, default_value=None, description=u'范围检索 日期整数 格式为 20160801'),
                num_max=g.Argument(IntRange, default_value=None, description=u'范围检索 峰值人数'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                useDmsData=g.Argument(g.Int, default_value=1, description=u'是否使用 来源为 DMS 的数据  1使用  0不使用'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_dailyRoomRunning(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                dao = DailyRoomRunningDms if args.get('useDmsData', 1) else DailyRoomRunning
                query = dao.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(dao, SortableField))
                return DailyRoomRunningPagination(rows=rows, pageInfo=pageInfo)


            dailyAdminRunning = Field(lambda :DailyRoomRunningPagination, description=u'每日峰值 流水数据 客户并发数据 每日最大值',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),

                per_day=g.Argument(IntRange, default_value=None, description=u'范围检索 日期整数 格式为 20160801'),
                num_max=g.Argument(IntRange, default_value=None, description=u'范围检索 峰值人数'),
                admin_slug=g.Argument(g.String, default_value='', description=u'admin_slug 检索 用于 超管检索全部数据'),
                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                useDmsData=g.Argument(g.Int, default_value=1, description=u'是否使用 来源为 DMS 的数据  1使用  0不使用'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_dailyAdminRunning(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                dao = DailyRoomRunningDms if args.get('useDmsData', 1) else DailyRoomRunning
                query = dao.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(dao, SortableField))
                return DailyRoomRunningPagination(rows=rows, pageInfo=pageInfo)

            roomRunning = Field(lambda :RoomRunningPagination, description=u'并发流水数据 分页查询 时间间隔根据范围大小自动设置',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),
                room_id=g.Argument(g.Int, default_value=0, description=u'频道room_id'),
                ref_host=g.Argument(g.String, default_value='', description=u'模糊检索 数量统计 来源域名'),
                timer_type=g.Argument(g.String, default_value='', description=u'数据刻度 timer_type 默认为空 表示自适应'),

                live_state=g.Argument(LiveStateEnum, default_value=None, description=u'直播状态 参见 LiveStateEnum'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),

                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                target=g.Argument(g.String, default_value='room_running', description=u'数据来源 数据表 默认为 room_running'),
                sum_by=g.Argument(g.String, default_value='room', description=u'数据分组统计 默认为 room'),
                is_ref=g.Argument(g.Int, default_value=0, description=u'是否使用 ref_host  的数据  1使用  0不使用'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_roomRunning(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                dao = RoomRunningDms if args.get('useDmsData', 1) else RoomRunning
                query = dao.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(dao, SortableField))
                return RoomRunningPagination(rows=rows, pageInfo=pageInfo)

            dailyViewCount = Field(lambda :DailyViewCountPagination, description=u'每日点击数 分页查询',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),
                room_id=g.Argument(g.Int, default_value=0, description=u'频道room_id'),

                data_type=g.Argument(g.String, default_value='room', description=u'数据类型 支持 room parent '),
                useDmsData=g.Argument(g.Int, default_value=1, description=u'是否使用 来源为 DMS 的数据  1使用  0不使用'),
                per_day=g.Argument(IntRange, default_value=None, description=u'范围检索 日期整数 格式为 20160801'),
                view_count=g.Argument(IntRange, default_value=None, description=u'范围检索 每日观看计数'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_dailyViewCount(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = DailyViewCount.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(DailyViewCount, SortableField))
                return DailyViewCountPagination(rows=rows, pageInfo=pageInfo)

            roomViewRecord = Field(lambda :RoomViewRecordPagination, description=u'频道访问记录 分页查询',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),
                room_id=g.Argument(g.Int, default_value=0, description=u'频道room_id'),

                user_id=g.Argument(g.String, default_value='', description=u'模糊查询 用户id'),
                login_ip_addr=g.Argument(g.String, default_value='', description=u'模糊查询 用户ip地域'),
                login_ip=g.Argument(g.String, default_value='', description=u'模糊查询 用户ip'),
                record_state=g.Argument(RecordStateEnum, default_value=None, description=u'查询 记录状态 参见 RecordStateEnum'),
                agent=g.Argument(g.String, default_value='', description=u'查询 登陆设备'),
                in_time=g.Argument(DateRange, default_value=None, description=u'范围检索 进入频道时间'),
                out_time=g.Argument(DateRange, default_value=None, description=u'范围检索 退出频道时间'),
                interval_time=g.Argument(IntRange, default_value=None, description=u'范围检索 用户观看时间'),
                ref_host=g.Argument(g.String, default_value='', description=u'模糊查询 用户访问来源网页域名'),
                ref_url=g.Argument(g.String, default_value='', description=u'模糊查询 用户访问来源域名'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                useDmsData=g.Argument(g.Int, default_value=1, description=u'是否使用 来源为 DMS 的数据  1使用  0不使用'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_roomViewRecord(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                dao = RoomRunningDms if args.get('useDmsData', 1) else RoomRunning
                query = RoomViewRecord.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(RoomViewRecord, SortableField))
                return RoomViewRecordPagination(rows=rows, pageInfo=pageInfo)

            roomPublishRecord = Field(lambda :RoomPublishRecordPagination, description=u'视频流 直播记录 分页查询',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                agent_id=g.Argument(g.Int, default_value=0, description=u'所属代理id 只用于超管检索 优先级低于 admin_id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'所属帐号id'),

                stream_type=g.Argument(StreamTypeEnum, default_value=None, description=u'查询 视频流类型 参见 StreamTypeEnum'),
                stream_id=g.Argument(g.Int, default_value=0, description=u'视频流stream_id'),
                live_state=g.Argument(LiveStateEnum, default_value=None, description=u'直播状态 参见 LiveStateEnum'),

                start_time=g.Argument(DateRange, default_value=None, description=u'范围检索 开始直播时间'),
                end_time=g.Argument(DateRange, default_value=None, description=u'范围检索 结束直播时间'),
                interval_time=g.Argument(IntRange, default_value=None, description=u'范围检索 直播时长'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_roomPublishRecord(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = RoomPublishRecord.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(RoomPublishRecord, SortableField))
                return RoomPublishRecordPagination(rows=rows, pageInfo=pageInfo)

            adminRecord = Field(lambda :AdminRecordPagination, description=u'后台 操作记录记录 分页查询',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                admin_id=g.Argument(g.Int, default_value=0, description=u'相关管理员id'),
                room_id=g.Argument(g.Int, default_value=0, description=u'相关频道id'),
                player_id=g.Argument(g.Int, default_value=0, description=u'相关播放器id'),
                stream_id=g.Argument(g.Int, default_value=0, description=u'相关视频流id'),
                mgr_id=g.Argument(g.Int, default_value=0, description=u'相关管理员id'),

                op_admin_id=g.Argument(g.Int, default_value=0, description=u'操作者 管理员id'),

                op_desc=g.Argument(g.String, default_value='', description=u'模糊查询 操作描述'),
                op_ref=g.Argument(g.String, default_value='', description=u'模糊查询 操作ref'),
                op_url=g.Argument(g.String, default_value='', description=u'模糊查询 操作网址'),
                op_args=g.Argument(g.String, default_value='', description=u'模糊查询 操作参数'),
                op_method=g.Argument(g.String, default_value='', description=u'模糊查询 调用方法'),
                op_ip=g.Argument(g.String, default_value='', description=u'模糊查询 操作来源ip'),
                op_location=g.Argument(g.String, default_value='', description=u'模糊查询 操作来源地域'),

                id=g.Argument(g.Int, default_value=0, description=u'操作记录id'),
                op_type=g.Argument(g.Int, default_value=0, description=u'操作类型  0 未知  1 登录  2 登出 3 其他操作'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_adminRecord(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = AdminRecord.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(AdminRecord, SortableField))
                return AdminRecordPagination(rows=rows, pageInfo=pageInfo)


            xdyProduct = Field(lambda :XdyProductPagination, description=u'客户相关订单 分页查询',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                admin_id=g.Argument(g.Int, default_value=0, description=u'订单 所属客户id'),
                operator_id=g.Argument(g.Int, default_value=0, description=u'操作者admin_id'),

                product_id=g.Argument(g.Int, default_value=0, description=u'产品id'),
                product_title=g.Argument(g.String, default_value='', description=u'模糊匹配 产品标题'),

                product_type=g.Argument(g.String, default_value='', description=u'产品类型 package 套餐   默认为套餐'),
                limit_type=g.Argument(g.String, default_value='', description=u'限额类型  支持  onlinenum  并发人数  '),
                limit_value=g.Argument(IntRange, default_value=None, description=u'范围检索 限额数量  限制类型为人数（个） '),
                expired_days=g.Argument(IntRange, default_value=None, description=u'范围检索 产品有效时间 单位为天'),

                product_num=g.Argument(IntRange, default_value=None, description=u'范围检索 产品库存 默认为 1  表示产品可购买的数量 为0后无法购买'),
                sell_count=g.Argument(IntRange, default_value=None, description=u'范围检索 产品销售数量'),


                product_price=g.Argument(FloatRange, default_value=None, description=u'范围检索 产品 单价'),

                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),

                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_xdyProduct(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = XdyProduct.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(XdyProduct, SortableField))
                return XdyProductPagination(rows=rows, pageInfo=pageInfo)



            xdyAdminProduct = Field(lambda :XdyAdminProductPagination, description=u'客户已购买产品 分页查询',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                id=g.Argument(g.Int, default_value=0, description=u'记录id'),
                product_id=g.Argument(g.Int, default_value=0, description=u'产品id'),
                order_id=g.Argument(g.Int, default_value=0, description=u'产品id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'产品 所属客户id'),
                product_type=g.Argument(g.String, default_value='', description=u'产品类型 package 套餐   默认为套餐'),
                expired_days=g.Argument(IntRange, default_value=None, description=u'范围检索 产品有效时间 单位为天'),
                start_time=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                admin_slug=g.Argument(g.String, default_value='', description=u'admin_slug 检索 用于 超管检索全部数据'),
                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_xdyAdminProduct(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = XdyAdminProduct.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(XdyAdminProduct, SortableField))
                return XdyAdminProductPagination(rows=rows, pageInfo=pageInfo)

            xdyOrder = Field(lambda :XdyOrderPagination, description=u'客户相关订单 分页查询',
                num=g.Argument(g.Int, default_value=20, description=u'每页数量'),
                page=g.Argument(g.Int, default_value=1, description=u'页数'),

                order_id=g.Argument(g.Int, default_value=0, description=u'订单 id'),
                admin_id=g.Argument(g.Int, default_value=0, description=u'订单 所属客户id'),
                operator_id=g.Argument(g.Int, default_value=0, description=u'操作者admin_id'),

                product_id=g.Argument(g.Int, default_value=0, description=u'产品id'),
                order_type=g.Argument(g.String, default_value='', description=u'订单类型 product 购买产品   recharge 充值  overranging 超出额度'),

                product_type=g.Argument(g.String, default_value='', description=u'产品类型 package 套餐   默认为套餐'),

                order_money=g.Argument(FloatRange, default_value=None, description=u'范围检索 订单金额  大于0表示充值  小于0表示扣除'),

                m_state=g.Argument(g.Int, default_value=0, description=u'状态  1 处理中  2 已完成  8取消 9删除'),

                state=g.Argument(StateEnum, default_value=0, description=u'检索 状态枚举值'),
                admin_slug=g.Argument(g.String, default_value='', description=u'admin_slug 检索 用于 超管检索全部数据'),
                created_at=g.Argument(DateRange, default_value=None, description=u'范围检索 创建时间'),
                updated_at=g.Argument(DateRange, default_value=None, description=u'范围检索 记录更新时间'),
                sortOption=g.Argument(SortOption, default_value=None, description=u'排序依据 为空将使用默认排序')
            )
            def resolve_xdyOrder(self, args, context, info):
                page = args.get('page', 1)
                num = args.get('num', 20)
                query = XdyOrder.query.filter_by() # TODO 增加检索条件  判断当前用户类型 设置可见性
                rows = query.skip((page - 1)*num).limit(num).all()
                total = query.count()
                pageInfo = PageInfo.buildPageInfo(total=total, num=num, page=page, sortOption=args.get('sortOption', None), allowSortField=mask_keys(XdyOrder, SortableField))
                return XdyOrderPagination(rows=rows, pageInfo=pageInfo)


        return AdminUser


class AdminUserPagination(g.ObjectType):
    u''' AdminUser 分页查询 列表'''
    rows = List(AdminUser, description=u'当前查询 AdminUser 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


from player_base import (
    PlayerBase,
    PlayerBasePagination,
    PlayerMps,
    PlayerAodian,
    PlayerAli,
    PlayerUnion,
)

from stream_base import (
    StreamBase,
    StreamBasePagination,
    StreamMcs,
    StreamVod,
    StreamVodPagination,
    StreamPull,
    StreamUnion,
)

from admin_base import AdminUser

from admin_other import (
    AdminCountly,
    AdminAccessControl,
    AdminRecord,
    AdminRecordPagination,
)

from daily_base import (
    DailyRoomRunning,
    DailyRoomRunningPagination,
    DailyRoomRunningDms,
    DailyRoomRunningDmsPagination,
    DailyViewCount,
    DailyViewCountPagination,
)

from room_base import (
    LiveRoom,
    LiveRoomPagination,
)

from room_other import (
    RoomContentConfig,
    RoomContentConfigPagination,
    RoomPublishRecord,
    RoomPublishRecordPagination,
)

from running_base import (
    RoomRunning,
    RoomRunningPagination,
)

from site_base import (
    SiteMgrUser,
    SiteMgrUserPagination,
    SiteOpRecord,
    ArticleClassify,
    ArticleList,
    ArticleListPagination,
    HelpDocList,
    SendLog,
    SendLogPagination,
)

from viewrecord_base import (
    RoomViewRecord,
    RoomViewRecordPagination,
)

from xdy_base import (
    XdyProduct,
    XdyProductPagination,
    XdyOrder,
    XdyOrderPagination,
    XdyAdminProduct,
    XdyAdminProductPagination,
)
