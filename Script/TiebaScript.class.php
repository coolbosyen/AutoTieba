<?php

namespace Script;

use Action\BaiduAction;
use Action\TiebaAction;
use Libs\Email;
use Core\Log;

class TiebaScript {

    public function run() {
        $user_list = config('user_list') ? config('user_list') : array();
        $date = date("Y-m-d");
        $user_check = array();
        for (;;) {
            foreach ($user_list as $user => $user_info) {
                $log_head = date("H:i:s") . ':' . $user . '--';
                $check = isset($user_check[$user]) ? $user_check[$user] : FALSE;
                if (!$check) {
                    $baidu = new BaiduAction($user, $user_info['password']);
                    $tieba = new TiebaAction($baidu->getCookie());
                    $result = $tieba->sign();
                    if ($result) {
                        foreach ($result as $key => $value) {
                            $result_info = implode(';', $result[$key]);
                            switch ($key) {
                                case 'success':
                                    $info = '以下贴吧签到成功：';
                                    break;
                                case 'repeat':
                                    $info = '以下贴吧已经签到：';
                                    break;
                                case 'ban':
                                    $info = '以下贴吧已被封：';
                                    break;
                                case 'error':
                                    $info = '以下贴吧签到失败：';
                                    break;
                                default: break;
                            }
                            Log::log($log_head . $info . $result_info . ENTER);
                        }
                        if ($tieba->success_count + $tieba->repeat_count + $tieba->ban_count == $tieba->num) {
                            Log::log($log_head . '本日全部贴吧签到完成。' . ENTER);
                            $user_check[$user] = TRUE; //完成标记
                        }
                    } else {
                        $info = 'COOKIE错误，可能需要验证码登录。--';
                        Log::log($log_head . $info);
                        Log::log(Email::send($user_info['email'], $user, $info) . ENTER);
                        unlink(COOKIE_PATH . '/' . md5($user));
                    }
                    Log::save(); //保存日志
                    $date = date("Y-m-d");
                }
                if ($date != date("Y-m-d"))
                    $user_check[$user] = FALSE; //重置完成状态
            }
            sleep(mt_rand(config('min_time'), config('max_time')));
        }
    }

}
