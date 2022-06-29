/*
 * @Author       :  Ayouth
 * @Date         :  2022-06-29 GMT+0800
 * @LastEditTime :  2022-06-29 GMT+0800
 * @FilePath     :  query.go
 * @Description  :
 * Copyright (c) 2022 by Ayouth, All Rights Reserved.
 */
package query

import (
	"encoding/json"
)

// 查询统计结果
func Query(dbFile, domain, path, ip, ua string) string {
	info := queryFromSQLite(dbFile, domain, path, ip, ua)
	b, _ := json.Marshal(info)
	return string(b)
}
