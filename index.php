<?php

if (version_compare(PHP_VERSION, '5.3.0', '<'))
    die('require PHP > 5.3.0 !');
defined('DIR_PATH') or define('DIR_PATH', __DIR__ . '/'); //当前目录
defined('LOG_PATH') or define('LOG_PATH', DIR_PATH . 'Runtime/Logs/'); //日志目录
defined('PUBLIC_PATH') or define('PUBLIC_PATH', DIR_PATH . 'Libs/Public/'); //外部公共库目录
defined('COOKIE_PATH') or define('COOKIE_PATH', DIR_PATH . 'Runtime/Cookies/'); //Cookie目录
defined('VERIFY_PATH') or define('VERIFY_PATH', DIR_PATH . 'Runtime/Verify/'); //临时目录
defined('SCRIPT_NAME') or define('SCRIPT_NAME', isset($argv[1]) ? $argv[1] : ''); //脚本类名
defined('METHOD_NAME') or define('METHOD_NAME', isset($argv[2]) ? $argv[2] : (isset($argv[1]) ? 'run' : '')); //脚本方法名

date_default_timezone_set('PRC'); //设置时区

define('PRINT_LOG', TRUE); //调试模式,打印log日志
        const CLASS_EXTENSION = '.class.php'; // 类文件后缀
        const INI_EXTENSION = '.ini'; // 类配置文件后缀
        const ENTER = "\r\n"; //日志中的换行标志

require_once DIR_PATH . 'Core/MiniScript' . CLASS_EXTENSION; //加载系统文件入口
require_once DIR_PATH . 'Common/functions.php'; //加载全局系统函数库
Core\MiniScript::start(); // 初始化 