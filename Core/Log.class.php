<?php

namespace Core;

/**
 * 日志处理类
 */
class Log {

    static private $log = ''; //日志信息

    /**
     * 写入日志
     * @param type $message
     * @param type $flags
     */

    static public function write($message, $flags = FILE_APPEND, $file = NULL) {
        $file = $file ? $file : self::get_log_path();
        file_put_contents($file, $message, $flags);
    }

    /**
     * 保存缓存中的日志
     * @param type $flags
     */
    static public function save($flags = FILE_APPEND) {
        if (self::$log) {
            self::write(self::$log, $flags);
        }
        self::$log = ''; //保存日志后清除日志缓存
    }

    /**
     * 缓存日志信息
     * @param type $log
     */
    static public function log($log) {
        if (PRINT_LOG)
            echo $log;
        self::$log .= $log;
    }

    /**
     * 获取日志文件路径
     * @return type
     */
    static public function get_log_path() {
        return LOG_PATH . date('Y-m-d') . '.log';
    }

    /**
     * 清空缓存日志
     */
    static public function clear() {
        self::$log = '';
    }

}
