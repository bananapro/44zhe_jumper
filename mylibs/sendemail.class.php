<?php

require_once MYLIBS . 'email' . DS . 'phpmailer.class.php';

class sendemail {

	var $mail_config = array(
		"host" => EMAIL_HOST,
		"username" => EMAIL_FROM_EMAIL,
		"password" => EMAIL_FROM_PASSWD,
		"fromname" => EMAIL_FROM_NAME,
		"is_ssl" => '',
		"port" => EMAIL_PORT,
		"smtpauth" => EMAIL_AUTH
	);

	var $paras = array();
	var $template_content = '';

	function __construct($mailer_content, $template) {

		if(!$template)return false;
		$this->paras = $mailer_content;
		$this->template_content = $this->_loadTemplate($template);
	}

	/**
	 * $mailer_content =array
	 *
	 * @param unknown_type $mailer_content
	 * @return array  status(0/1) msg(error detail)
	 */
	function send() {

		$mail_config = $this->mail_config;

		$mail = new PHPMailer(true);
		try {
			$mail->IsSMTP();  // SMTP
			//$mail->SMTPDebug   =2;							  //开启debug
			$mail->SMTPAuth = $mail_config["smtpauth"];   // 开启SMTP授权
			$mail->Host = $mail_config["host"]; // SMTP server
			$mail->SMTPSecure = $mail_config["is_ssl"];  //gmail需要这个
			$mail->Port = $mail_config['port']; //默认25

			$mail->CharSet = "UTF-8";
			$mail->Encoding = "base64";

			$mail->Username = $mail_config["username"];  // SMTP server username
			$mail->Password = $mail_config["password"];  // SMTP server password

			$mail->AddReplyTo($mail_config["username"], $mail_config["fromname"]);  //

			$mail->From = $mail_config["username"]; //   发送邮箱地址
			$mail->FromName = $mail_config["fromname"];

			foreach ($this->paras["emails"] as $value) {
				$email_arr = explode("@", $value);
				$email_pre = $email_arr[0];
				$mail->AddAddress($value, $email_pre); // 接收方
			}

			$subject = "=?UTF-8?B?" . base64_encode($this->_parseTemplateTitle()) . "?=";
			$mail->Subject = $subject;   // 邮件标题
			$mail->AltBody = @$this->paras["altbody"]; // 提示内容
			$mail->MsgHTML($this->_parseTemplateContent());
			$mail->IsHTML(true); // send as HTML
			$mail->Send();
			return array('status'=>1);
		}
		catch (phpmailerException $e) {
			return array('status'=>0, 'msg'=>$e->errorMessage());
		}
	}

	function _parseTemplateContent() {

		$content = $this->template_content;
		if (!$content)
			return;

		$content = $this->_parse($content, $this->paras);
		$content = preg_replace('/{{.+}}/', '', $content);
		$content = trim($content);

		return $content;
	}

	function _parseTemplateTitle() {

		$content = $this->template_content;
		if (!$content)
			return;

		$content = $this->_parse($content, $this->paras);
		preg_match('/{{.+}}/', $content, $match);
		$title = preg_replace('/{{(.*)}}/', '\1', $match[0]);
		return $title;
	}

	function _parse($content, $paras){

		foreach ($paras as $k => $v) {
			if($k == 'emails')
								continue;
			$content = str_replace('{' . $k . '}', $v, $content);
		}
		$content = str_replace('{date}', date('Y-m-d'), $content);

		return $content;
	}

	function _loadTemplate($template) {

		$path = ROOT . DS . MYLIBS . 'email' . DS;
		$template = file_get_contents($path . 'templates' . DS . $template . '.html');
		return $template;
	}

}

?>
