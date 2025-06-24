<?php
// 在脚本最开头添加执行时间限制（0表示无限制）
//set_time_limit(0);
include_once 'config.php';

// LDAP 配置
$ldapServer = LDAP_SERVER;
$ldapUser = LDAP_ADMIN_DN;
$ldapPassword = LDAP_ADMIN_PASSWORD;
$ldapBaseDn = LDAP_BASE_DN;
$ldapFilter = LDAP_FILTER;

// 企业微信群聊机器人 webhook 配置
$webhookUrl = NOTICE_WEBHOOK;

// 获取用户信息接口
$bs_api = USER_API;

// 发送企业微信群聊机器人消息
function sendWechatGroupRobotMessage($webhookUrl, $message) {
    $data = [
        "msgtype" => "text",
        "text" => [
            "content" => $message
        ]
    ];
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($data)
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($webhookUrl, false, $context);
    return $response;
}
//获取用户信息
function getBsUserInfo($bs_api,$email) {
        $bs_url = $bs_api.$email;
        //获取用户信息
        $ch = curl_init($bs_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 新增超时设置
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 连接超时10秒
        // curl_setopt($ch, CURLOPT_TIMEOUT, 30);        // 总超时30秒
        
        $get_user_info = curl_exec($ch);
        // 检查 cURL 请求是否成功
        if ($get_user_info === false) {
            $error = curl_error($ch);
            curl_close($ch);
            // 处理错误，这里简单返回 null 表示获取信息失败
            return null;
        }
        curl_close($ch);
        $bs_data = json_decode($get_user_info, true);
        // 检查 JSON 解码是否成功
        if ($bs_data === null) {
            // 处理错误，这里简单返回 null 表示解码失败
            return null;
        }
        $bs_empStatus = $bs_data['empStatus'] ?? null;  //获取用户状态
    
        // 根据北森用户状态更新 LDAP 条目信息
        if ($bs_empStatus ==='8') {
            return 1;
        } else {
            return 0;
        }
}

// 检测 LDAP 用户是否离职
function checkLdapUsers($ldapServer, $ldapUser, $ldapPassword, $ldapBaseDn, $ldapFilter, $webhookUrl) {
    echo "[" . date('Y-m-d H:i:s') . "] 开始执行LDAP用户离职检查...\n";
    
    $ldapconn = ldap_connect($ldapServer) or die("Could not connect to LDAP server.");
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    $ldapbind = ldap_bind($ldapconn, $ldapUser, $ldapPassword) or die("Error trying to bind: ".ldap_error($ldapconn));

    $ldapSearch = ldap_search($ldapconn, $ldapBaseDn, $ldapFilter, array("cn", "uid","mail","givenname"));
    $ldapEntries = ldap_get_entries($ldapconn, $ldapSearch);
    echo "发现待检查用户数：" . $ldapEntries['count'] . "\n";
    
    $usernames = [];
    for ($i = 0; $i < $ldapEntries['count']; $i++) {
        $email = $ldapEntries[$i]['mail'][0] ?? null;
        $dn = $ldapEntries[$i]['dn']; // 获取用户DN
        echo "正在处理用户：" . ($email ?? '无邮箱用户') . "\n";
        
        if ($email) {
            global $bs_api;
            $isLeft = getBsUserInfo($bs_api, $email);
            if ($isLeft === 1) {
                // 执行LDAP删除操作
                if (@ldap_delete($ldapconn, $dn)) {
                    echo "成功删除用户：" . $ldapEntries[$i]['givenname'][0] . "\n";
                    $usernames[] = $ldapEntries[$i]['givenname'][0];
                } else {
                    echo "删除失败：".ldap_error($ldapconn)."\n";
                }
            }
        }
    }
    if (!empty($usernames)) {
        $usernameList = implode(', ', $usernames);
        $message = "LDAP离职用户汇总：{$usernameList}，已自动删除账号。";
        echo "准备发送通知：" . $message . "\n";
        sendWechatGroupRobotMessage($webhookUrl, $message);
    } else {
        echo "未发现离职用户\n";
    }
    ldap_close($ldapconn);
    echo "[" . date('Y-m-d H:i:s') . "] 检查完成\n";
}

// 执行检测
checkLdapUsers($ldapServer, $ldapUser, $ldapPassword, $ldapBaseDn, $ldapFilter, $webhookUrl);
?>