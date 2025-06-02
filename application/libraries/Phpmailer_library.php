<?php
class Phpmailer_library 
{
    public function __construct(){
        log_message('Debug', 'PHPMailer class is loaded.');
    }
	public function load(){
        require_once(APPPATH."third_party/Phpmailer/PHPMailer.php");
        require_once(APPPATH."third_party/Phpmailer/SMTP.php");
        require_once(APPPATH."third_party/Phpmailer/Exception.php");
        $objMail = new \PHPMailer\PHPMailer\PHPMailer;
        return $objMail;
    }
}
?>