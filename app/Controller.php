<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/24
 * Time: 17:21
 */


// view 文件 顶部注释

/** @var \app\App $app */
/** @var \app\Controller $ctrl */
/** @var \app\Request $request */
/** @var array $routeInfo */
/** @var string $webname */
/** @var string $webver */
/** @var string $cdn */

namespace app;

use app\api\Abstracts\AbstractApi;
use app\api\Abstracts\Api;
use app\Libs\AdminAuth;
use app\Model\AdminUser;
use Illuminate\Support\HtmlString;
use Tiny\Controller\BladeController;
use Tiny\Interfaces\AuthInterface;

abstract class Controller extends BladeController
{
    protected static $max_csv_items = 5000;

    protected $num = 12;

    ################################################################
    ############################ beforeAction 函数 ##########################
    ################################################################

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);
        self::$_instance = $this;

        self::setBladePath(App::path(['resources', 'views']), self::getViewCachePath());

        $host = $this->getRequest()->host();
        $admin_id = AdminUser::_getAdminByCname($host);
        $base_cname_map = App::config('services.base_cname_map');
        if (!empty($admin_id) || !empty($base_cname_map[$host])) {
            $routeInfo = $this->getRequest()->getRouteInfo();
            if ($routeInfo[1] == "Front\\IndexController") {  // CNAME 过来的访问  不允许访问主页 直接重定向到 登陆页面
                return App::redirect($this->getResponse(), '/auth');
            }
        }
        $this->assign('siteConfig', !empty($admin_id) ? Api::_getAdminConfigByAdmin($admin_id) : []);
        $this->assign('siteAdmin', !empty($admin_id) ? AdminUser::getOneById($admin_id) : []);
        $this->assign('baseHost', $this->_requestHost());

        $num = $this->_request('num', $this->num);
        $this->num = $num > 5 && $num < 100 ? intval($num) : $this->num;
        $this->assign('num', $this->num);

        return $params;
    }

    /** 组合一系列的条件参数 返回一个函数 依次调用参数中的函数 为 $table 增加检索条件
     * @param $func_list
     * @return \Closure
     */
    protected static function _buildWhereList($func_list)
    {
        $func_list = is_array($func_list) ? $func_list : func_get_args();
        return function ($table) use ($func_list) {
            foreach ($func_list as $func) {
                if (!empty($func)) {
                    $table = $func($table);
                }
            }
            return $table;
        };
    }

    ################################################################
    ############################ 不规范的辅助函数 ##########################
    ################################################################

    /**
     * @param Controller $ctrl
     * @return static
     */
    final public static function _createFromController(Controller $ctrl)
    {
        $obj = new static($ctrl->getRequest(), $ctrl->getResponse());
        $obj->_setAuth($ctrl->auth());
        $obj->beforeAction($ctrl->getRequest()->getParams());
        return $obj;
    }

    final public static function _createFromApi(AbstractApi $api)
    {
        $obj = new static($api->getRequest(), $api->getResponse());
        $obj->_setAuth($api->auth());
        $obj->beforeAction($api->getRequest()->getParams());
        return $obj;
    }

    /**
     * @param AuthInterface $auth
     */
    final public function _setAuth(AuthInterface $auth)
    {
        $this->_auth = $auth;
    }

    /** @var AuthInterface */
    private $_auth = null;

    final public function auth()
    {
        if (is_null($this->_auth)) {
            $this->_auth = $this->_initAuth();
        }
        return $this->_auth;
    }

    /**
     * @return AuthInterface
     */
    protected function _initAuth()
    {
        return new AdminAuth($this);
    }

    /** @var Controller */
    private static $_instance = null;

    /**
     * @return null|AuthInterface
     */
    public static function _getAuthByCtx()
    {
        if (!empty(self::$_instance)) {
            return self::$_instance->auth();
        }
        return !empty(Api::$_instance) ? Api::$_instance->auth() : null;
    }

    /**
     * @return null|\Tiny\Interfaces\RequestInterface
     */
    public static function _getRequestByCtx()
    {
        if (!empty(self::$_instance)) {
            return self::$_instance->getRequest();
        }
        return !empty(AbstractApi::$_instance) ? AbstractApi::$_instance->getRequest() : null;
    }

    /**
     * @return null|\Tiny\Interfaces\ResponseInterface
     */
    public static function _getResponseByCtx()
    {
        if (!empty(self::$_instance)) {
            return self::$_instance->getResponse();
        }
        return !empty(Api::$_instance) ? Api::$_instance->getResponse() : null;
    }

    public static function getViewCachePath()
    {
        return App::cache_path(['view_tpl']);
    }

    protected function extendAssign(array $params)
    {
        $params['webname'] = App::config('ENV_WEB.name');
        $params['cdn'] = App::config('ENV_WEB.cdn');

        list($common_cdn, $webver) = Util::getCdn(0);
        $params['webver'] = !empty($params['webver']) ? $params['webver'] : $webver;
        $params['common_cdn'] = !empty($params['common_cdn']) ? $params['common_cdn'] : $common_cdn;

        if (empty($params['user'])) {
            if ($this->auth() && $this->auth()->check()) {
                $user = $this->auth()->user();
                $params['user'] = $user;
            } else {
                $params['user'] = null;
            }
        }
        if (empty($params['agentBrowser'])) {
            $params['agentBrowser'] = $this->getRequest()->agent_browser();
        }

        $self_admin_id = $this->auth()->id();
        $agent = $this->getRequest()->_server('HTTP_USER_AGENT', '');
        $agentHash = Util::short_md5(Util::trimlower($agent), 16);
        $params['token'] = AdminAuth::_encode("{$self_admin_id}", 36000, $agentHash);

        $params = parent::extendAssign($params);
        return $params;
    }

    protected static function _D($data, $tags = null, $ignoreTraceCalls = 0)
    {
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            $tags = $request->debugTag($tags);
        }
        App::_D($data, $tags, $ignoreTraceCalls);
    }

    public function csrf_token()
    {
        $agent = $this->getRequest()->_header('User-Agent', 'unknown agent');
        $token = $this->getRequest()->session_id() . '|' . md5($agent . '_csrf');
        $csrf_token = App::encrypt($token, 3600 * 24, '_csrf');
        return $csrf_token;
    }

    public function csrf_field()
    {
        $csrf_token = $this->csrf_token();
        $csrf_field = '<input type="hidden" name="_token" value="' . $csrf_token . '">';
        return new HtmlString($csrf_field);

    }

    final protected function jump_index()
    {
        $url = App::url($this->getRequest(), ['', 'index', 'index']);
        App::redirect($this->getResponse(), $url);
    }

    public function is_post()
    {
        return Util::stri_cmp($this->getRequest()->getMethod(), 'post');
    }

    ##########################
    ######## EXCEL 相关 ########
    ##########################

    const EXCEL_TYPE_STRING2 = '#t=str';
    const EXCEL_TYPE_STRING = '#t=s';
    const EXCEL_TYPE_FORMULA = '#t=f';
    const EXCEL_TYPE_NUMERIC = '#t=n';
    const EXCEL_TYPE_BOOL = '#t=b';
    const EXCEL_TYPE_NULL = '#t=null';
    const EXCEL_TYPE_INLINE = '#t=inlineStr';
    const EXCEL_TYPE_ERROR = '#t=e';

    /**
     * Load a new excel file
     * @param  string $filename
     * @param  callable $callback
     * @param string $file_type
     * @return mixed
     */
    protected static function load_excel($filename, callable $callback, $file_type = 'xls')
    {
        require_once PLUGIN_PATH . 'excel/excel_reader2.php';
        if ($file_type == 'xls') {
            $reader = \PHPExcel_IOFactory::createReader('Excel5'); //设置以Excel5格式(Excel97-2003工作簿)
        }
        if ($file_type == 'xlsx') {
            $reader = new \PHPExcel_Reader_Excel2007();
        }
        if (!empty($reader)) {
            // 读excel文件
            /** @var \PHPExcel_Reader_Excel2007 $reader */
            /** @var \PHPExcel $PHPExcel */
            $PHPExcel = $reader->load($filename); // 载入excel文件
            $sheet = $PHPExcel->getAllSheets();
            return $callback($sheet);
        }
        return null;
    }

    /**
     * Create a new excel file
     * @param  string $filename
     * @param  array $data
     * @param string $sheet_name
     * @return mixed
     */
    protected function create_excel($filename, array $data = [], $sheet_name = 's1')
    {
        $title_map = array_shift($data);
        return $this->exportExcel($filename, $sheet_name, $data, function ($val, $idx) use ($title_map) {
            if ($idx == 0) {
                $ret = [];
                foreach ($val as $key => $item) {
                    $ret[$title_map[$key]] = $item;
                }
                return $ret;
            } else {
                return $val;
            }
        });
    }

    protected static function colWidth($width)
    {
        $width = intval($width);
        $width = $width > 255 ? 255 : $width;
        return $width > 0 ? "#w={$width}" : "";
    }

    protected function _excelSheet(\PHPExcel_Worksheet $objActSheet, $list, callable $func = null)
    {
        static $tag_map = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
            'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
            'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
            'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ',
        ];
        $col_map = [];
        if (empty($list)) {
            $objActSheet->setCellValueExplicit(strtoupper("A1"), '无数据', \PHPExcel_Cell_DataType::TYPE_STRING);
            return;
        } else {
            $idx = 0;
            foreach ($list as $key => $val) {
                $item = !empty($func) ? $func($val, $idx) : $val;
                if ($idx == 0) {
                    $c_idx = 0;
                    foreach ($item as $k => $v) {  // 第一行  尝试读取 key 信息 当作表头
                        $tag = $tag_map[$c_idx];
                        $dsl = is_numeric($k) ? [] : Util::dsl($k, '#', '=');
                        $title = !empty($dsl['base']) ? $dsl['base'] : $tag;
                        $type = !empty($dsl['args']['t']) ? $dsl['args']['t'] : \PHPExcel_Cell_DataType::TYPE_STRING;
                        $width = !empty($dsl['args']['w']) ? $dsl['args']['w'] : 0; // auto
                        $col_map[$c_idx] = ['tag' => $tag, 'type' => $type, 'width' => $width,];
                        $objActSheet->setCellValueExplicit(strtoupper("{$tag}1"), $title, \PHPExcel_Cell_DataType::TYPE_STRING);
                        $c_idx += 1;
                    }
                }

                $num = $idx + 2;
                $c_idx = 0;
                foreach ($item as $k => $v) {   // 读取每一行信息  写入表格
                    $objActSheet->setCellValueExplicit("{$col_map[$c_idx]['tag']}{$num}", $v, $col_map[$c_idx]['type']);
                    $c_idx += 1;
                }
                $idx += 1;
            }
        }
        foreach ($col_map as $col) {
            if (empty($col['width'])) {
                $objActSheet->getColumnDimension("{$col['tag']}")->setAutoSize(true);
            } else {
                $objActSheet->getColumnDimension("{$col['tag']}")->setWidth($col['width']);
            }
        }
    }

    protected static function loadWorksheet(\PHPExcel_Worksheet $sheet = null)
    {
        if (empty($sheet)) {
            return [];
        }
        $row_list = $sheet->getRowIterator();
        $row_idx = 1;
        $title_list = [];
        $ret = [];
        foreach ($row_list as $row) {
            if ($row_idx == 1) {
                foreach ($row->getCellIterator() as $cel) {
                    /** @var mixed $cel */
                    $title_list[] = $cel->getValue();
                }
            } else {
                $tmp = [];
                $tmp_idx = 0;
                foreach ($row->getCellIterator() as $cel) {
                    if ($tmp_idx >= count($title_list)) {
                        break;
                    }
                    $key = $title_list[$tmp_idx];
                    /** @var mixed $cel */
                    $tmp[$key] = $cel->getValue();
                    $tmp_idx += 1;
                }
                $ret[] = $tmp;
            }
            $row_idx += 1;
        }
        return $ret;
    }

    protected function exportCsv($file, $data, callable $func = null, $split = ",", $new_line = "\r\n", $buffer_len = 1000)
    {
        if (!Util::stri_endwith($file, '.csv')) {
            $file = "{$file}.csv";
        }
        $this->_addDownloadHeader($file);
        $this->getResponse()->sendHeader();

        $idx = 0;
        $buffer = [];
        foreach ($data as $key => $val) {
            $item = !empty($func) ? $func($val) : $val;
            if ($idx == 0) {
                $buffer[] = self::_tryBuildCsvHeader($item, $split);
            }
            $buffer[] = join($split, array_values($item));;
            $idx += 1;
            if (count($buffer) >= $buffer_len) {
                $line_str = join($new_line, $buffer) . $new_line;
                $this->getResponse()->appendBody(iconv("UTF-8", "GB2312//IGNORE", $line_str));
                $this->getResponse()->send();
                $buffer = [];
            }
        }
        if (!empty($buffer)) {
            $line_str = join($new_line, $buffer) . $new_line;
            $this->getResponse()->appendBody(iconv("UTF-8", "GB2312//IGNORE", $line_str));
            $this->getResponse()->send();
        }
        return $this->getResponse()->end();
    }

    private static function _tryBuildCsvHeader($val, $split = ',')
    {
        $cdx = 0;
        $headers = [];
        foreach ($val as $k => $v) {  // 第一行  尝试读取 key 信息 当作表头
            $cdx += 1;
            $tag = "col_{$cdx}";
            $dsl = is_numeric($k) ? [] : Util::dsl($k, '#', '=');
            $headers[] = !empty($dsl['base']) ? $dsl['base'] : $tag;
        }
        $header_str = join($split, $headers);
        return $header_str;
    }

    protected function exportExcel($file, $sheet, $list, callable $func = null)
    {
        if (!Util::stri_endwith($file, '.xlsx')) {
            $file = "{$file}.xlsx";
        }
        $this->_addDownloadHeader($file);
        $this->getResponse()->sendHeader();

        include PLUGIN_PATH . 'excel/PHPExcel.php';
        include PLUGIN_PATH . 'excel/PHPExcel/Writer/Excel5.php';

        $objPHPExcel = new \PHPExcel();
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle("{$sheet}");

        $this->_excelSheet($objActSheet, $list, $func);

        $objWriter->save('php://output');
        return $this->getResponse()->end();
    }

    protected function _addDownloadHeader($file)
    {
        $filename = $file;
        $encoded_filename = rawurlencode($filename);

        $ua = strtolower($this->getRequest()->_server('HTTP_USER_AGENT'));
        if (preg_match("/msie/", $ua) || preg_match("/edge/", $ua)) {
            $filename = iconv('UTF-8', 'GB2312//IGNORE', $filename);
            $encoded_filename = rawurlencode($filename);
            $fileHead = <<<EOT
attachment; filename="{$encoded_filename}"; filename*=utf-8''{$encoded_filename}
EOT;
        } else if (preg_match("/firefox/", $ua)) {
            $fileHead = <<<EOT
attachment; filename="{$encoded_filename}"; filename*=utf-8''{$encoded_filename}
EOT;
        } else {
            $fileHead = <<<EOT
attachment; filename="{$encoded_filename}"; filename*=utf-8''{$encoded_filename}
EOT;
        }
        $this->getResponse()->addHeader("Content-Type: application/force-download")
            ->addHeader("Content-Type: application/octet-stream")
            ->addHeader("Content-Type: application/download")
            ->addHeader("Content-Disposition:inline;filename=\"{$file}\"")
            ->addHeader("Content-Transfer-Encoding: binary")
            ->addHeader("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT")
            ->addHeader("Cache-Control: must-revalidate, post-check=0, pre-check=0")
            ->addHeader("Pragma: no-cache")
            ->addHeader("Content-Disposition:{$fileHead}");
    }

}