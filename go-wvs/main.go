/*
 * @Author       :  Ayouth
 * @Date         :  2022-06-29 GMT+0800
 * @LastEditTime :  2022-06-29 GMT+0800
 * @FilePath     :  main.go
 * @Description  :
 * Copyright (c) 2022 by Ayouth, All Rights Reserved.
 */
package main

import (
	"flag"
	"fmt"
	App "go-wvs/app"
	"strings"
)

func main() {
	var addr string
	var reqPath string
	var hostsStr string
	var dbFile string
	var putsLog bool
	flag.StringVar(&addr, "a", "0.0.0.0:5200", "监听地址，默认 0.0.0.0:5200")
	flag.StringVar(&reqPath, "r", "/visit-stat.js", "js请求路径，默认 /visit-stat.js")
	flag.StringVar(&hostsStr, "h", "0.0.0.0:5200", "允许的主机名，默认 0.0.0.0:5200 多个主机中间用英文逗号 , 分割 例如 127.0.0.1:5200,example.com ")
	flag.StringVar(&dbFile, "f", "./stat.db", "SQLite文件地址，默认 ./stat.db  推荐绝对路径")
	flag.BoolVar(&putsLog, "l", false, "是否开启Gin Web日志输入 默认不输出")
	flag.Parse()
	var hosts []string
	for _, host := range strings.Split(hostsStr, ",") {
		if h := strings.Trim(strings.ToLower(host), " "); h != "" {
			hosts = append(hosts, h)
		}
	}
	fmt.Println("执行信息：", "\nSQLite文件路径：", dbFile, "\n监听地址：", addr, "\njs请求路径：", reqPath, "\n输出Web日志：", putsLog, "\n获得许可的主机名：", strings.Join(hosts, " | "), "\n")
	app := App.Create(dbFile, reqPath, putsLog, hosts...)
	app.Run(addr)
}
