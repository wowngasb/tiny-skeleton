# coding: utf-8
from __future__ import unicode_literals, division, print_function, absolute_import
import argparse
import codecs
import sys

from sqlalchemy.engine import create_engine
from sqlalchemy.schema import MetaData

from codegen import CodeGenerator

def main():
    parser = argparse.ArgumentParser(description='Generates SQLAlchemy model code from an existing database.')
    parser.add_argument('url', nargs='?', help='SQLAlchemy url to the database')
    parser.add_argument('--version', action='store_true', help="print the version number and exit")
    parser.add_argument('--schema', help='load tables from an alternate schema')
    parser.add_argument('--tables', help='tables to process (comma-separated, default: all)')
    parser.add_argument('--noviews', action='store_true', help="ignore views")
    parser.add_argument('--noindexes', action='store_true', help='ignore indexes')
    parser.add_argument('--noconstraints', action='store_true', help='ignore constraints')
    parser.add_argument('--nojoined', action='store_true', help="don't autodetect joined table inheritance")
    parser.add_argument('--noinflect', action='store_true', help="don't try to convert tables names to singular form")
    parser.add_argument('--noclasses', action='store_true', help="don't generate classes, only tables")
    parser.add_argument('--nocomments', action='store_true', help="don't add comments to columns")
    parser.add_argument('--outfile', help='file to write output to (default: stdout)')

    fix_args = None
    args = parser.parse_args(fix_args)

    if not args.url:
        print('You must supply a url\n', file=sys.stderr)
        parser.print_help()
        return

    engine = create_engine(args.url)
    metadata = MetaData(engine)
    tables = args.tables.split(',') if args.tables else None
    metadata.reflect(engine, args.schema, not args.noviews, tables)

    outfile = codecs.open(args.outfile, 'w', encoding='utf-8') if args.outfile else sys.stdout
    generator = CodeGenerator(metadata, args.noindexes, args.noconstraints, args.nojoined, args.noinflect,
                              args.noclasses)

    db_comments_map = {} if args.nocomments else get_db_doc(engine, metadata)
    generator.render(outfile, db_comments_map)


def get_db_doc(engine, metadata):
    stricmp = lambda s1, s2: s1.lower()==s2.lower()
    db_name = engine.url.database
    db_comments_map = {}
    def _doc(col):
        field, comment = col['Field'], col['Comment'].strip()
        if not comment:
            comment = u'自增主键' if stricmp(col['Extra'], 'auto_increment') and stricmp(col['Key'], 'PRI') else \
                u'创建时间' if stricmp(field, 'create_time') else \
                u'更新时间' if stricmp(field, 'uptime') else u''
        return comment

    for table in metadata.tables.values():
        if stricmp(table.name, 'migrate_version'):
            continue
        sql_str = 'show full columns from %s.%s' % (db_name, table.name)
        tmp_rst = engine.execute(sql_str)
        db_comments_map[table.name] = {col['Field']: _doc(col) for col in tmp_rst}
    return db_comments_map

if __name__ == "__main__":
    main()