<?php
/*
 * @Author       :  Ayouth
 * @Date         :  2022-04-30 GMT+0800
 * @LastEditTime :  2022-05-01 GMT+0800
 * @FilePath     :  db_handle.php
 * @Description  :  数据库处理
 * Copyright (c) 2022 by Ayouth, All Rights Reserved. 
 */
require_once(__DIR__.'/db.php');
class DBHandle extends DB
{
    public function __construct(array $config)
    {
        parent::__construct($config);
    }
    /**
     * @description: 查询domain信息 返回布尔值或数组
     * @param {string} $domain
     * @param {string} $instruct
     * @return {*}
     */
    public function query_domain(string $domain, string $instruct = 'exist')
    {
        // 哈希缩短长度
        $domain_hash = md5($domain);
        $sql = 'SELECT `ip_access_count` `site_uv`,`total_access_count` `site_pv` FROM `access_stat` WHERE `domain_hash`="%s"';
        $result = $this->excute_sql($sql, $domain_hash);
        $value = null;
        $instruct = strtolower($instruct);
        switch ($instruct) {
            case 'exist':
                if ($result->num_rows > 0) {
                    $value = true;
                } else {
                    $value = false;
                }
                break;
            case 'value':
                $value = $result->fetch_assoc();
                break;
            default:
                throw new Exception("查询指令不合法");
        }
        $result->close();
        return $value;
    }
    /**
     * @description: 更新记录
     * @param {string} $domain
     * @param {string} $path
     * @param {string} $ip
     * @return {*}
     */
    public function update_record(string $domain, string $path, string $ip)
    {
        // 哈希缩短长度
        $hash_id = md5($domain . $path . $ip);
        $domain_hash = md5($domain);
        $sql = 'SELECT COUNT(*) `num` FROM `access_detail` WHERE  `hash_id`="%s"';
        $result = $this->excute_sql($sql, $hash_id);
        $row = $result->fetch_assoc();
        $exist = $row['num'] == '0' ? false : true;
        $result->close();
        if ($exist) {
            //存在此纪录则更新
            $sql = 'UPDATE `access_detail` SET `count`=`count`+1 WHERE `hash_id`="%s"';
            $this->excute_sql($sql, $hash_id);
            $this->update_domain($domain, 'increase', 0, 1);
        } else {
            //数据库不存在此纪录 则查询是否存在该ip访问过站点
            $sql = 'SELECT COUNT(*) `num` FROM `access_detail` WHERE `domain`="%s" AND `ip`="%s"';
            $result = $this->excute_sql($sql, $domain, $ip);
            $row = $result->fetch_assoc();
            $result->close();
            $new_ip = 0;
            if ($row['num'] == '0') {
                $new_ip = 1;
            }
            $sql = 'INSERT INTO `access_detail` (`hash_id`,`domain_hash`, `domain`,`path`,`ip`,`count`) VALUES ("%s","%s","%s", "%s","%s" , 1)';
            $this->excute_sql($sql, $hash_id, $domain_hash, $domain, $path, $ip);
            $this->update_domain($domain, 'increase', $new_ip, 1);
        }
    }
    /**
     * @description: 更新domain表
     * @param {string} $domain
     * @param {string} $instruct
     * @param {int} $ip_access_count
     * @param {int} $total_access_count
     * @return {*}
     */
    public function update_domain(string  $domain, string $instruct = 'increase', int $ip_access_count = 0, int $total_access_count = 0)
    {
        //缩短长度
        $domain_hash = md5($domain);
        $instruct = strtolower($instruct);
        switch ($instruct) {
            case 'increase':
                $sql = 'UPDATE `access_stat` SET `ip_access_count`=`ip_access_count`+%d,`total_access_count`=`total_access_count`+%d WHERE `domain_hash`="%s"';
                $this->excute_sql($sql, $ip_access_count, $total_access_count, $domain_hash);
                break;
            case 'SET':
                $sql = 'UPDATE `access_stat` SET `ip_access_count`=%d,`total_access_count`=%d WHERE `domain_hash`="%s"';
                $this->excute_sql($sql, $ip_access_count, $total_access_count, $domain_hash);
                break;
            case 'insert':
                $sql = 'INSERT INTO `access_stat` (`domain_hash`,`domain`,`ip_access_count`,`total_access_count`) VALUES ("%s","%s", 0, 0)';
                $this->excute_sql($sql, $domain_hash, $domain, $ip_access_count, $total_access_count);
                break;
            default:
                throw new Exception("更新指令不合法");
        }
    }
    /**
     * @description: 查询domian 或path详情
     * @param {string} $domain
     * @param {string} $instruct
     * @param {string} $path
     * @return {*}
     */
    public function query_domain_detail(string $domain, string $instruct = 'path', string $path = '/')
    {
        $domain_hash = md5($domain);
        $instruct = strtolower($instruct);
        switch ($instruct) {
            case 'domain':
                $sql = 'SELECT COUNT(*) `path_count`, COUNT(`ip`) `site_uv`, SUM(`count`) `site_pv` FROM `access_detail` WHERE `domain_hash`="%s"';
                $result = $this->excute_sql($sql, $domain_hash);
                return $result->fetch_assoc();
            case 'path':
                $sql = 'SELECT `path`, COUNT(`ip`) `path_uv`, SUM(`count`) `path_pv` FROM `access_detail` WHERE `domain_hash`="%s" and `path`="%s"';
                $result = $this->excute_sql($sql, $domain_hash, $path);
                return $result->fetch_assoc();
            default:
                throw new Exception("查询指令不合法");
        }
    }
}
// $r = new DBHANDle(DB_CONFIG);
// $r->query_domain('dev.ayouth.xyz', 'value');
// $r->update_record('dev.ayouth.xyz','/test','1.1.1.1');
// $res=$r->query_domain_detail('dev.ayouth.xyz');
// header('Content-Type:text/plain');
// print_r($res);
