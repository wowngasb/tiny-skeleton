#-*- coding: utf-8 -*-
import os
import json
import requests

from pykl.tiny.grapheneinfo import BuildType

from app import schema, tables
from pykl.tiny.codegen import BuildPHP, BuildGO, BuildJAVA, BuildAliApiPHP

def main():
    _output = lambda *tag: os.path.join(os.getcwd(), 'output', *tag)
    _app_output = lambda *s: os.path.join( os.path.dirname(os.path.dirname(os.getcwd())), 'app', *s)

    BuildPHP(schema=schema, tables=tables, output=_output('phpsrc'), graphql=dict(output=_app_output('api')), model_=dict(output=_app_output())).build()
    BuildGO(schema=schema, tables=tables, output=_output('gosrc')).build()
    BuildJAVA(schema=schema, tables=tables, output=_output('javasrc')).build()

    config = load_ali_api_config(os.path.join(os.getcwd(), 'aliapi.json'))
    BuildAliApiPHP(config, output=_output('phpsrc')).build()

    print "======== END ========="


def load_ali_api_config(in_file):
    tmp_file = in_file + '.tmp'
    if os.path.isfile(tmp_file):
        with open(tmp_file, 'r') as rf:
            return json.load(rf)

    retMap = {}
    if not os.path.isfile(in_file):
        return retMap

    with open(in_file, 'r') as rf:
        jsonArgs = json.load(rf)
        productList = [i for i in jsonArgs.get('productList', []) \
            if i.get('name', '') and i.get('name', '') != 'api-marketplace' ]
        for product in productList:
            name = product.get('name', '')
            apiMap = _load_api_map_by_name(name, product.get('version', ''))
            if apiMap:
                product['api'] = apiMap
                retMap[name] = product

    with open(tmp_file, 'w') as wf:
        json.dump(retMap, wf, indent=4)

    return retMap

def _load_api_map_by_name(name, ver=''):
    url = 'https://api.aliyun.com/api/v1/describeApiList.json?productName=%s&productVersion=%s' % (name, ver)

    rep = requests.get(url, headers={
        'cookie': 'cna=NtLyEgNEhCYCAX16M5MT5fA+; cnz=6uvyEkKYLUECAX16M5MsBFXB; UM_distinctid=16135d9626f6-0f8dba24eb43b1-5d4e211f-1fa400-16135d96271f6; aliyun_lang=zh; channel=d2FlpAaV02et5rj7vfARTaRxDrih2m8a; currentRegionId=cn-hangzhou; ping_test=true; login_aliyunid="58wen****"; login_aliyunid_ticket=V*0*Cm58slMT1tJw3_0$$kDxEpqBFUjC1k7zh5CSsr_9YkrD_50oa4Ppz3cUIFvof_BNpwU_TOTNChZBoeM1KJexdfb9zhYnsN5Zos6qISCrRt7mGxbigG2Cd4fWaCmBZHIzsgdZq64XXWQgyKFeufpv0; login_aliyunid_csrf=_csrf_tk_1928724812740873; login_aliyunid_pk=1127868394972717; hssid=1M2QTQtNxJrs7Wr_vG0T7WQ1; hsite=6; aliyun_country=CN; aliyun_site=CN; _bl_uid=eyjkngwphe7mp64R7ygvmtsaem3z; aliyun_choice=CN; _ga=GA1.2.1598696476.1517026797; _gid=GA1.2.168234188.1524446277; koa.sid=90G42zmSUDk3nK5tYEPXvqrl_pYXnDH5; koa.sid.sig=Qp4CrHMPVcsPWpUVhQs6TwntPws; isg=BICAfrMjzqFgt7IsV-1TIFVjUQ6SoXif5KqrKPoR0xsudSCfohk0Y1aEidu1RRyr'
    })
    if rep.ok:
        tmp = rep.json()
        return tmp.get('data', {}).get('api', {})

    return {}

if __name__ == '__main__':
    main()

