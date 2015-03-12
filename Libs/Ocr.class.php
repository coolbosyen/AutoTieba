<?php

namespace Libs;

// 引入必须类库
require_once(PUBLIC_PATH . 'OcrKing/class.ocrking.php');

/*
 * 验证码识别类
 */

class Ocr {

    static public function ocr($url, $path) {
        //屏蔽错误
        error_reporting(E_ALL || ~ E_NOTICE);
        $var = array(
            'language' => 'eng',
            'service' => 'OcrKingForCaptcha',
            'charset' => '7',
            'gbk' => false,
            'type' => $url,
        );
        //实例化OcrKing识别
        $ocrking = new \OcrKing(config('api'));

        //上传图片识别 请在doOcrKing方法前调用
        $ocrking->setFilPath($path);

        //提交识别
        $ocrking->doOcrKing($var);

        //获取识别结果
        $result = $ocrking->getResult();
        return $result['ResultList']['Item']['Result'];
    }

}
