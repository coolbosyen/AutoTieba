<?php

namespace Libs;

require_once(PUBLIC_PATH . 'PHPMailer/class.phpmailer.php');
require_once(PUBLIC_PATH . 'PHPMailer/class.smtp.php');

/**
 * Email包装类
 */
class Email {

    /**
     * 
     * @param type $email                 邮件地址
     * @param type $username          邮件标题
     * @param type $info                    邮件正文
     * @return boolean                        发送结果
     */
    static public function send($email, $username, $info) {
        $mail = new \PHPMailer();
        $mail->CharSet = 'UTF-8';                 //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置为 UTF-8
        $mail->IsSMTP();                            // 设定使用SMTP服务
        $mail->SMTPAuth = true;                   // 启用 SMTP 验证功能
        $mail->SMTPSecure = config('SMTPSecure');    // SMTP 安全协议
        $mail->Host = config('host');               // SMTP 服务器
        $mail->Port = config('port');                    // SMTP服务器的端口号
        $mail->Username = config('username');  // SMTP服务器用户名
        $mail->Password = config('password');    // SMTP服务器密码
        $mail->SetFrom(config('username'), config('name'));    // 设置发件人地址和名称
        $mail->AddReplyTo(config('username'), config('name')); // 设置邮件回复人地址和名称
        $mail->Subject = $username;                     // 设置邮件标题
        $mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端"; // 可选项，向下兼容考虑
        $mail->MsgHTML($info);                         // 设置邮件内容
        $mail->AddAddress($email, $username);
        //$mail->AddAttachment("images/phpmailer.gif"); // 附件 
        if (!$mail->Send()) {
            return "邮件发送失败：" . $mail->ErrorInfo;
        } else {
            return "邮件发送成功。";
        }
    }

}
