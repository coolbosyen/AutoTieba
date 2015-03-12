<?php

namespace Action;

use Core\Log;

class TiebaAction {

    public $error_count = 0; //签到错误的贴吧数量
    public $success_count = 0; //签到成功的贴吧数量
    public $repeat_count = 0; //已经签到的贴吧数量
    public $ban_count = 0; //被封贴吧数量
    public $num = 0; //关注的贴吧总数
    public $cookie = '';

    public function __construct($cookie) {
        $this->cookie = $cookie;
    }

    /**
     * 贴吧签到入口
     * @return boolean
     */
    public function sign() {
        $kw_name = $this->getmylike();
        return $kw_name ? $this->sign_all($kw_name) : FALSE;
    }

    /**
     * 贴吧CURL请求
     * @param type $url
     * @param type $ua
     * @return type
     */
    private function curl_get($url, $ua = FALSE) {
        $ch = curl_init($url);
        if ($ua) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:Mozilla/5.0 (Linux; U; Android 2.3.4; zh-cn; W806 Build/GRJ22) AppleWebKit/530.17 (KHTML, like Gecko) FlyFlow/2.4 Version/4.0 Mobile Safari/530.17 baidubrowser/042_1.8.4.2_diordna_008_084/AIDIVN_01_4.3.2_608W/1000591a/9B673AC85965A58761CF435A48076629%7C880249110567268/1'));
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0', 'Connection:keep-alive', 'Referer:http://wapp.baidu.com/'));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        $get_url = curl_exec($ch);
        curl_close($ch);
        return $get_url;
    }

    /**
     * 贴吧签到实现
     * @param type $kw_name
     * @return type
     */
    private function sign_all($kw_name) {
        $result = array();
        $arr = explode(';', $kw_name);
        if (count($arr) > 0) {
            foreach ($arr as $value) {
                if ($value == '')
                    continue;
                $url = "http://tieba.baidu.com/mo/m?kw={$value}";
                $get_url = $this->curl_get($url);
                $tieba_name = iconv('GB2312', 'UTF-8', urldecode($value));
                preg_match_all('/<td style="text-align:right;"><a href="(.*?)">签到<\/a>/', $get_url, $matches);
                if (isset($matches[1][0])) {
                    $s = str_replace('&amp;', '&', $matches[1][0]);
                    $sign_url = 'http://tieba.baidu.com' . $s;
                    $get_sign = $this->curl_get($sign_url, true);
                    if (strpos($get_sign, '签到成功')) {
                        $result['success'][] = $tieba_name;
                        ++$this->success_count;
                    } else {
                        sleep(mt_rand(1, 5)); //休息随机一秒到五秒后重复一次，简单应对频率太快。
                        $get_sign = $this->curl_get($sign_url, true);
                    }
                } elseif (strpos($get_url, '已签到')) {
                    $result['repeat'][] = $tieba_name;
                    ++$this->repeat_count;
                } elseif (strpos($get_url, '尚未建立')) {
                    $result['ban'][] = $tieba_name;
                    ++$this->ban_count;
                } else {
                    $result['error'][] = $tieba_name;
                    ++$this->error_count;
                }
            }
        }
        return $result;
    }

    /**
     * 获取关注贴吧列表
     * @return boolean
     */
    private function getmylike() {
        $islogin = "http://tieba.baidu.com/dc/common/tbs?t=" . time();
        $check = json_decode($this->curl_get($islogin));
        if (!$check->is_login) {
            return FALSE;
        }
        $mylikeurl = "http://tieba.baidu.com/f/like/mylike?";
        $result = $this->curl_get($mylikeurl);
        $page = 2;
        $result2 = $this->curl_get($mylikeurl . '&pn=2');
        $result .= $result2;
        while (strpos($result2, '/f?kw')) {
            $page ++;
            $result2 = $this->curl_get($mylikeurl . '&pn=' . $page);
            $result .=$result2;
        }
        $pre_reg = '/f\?kw=(.*?)"/';
        preg_match_all($pre_reg, $result, $matches);
        $i = 0;
        $kw_name = '';
        foreach ($matches[1] as $value) {
            $kw_name .= $value . ';';
            $i ++;
        }
        Log::log("获取结束,一共[ $i ]个贴吧。" . ENTER);
        $this->num = $i;
        return $kw_name;
    }

}
