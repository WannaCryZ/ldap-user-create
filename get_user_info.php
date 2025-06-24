<?php
include_once 'config.php';
if (session_status() == PHP_SESSION_NONE) {
    // session不存在，启动session
    session_start();
 
    // 检查特定的session变量是否存在
    if (!isset($_SESSION['username'])) {
        $return = array('code'=>'9999','status'=>'false','message'=>'请先登录');
		header('Content-Type: application/json');
		echo json_encode($return);
		exit;
    }
}
$email = $_POST['email'];
if (!isset($email) || $email == null) {
	$return = array('code'=>'9999','status'=>'false','message'=>'email不能为空');
	header('Content-Type: application/json');
	echo json_encode($return);
	exit;
}
$bs_api = USER_API;
$get_user_info_api = $bs_api.$email;
$ch = curl_init($get_user_info_api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$get_user_info = curl_exec($ch);
curl_close($ch);
$user_info = json_decode($get_user_info,true);
//var_dump($user_info);
//var_dump($user_info['message']);
if($get_user_info == false || $get_user_info == null || $user_info['message'] == "接口错误"){
	$return = array('code'=>'9999','status'=>'false','message'=>'获取信息失败');
	header('Content-Type: application/json');
	echo json_encode($return);
	exit;
}
//邮箱字符串处理
$email_prefix = strstr($email, '@', true);
//var_dump($email_prefix);
$search_sn = $email_prefix;
//ldap信息获取
$ldapconn = ldap_connect(LDAP_SERVER) or die("Could not connect to LDAP server.");
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
$ldapbind = ldap_bind($ldapconn, LDAP_ADMIN_DN, LDAP_ADMIN_PASSWORD) or die("Error trying to bind: ".ldap_error($ldapconn));
$searchBase = LDAP_BASE_DN;
$searchFilter = '(sn='.$search_sn.')';
$basedn = LDAP_BASE_DN;
$ldapSearch = ldap_search($ldapconn, $searchBase, $searchFilter, array("cn","uidnumber","givenname","mail","telephonenumber","description","createtimestamp","userpassword"));
$ldap_message = '';
$is_ldap_user = '';
if (!$ldapSearch) {
    $ldap_message = "LDAP search failed.";
    //exit;
}
$ldapEntries = ldap_get_entries($ldapconn, $ldapSearch);
//var_dump($ldapSearch);
//var_dump($ldapEntries);
if ($ldapEntries['count'] === 0) {
    $ldap_message = "User not found in LDAP directory.";
    $ldap_status = 0;
}else{
	$ldap_message = "User [".$search_sn."] found in LDAP directory.";
	$ldap_status = 1;
}
ldap_close($ldapconn);
//返回信息
$name = $user_info['name'];
$org = $user_info['department_treepath'];
$mobile = substr($user_info['mobile'],0,3).'****'.substr($user_info['mobile'],7,10);
$job = $user_info['role'];
$email = $user_info['email'];
$data = array(
	'name' => $name,
	'org' => $org,
	'mobile'=> $mobile,
	'job'=> $job,
	'email' => $email,
	'ldap_status' => $ldap_status,
	'emp_status' => $user_info['empStatus'],
	'username' => $email_prefix,
	'clear_mobile' => $user_info['mobile']
	 );
$return = array('code'=>'0','status'=>'success','message'=>'获取成功', 'ldap_message' => $ldap_message, 'data'=>$data);
header('Content-Type: application/json');
print_r(json_encode($return));
//var_dump($user_info);


