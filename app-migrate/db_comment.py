# coding: utf-8
import sys
import os
from config import SQLALCHEMY_DATABASE_URI
from config import SQLALCHEMY_MIGRATE_REPO
from sqlalchemy.engine import create_engine

from app import models

from sqlalchemy.inspection import inspect as sqlalchemyinspect

def update_comment(engine, tablename, tabledoc, doc_map):
    dbname = engine.url.database
    tabledoc = tabledoc.strip().encode('utf-8') if tabledoc else ''
    tbl_str = '''ALTER TABLE `{dbname}`.`{tablename}` COMMENT "{tabledoc}" '''.format(
        dbname = dbname,
        tablename = tablename,
        tabledoc = tabledoc,
    )
    tabledoc and engine.execute(tbl_str)

    sch_db = 'information_schema'
    for col, doc in doc_map.items():
        doc = doc.strip().encode('utf-8') if doc else ''
        sql_str = '''UPDATE `{sch_db}`.`COLUMNS` `t` SET `t`.`column_comment`="{doc}" WHERE `t`.`TABLE_SCHEMA`="{dbname}" AND `t`.`table_name`="{tablename}" AND `t`.`COLUMN_NAME`="{col}" '''.format(
            sch_db = sch_db,
            dbname = dbname,
            tablename = tablename,
            col = col,
            doc = doc,
        )
        doc and engine.execute(sql_str)


def main():
    engine = create_engine(SQLALCHEMY_DATABASE_URI)
    tables = models.tables
    for _table in tables:
        table = sqlalchemyinspect(_table)
        doc_map = {k:getattr(v, 'doc', None) for k, v in table.columns.items()}
        tablename = _table.__tablename__
        tabledoc = _table.__doc__
        update_comment(engine, tablename, tabledoc, doc_map)

if __name__ == '__main__':
    main()
