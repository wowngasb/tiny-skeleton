# coding: utf-8
import os

basedir = os.path.abspath(os.path.dirname(__file__))

db_config = {
    'driver': 'mysql',
    'host': '127.0.0.1',
    'port': 3306,
    'database': 'test',
    'username': 'root',
    'password': '',
    'charset': 'utf8',
}


SQLALCHEMY_TRACK_MODIFICATIONS = True

SQLALCHEMY_DATABASE_URI = "{driver}://{username}:{password}@{host}:{port}/{database}?charset={charset}".format(**db_config)

SQLALCHEMY_MIGRATE_REPO = os.path.join(basedir, 'db_repository')