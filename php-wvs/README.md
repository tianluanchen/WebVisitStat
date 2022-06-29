# WebVisitStat

[![PHPVersion](https://img.shields.io/badge/PHP-v7.4-blue?logo=php&style=flat-square)](https://www.php.net/downloads)
[![](https://img.shields.io/github/license/tianluanchen/WebVisitStat?style=flat-square)](https://github.com/tianluanchen/WebVisitStat/blob/main/LICENSE)

在网页添加一行script标签，即可统计并显示网页访客数和访问量，管理界面采取前后端分离架构，目前还处于开发中。

## 介绍

有时候我们并不能直接从像Google Analytics这些工具中获得总访问和总访客数，另外对于一些静态博客或网站，自身也不易获取访问数据，而本项目则实现了一种类似[不蒜子网页计数器](https://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=&cad=rja&uact=8&ved=2ahUKEwjtysTJ-r33AhWYilwKHQSICFIQFnoECAsQAQ&url=https%3A%2F%2Fbusuanzi.ibruce.info%2F&usg=AOvVaw1v9qHLhSebezGd8uFy7bxZ)的功能，并开源于此。当你成功的部署该项目后，并在自己的网页引入后，网站统计数据将展现在每一个访问该网页的用户面前。访客数统计功能依据ip实现。
（目前管理界面功能尚未完善，只临时实现添加许可域名的功能）


## 特点

- [x] 使用jsonp技术
- [x] 可自选ip获取方法（不受cdn干扰）
- [x] 单网页独立记录（含访客）
- [ ] 前后端分离（管理界面 待完成）

演示 (/test.html)

![项目演示](https://s3.bmp.ovh/imgs/2022/05/01/7e61059f954205cc.gif)

## 部署

PHP版本>=`7.4`，且需要开启PHP的mysqli库扩展，使用MySql数据库，执行根目录下的`db.sql`文件，生成两张数据表。

在`/php/config.php`文件下进行配置。管理界面入口是项目中的`/admin`，而提供的服务入口是`/php/stat.php`
```php

//管理员账户和密码 用于管理员登录
define("ADMIN_ACCOUNT",    "abc");
define("ADMIN_PASSWORD",    "123");

// 此处配置管理界面API的允许主机名
define('ALLOWED_HOSTS', [
    "127.0.0.1",
    "0.0.0.0:3000",
    "your-domain.com"
]);
/* 这里是访客ip的获取方式
0  获取直连ip
1  若用户是代理状态,则获取真实ip 
2  若你的服务器使用cloudflare cdn，可选这项，这是获取cloudflare提供的用户ip
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
```

## 网页引入

使用默认的js脚本
```html

站点访客数：<span id="ay-site-uv"></span>
站点访问数：<span id="ay-site-pv"></span>
当前页访客数：<span id="ay-path-uv"></span>
当前页访问数：<span id="ay-path-pv"></span>
<!-- 在网页引入此script 注意script的referrerpolicy如下写上 -->
<script async referrerpolicy="unsafe-url" src="你部署后的url/php/stat.php"></script>
<!-- 当浏览器执行该脚本时，该脚本会自动寻找对应id的标签并填入数据 
另外需要注意的时，该脚本默认会将原来的数字进行转换
比如 1000  => 1K  1000000 => 1M
     9969 => 9.9K 1230000 => 1.2M
当然你可在url后面添加 purenum 参数将取消转换 即 ..../php/stat.php?purenum=true
-->
```
使用jsonp回调函数
```html
<script>
    function yourCallback(data){
        console.log(data);
        /*
        data 结构
        Object {
            "domain":"域名或主机名",
            "path":"当前页面路径",
            "site-uv":"站点访客数",
            "site-pv":"站点访问量",
            "path-uv":"当前页面访客数",
            "path-pv":"当前页面访问量"
        }
        */
    }
</script>
<script async referrerpolicy="unsafe-url" src="你部署后的url/php/stat.php?cb=yourCallback"></script>
```

## License

The GPL-3.0 License.
