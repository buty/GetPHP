<?php

//选择语言
define('LANG', load_config('base_lang'));

//模板扩展名与路径
define('HTML_EXT', load_config('base_html_ext'));
define('TEMPLATE', load_config('base_template'));
define('THEME', load_config('base_theme'));

//站点加密信息
$sitekey = 	load_config('base_sitekey') ? load_config('base_sitekey') : substr(time(), 0, -7);
$authcode = load_config('base_authcode') ? load_config('base_authcode') : substr(time(), 0, -7);
//加密
define('AUTHCODE', $authcode);

//表单验证
define('FORMHASH_NAME', load_config('base_hashname'));
define('FORMHASH', substr(md5(substr(time(), 0, -6).'|'.md5($sitekey)),8,8));


//静态文件常量
//define('BASE_URL', load_config('site_url'));
define('PUBLIC_URL', BASE_URL.load_config('public_url'));
define('PUBLIC_ATTACHMENT_URL', PUBLIC_URL.load_config('public_attachment_url'));
define('PUBLIC_CSS_URL', PUBLIC_URL.load_config('public_css_url'));
define('PUBLIC_IMAGE_URL', PUBLIC_URL.load_config('public_image_url'));
define('PUBLIC_JS_URL', PUBLIC_URL.load_config('public_js_url'));
define('PLUGIN_URL', BASE_URL . APPNAME . '/' . PLUGIN_DIR_NAME. '/'); //插件的URL地址