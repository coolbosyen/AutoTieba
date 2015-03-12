<?php

namespace Core;

class MiniScript {

    function __construct() {
        
    }

    static public $config = array();
    static public $config_ini = array();
    static public $class = '';

    static public function start() {
        // 注册自动加载方法
        spl_autoload_register('Core\MiniScript::autoload');
        //加载全局配置文件
        self::$config = self::require_check(DIR_PATH . 'config.php');
        self::run();
    }

    /**
     * 自动加载方法
     * @param type $class
     */
    static public function autoload($class) {
        self::$class = $class;
        //检查类配置文件并加载
        self::load_config();
        //加载类文件
        self::require_check(DIR_PATH . str_replace('\\', '/', $class) . CLASS_EXTENSION);
    }

    static public function run() {
        $class_name = SCRIPT_NAME;
        $class = new $class_name();
        $method = METHOD_NAME;
        $class->$method();
    }

    /**
     * 加载类配置文件
     */
    static private function load_config($flag = FALSE) {
        $file = DIR_PATH . str_replace('\\', '/', self::$class) . INI_EXTENSION;
        if (is_file($file)) {
            self::$config_ini[self::$class] = parse_ini_file($file, $flag);
        }
    }

    /**
     * 检查文件并加载
     * @param type $file
     */
    static private function require_check($file) {
        if (is_file($file)) {
            return require_once $file;
        }
    }

}
