<?php
/*
 * @Author       :  Ayouth
 * @Date         :  2022-04-29 GMT+0800
 * @LastEditTime :  2022-05-01 GMT+0800
 * @FilePath     :  db.php
 * @Description  :  数据库基类
 * Copyright (c) 2022 by Ayouth, All Rights Reserved. 
 */
class DB
{
    public function __construct(array $config)
    {
        try {
            $this->conn = new mysqli($config['hostname'], $config['username'], $config['password'], $config['database'], $config['port']);
        } catch (\Throwable $th) {
            // 数据库连接出错终止程序
            throw new Exception("数据库连接错误");
        }
        //设置默认字符编码
        $this->conn->set_charset("utf8");
    }
    /**
     * @description: 防注入
     * @param {*} $data
     * @return {*}
     */
    public function escape($data)
    {
        if (is_string($data)) {
            $data = $this->conn->real_escape_string($data);
        } else if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = $this->conn->real_escape_string($value);
                }
            }
        }
        return $data;
    }
    /**
     * @description: 执行
     * @param {string} $sql
     * @param {*} $values
     * @return {*}
     */
    public function excute_sql(string $sql, ...$values)
    {
        // 防止sql注入
        $values = $this->escape($values);
        if (count($values) > 0) {
            $sql =  sprintf($sql, ...$values);
        }
        $result = $this->conn->query($sql);
        return $result;
    }
    /**
     * @description: 关闭
     * @param {*}
     * @return {*}
     */
    public function close()
    {
        $this->conn->close();
    }
    public  function __destruct()
    {
        $this->close();
    }
}


