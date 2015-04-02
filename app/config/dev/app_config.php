<?php

//设定项目配置
return $array = array(
    
    //HTML字符编码
    'default_charset' => 'utf-8',

    //URI类型
    'urlmethod' => '2', //1为<<siteurl>>/index.php/Index/index类型,2为重写类型<<siteurl>>/Index/index,3为重写类型<<siteurl>>?s=/Index/index

    'public_url' => 'public/',
    'public_attachment_url' => 'attachment/',
    'public_css_url' => 'css/',
    'public_image_url' => 'images/',
    'public_js_url' => 'js/',

    //IMG图片大小
    'img_resize' => true, //开户缩图处理
    'max_width' => 600,
    'max_height'=> 400,

    //其它设置
    'loginfailnum'  =>  '3', //登录最大的失败次数
    'loginlocktime' =>  '300' //用户锁定的时长
);

