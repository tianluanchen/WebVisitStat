<?php
/*
 * @Author       :  Ayouth
 * @Date         :  2022-05-01 GMT+0800
 * @LastEditTime :  2022-05-01 GMT+0800
 * @FilePath     :  admin.php
 * @Description  :  管理员api 尚未完成
 * Copyright (c) 2022 by Ayouth, All Rights Reserved. 
 */
require_once(__DIR__ . '/db_handle.php');
require_once(__DIR__ . '/config.php');

try {
    main();
} catch (\Throwable $th) {
    print '{"code":500,"message":"服务器内部错误"}';
}

function main()
{
    // 设置json响应头
    header('Content-Type:application/json; charset=UTF-8');
    //过滤非法
    request_filter();
    //身份认证处理 包含登录退出等
    auth_process();
    // 事务处理
    $request_method = $_SERVER['REQUEST_METHOD'];
    if ($request_method == 'GET') {
    } else if ($request_method == 'POST' && get_value($_POST, 'instruct')) {
        $handle = new DBHandle(DB_CONFIG);
        switch ($_POST['instruct']) {
                // 添加新域名
            case 'insert':
                $domain = get_value($_POST, 'domain', 'illegal domain');
                $uv = get_value($_POST, 'domain', 0);
                $pv = get_value($_POST, 'domain', 0);
                if (is_validate_domain($domain)) {
                    if ($handle->query_domain($domain)) {
                        send_res('域名已经存在', 400);
                    } else {
                        $handle->update_domain($domain, 'insert', (int)($uv), (int)($pv));
                        send_res('域名添加成功', 200);
                    }
                } else {
                    send_res('非法域名', 400);
                }
                break;
            default:
                send_res('参数错误', 400);
        }
    }
    send_res('非法的方法或无法处理的请求', 400);
}


/**
 * @description: 身份认证相关处理
 * @param {*}
 * @return {*}
 */
function auth_process()
{
    // 开启会话
    session_start([
        "cookie_httponly" => true,
        "cookie_samesite" => "Strict"
    ]);
    if (get_value($_SESSION, 'webstatadmin') == ADMIN_ACCOUNT) {
        $is_admin = true;
    } else {
        $is_admin = false;
    }
    // 退出登录
    if (get_value($_GET, 'logout')) {
        if ($is_admin) {
            unset($_SESSION['webstatadmin']);
            send_res('已退出认证状态', 200);
        } else {
            send_res('还未曾认证', 400);
        }
    }
    // 查询认证
    if (get_value($_GET, 'auth')) {
        if ($is_admin) {
            send_res(['auth' => true, 'message' => '查询为已认证'], 200);
        } else {
            send_res(['auth' => false, 'message' => '查询为未认证'], 200);
        }
    }
    if (!$is_admin) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['account']) && isset($_POST['password'])) {
            if ($_POST['account'] == ADMIN_ACCOUNT && $_POST['password'] == ADMIN_PASSWORD) {
                $_SESSION['webstatadmin'] = ADMIN_ACCOUNT;
                send_res("登录成功", 200);
            } else {
                send_res("用户或密码错误", 400);
            }
        }
        send_res("权限尚未认证", 400);
    }
}


/**
 * @description: 从数组中取值
 * @param {array} $arr
 * @param {*} $index
 * @param {*} $default
 * @return {*}
 */
function get_value(array $arr, $index, $default = null)
{
    if (isset($arr[$index])) {
        return $arr[$index];
    } else {
        return $default;
    }
}


/**
 * @description: 发送响应
 * @param {*} $arr
 * @param {int} $code
 * @return {*}
 */
function send_res($res, int $code = 0)
{
    if (is_array($res)) {
        if ($code != 0) {
            if (isset($res['code'])) {
                unset($res['code']);
            }
            $res = ['code' => $code] + $res;
        }
        print json_encode($res);
    } else {
        print json_encode(['code' => $code, 'message' => (string)($res)]);
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
    //  判断是否引用
    if (!isset($_SERVER["HTTP_REFERER"]) || !filter_var($_SERVER["HTTP_REFERER"], FILTER_VALIDATE_URL) || !parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH)) {
        send_res('拒绝直接请求', 400);
    } else {
        $domain = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
        $port = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PORT);
        $port = $port ? ':' . (string)($port) : '';
        $host = $domain . $port;
        if (!in_array($host, ALLOWED_HOSTS)) {
            send_res('未许可的API引用', 400);
        }
    }
}


/**
 * @description: 判断域名合法否
 * @param {string} $domain
 * @return {*}
 */
function is_validate_domain(string $domain)
{
    $domain = trim($domain);
    $validate = 1;
    $validate = filter_var($domain, FILTER_VALIDATE_DOMAIN) ? 1 : 0;
    $arr = explode('.', $domain);
    if (count($arr) < 2) {
        $validate -= 1;
    } else {
        foreach ($arr as $value) {
            $p = '/^[A-Za-z0-9_-]+$/';
            if (preg_match($p, $value) == 0) {
                $validate -= 1;
            };
        }
    }
    return $validate > 0 ? true : false;
}
