<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require './vendor/autoload.php';
use \Firebase\JWT\JWT;

class MY_Controller extends CI_Controller {

    protected $user_data;

    public function __construct() {
        parent::__construct();
        $this->load->library('Response',NULL,'response');
        $this->load->helper('jwt');
        $this->key = $this->config->item('jwt_key'); // Ensure this key is in your config
        // Check token
        $this->validate_token();
    }

    private function validate_token() {
        try {
            $headers = $this->input->request_headers();
            error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting
    
            // Retrieve the token from Authorization header
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
            if (!$token) {
                $this->send_error_response('No token provided.', 401);
            }
    
            // Remove 'Bearer ' prefix if present
            if (strncasecmp($token, 'Bearer ', 7) === 0) {
                $token = substr($token, 7);
            }
    
            // Check if the token is blacklisted
            if ($this->getBlackListToken($token) > 0) {
                $this->send_error_response('Token is invalid or expired.', 401);
            }
    
            // Decode and verify token
            $decoded = decode_jwt($token, $this->key);
    
            // Validate decoded token structure
            if (!$decoded || !is_object($decoded) || !isset($decoded->data) || !is_object($decoded->data)) {
                error_log('Invalid token structure: ' . print_r($decoded, true));
                $this->send_error_response('Invalid token.', 401);
            }
    
            // Validate user_id
            if (!isset($decoded->data->user_id) || empty($decoded->data->user_id)) {
                error_log('Token missing user_id: ' . print_r($decoded->data, true));
                $this->send_error_response('Invalid user in token.', 401);
            }
    
            return true; // Return user_id for further processing
    
        } catch (Throwable $e) {
            error_log('Token Validation Error: ' . $e->getMessage());
            $this->send_error_response('An error occurred while processing the token.', 500);
        }
    }
    
    
    
    /**
     * Utility function to send error responses and exit
     */
    private function send_error_response($message, $status_code) {
        $response = array('isError' => true, 'message' => $message);
        $this->response->output($response, 0, $status_code);
        exit;
    }
    

    private function getBlackListToken($token){
        $sql = "SELECT blacklist_token.token FROM blacklist_token
                WHERE blacklist_token.token = '$token'";
        return $this->db->query($sql)->num_rows();
    }
}