<?php
//post信息处理
error_reporting(E_ALL);
if (session_status() == PHP_SESSION_NONE) {
    // session不存在，启动session
    session_start();
 
    // 检查特定的session变量是否存在
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        header('Location: login.html');
        exit; // 确保之后的代码不会执行
    }
}
header('Location: index.html');
var_dump($_SESSION['username']);