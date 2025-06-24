<?php
//LDAP配置
define('LDAP_SERVER', "ldap://localhost:389"); //服务器地址
define('LDAP_ADMIN_DN', 'cn=admin,dc=example,dc=net'); //管理员DN
define('LDAP_ADMIN_PASSWORD', 'ldappassword'); //管理员密码
define('LDAP_BASE_DN', 'ou=demo,dc=example,dc=net'); //搜索DN
define('LDAP_FILTER', '(uid=%s)'); //搜索过滤器

//邮箱服务配置
define('MAIL_HOST', 'smtp.exmail.qq.com'); //SMTP服务器
define('MAIL_PORT', 465); //SMTP端口
define('MAIL_USER', 'it@example.com'); //SMTP账号
define('MAIL_PASS', 'emailpassword'); //SMTP密码
define('RESET_LINK', 'http://localhost/');//填写默认服务器地址

//API配置
define('USER_API', 'https://api.example.com/user/get?email=');//用户信息接口
define('NOTICE_WEBHOOK', 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=key');//webhook通知地址