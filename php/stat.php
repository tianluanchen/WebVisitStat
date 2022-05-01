<?php
/*
 * @Author       :  Ayouth
 * @Date         :  2022-04-29 GMT+0800
 * @LastEditTime :  2022-05-01 GMT+0800
 * @FilePath     :  stat.php
 * @Description  :  网站统计 jsonp接口 
 * Copyright (c) 2022 by Ayouth, All Rights Reserved. 
 */
require_once(__DIR__.'/db_handle.php');
require_once(__DIR__.'/config.php');

try {
    main();
} catch (\Throwable $th) {
    print 'console.error("服务器内部错误")';
}

function main()
{
    // 基本响应头设置
    header('Access-Control-Allow-Origin: *');
    header('Cache-control: no-store');
    header('Content-Type:application/javascript; charset=UTF-8');
    // 过滤并解析请求
    $info = request_filter();
    $handle = new DBHandle(DB_CONFIG);
    //如果是允许的domain
    if (!$handle->query_domain($info['domain'])) {
        send_res('Unauthenticated domain name ' . $info['domain'], 'wrong');
    }
    // 根据ip选项获取ip
    $ip = get_client_ip(IP_OPTION);
    // 更新记录
    $handle->update_record($info['domain'], $info['path'], trim($ip));

    // 返回数据
    $domain_data = $handle->query_domain($info['domain'], 'value');
    $path_data = $handle->query_domain_detail($info['domain'], 'path', $info['path']);
    $data = array_merge($domain_data, $path_data);
    $data['domain'] = $info['domain'];
    // 自定义排序
    uksort($data, function ($a, $b) {
        $rank = ["domain" => 10, "path" => 9, "site_uv" => 8, "site_pv" => 7, "path_uv" => 6, "path_pv" => 5];
        $a = isset($rank[$a]) ? $rank[$a] : 0;
        $b = isset($rank[$b]) ? $rank[$b] : 0;
        return $b - $a;
    });
    $data = json_encode($data);
    send_res($data, 'success', $info['callback'],$info['purenum']);
}

/**
 * @description: 发送响应
 * @param {string} $res
 * @param {string} $status
 * @param {string} $cb
 * @return {*}
 */
function send_res(string $res, string $status = 'wrong', string $cb = null,bool $purenum =false)
{
    if ($status == 'wrong') {
        print ';!function(){window.alert("' . $res . '")}();';
    } else if ($cb) {
        print 'window.' . $cb . '(' . $res . ')';
    } else if($purenum==false){
        // 脚本自动转换数字到K M
        print '; !function (data) { var dict = { "site_uv": "#ay-site-uv", "site_pv": "#ay-site-pv", "path_uv": "#ay-path-uv", "path_pv": "#ay-path-pv" }; var easyNumber = function (num) { num = parseInt(num); var k, m, p; if (1e6 > num && num >= 1e3) { k = parseInt(num / 1e3); p = parseInt((num % 1e3) / 1e2); if (p == 0) { num = `${k}K`; } else { num = `${k}.${p}K`; } } else if (num >= 1e6) { m = parseInt(num / 1e6); p = parseInt((num % 1e6) / 1e5); if (p == 0) { num = `${m}M`; } else { num = `${m}.${p}M`; } } return num; }; Object.keys(dict).forEach((key) => { var e = document.querySelector(dict[key]); e && (e.textContent = easyNumber(data[key])); }); }(' . $res . ');';
    }else{
        print '; !function (data) { var dict = { "site_uv": "#ay-site-uv", "site_pv": "#ay-site-pv", "path_uv": "#ay-path-uv", "path_pv": "#ay-path-pv" };  Object.keys(dict).forEach((key) => { var e = document.querySelector(dict[key]); e && (e.textContent = data[key]); }); }(' . $res . ');';
    }
    exit(0);
}

/**
 * @description: 解析请求并返回信息数组
 * @param {*}
 * @return {*}
 */
function request_filter()
{
    $info = [];
    $res = '';
    // 只允许GET请求
    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
        $res = 'Bad Request Method ' . $_SERVER['REQUEST_METHOD'];
    };
    // 记录是否回调
    $info['callback'] = isset($_GET['cb']) ? $_GET['cb'] : null;
    $info['callback'] = isset($_GET['callback']) ? $_GET['callback'] : $info['callback'];

    // 设置返回的js脚本是否加入1000 => k的功能
    $info['purenum'] = false;
    if (isset($_GET['purenum'])) {
        $info['purenum'] = true;
    }
    //  判断是否引用
    if (!isset($_SERVER["HTTP_REFERER"]) || !filter_var($_SERVER["HTTP_REFERER"], FILTER_VALIDATE_URL) || !parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH)) {
        $res = 'Illegal Referrer';
    } else {
        $info['ref'] = strtolower($_SERVER["HTTP_REFERER"]);
        $info['path'] = parse_url($info['ref'], PHP_URL_PATH);
        $info['domain'] = parse_url($info['ref'], PHP_URL_HOST);
        $info['port'] = parse_url($info['ref'], PHP_URL_PORT);
    }
    if ($res != '') {
        send_res($res, 'wrong');
    }
    return $info;
}

/**
 * @description: 返回ip
 * @param {*} $type 若0直接remoteaddr 若1 x_forwarded_for  若2 cf-ip
 * @return {*}
 */
function get_client_ip($type = 0)
{
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $remote_addr = $_SERVER['REMOTE_ADDR'];
    } else {
        $remote_addr = 'Non-existent key REMOTE_ADDR';
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $real_ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $real_ip = $remote_addr;
    }
    switch ($type) {
        case 1:
            return $real_ip;
        case 2:
            $cf_ip = $real_ip;
            if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $cf_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
            }
            return $cf_ip;
        default:
            //直接返回remote addr
            return  $remote_addr;
    }
}

/* 
//js
;!function (data) {  
    var dict = {
        "site_uv":"#ay-site-uv",
        "site_pv":"#ay-site-pv",
        "path_uv":"#ay-path-uv",
        "path_pv":"#ay-path-pv"
    };
    var easyNumber = function (num) {
    num = parseInt(num);
    var k, m, p;
    if (1e6 > num && num >= 1e3) {
        k = parseInt(num / 1e3);
        p = parseInt((num % 1e3) / 1e2);
        if (p == 0) {
            num = `${k}K`;
        } else {
            num = `${k}.${p}K`;
        }
    } else if (num >= 1e6) {
        m = parseInt(num / 1e6);
        p = parseInt((num % 1e6) / 1e5);
        if (p == 0) {
            num = `${m}M`;
        } else {
            num = `${m}.${p}M`;
        }
    }
    return num;
}
    Object.keys(dict).forEach((key)=>{
        var e = document.querySelector(dict[key]);
        e&&(e.textContent=data[key]);
    })
}({ 
    "domain": "dev.ayouth.xyz", 
    "path": "\/", 
    "site_uv": "67", 
    "site_pv": "68", 
    "path_uv": "2", 
    "path_pv": "69" 
})

*/