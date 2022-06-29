/*
 * @Author       :  Ayouth
 * @Date         :  2022-06-29 GMT+0800
 * @LastEditTime :  2022-06-29 GMT+0800
 * @FilePath     :  db.go
 * @Description  :
 * Copyright (c) 2022 by Ayouth, All Rights Reserved.
 */
package query

import (
	"database/sql"
	"fmt"
	"sync"
	"time"

	_ "github.com/mattn/go-sqlite3"
)

var mutex sync.Mutex

// 从SQLite中查询
func queryFromSQLite(dbFile, domain, path, ip, ua string) *SiteInfo {
	mutex.Lock()
	defer mutex.Unlock()
	db, err := sql.Open("sqlite3", dbFile)
	checkErr(err)
	defer db.Close()
	//创建表
	createTable := `
		CREATE TABLE IF NOT EXISTS "main"."access" (
			"id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
			"domain" TEXT NOT NULL,
			"path" TEXT NOT NULL,
			"ip" TEXT,
			"useragent" TEXT,
			"timestamp" text NOT NULL
		  )
		`
	_, err = db.Exec(createTable)
	checkErr(err)

	// 保存记录
	tp := fmt.Sprint(time.Now().UnixMilli())
	stmt, err := db.Prepare("INSERT INTO access(domain,path,ip,useragent,timestamp) values(?,?,?,?,?)")
	checkErr(err)
	_, err = stmt.Exec(domain, path, ip, ua, tp)
	checkErr(err)

	// 查询数据
	siterow, err := db.Query("SELECT COUNT(*),COUNT(DISTINCT ip) FROM access")
	checkErr(err)
	var sitepv, siteuv, pathuv, pathpv int64
	if siterow.Next() {
		err = siterow.Scan(&sitepv, &siteuv)
		checkErr(err)
	}
	siterow.Close()
	pagerow, _ := db.Query("SELECT COUNT(*) ,COUNT(DISTINCT ip) FROM access WHERE path=?", path)
	if pagerow.Next() {
		err = pagerow.Scan(&pathpv, &pathuv)
		checkErr(err)
	}
	pagerow.Close()
	fmt.Println(sitepv, siteuv, pathpv, pathuv)
	return &SiteInfo{
		Domain: domain,
		Path:   path,
		SiteUv: siteuv,
		SitePv: sitepv,
		PathUv: pathuv,
		PathPv: pathpv,
	}
}

// 判断错误
func checkErr(err error) {
	if err != nil {
		panic(err)
	}
}
