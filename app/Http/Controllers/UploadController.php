<?php

namespace app\Http\Controllers;


use app\Controller;
use app\Libs\AliOss;
use app\Util;

class UploadController extends Controller
{

    public function index()
    {
        return $this->getResponse()->end('no data');
    }

    public function ajaxUpload()
    {
        $request = $this->getRequest();
        $typeName = "file";
        if ($this->_has('typeName')) {
            $typeName = $this->_request('typeName');
        }
        $redirect = $this->_has('redirect');
        if (!$request->hasFile($typeName)) {
            return $this->response()->json(['msg' => '参数错误', 'code' => 1]);
        }
        $dir = "upload/";
        if ($this->_has('dir')) {
            $dir = $dir . $this->_request('dir') . "/";
        } else {
            $dir = "upload/used/";
        }
        $file = $request->file($typeName);
        $fileName = $this->_request('fileName');
        try {
            $img = AliOss::uploadFile($file, $dir, $fileName, $this->_request('useOriginFileName'));
            if ($redirect) {
                $callback = $this->_request('CKEditorFuncNum');
                $response = $this->getResponse()->addHeader('Content-Type:text/html; charset=UTF-8', true);
                $ret = '<script type="text/javascript"> window.parent.CKEDITOR.tools.callFunction(' . $callback . ",'" . $img . "','')</script>";
                $response->appendBody($ret);
                return $response;
            }
            return $this->response()->json([
                'img' => $img,
                "size" => $file->getClientSize(),
                "name" => $file->getClientOriginalName(),
                'code' => 0,
            ]);
        } catch (\Exception $e) {
            $log_msg = "UploadController ajaxUpload fileName:{$fileName}, error:" . $e->getMessage();
            self::error($log_msg, __METHOD__, __CLASS__, __LINE__);
            return $this->response()->json([
                'msg' => $e->getMessage(),
                'code' => 1,
            ]);
        }
    }

    const UPLOAD_VSS_KEY = "oss_upload_vss";

    public function ajaxUploadVss()
    {
        error_log("file_obj" . print_r($_REQUEST, true) . print_r($_FILES, true));

        $json = ['code' => 0, 'msg' => 'suc'];

        if (empty($_FILES) || $_FILES['file']['error']) {
            return $this->response()->json(['code' => 1, 'msg' => 'file error']);
        }
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $fileName = isset($_FILES['file']["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
        $filePath = Util::path_join(CACHE_PATH, ['upload', $fileName], false);

        $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        if ($out) {
            $in = @fopen($_FILES['file']['tmp_name'], "rb");
            if ($in) {
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }
            } else {
                return $this->response()->json(['code' => 1, 'msg' => 'fopen in error']);
            }
            @fclose($in);
            @fclose($out);
            @unlink($_FILES['file']['tmp_name']);
        } else {
            return $this->response()->json(['code' => 1, 'msg' => 'fopen out error']);
        }

        if (!$chunks || $chunk == $chunks - 1) {
            rename("{$filePath}.part", $filePath);

            $redis = self::_getRedisInstance();
            $redis->rPush(self::UPLOAD_VSS_KEY, json_encode([
                'filePath' => $filePath,
                'fileName' => $fileName,
            ]));
        }

        error_log("uploadFileVss filePath:{$filePath}, fileName:{$fileName}");

        return $this->response()->json($json);
    }
}
