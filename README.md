# 基于PHP开发的LDAP账号创建/删除工具
# 痛点分析
- IT运维中手动创建LDAP账号的重复性工作现状
- 传统流程耗时、易出错、难以批量处理的局限性
- 自动化需求在团队协作、权限管理中的必要性
# 一、功能设计
## 1、管理员登录功能
- 需要登录才能使用该创建工具，避免未授权的账号创建；
- 管理员账号密码使用本地文件管理，无需数据库；
## 2、账号创建功能
- 使用邮箱号通过HR系统接口获取人员基础信息
- 根据人员信息一键生成LDAP账号
## 3、发送邮件功能
- 账号创建后通过邮件下发给申请人；
- 使用PHPMailer发送组件，无需额外的邮件服务器；
## 4、日志功能
- 每次创建记录账号、邮件发送信息，管理员、IP地址等信息到本地log文件；
# 使用
部署环境：Linux+Apache+PHP（5.5+），PHP安装ldap扩展
修改config.php文件中的配置信息
# 页面预览
![架构图](https://github.com/user-attachments/assets/af9fb678-f2f6-43dd-aca1-d1d3ad729624)
![登录页面](https://github.com/user-attachments/assets/a1b1d279-26e8-44ad-b75d-1f26d17e5f56)
![创建页面](https://github.com/user-attachments/assets/a66a3053-101f-4807-afb5-a7a7395b28bc)
![邮件模板](https://github.com/user-attachments/assets/2ecee96c-d959-47d9-aa8e-46d5c0561427)
