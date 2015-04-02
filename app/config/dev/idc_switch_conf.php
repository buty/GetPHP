<?php

//app配置
load_config(include dirname(__FILE__) . DS . 'app_config.php');
//db配置
load_config(include dirname(__FILE__) . DS . 'database.php');
//redis配置
load_config(include dirname(__FILE__) . DS . 'redis.local.php');
//第三方服务常量定义
include dirname(__FILE__) . DS . 'server.local.php';