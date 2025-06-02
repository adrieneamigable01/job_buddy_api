<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Otp extends MY_Controller {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('OTPModel');
        $this->CI->load->library('EmailLib',NULL,'emailib');
    }

    // Generate OTP and send to user (or display it for testing)
    
    public function generate_otp($data) {
        $this->remove_used_otps($data['userid']); // Remove used OTPs
        $otp = $this->generate($data);
        return $this->CI->emailib->sendOTP($otp);
    }

    // Validate OTP
    public function validate_otp($data,$deleteOTP = 1) {
        if ($this->validate($data)) {
            if($deleteOTP){
                $this->remove_used_otps($data['userid'],$data['action']); // Remove used OTPs
            }
           return true;
        } else {
            return false;
        }
    }
    


    public function generate($payload) {
        $otp = random_int(100000, 999999); // 6-digit OTP
        $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); // Valid for 5 minutes

        $data = array(
            'userid' => $payload['userid'],
            'name' => $payload['name'],
            'otp' => $otp,
            'expires_at' => $expires_at,
            'action' => $payload['action'],
            'foreign_id'=> $payload['foreign_id'],
            'email'=> $payload['email'],
        );

        $this->CI->db->insert('otp_codes', $data);
        return $data;
    }

    // Function to validate OTP
    public function validate($data) {
        $this->CI->db->where('userid', $data['userid']);
        $this->CI->db->where('otp', $data['otp']);
        $this->CI->db->where('action', $data['action']);
        $this->CI->db->where('is_active', 1);
        $this->CI->db->where('expires_at >=', date('Y-m-d H:i:s'));
        $query =$this->CI->db->get('otp_codes');

        return $query->num_rows() > 0;
    }

    // Function to remove used OTPs
    public function remove_used_otps($user_id) {
        $this->CI->db->where('userid', $user_id);
        $data = array(
            'is_active' => 0
        );
        $this->CI->db->update('otp_codes',$data);
    }
}
?>