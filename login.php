<?php
session_start();
error_reporting(E_ERROR);
// 设置session时长为1小时（3600秒）
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
$username = $_POST['username'];
$password = $_POST['password'];
 
$accountsFile = 'asset/accounts.txt'; // 账号密码文件路径
 
// 检查文件是否存在
if (!file_exists($accountsFile)) {
    die('Account file not found.');
}
    
// 读取文件并检查凭证
$accounts = file($accountsFile, FILE_IGNORE_NEW_LINES);
 
foreach ($accounts as $account) {
    list($storedUsername, $storedPassword) = explode(':', $account);
    
    if ($username == $storedUsername && $password == $storedPassword) {
        $_SESSION['username'] = $username;
        $return = array('code'=>'0','status'=>'success','message'=>'登录成功');
        header('Content-Type: application/json');
        echo json_encode($return);
        break;
    }

}
 
if ($username !== $storedUsername) {
   $return = array('code'=>'9999','status'=>'false','message'=>'账号不对');
        header('Content-Type: application/json');
        echo json_encode($return);
} elseif ($password !== $storedPassword) {
    $return = array('code'=>'9999','status'=>'false','message'=>'密码不对');
        header('Content-Type: application/json');
        echo json_encode($return);
}
?>