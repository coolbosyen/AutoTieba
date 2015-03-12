<?php

/**
 * 全局系统函数库
 */
use Core\MiniScript;

/**
 * 获得配置文件的值
 * @param type $config
 * @return type
 */
function config($config) {
    $backtrace = debug_backtrace();
    $class = isset($backtrace[1]['class']) ? $backtrace[1]['class'] : NULL;
    if (isset(MiniScript::$config_ini[$class][$config]))
        return MiniScript::$config_ini[$class][$config];
    return isset(MiniScript::$config[$config]) ? MiniScript::$config[$config] : NULL;
}
