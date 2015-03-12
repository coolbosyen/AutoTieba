<?php

namespace Action;

use Core\Log;
use Libs\Ocr;

/**
 * 百度基础服务类
 * 提供登陆和Cookie服务
 */
class BaiduAction {

    private $cookie = '';
    private $username = '';
    private $password = '';
    private $codestring = '';
    private $verifycode = '';
    private $cookie_validate = 180; //cookie有效期,默认为180天

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
        $filename = COOKIE_PATH . '/' . md5($this->username);
        if (((time() - @filemtime($filename)) / 86400 < $this->cookie_validate) && ($cookie = file_get_contents($filename)) != '') {
            //如果cookie在有效期内且不为空
            $this->cookie = $cookie;
        } else {
            $this->login();
        }
    }

    /**
     * CURL请求实现
     * @param type $url
     * @param type $post_data
     * @param type $referef
     * @param type $header
     * @return type
     */
    private function http_request($url, $post_data, $referef, $header = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($post_data != "") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }

        if ($referef != "") {
            curl_setopt($ch, CURLOPT_REFERER, $referef);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31");

        if ($this->cookie != "") {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }
        $data = curl_exec($ch);
        curl_close($ch);

        if ($header) {
            preg_match_all('/Set-Cookie:((.+)=(.+))$/m ', $data, $cookies);
            if (is_array($cookies) && count($cookies) > 1 && count($cookies[1]) > 0) {
                foreach ($cookies[1] as $i => $k) {
                    $cookieinfos = explode(";", $k);
                    if (is_array($cookieinfos) && count($cookieinfos) > 1) {
                        $this->cookie .= $cookieinfos[0];
                        $this->cookie .= "; ";
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 百度登陆方法
     */
    private function login() {
        //生成一个cookie
        $ret = $this->http_request("https://passport.baidu.com/v2/api/?getapi&class=login&tpl=mn&tangram=true", "", "");

        //获取token并保存cookie
        $ret = $this->http_request("https://passport.baidu.com/v2/api/?getapi&class=login&tpl=mn&tangram=true", "", "");
        preg_match_all('/login_token=\'(.+)\'/', $ret, $tokens);
        $login_token = $tokens[1][0];

        //登陆并保存cookie
        $post_data = array();
        $post_data['username'] = $this->username;
        $post_data['password'] = $this->password;
        $post_data['token'] = $login_token;
        $post_data['codestring'] = $this->codestring;
        $post_data['charset'] = 'UTF-8';
        $post_data['callback'] = 'parent.bd12Pass.api.login._postCallback';
        $post_data['index'] = '0';
        $post_data['ppui_logintime'] = '8213';
        $post_data['isPhone'] = 'false';
        $post_data['mem_pass'] = 'on';
        $post_data['loginType'] = '1';
        $post_data['safeflg'] = '0';
        $post_data['staticpage'] = "https://passport.baidu.com/v2Jump.html";
        $post_data['tpl'] = 'mn';
        $post_data['u'] = "http://www.baidu.com/";
        $post_data['verifycode'] = $this->verifycode;

        $ret = $this->http_request("http://passport.baidu.com/v2/api/?login", $post_data, "https://passport.baidu.com/v2/?login&tpl=mn&u=http%3A%2F%2Fwww.baidu.com%2F");
        if (strstr($ret, 'captchaservice')) {
            if (preg_match('/(captchaservice\w{200,})/', $ret, $match)) {
                $this->codestring = $match[1];
                $code_ini = VERIFY_PATH . config('code_ini');
                Log::log('需要验证码，即将下载验证码' . ENTER);
                $img_path = VERIFY_PATH . config('img_name');
                $img_url = 'https://passport.baidu.com/cgi-bin/genimage?' . $this->codestring;
                if (config('manual') == 1) {
                    $this->getImg($img_url, $img_path);
                    Log::log('当前为手动模式，请手动输入验证码到配置文件中' . ENTER);
                    for (;;) {
                        if (is_file($code_ini))
                            $verifycode = parse_ini_file($code_ini);
                        if (isset($verifycode['verifycode']) && $verifycode['verifycode'] && isset($verifycode['status']) && $verifycode['status'] == 1) {
                            $this->verifycode = $verifycode['verifycode'];
                            Log::log('已获得验证码：' . $this->verifycode . ENTER);
                            break;
                        }
                        sleep(10);
                    }
                } else {
                    Log::log('当前为自动模式，尝试获得验证码' . ENTER);
                    for (;;) {
                        $this->getImg($img_url, $img_path);
                        $verifycode = Ocr::ocr($img_url, realpath($img_path));
                        Log::log('已识别验证码：' . $verifycode . ENTER);
                        if (strlen($verifycode) == 4) {
                            $this->verifycode = $verifycode;
                            Log::log('格式符合，尝试登陆：' . ENTER);
                            break;
                        } else {
                            Log::log('格式不符，再次识别：' . ENTER);
                        }
                    }
                }
                $this->login();
            }
        }
        //记录下所有cookie
        $this->writeCookie();
        Log::save();
    }

    /**
     * 记录Cookie
     */
    private function writeCookie() {
        if (!file_exists(COOKIE_PATH)) {
            @mkdir(COOKIE_PATH);
        }
        $filename = COOKIE_PATH . '/' . md5($this->username);
        Log::write($this->cookie, 0, $filename);
    }

    /**
     * 获取cookie
     * @return string
     */
    public function getCookie() {
        return $this->cookie;
    }

    /**
     * 下载验证码到目录
     * @param type $url             地址
     * @param type $filename   文件路径
     */
    public function getImg($url = "", $filename = "") {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $imageData = curl_exec($curl);
        curl_close($curl);
        $tp = @fopen($filename, 'w');
        fwrite($tp, $imageData);
        fclose($tp);
    }

}
