# coding: utf-8
from base_type import *
from app import db, app
Base = db.Model

##############################################################
####################		XdyProduct		##################
##############################################################

class XdyProduct(Base):
    u'''自营账号下  套餐 导播台 产品表  超级管理员创建产品之后才可以购买'''
    __tablename__ = 'xdy_product'

    product_id = Column(Integer, primary_key=True, doc=u"""产品 自增id""", info=SortableField)
    product_title = Column(String(32), nullable=False, doc=u"""产品标题""", info=CustomField | SortableField)
    product_price = Column(Numeric(20, 2), nullable=False, doc=u"""产品 单价""", info=CustomField | SortableField)

    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""为0表示所有 客户可购买  大于0标识 指定admin_id专属""", info=CustomField | SortableField)

    product_num = Column(Integer, nullable=False, server_default=text("'1'"), doc=u"""产品库存 默认为 1  表示产品可购买的数量 为0后无法购买""", info=CustomField | SortableField)

    product_type = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""产品类型 package 套餐   默认为套餐""", info=CustomField | SortableField)
    product_note = Column(Text, doc=u"""产品信息备注  用于添加备注信息""", info=CustomField)

    sell_count = Column(Integer, nullable=False, server_default=text("'0'"), doc=u"""产品销售数量""", info=CustomField | SortableField)
    limit_type = Column(String(32), nullable=False, index=True, doc=u"""限额类型  支持  onlinenum  并发人数  """, info=CustomField | SortableField)
    limit_value = Column(Integer, doc=u"""限额数量  限制类型为人数（个） """, info=CustomField | SortableField)
    expired_days = Column(Integer, nullable=False, doc=u"""产品有效时间 单位为天""")

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)


    @classmethod
    def info(cls):
        class XdyProduct(SQLAlchemyObjectType):
            class Meta:
                model = cls

            applyAdmin = Field(lambda :AdminUser, description=u'对应 用户 信息')
            def resolve_applyAdmin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None


        return XdyProduct

class XdyProductPagination(g.ObjectType):
    u''' XdyProduct 分页查询 列表'''
    rows = List(XdyProduct, description=u'当前查询 XdyProduct 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')

##############################################################
####################		XdyOrder		##################
##############################################################

class XdyOrder(Base):
    u'''自营账号下  客户 购买套餐 导播台 订单记录  每项记录为一个订单'''
    __tablename__ = 'xdy_order'

    order_id = Column(Integer, primary_key=True, doc=u"""自增id""", info=SortableField)

    admin_id = Column(Integer, nullable=False, index=True, doc=u"""订单对应的 管理员 admin_id""", info=CustomField | SortableField)
    operator_id = Column(Integer, nullable=False, index=True, doc=u"""订单的操作者 admin_id""", info=CustomField | SortableField)

    account_balance_after = Column(Numeric(20, 2), doc=u"""账号交易之后 余额""", info=CustomField | SortableField)
    account_balance_before = Column(Numeric(20, 2), doc=u"""账户交易之前 余额""", info=CustomField | SortableField)

    order_money = Column(Numeric(20, 2), nullable=False, index=True, doc=u"""订单金额  大于0表示充值  小于0表示扣除""", info=CustomField | SortableField)

    order_note = Column(String(128), nullable=False, server_default=text("''"), doc=u"""订单描述信息""", info=CustomField)
    order_type = Column(String(32), nullable=False, index=True, doc=u"""订单类型 product 购买产品   recharge 充值  overranging 超出额度""", info=CustomField)

    product_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""产品id 可以为 0 表示无对应产品""", info=CustomField | SortableField)
    product_type = Column(String(32), nullable=False, index=True, server_default=text("''"), doc=u"""产品类型 package 套餐  默认为套餐""", info=CustomField | SortableField)

    product_config = Column(Text, doc=u"""购买产品的配置信息 用于存储 产品的购买详细参数""", info=CustomField)
    product_value = Column(Text, doc=u"""产生此订单时 对应产品的快照信息 格式为json 可以为空""", info=CustomField)

    admin_email = Column(String(64), nullable=False, server_default=text("''"), doc=u"""客户邮箱""", info=CustomField | SortableField)
    admin_mobile = Column(String(32), nullable=False, server_default=text("''"), doc=u"""客户手机""", info=CustomField | SortableField)

    m_state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态  1 处理中  2 已完成  8取消 9删除""", info=CustomField | SortableField)
    error_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)

    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class XdyOrder(SQLAlchemyObjectType):
            class Meta:
                model = cls

            admin = Field(lambda :AdminUser, description=u'操作目标 用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

            opAdmin = Field(lambda :AdminUser, description=u'操作者 用户 信息')
            def resolve_opAdmin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.operator_id).first() \
                        if self.operator_id \
                            else None

            product = Field(lambda :XdyProduct, description=u'对应产品信息')
            def resolve_product(self, args, context, info):
                return XdyProduct.query.filter_by(product_id=self.product_id).first() \
                        if self.product_id \
                            else None

        return XdyOrder

class XdyOrderPagination(g.ObjectType):
    u''' XdyOrder 分页查询 列表'''
    rows = List(XdyOrder, description=u'当前查询 XdyOrder 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')



##############################################################
####################		XdyAdminProduct		##################
##############################################################

class XdyAdminProduct(Base):
    u'''自营账号下  客户 购买套餐 导播台 记录  每项记录为一个套餐 或者导播台'''
    __tablename__ = 'xdy_admin_product'

    id = Column(Integer, primary_key=True, doc=u"""主键""", info=SortableField)

    product_value = Column(Text, doc=u"""此产品的快照 格式为 json""", info=CustomField)
    product_config = Column(Text, doc=u"""购买产品的配置信息 用于存储 产品的购买详细参数""", info=CustomField)
    product_result = Column(Text, doc=u"""产品调用 默认结果 用于存储第三方返回的服务信息""", info=CustomField)

    product_id = Column(Integer, nullable=False, index=True, doc=u"""产品 id""", info=CustomField | SortableField)
    product_type = Column(String(32), nullable=False, server_default=text("''"), doc=u"""产品类型 package 套餐   默认为套餐""", info=CustomField | SortableField)
    order_id = Column(Integer, nullable=False, index=True, doc=u"""购买产品的订单号""", info=CustomField | SortableField)
    admin_id = Column(Integer, nullable=False, index=True, server_default=text("'0'"), doc=u"""购买者 admin_id""", info=CustomField | SortableField)
    start_time = Column(DateTime, nullable=False, index=True, doc=u"""产品开始计算时间""", info=CustomField | SortableField)
    expired_days = Column(Integer, nullable=False, doc=u"""产品有效时间 单位为天""", info=CustomField | SortableField)


    state = Column(SmallInteger, nullable=False, index=True, server_default=text("'0'"), doc=u"""状态 参见 StateEnum 枚举""", info=BitMask(CustomField | SortableField, StateEnum))
    last_msg = Column(String(128), nullable=False, server_default=text("''"), doc=u"""上次操作附加信息""", info=CustomField)
    deleted_at = Column(DateTime, nullable=False, server_default=text("'0000-00-00 00:00:00'"), doc=u"""删除时间""", info=SortableField)
    created_at = Column(DateTime, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP"), doc=u"""创建时间""", info=SortableField)
    updated_at = Column(TIMESTAMP, nullable=False, index=True, server_default=text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"), doc=u"""记录更新时间""", info=SortableField)

    @classmethod
    def info(cls):
        class XdyAdminProduct(SQLAlchemyObjectType):
            class Meta:
                model = cls

            admin = Field(lambda :AdminUser, description=u'操作目标 用户 信息')
            def resolve_admin(self, args, context, info):
                return AdminUser.query.filter_by(admin_id=self.admin_id).first() \
                        if self.admin_id \
                            else None

            order = Field(lambda :XdyOrder, description=u'对应订单信息')
            def resolve_order(self, args, context, info):
                return XdyOrder.query.filter_by(order_id=self.order_id).first() \
                        if self.order_id \
                            else None

            product = Field(lambda :XdyProduct, description=u'对应产品信息')
            def resolve_product(self, args, context, info):
                return XdyProduct.query.filter_by(product_id=self.product_id).first() \
                        if self.product_id \
                            else None

        return XdyAdminProduct


class XdyAdminProductPagination(g.ObjectType):
    u''' XdyAdminProduct 分页查询 列表'''
    rows = List(XdyAdminProduct, description=u'当前查询 XdyAdminProduct 列表')
    pageInfo = Field(PageInfo, description=u'分页信息')


from admin_base import AdminUser