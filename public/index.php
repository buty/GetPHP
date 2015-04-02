<?php
//定义时区
date_default_timezone_set('Asia/Hong_Kong');
//应用名称
define('APPNAME','app');
//路径分隔符简写
define('DS', DIRECTORY_SEPARATOR);

//应用的根目录
define('APP_ROOT', dirname(dirname(__FILE__)) . DS);

//当前脚本名
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

//检测应用目录是否存在
if (!is_dir(APP_ROOT . APPNAME)){
	exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . SELF);
}

//应用路径与GetPHP路径
define('APP_PATH', APP_ROOT . APPNAME . DS);
define('GETPHP_PATH', APP_ROOT . 'GetPHP' . DS); //自己控制GETPHP的位置


//包含GetPHP的启动文件
require(GETPHP_PATH . 'Launch.php');
//defined('STATIC_URL') or define('STATIC_URL', load_config('site_url') . 'index.php/');
//系统开始运行
Init::run();

?>