<?php
//定义应用目录名称
define('CONFIG_DIR_NAME', 'config');
define('CACHE_DIR_NAME', 'cache');
define('LANG_DIR_NAME', 'lang');
define('LIB_DIR_NAME', 'lib');
define('PLUGIN_DIR_NAME', 'plugin');
define('CONTROLL_DIR_NAME', 'controllers');
define('MODEL_DIR_NAME', 'model');
define('TEMPLATE_DIR_NAME', 'template');
define('TEMPLATE_CACHE_DIR_NAME', 'tpl_cache');

//公共目录名称
define('PUBLIC_DIR_NAME', 'public');
define('ATTCHMENT_DIR_NAME', 'attachment');
define('CSS_DIR_NAME', 'css');
define('IMAGES_DIR_NAME', 'images');
define('JS_DIR_NAME', 'js');

//应用路径
define('APP_CONFIG_PATH', 	APP_PATH . CONFIG_DIR_NAME . DS); //应用配置文件路径
define('APP_CACHE_PATH', 	APP_PATH . CACHE_DIR_NAME . DS); //缓存路径
define('APP_LANG_PATH', 	APP_PATH . LANG_DIR_NAME . DS); //语言路径
define('APP_LIB_PATH', 		APP_PATH . LIB_DIR_NAME . DS); //应用库路径
define('APP_PLUGIN_PATH', 	APP_PATH . PLUGIN_DIR_NAME . DS); //插件路径
define('APP_CONTROLL_PATH', APP_PATH . CONTROLL_DIR_NAME . DS); //控制器路径
define('APP_MODEL_PATH', 	APP_PATH . MODEL_DIR_NAME . DS); //数据模型路径
define('APP_TEMPLATE_PATH', APP_PATH . TEMPLATE_DIR_NAME . DS); //视图模板路径

define('APP_TEMPLATE_CACHE_PATH', APP_CACHE_PATH . TEMPLATE_CACHE_DIR_NAME . DS); //模板缓存路径

//公共路径
define('PUBLIC_PATH', APP_ROOT . PUBLIC_DIR_NAME . DS); //公共文件入口路径
define('PUBLIC_ATTCHMENT_PATH', PUBLIC_PATH . ATTCHMENT_DIR_NAME . DS); //附件路径
define('PUBLIC_CSS_PATH', PUBLIC_PATH . CSS_DIR_NAME . DS); //公共样式路径
define('PUBLIC_IMAGES_PATH', PUBLIC_PATH . IMAGES_DIR_NAME . DS); //公共图片路径
define('PUBLIC_JS_PATH', PUBLIC_PATH . JS_DIR_NAME . DS); //公共JS路径

//系统路径
define('GETPHP_LIB_PATH', GETPHP_PATH . 'lib' . DS); //LIB路径, 此处为GETPHP内核库地址，固定不变



