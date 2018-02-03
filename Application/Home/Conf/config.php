<?php
return array(
	//'配置项'=>'配置值'
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '118.89.244.53', // 服务器地址
        'DB_NAME'   => 'envireoment_detection', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'passwd', // 密码
        'DB_PORT'   => 3306, // 端口
        'DB_PREFIX' => '', // 数据库表前缀
        'TMPL_PARSE_STRING'=>array(
            '__ico__'=>__ROOT__.'/Public/asserts_new/ico',
            '__img__'=>__ROOT__.'/Public/asserts_new/img'
        )
);
