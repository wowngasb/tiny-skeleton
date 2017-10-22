from sqlalchemy import *
from migrate import *


from migrate.changeset import schema
pre_meta = MetaData()
post_meta = MetaData()
basic_room = Table('basic_room', post_meta,
    Column('room_id', Integer, primary_key=True, nullable=False),
    Column('room_title', String(length=32), nullable=False),
    Column('chat_topic', String(length=32), nullable=False),
    Column('dms_sub_key', String(length=64), nullable=False),
    Column('dms_pub_key', String(length=64), nullable=False),
    Column('dms_s_key', String(length=64), nullable=False),
    Column('aodian_uin', Integer, nullable=False),
    Column('lss_app', String(length=32), nullable=False),
    Column('stream', String(length=32), nullable=False),
    Column('room_status', SmallInteger, nullable=False),
    Column('updated_at', TIMESTAMP, nullable=False),
    Column('created_at', DateTime, nullable=False),
)


def upgrade(migrate_engine):
    # Upgrade operations go here. Don't create your own engine; bind
    # migrate_engine to your metadata
    pre_meta.bind = migrate_engine
    post_meta.bind = migrate_engine
    post_meta.tables['basic_room'].columns['created_at'].create()
    post_meta.tables['basic_room'].columns['updated_at'].create()


def downgrade(migrate_engine):
    # Operations to reverse the above upgrade go here.
    pre_meta.bind = migrate_engine
    post_meta.bind = migrate_engine
    post_meta.tables['basic_room'].columns['created_at'].drop()
    post_meta.tables['basic_room'].columns['updated_at'].drop()
