/*
 * @Author       :  Ayouth
 * @Date         :  2022-06-29 GMT+0800
 * @LastEditTime :  2022-06-29 GMT+0800
 * @FilePath     :  create.go
 * @Description  :
 * Copyright (c) 2022 by Ayouth, All Rights Reserved.
 */
package app

import (
	"fmt"
	"io"
	"net/http"
	URL "net/url"
	"os"
	"strings"
	Q "go-wvs/query"

	"github.com/gin-gonic/gin"
)

// 检查host是否合法
func check(host string, hostArr []string) bool {
	for _, v := range hostArr {
		if host == v {
			return true
		}
	}
	return false
}

// 创建APP实例
func Create(dbFile, reqPath string, putsLog bool, hostArr ...string) (router *gin.Engine) {
	gin.DisableConsoleColor()
	if !putsLog {
		f, _ := os.Open(os.DevNull)
		gin.DefaultWriter = io.MultiWriter(f)
	}
	router = gin.Default()
	router.GET(reqPath, func(c *gin.Context) {
		c.Header("Cache-Control", "no-cache")
		ref := c.Request.Header.Get("Referer")
		url, err := URL.Parse(ref)
		if err != nil || len(strings.Trim(ref, " ")) < 1 {
			c.Data(http.StatusOK, "application/javascript;charset=UTF-8", []byte("console.error(\"Unresolved Referrer\")"))
			return
		}
		if !check(url.Host, hostArr) {
			c.Data(http.StatusOK, "application/javascript;charset=UTF-8", []byte("console.error(\"Permission Denied\")"))
			return
		}
		data := Q.Query(dbFile, url.Host, url.Path, c.ClientIP(), c.Request.Header.Get("User-Agent"))
		var js string
		if cb := c.Query("callback"); len(cb) > 0 {
			js = cb
		} else if cb := c.Query("cb"); len(cb) > 0 {
			js = cb
		} else if len(c.Query("purenum")) > 0 {
			js = `; !function (data) { var dict = { "site-uv": "#ay-site-uv", "site-pv": "#ay-site-pv", "path-uv": "#ay-path-uv", "path-pv": "#ay-path-pv" };` + "Object.keys(dict).forEach((key) => { var e = document.querySelector(dict[key]); e && (e.textContent = data[key]); }); }"
		} else {
			js = `; !function (data) { var dict = { "site-uv": "#ay-site-uv", "site-pv": "#ay-site-pv", "path-uv": "#ay-path-uv", "path-pv": "#ay-path-pv" };` + "var easyNumber = function (num) { num = parseInt(num); var k, m, p; if (1e6 > num && num >= 1e3) { k = parseInt(num / 1e3); p = parseInt((num % 1e3) / 1e2); if (p == 0) { num = `${k}K`; } else { num = `${k}.${p}K`; } } else if (num >= 1e6) { m = parseInt(num / 1e6); p = parseInt((num % 1e6) / 1e5); if (p == 0) { num = `${m}M`; } else { num = `${m}.${p}M`; } } return num; }; Object.keys(dict).forEach((key) => { var e = document.querySelector(dict[key]); e && (e.textContent = easyNumber(data[key])); }); }"
		}
		js += fmt.Sprintf("(%s);", data)
		c.Data(http.StatusOK, "application/javascript;charset=UTF-8", []byte(js))
	})
	return
}
