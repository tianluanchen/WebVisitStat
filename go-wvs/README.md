# WebVisitStat

[![GOVersion](https://img.shields.io/badge/GO-v1.18.3-blue?logo=go&style=flat-square)](https://go.dev/dl/)
[![](https://img.shields.io/github/license/tianluanchen/PHPMessageBoard?style=flat-square)](https://github.com/tianluanchen/WebVisitStat/blob/main/LICENSE)

在网页添加一行script标签，即可统计并显示网页访客数和访问量。

## 介绍

用GO实现简单的Web访客统计，其中包含站点总访问次数、访客数，单页面（路径）访问次数、访客数，其中访客数依据IP计算，通过SQLite存储。只要在网页中引入一行`Script`标签即可显示数量，同时也支持JSONP。

## 特点

- [x] 使用jsonp技术
- [x] 每个网页（路径）访客记录
- [x] 高性能且便捷，无需数据库服务程序

## 部署

下载`./bin`目录下对应你系统的二进制文件或下载源码自行编译。

基本用法
```bash
$ ./指定的二进制文件 -help
Usage of ./指定的二进制文件:
  -a string
        监听地址，默认 0.0.0.0:5200 (default "0.0.0.0:5200")
  -f string
        SQLite文件地址，默认 ./stat.db  推荐绝对路径 (default "./stat.db")
  -h string
        允许的主机名，默认 127.0.0.1:5200 多个主机中间用英文逗号 , 分割 例如 127.0.0.1:5200,example.com  (default "127.0.0.1:5200")
  -l    是否开启Gin Web日志输入 默认不输出
  -r string
        js请求路径，默认 /visit-stat.js (default "/visit-stat.js")
```
Linux后台运行命令（推荐与Nginx服务器配合反向代理使用）
```bash
# 给予执行权限
chmod +x ./指定的二进制文件 
# 后台运行，监听0.0.0.0:5200，不输出web日志，其他信息均输入start.log中，进程ID输入到 process.pid中
nohup ./指定的二进制文件 -a 0.0.0.0:5200 > ./start.log 2>&1  & echo $! > ./process.pid 
# 结束进程
kill -9 `cat ./process.pid`
```

## 网页引入

使用默认的js脚本
```html

站点访客数：<span id="ay-site-uv"></span>
站点访问数：<span id="ay-site-pv"></span>
当前页访客数：<span id="ay-path-uv"></span>
当前页访问数：<span id="ay-path-pv"></span>
<!-- 在网页引入此script 注意script的referrerpolicy如下写上 -->
<script async referrerpolicy="unsafe-url" src="你部署后的主机名/你设置的js路径"></script>
<!-- 当浏览器执行该脚本时，该脚本会自动寻找对应id的标签并填入数据 
另外需要注意的时，该脚本默认会将原来的数字进行转换
比如 1000  => 1K  1000000 => 1M
     9969 => 9.9K 1230000 => 1.2M
当然你可在url后面添加 purenum 参数将取消转换 即 ....?purenum=true
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
<script async referrerpolicy="unsafe-url" src="你部署后的主机名/你设置的js路径?cb=yourCallback"></script>
```

## License

The GPL-3.0 License.
