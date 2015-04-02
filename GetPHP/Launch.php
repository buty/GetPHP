<?php
if(!defined('APP_PATH')) 
	exit('App Dir not Exists');

//核心文件路径
define('GETPHP_CORE_PATH', GETPHP_PATH . 'core' . DS);

//路径定义
require(GETPHP_CORE_PATH . 'DefinePath.php');

//系统函数库
require(GETPHP_CORE_PATH . 'Functions.php');

//注意: 加载配置文件,如果存在配置文件,则系统无法启动
if(file_exists(APP_CONFIG_PATH . 'config.inc.php')) {
	include APP_CONFIG_PATH . 'config.inc.php';
} else {
	exit('App Config Dir not Exists');
}

//PHP扩展名
define('EXT', load_config('base_php_ext'));

//加载针对应用的函数
if(file_exists(APP_CONFIG_PATH . 'GlobalFunction.php'))
	include_once(APP_CONFIG_PATH . 'GlobalFunction.php');


//启动Input过滤	
load_class('Input');
//加载初始化类
load_class('Init');

?>