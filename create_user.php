<?php
//post信息处理
//error_reporting(E_ALL);
include_once 'config.php';
if (session_status() == PHP_SESSION_NONE) {
    // session不存在，启动session
    session_start();
 
    // 检查特定的session变量是否存在
    if (!isset($_SESSION['username'])) {
        $return = array('code'=>'9999','status'=>'false','message'=>'请先登录');
		header('Content-Type: application/json');
		echo json_encode($return);
    }
}
$user_info = $_POST;
//var_dump($user_info);
if (!isset($user_info) || $user_info == null) {
	$return = array('code'=>'9999','status'=>'false','message'=>'user_info不能为空');
	header('Content-Type: application/json');
	echo json_encode($return);
	exit;
}
/**
 * SSHA加密算法
 * @param $password  需要加密的字符串
 * @return 返回加密好的字符串
 * */
function ldap_ssha($password){
	$salt = "";
	for ($i=1; $i<=10; $i++){
          $salt .= substr('0123456789abcdef',rand(0,15),1);
     }
	$hash = "{SSHA}" . base64_encode(pack("H*",sha1($password.$salt)).$salt);
	return $hash;
}
/**
 * 随机密码生成
 * @return 返回随机字符串
 * */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

$username = $_POST['username'];
$mail = $_POST['email'];
$givenName = $_POST['givenname'];
$telephoneNumber = $_POST['mobile'];
$password = generateRandomString(12);
$ssha_password = ldap_ssha($password);
$org = $_POST['org'];
$group = $_POST['select_group'];
//ldap信息获取
//echo $password;
$ldapconn = ldap_connect(LDAP_SERVER) or die("Could not connect to LDAP server.");
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
$ldapbind = ldap_bind($ldapconn, LDAP_ADMIN_DN, LDAP_ADMIN_PASSWORD) or die("Error trying to bind: ".ldap_error($ldapconn));
//不同的ou
$searchBase = LDAP_BASE_DN;
if ($group == "business") {
	$basedn = 'ou=business,'.LDAP_BASE_DN;
}else{
	$basedn = LDAP_BASE_DN;
}
$searchFilter = '(sn=*)';
//搜索LDAP信息，获取NextuidNumber
$ldapSearch = ldap_search($ldapconn, $searchBase, $searchFilter, array("uidNumber"))or die('搜索失败');
$ldapEntries = ldap_get_entries($ldapconn, $ldapSearch);
$maxuidNumber =0;
for ($i=0; $i < $ldapEntries['count'] ; $i++) { 
	if (!empty($ldapEntries[$i]['uidnumber'][0])) {
        $currentuidNumber = $ldapEntries[$i]['uidnumber'][0];
        if ($currentuidNumber > $maxuidNumber) {
            $maxuidNumber = $currentuidNumber;
        }
    }
}
$nextuidNumber = $maxuidNumber + 1;


// 要添加的用户信息
$userDn = "cn=".$username.",".$basedn; // 用户的DN
$userData['cn'] = $username;
$userData['gidNumber'] = '10000';
$userData['givenName'] = $givenName;
$userData['mail'] = $mail;
$userData['objectclass'][0] = "posixAccount";
$userData['objectclass'][1] = "inetOrgPerson";
$userData['objectclass'][2] = "organizationalPerson";
$userData['objectclass'][3] = "person";
$userData['telephoneNumber'] = $telephoneNumber;
$userData['uid'] = $username;
$userData['uidNumber'] = $nextuidNumber;
$userData['sn'] = $username;
$userData['userPassword'] = $ssha_password;
$userData['homedirectory'] = '/home/'.$username;
$userData['loginShell'] = '/bin/bash';
$userData['description'] = $org;
// 添加用户
$ldap_add = ldap_add($ldapconn, $userDn, $userData);
if ($ldap_add==true) {

	//发送邮件通知
	include_once('send_mail.php');
	$r_send_mail = "";
	send_mail($mail, $givenName, $username, $password, $r_send_mail);
	$return_user_info = [
		'name' => $givenName,
		'username' => $username,
		'password' => $password,
		'sendmail' => $r_send_mail
	];
	//保存日志
	$time = date('Y-m-d H:i:s');
	$ip_addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
	$logMessage = $time.",".$return_user_info['name'].",".$return_user_info['username'].",".$return_user_info['password'].",".$ip_addr.",".$r_send_mail."\n";
	// 日志文件路径
	$logFile = 'log/create.log';
	// 将日志信息追加到文件
	file_put_contents($logFile, $logMessage, FILE_APPEND);
	$return = array('code'=>'0','status'=>'success','message'=>'用户创建成功','data'=>$return_user_info);
	header('Content-Type: application/json');
	echo json_encode($return);
} else {
	$return = array('code'=>'9999','status'=>'false','message'=>'无法创建用户账号,请检查信息');
	header('Content-Type: application/json');
	echo json_encode($return);
}
 
// 解除绑定
ldap_unbind($ldapconn);
 
// 关闭连接
//ldap_close($ldapconn);

exit;
