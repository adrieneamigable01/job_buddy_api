<?php
date_default_timezone_set('Asia/Manila');
defined('BASEPATH') OR exit('No direct script access allowed');
    class EmailLib extends CI_Controller{
        /* Global Variables */
        private $res = array();

        public function __construct() {
            ini_set('max_execution_time', 300); //300 seconds = 5 minutes
            $this->CI =& get_instance();
            $this->CI->load->library('phpmailer_library',NULL,'phpmailer_library');
        }

        public function emailConfirm(){
            $config = [
                'protocol'      => 'smtp',
                'smtp_host'     =>'smtp.gmail.com',
                'smtp_user'     =>'notificationbariliprimelending@gmail.com',
                'smtp_pass'     =>'wuji mvst chmz yhaa',
                'smtp_port'     =>'465',
                'validate'      =>'true',
                'encrypt'       =>'ssl',
                'from_name'     => 'Barili Prime Lending Corp.',
                'from_email'    =>'notificationbariliprimelending@gmail.com',
                'reply'         =>'notificationbariliprimelending@gmail.com',
            ];
            return $config;
        }

        public function otpEmailBody($data){

       
            
            $template = "
                Dear {name}<br><br><br>

                To complete your {action} request, please use the following One-Time Password (OTP): <br><br>

                {otp} <br><br>

                This OTP is valid until [{expires_at}] and can be used only once. If you did not request this OTP, please disregard this email or contact our support team for assistance.
            ";

            $name = isset($data['name']) ? $data['name'] : 'user';
            $resdata = str_replace(
                array('{name}','{otp}','{expires_at}','{action}'),
                array($name,$data['otp'],$data['expires_at'],$data['action']),
                $template
            );

            return $resdata;
        }

        public function sendOTP($data){
           
            $html = $this->otpEmailBody($data);
          
            return $this->send($html,$data['email']);
        }

        public function sendEmail($body,$email,$subject){
            
            $email       = $email;
            $send = $this->send($body,$email,$subject);
            return $send;
        }

        private function send($body,$recipient = 'noreply@gmail.com',$subject = "POS",$cc = ""){
            $default = $this->emailConfirm();
            // print_r($default);exit;
            
            $send =  $this->CI->phpmailer_library->load();
         

            // $send->SMTPDebug = 1; // Enable verbose debug output
            $send->SMTPDebug = 0; 
            $send->isSMTP(); // Set mailer to use SMTP
            $send->Host = $default['smtp_host'];
            $send->SMTPAuth = true; // Enable SMTP authentication
            $send->Username = $default['smtp_user']; // SMTP username
            $send->Password = $default['smtp_pass']; // SMTP password
            $send->SMTPSecure = $default['encrypt']; // Enable TLS encryption, `ssl` also accepted
            $send->Port = $default['smtp_port'];
            $send->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $send->isHTML(true);
            $send->AddReplyTo('no-reply@example.com');
            // Sender information
            $send->setFrom('no-reply@example.com',$subject);
            $send->addAddress($recipient);
            if (isset($_FILES['images'])) {
                $files = $_FILES['images'];
    
                // Loop through each uploaded file
                for ($i = 0; $i < count($files['name']); $i++) {
                    $file_name = $files['name'][$i];
                    $file_tmp = $files['tmp_name'][$i];
    
                    // Add each file as an attachment
                    $send->addAttachment($file_tmp, $file_name);
                }
            }

            $send->Subject = $subject.' '.date("F d, Y H:i:s");
            $send->Body = $body;

            if(!empty($cc)){
                $cc = explode(",", $cc);
                for ($i=0; $i < sizeof($cc); $i++) { 
                    $send->AddCC($cc[$i]);
                }
            }
           
            
            if($send->send()){
               return true;
            }else{
                return false;
            }
        }
        
    }
?>
