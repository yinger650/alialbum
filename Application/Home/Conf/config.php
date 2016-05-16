<?php
return array(
    // 添加数据库配置信息
    'DB_TYPE'=>'mysql',// 数据库类型
    'DB_HOST'=>'localhost',// 服务器地址
    'DB_NAME'=>'alialbum',// 数据库名
    'DB_USER'=>'',// 用户名
    'DB_PWD'=>'',// 密码
    'DB_PORT'=>3306,// 端口
    'DB_PREFIX'=>'',// 数据库表前缀
    'DB_CHARSET'=>'utf8',// 数据库字符集

    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/assets',
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
    ),


);

