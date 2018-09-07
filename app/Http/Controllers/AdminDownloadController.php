<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/6/22 0022
 * Time: 0:09
 */

namespace app\Http\Controllers;


use app\AdminController;
use app\api\DataAnalysis;
use app\api\GraphQL\AdminUser;
use app\api\GraphQL\Query;
use app\api\GraphQL_\Enum\LiveStateEnum;
use app\api\GraphQL_\Enum\PlayerTypeEnum;
use app\api\GraphQL_\Enum\RecordStateEnum;
use app\api\GraphQL_\Enum\StateEnum;
use app\api\GraphQL_\Enum\StreamTypeEnum;
use app\api\RoomMgr;
use app\api\RoomRecord;
use app\CashController;
use app\Exception\Error;
use app\Util;

class AdminDownloadController extends AdminController
{
    protected static $detail_log = false;

    public function homeAnalysisDataCsv($admin_id, $room_id = 0, $startTime = '', $endTime = '', $useDmsData = 0, $file = '', $sheet = '')
    {
        $api = DataAnalysis::_createFromController($this);
        $ret = [];
        // $api->homeAnalysisData($admin_id, $room_id, $startTime, $endTime, $useDmsData);
        $ret = array_merge($ret, $api->homeAnalysisDataIp($admin_id, $room_id, $startTime, $endTime, $useDmsData));
        $ret = array_merge($ret, $api->homeAnalysisDataSumRange($admin_id, $room_id, $startTime, $endTime, $useDmsData));
        $ret = array_merge($ret, $api->homeAnalysisDataViewAgent($admin_id, $room_id, $startTime, $endTime, $useDmsData));
        $ret = array_merge($ret, $api->homeAnalysisDataViewCount($admin_id, $room_id, $startTime, $endTime));
        $ret = array_merge($ret, $api->homeAnalysisDataViewInterval($admin_id, $room_id, $startTime, $endTime, $useDmsData));
        $ret = array_merge($ret, $api->homeAnalysisDataVisitor($admin_id, $room_id, $startTime, $endTime, $useDmsData));
        $ret = array_merge($ret, $api->homeAnalysisPublishInterval($admin_id, $room_id, $startTime, $endTime));

        $file = !empty($file) ? trim($file) : '数据分析';
        $sheet = !empty($sheet) ? trim($sheet) : $file;
        $file = "{$file}_" . date('ymd_His');

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($ret, __METHOD__, __CLASS__, __LINE__);

        return $this->_exportHomeExcel($file, $sheet, $ret);
    }

    private function _exportHomeExcel($file, $sheet, $ret)
    {
        if (!Util::stri_endwith($file, '.xlsx')) {
            $file = "{$file}.xlsx";
        }
        $this->_addDownloadHeader($file);
        $this->getResponse()->sendHeader();

        include PLUGIN_PATH . 'excel/PHPExcel.php';
        include PLUGIN_PATH . 'excel/PHPExcel/Writer/Excel5.php';
        $phpExcel = new \PHPExcel();
        $phpExcel->getActiveSheet()->setTitle($file);
        $phpExcel->getActiveSheet()->setCellValue('A1', '点击次数')
            ->setCellValue('A2', $ret['sumViewCount'])
            ->setCellValue('B1', '观看总长(分钟)')
            ->setCellValue('B2', $ret['sumViewInterval'])
            ->setCellValue('C1', '观看人数')
            ->setCellValue('C2', $ret['sumUniqueVisitor'])
            ->setCellValue('D1', 'IP数')
            ->setCellValue('D2', $ret['sumIpDistribution'])
            ->setCellValue('E1', '省份')
            ->setCellValue('F1', 'IP数(个)')
            ->setCellValue('G1', $ret['map_view_sum_data']['tag_list'][0])
            ->setCellValue('G2', $ret['map_view_sum_data']['val_list'][0])
            ->setCellValue('H1', $ret['map_view_sum_data']['tag_list'][1])
            ->setCellValue('H2', $ret['map_view_sum_data']['val_list'][1])
            ->setCellValue('I1', $ret['map_view_sum_data']['tag_list'][2])
            ->setCellValue('I2', $ret['map_view_sum_data']['val_list'][2])
            ->setCellValue('J1', $ret['map_view_sum_data']['tag_list'][3])
            ->setCellValue('J2', $ret['map_view_sum_data']['val_list'][3])
            ->setCellValue('K1', $ret['map_view_sum_data']['tag_list'][4])
            ->setCellValue('K2', $ret['map_view_sum_data']['val_list'][4])
            ->setCellValue('L1', $ret['map_view_sum_data']['tag_list'][5])
            ->setCellValue('L2', $ret['map_view_sum_data']['val_list'][5])
            ->setCellValue('M1', $ret['map_view_sum_data']['tag_list'][6])
            ->setCellValue('M2', $ret['map_view_sum_data']['val_list'][6])
            ->setCellValue('N1', 'PC访问')
            ->setCellValue('N2', $ret['map_agent_data']['WEB'])
            ->setCellValue('O1', '手机访问')
            ->setCellValue('O2', $ret['map_agent_data']['WAP'])
            ->setCellValue('P1', '统计访问')
            ->setCellValue('P2', $ret['map_agent_data']['sum_total']);

        $phpExcel->getActiveSheet()->setCellValue('A2', $ret['sumViewCount']);

        $i = 2;
        $series_lists = $ret['map_series_data'];
        foreach ($series_lists as $series_list) {
            $phpExcel->getActiveSheet()->setCellValue('E' . $i, $series_list['name'])
                ->setCellValue('F' . $i, $series_list['value']);
            $i++;
        }

        $obj_Writer = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $phpExcel->setActiveSheetIndex(0);
        $objActSheet = $phpExcel->getActiveSheet();
        $objActSheet->setTitle("{$sheet}");

        $obj_Writer->save('php://output');//输出
        return $this->getResponse()->end();
    }

    public function roomVodDataCsv($room_id, $file = '', $sheet = '', $dtag = '')
    {
        if (empty($room_id)) {
            throw new Error('参数错误');
        }
        $file = !empty($file) ? trim($file) : '数据检索';
        $sheet = !empty($sheet) ? trim($sheet) : $file;
        $file = "{$file}_" . date('ymd_His');

        $api = RoomMgr::_createFromController($this);
        $dataRst = $api->getVodByRoomId($room_id);
        $callback = null;
        $callback = function ($item) {
            return [
                '视频流ID' . self::colWidth(10) => $item['stream_id'],
                '视频ID' . self::colWidth(40) => $item['RecordId_m3u8'],
                '开始时间' . self::colWidth(20) => $item['StartTime'],
                '结束时间' . self::colWidth(20) => $item['EndTime'],
                '视频时长' . self::colWidth(15) => Util::interval2str($item['Duration']),
                'm3u8地址' . self::colWidth(110) => $item['RecordUrl_m3u8'],
                'mp4地址' . self::colWidth(110) => $item['RecordUrl_mp4'],
                '视频封面' . self::colWidth(110) => $item['Snapshot'],
            ];
        };

        if (empty($callback)) {
            throw new Error('参数错误');
        }

        $ret = Util::v($dataRst, 'groupList', []);

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($ret, __METHOD__, __CLASS__, __LINE__);


        return $this->_exportDownload($dtag, $ret, "{$file}_" . date('Y-m-d_His'), $sheet, $callback);
    }

    public static function _autoFixGraphQLArgs($variables = '')
    {
        $variables = !empty($variables) ? json_decode($variables, true) : [];

        if (isset($variables['state'])) {
            $variables['state'] = Util::v(StateEnum::ALL_ENUM_MAP, $variables['state'], 0);
        }
        if (isset($variables['live_state'])) {
            $variables['live_state'] = Util::v(LiveStateEnum::ALL_ENUM_MAP, $variables['live_state'], 0);
        }
        if (isset($variables['player_type'])) {
            $variables['player_type'] = Util::v(PlayerTypeEnum::ALL_ENUM_MAP, $variables['player_type'], '');
        }
        if (isset($variables['stream_type'])) {
            $variables['stream_type'] = Util::v(StreamTypeEnum::ALL_ENUM_MAP, $variables['stream_type'], '');
        }
        if (isset($variables['record_state'])) {
            $variables['record_state'] = Util::v(RecordStateEnum::ALL_ENUM_MAP, $variables['record_state'], 0);
        }
        return $variables;
    }

    public function csvDownload($file = '', $sheet = '', $dtag = '', $operationName = '', $query = '', $updateDeps = '', $variables = '')
    {
        $file = !empty($file) ? trim($file) : '数据检索';
        $sheet = !empty($sheet) ? trim($sheet) : $file;
        $file = "{$file}_" . date('ymd_His');

        Query::$max_page_num = Query::$max_csv_num;

        $curAdmin = $this->auth()->user();
        $updateDeps = !empty($updateDeps) ? explode('.', $updateDeps) : [];
        $variables = self::_autoFixGraphQLArgs($variables);

        if (empty($operationName) || empty($query) || empty($updateDeps)) {
            throw new Error('参数错误');
        }

        $api = new AdminUser();
        $callback = null;
        $dataRst = [];
        switch ($operationName) {
            case 'roomRunningSelect' :
                $dataRst = $api->roomRunning($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        'id' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['id'],
                        '频道' . self::colWidth(25) => $item['room']['room_title'],
                        '频道id' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['room_id'],
                        '客户' . self::colWidth(20) => $item['admin']['title'],
                        '客户ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin_id'],
                        '并发' . self::colWidth(10) => $item['num'],
                        '时间' . self::colWidth(20) => RoomRecord::_buildDateStrByTimer($item['timer_count'], $item['timer_type']),
                        '域名' . self::colWidth(20) => $item['ref_host'],
                    ];
                };
                break;
            case 'agentRoomRunningSelect' :
                $dataRst = $api->roomRunning($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        'id' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['id'],
                        '客户' . self::colWidth(20) => $item['admin']['title'],
                        '客户ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin_id'],
                        '代理' . self::colWidth(10) => $item['agent']['title'],
                        '代理ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['agent_id'],
                        '并发' . self::colWidth(10) => $item['num'],
                        '时间' . self::colWidth(20) => RoomRecord::_buildDateStrByTimer($item['timer_count'], $item['timer_type']),
                    ];
                };
                break;
            case 'roomListSelect' :
                $dataRst = $api->roomList($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        '频道ID' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['room_id'],
                        '直播状态' . self::colWidth(13) => $item['live_state'] == LiveStateEnum::START_VALUE ? '直播中' : '',
                        '频道名称' . self::colWidth(25) => $item['room_title'],
                        '所属代理' . self::colWidth(20) => $item['admin']['agent']['title'],
                        '代理ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin']['agent']['admin_id'],
                        '所属客户' . self::colWidth(20) => $item['admin']['title'],
                        '客户ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin']['admin_id'],
                        '状态' . self::colWidth(10) => Util::v(CashController::STATE_TYPE, $item['state'], ''),
                        '直播账号' . self::colWidth(20) => $item['stream']['mcs_account'],
                        '直播密码' . self::colWidth(35) => $item['stream']['mcs_password'],
                        'stream' . self::colWidth(45) => $item['stream']['mcs_stream'],
                        '并发限制' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewlimit'],
                        '当前并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_now'],
                        '最高并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_max'],
                        '最高并发时刻' . self::colWidth(20) => $item['viewer_max_at'],
                        '累计点击' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_count'],
                        '创建时间' . self::colWidth(20) => $item['created_at'],
                        '更新时间' . self::colWidth(20) => $item['updated_at'],
                    ];
                };
                break;
            case 'roomViewRecordSelect' :
                $dataRst = $api->roomViewRecord($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        'id' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['id'],
                        '用户ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['user_id'],
                        '所属客户' . self::colWidth(20) => $item['admin']['title'],
                        '客户ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin']['admin_id'],
                        '所属频道' . self::colWidth(30) => $item['room']['room_title'],
                        '频道id' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['room']['room_id'],
                        '进入时间' . self::colWidth(20) => $item['in_time'],
                        '退出时间' . self::colWidth(20) => $item['out_time'],
                        '观看时长' . self::colWidth(16) => Util::interval2str($item['interval_time']),
                        '状态' . self::colWidth(10) => Util::v(CashController::RECORD_STATE, $item['record_state'], ''),
                        '设备' . self::colWidth(15) => $item['agent'] == 'WAP' ? '手机端' : 'PC端',
                        'IP' . self::colWidth(15) => $item['login_ip'],
                        '地域' . self::colWidth(25) => $item['login_ip_addr'],
                        '来源域名' . self::colWidth(15) => $item['ref_host'],
                        '来源网址' . self::colWidth(20) => $item['ref_url'],
                        '创建时间' . self::colWidth(20) => $item['created_at'],
                        '更新时间' . self::colWidth(20) => $item['updated_at'],
                    ];
                };
                break;
            case 'subListSelect' :
                $dataRst = $api->subList($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        '子账号ID' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['admin_id'],
                        '名称' . self::colWidth(15) => $item['title'],
                        '备注' . self::colWidth(20) => $item['admin_note'],
                        '所属代理' . self::colWidth(20) => $item['agent']['title'],
                        '代理ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['agent']['admin_id'],
                        '所属客户' . self::colWidth(20) => $item['parent']['title'],
                        '客户ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['parent']['admin_id'],
                        '账号' . self::colWidth(15) => $item['name'],
                        '状态' . self::colWidth(10) => Util::v(CashController::STATE_TYPE, $item['state'], ''),
                        '当前并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_now'],
                        '最高并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_max'],
                        '最高并发时刻' . self::colWidth(20) => $item['viewer_max_at'],
                        '来源' . self::colWidth(15) => $item['register_from'],
                        '创建时间' . self::colWidth(20) => $item['created_at'],
                        '更新时间' . self::colWidth(20) => $item['updated_at'],
                    ];
                };
                break;
            case 'streamListSelect' :
                $dataRst = $api->streamList($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        '直播账号ID' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['stream_id'],
                        '直播状态' . self::colWidth(10) => $item['live_state'] == LiveStateEnum::START_VALUE ? '直播中' : '未开播',
                        '账号' . self::colWidth(15) => $item['mcs_account'],
                        '密码' . self::colWidth(15) => $item['mcs_password'],
                        '账号名称' . self::colWidth(30) => $item['stream_name'],
                        '所属代理' . self::colWidth(20) => $item['admin']['agent']['title'],
                        '代理ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin']['agent']['admin_id'],
                        '所属客户' . self::colWidth(20) => $item['admin']['title'],
                        '客户ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin']['admin_id'],
                        '状态' . self::colWidth(10) => Util::v(CashController::STATE_TYPE, $item['state'], '-'),
                        'stream' . self::colWidth(40) => $item['mcs_stream'],
                        '创建时间' . self::colWidth(20) => $item['created_at'],
                        '更新时间' . self::colWidth(20) => $item['updated_at'],
                    ];
                };
                break;
            case 'siteMgrListSelect' :
                $dataRst = (new Query())->siteMgrList([], $variables, $this);
                $callback = function ($item) {
                    return [
                        'id' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['mgr_id'],
                        '名称' . self::colWidth(15) => $item['title'],
                        '帐号' . self::colWidth(15) => $item['name'],
                        '状态' . self::colWidth(10) => Util::v(CashController::STATE_TYPE, $item['state'], '-'),
                        '类型' . self::colWidth(10) => Util::v(CashController::MGR_SLUG_TYPE, $item['mgr_slug'], '-'),
                        '上次登录IP' . self::colWidth(15) => $item['login_ip'],
                        '登录地域' . self::colWidth(20) => $item['login_location'],
                        '登录次数' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['login_count'],
                        '上次登陆时间' . self::colWidth(20) => $item['login_time'],
                        '邮箱' . self::colWidth(10) => $item['email'],
                        '手机' . self::colWidth(10) => $item['email'],
                        '创建时间' . self::colWidth(20) => $item['created_at'],
                        '更新时间' . self::colWidth(20) => $item['updated_at'],
                    ];
                };
                break;
            case 'dailyViewCountSelect' :
                $dataRst = $api->dailyViewCount($curAdmin, $variables, $this);
                $callback = function ($item) use ($variables) {
                    $data_type = isset($variables['data_type']) ? strval($variables['data_type']) : "room";    //  String  数据类型 支持 room parent
                    $data_type = in_array($data_type, ['room', 'parent']) ? $data_type : 'room';

                    return [
                            'id' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['id'],
                            '日期' . self::colWidth(15) => date('Y-m-d', Util::intday2time($item['per_day'])),
                        ] + ($data_type == 'parent' ? [] : [
                            '频道' . self::colWidth(30) => $item['room']['room_title'],
                            '频道id' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['room']['room_id'],
                        ]) + [
                            '代理' . self::colWidth(20) => $item['admin']['agent']['title'],
                            '代理ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin']['agent']['admin_id'],
                            '客户' . self::colWidth(20) => $item['admin']['title'],
                            '客户ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['admin']['admin_id'],
                            '最高并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['num_max'],
                            '最高并发时刻' . self::colWidth(20) => $item['num_max_time'],
                            '用户总数' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['total_unique_user'],
                            'IP总数' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['total_ip'],
                            '点击数' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['view_count'],
                            '观看总时长' . self::colWidth(12) => Util::interval2str($item['total_interval_time']),
                            '观看记录数' . self::colWidth(12) . self::EXCEL_TYPE_NUMERIC => $item['total_view_record'],
                            '创建时间' . self::colWidth(20) => $item['created_at'],
                            '更新时间' . self::colWidth(20) => $item['updated_at'],
                        ];
                };
                break;
            case 'agentListSelect' :
                $dataRst = $api->agentList($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        '代理ID' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['admin_id'],
                        '名称' . self::colWidth(15) => $item['title'],
                        '备注' . self::colWidth(15) => $item['admin_note'],
                        '账号' . self::colWidth(15) => $item['name'],
                        '状态' . self::colWidth(10) => Util::v(CashController::STATE_TYPE, $item['state'], '-'),
                        '硬性上限' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['vlimit_online_num'],
                        '客户数量限制' . self::colWidth(15) . self::EXCEL_TYPE_NUMERIC => $item['nlimit_count_parent'],
                        '单个客户并发限制' . self::colWidth(18) . self::EXCEL_TYPE_NUMERIC => $item['vlimit_per_parent'],
                        '单个频道并发限制' . self::colWidth(18) . self::EXCEL_TYPE_NUMERIC => $item['vlimit_per_room'],
                        '客户数量' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['parent_num'],
                        '频道数量' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['room_num'],
                        '当前并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_now'],
                        '最高并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_max'],
                        '最高并发时刻' . self::colWidth(20) => $item['viewer_max_at'],
                        '视频流vhost' . self::colWidth(20) => $item['mcs_vhost'],
                        'SLUG' . self::colWidth(10) => $item['admin_slug'],
                        '后台CNAME' . self::colWidth(20) => $item['cname_host'],
                        '来源' . self::colWidth(10) => $item['register_from'],
                        '有效期' . self::colWidth(20) => $item['expiration_date'],
                        '邮箱' . self::colWidth(10) => $item['email'],
                        '手机' . self::colWidth(10) => $item['cellphone'],
                        '公司' . self::colWidth(15) => $item['company'],
                        '行业' . self::colWidth(15) => $item['industry'],
                        '上次登录时间' . self::colWidth(20) => $item['login_time'],
                        '登录IP' . self::colWidth(15) => $item['login_ip'],
                        '登录地域' . self::colWidth(20) => $item['login_location'],
                        '创建时间' . self::colWidth(20) => $item['created_at'],
                        '更新时间' . self::colWidth(20) => $item['updated_at'],
                    ];
                };
                break;
            case 'parentListSelect' :
                $dataRst = $api->parentList($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        '客户ID' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['admin_id'],
                        '名称' . self::colWidth(15) => $item['title'],
                        '备注' . self::colWidth(20) => $item['admin_note'],
                        '账号' . self::colWidth(15) => $item['name'],
                        '所属代理' . self::colWidth(20) => $item['agent']['title'],
                        '代理ID' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['agent']['admin_id'],
                        '状态' . self::colWidth(10) => Util::v(CashController::STATE_TYPE, $item['state'], '-'),
                        '硬性上限' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['vlimit_online_num'],
                        '并发限制' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['vlimit_all_room'],
                        '频道限制' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['nlimit_count_room'],
                        '频道数量' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['room_num'],
                        '当前并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_now'],
                        '最高并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['viewer_max'],
                        '最高并发时刻' . self::colWidth(20) => $item['viewer_max_at'],
                        '来源' . self::colWidth(10) => $item['register_from'],
                        'SLUG' . self::colWidth(12) => $item['admin_slug'],
                        '视频流vhost' . self::colWidth(20) => $item['mcs_vhost'],
                        '有效期' . self::colWidth(20) => $item['expiration_date'],
                        '邮箱' . self::colWidth(10) => $item['email'],
                        '手机' . self::colWidth(12) => $item['cellphone'],
                        '公司' . self::colWidth(15) => $item['company'],
                        '行业' . self::colWidth(15) => $item['industry'],
                        '上次登录时间' . self::colWidth(20) => $item['login_time'],
                        '登录IP' . self::colWidth(15) => $item['login_ip'],
                        '登录地域' . self::colWidth(22) => $item['login_location'],
                        '创建时间' . self::colWidth(20) => $item['created_at'],
                        '更新时间' . self::colWidth(20) => $item['updated_at'],
                    ];
                };
                break;
            case 'dailyAdminRunningSelect' :
                $dataRst = $api->dailyAdminRunning($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        'id' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['id'],
                        '日期' . self::colWidth(10) => date('Y-m-d', Util::intday2time($item['per_day'])),
                        '峰值并发' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['num_max'],
                        '峰值时刻' . self::colWidth(20) => $item['num_max_time'],
                        '超出部分' . self::colWidth(15) . self::EXCEL_TYPE_NUMERIC => $item['num_max'] > $item['package_num'] ? $item['num_max'] - $item['package_num'] : '0',
                        '超出部分价格' . self::colWidth(15) . self::EXCEL_TYPE_NUMERIC => number_format($item['over_price'], 2),
                    ];
                };
                break;
            case 'xdyAdminProductSelect' :
                $dataRst = $api->xdyAdminProduct($curAdmin, $variables, $this);
                $callback = function ($item) {
                    $val = json_decode($item['product_value'], true);
                    return [
                        'id' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['id'],
                        '套餐名称' . self::colWidth(20) => Util::v($val, 'product_title', ''),
                        '套餐类型' . self::colWidth(10) => Util::v(CashController::PACKAGE_LIMIT_TYPE, $val['limit_type'], '-'),
                        '套餐额度' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => Util::v($val, 'limit_value', ''),
                        '套餐状态' . self::colWidth(10) => Util::v(CashController::STATE_TYPE, $item['state'], '-'),
                        '有效期' . self::colWidth(10) . self::EXCEL_TYPE_NUMERIC => $item['expired_days'],
                        '起始日期' . self::colWidth(15) => date('Y-m-d', strtotime($item['start_time'])),
                        '截止日期' . self::colWidth(15) => date('Y-m-d', strtotime($item['start_time']) + $item['expired_days'] * 24 * 3600),
                    ];
                };
                break;
            case 'xdyOrderSelect' :
                $dataRst = $api->xdyOrder($curAdmin, $variables, $this);
                $callback = function ($item) {
                    return [
                        '订单ID' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['order_id'],
                        '客户ID' . self::colWidth(8) . self::EXCEL_TYPE_NUMERIC => $item['admin']['admin_id'],
                        '名称' . self::colWidth(15) => $item['admin']['title'],
                        '备注' . self::colWidth(20) => $item['admin']['admin_note'],
                        '订单类型' . self::colWidth(10) => Util::v(CashController::ORDER_TYPE, $item['order_type'], '-'),
                        '订单金额' . self::colWidth(15) => $item['order_money'] >= 0 ? '充值' : '扣除' . number_format($item['order_money'], 2) . '元',
                        '订单信息' . self::colWidth(80) => $item['order_note'],
                        '余额' . self::colWidth(15) => number_format($item['account_balance_after'], 2) . '元',
                        '更新时间' . self::colWidth(20) => $item['updated_at'],
                    ];
                };
                break;
            default :
                break;
        }

        if (empty($callback)) {
            throw new Error('参数错误');
        }

        $ret = Util::v($dataRst, 'rows', []);

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($ret, __METHOD__, __CLASS__, __LINE__);

        return $this->_exportDownload($dtag, $ret, $file, $sheet, $callback);
    }

    private function _exportDownload($dtag, $data, $file, $sheet, $func)
    {
        if ($dtag == 'csv-download') {
            return $this->exportCsv($file, $data, $func);
        } else {
            return $this->exportExcel($file, $sheet, $data, $func);
        }
    }
}