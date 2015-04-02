<?php

//加载基础配置
load_config(include APP_CONFIG_PATH . 'base_config.php');

//获取当前php.ini的配置环境
$server_deploy_type = get_cfg_var('server_deploy_type');

//根据设备环境设置配置目录
$base_product_type = load_config('base_product_type');
if(!array_key_exists($server_deploy_type, $base_product_type)) {
    $config_dispatch_dir = current($base_product_type);
} else {
    $config_dispatch_dir = $base_product_type[$server_deploy_type];
}

//根据设备环境加载配置项
if(file_exists(APP_CONFIG_PATH . $config_dispatch_dir . DS . 'idc_switch_conf.php')) {
    include APP_CONFIG_PATH . $config_dispatch_dir . DS . 'idc_switch_conf.php';
}

