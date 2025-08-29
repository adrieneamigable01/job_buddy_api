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
                'smtp_user'     =>'doitcebu@gmail.com',
                'smtp_pass'     =>'eevr neue qktl pssn',
                'smtp_port'     =>'465',
                'validate'      =>'true',
                'encrypt'       =>'ssl',
                'from_name'     => 'JOB BUDDY',
                'from_email'    =>'no-reply@jobbuddy.com',
                'reply'         =>'no-reply@jobbuddy.com',
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

        public function sendEmail($body,$email,$subject,$cc = "",$decoded_pdf_data = ""){
            
            $email       = $email;
            $send = $this->send($body,$email,$subject,$cc,$decoded_pdf_data);
            return $send;
        }

        private function send($body,$recipient = 'noreply@gmail.com',$subject = "",$cc = "",$decoded_pdf_data = ""){

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
            $send->AddEmbeddedImage(FCPATH . 'assets/img/logo.png', 'logoimg', 'logo.png');
            $send->Subject = $subject.' '.date("F d, Y H:i:s");
            $body .= "<div style='margin-top:15px;'><img src='cid:logoimg' width='100' height='100' style='float-left'></div>";
            $send->Body = $body;
             
            if(!empty($decoded_pdf_data)){
               
                // Find the position of the comma
                $commaPosition = strpos($decoded_pdf_data, ',');

                // Extract the base64 string after the comma
                $base64String = substr($decoded_pdf_data, $commaPosition + 1);

                // Decode the base64 string to get the binary data
                $pdfBinaryData = base64_decode($base64String);
                $send->addStringAttachment($pdfBinaryData, 'contract.pdf', 'base64', 'application/pdf');
            }
            

            if(!empty($cc)){
                // $cc = explode(",", $cc);
                // for ($i=0; $i < sizeof($cc); $i++) { 
                //     $send->AddCC($cc[$i]);
                // }
                 $send->AddCC($cc);
            }
           
            
            if($send->send()){
               return true;
            }else{
                return false;
            }
        }
        
    }
?>
