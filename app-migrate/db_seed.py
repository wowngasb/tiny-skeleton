# coding: utf-8
import random, time, datetime, json

from app import db, models, md5key

def _save(tbl, items, filter_by=None, primary_key=''):
    all_num, save_num = len(items), 0
    print "\n", 'SAVE %r, len:%d, filter_by:%r, primary_key:%s' % (tbl, all_num, filter_by, primary_key)
    if filter_by is None and primary_key:
        def filter_by(item):
            return tbl.query.filter_by(**{primary_key: item[primary_key]}).first()

    for item in items:
        if filter_by and not filter_by(item):
            print '.',
            save_num += 1
            db.session.add( tbl(**item) )
        else:
            print 'x',
    db.session.commit()
    print "\n", 'COMMIT all:%d, save:%d' % (all_num, save_num)

'''
def _BasicRoom(idx):
    return {
        'room_id': idx,
        'room_title': 'live test %s' % (idx, ),
        'chat_topic': 'room_%s' % (idx, ),
        'dms_sub_key': 'sub_eae37e48dab5f305516d07788eaaea60',
        'dms_pub_key': 'pub_5bfb7a0ced7adb2ce454575747762679',
        'dms_s_key': 's_ceb80d29276f78653df081e5a9f0ac76',
        'aodian_uin': '13830',
        'lss_app': 'dyy_1736_133',
        'stream': 'a0c3d2dd3b4688f31da13991477980d9',
        'room_status': 1,
    }


BasicRoom_list = [_BasicRoom(idx) for idx in range(101, 106)]
_save(models.BasicRoom, BasicRoom_list, primary_key='room_id')
'''