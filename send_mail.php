<?php
include_once('inc/PHPMailer.php');
include_once 'config.php';
function send_mail($address,$name,$username,$password,&$result){
	if($address == null ||$name ==null || $username == null || $password == null){
		echo "信息校验失败";
		exit;
	}
	$mail = new PHPMailer(true);
	try {
		$mail->isSMTP();
		$mail->Host       = MAIL_HOST;  // SMTP服务器地址
		$mail->SMTPAuth   = true;
		$mail->Username   = MAIL_USER;   // SMTP 用户名
		$mail->Password   = MAIL_PASS;            // SMTP 密码
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 加密方式
		$mail->Port       = MAIL_PORT;                 // SMTP 端口

		//Recipients
		$mail->setFrom(MAIL_USER, 'IT支持');
		$mail->addAddress($address, $name); // 添加收件人
		//Content
		$mail->isHTML(true);
		$mail->Subject = '【IT支持】LDAP账号开通通知';
		$mail_content = '您好，'.$name.'：<br><br>您所申请的LDAP账号已开通，以下是您登录和开始使用账号的相关信息：<br><br>账号：'.$username.'<br><br>密码：'.$password.'<br><br>修改密码：https://it.example.com/ldap-reset/<br><br>
			获取更多信息：<br><br>-  IT导航页：<a href="https://it.example.com/">https://it.example.com/</a>（需公司内网访问）
				<br>';
		$mail_sign = '<div class="qqmail_sign" id="wemailsigcontent" signid="200">
		                <div class="rich_custom_signature" style="position: relative;">
		                    <p><b>IT组 </b><b>| </b><b>运维部</b></p>
		                    <div>重要提示：本邮件及附件具保密性质，可能包含商业秘密及根据法律享有特权或不得披露的信息。</div>
		                    <div>如果您意外收到此邮件，特此提醒您本邮件的机密性，请立即回复邮件通知我们并从您的系统中删除本邮件及附件。</div>
		                    <div>如果您不是本邮件应当的收件人，请注意不可利用、复制本邮件及其附件内容或向他人披露该等内容。</div>
		                </div>
		                <br>
		                <div style="font-size: 11pt;color: #000;line-height: 1.43;"><br>
		                </div>
		                <div style="font-size: 11pt;color: #000;line-height: 1.43;"><br>
		                </div>
		            </div>';
		$mail->Body = $mail_content.'<br>'.$mail_sign; // 邮件内容

		$mail->send();
		$result = '邮件发送成功！！';
	} catch(Exception $e) {
		$result = "邮件发送失败. 原因: {$mail->ErrorInfo}";
	}
	
}

