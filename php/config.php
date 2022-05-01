<?php
//生产环境请打开下面两行注释，隐藏可能出现的错误
// error_reporting(E_ERROR);
// ini_set("display_errors", "Off");

//中国北京时间时区
ini_set('date.timezone', 'Asia/Shanghai');

//管理员账户和密码
define("ADMIN_ACCOUNT",    "abc");
define("ADMIN_PASSWORD",    "123");

//允许调用管理员api的主机或域名，非80、443需带端口号
define('ALLOWED_HOSTS', [
    "127.0.0.1",
    "0.0.0.0:3000",
    "192.168.1.110"
]);

/* 
0  直接选择Remote Addr  
1  选择x-forwarded-for 
2  选择cloudflare提供的ip
*/
define('IP_OPTION', 0);

//数据库配置
define('DB_CONFIG', [
    "username" => "root",
    "password" => "123456",
    "database" => "stat",
    "hostname" => "localhost",
    "port" => 3306,
]);
