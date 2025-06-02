<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cors {

    public function set_cors_headers() {
        $CI =& get_instance();
        $CI->output
            ->set_header('Access-Control-Allow-Origin: *') // Adjust as needed for security
            ->set_header('Access-Control-Allow-Methods: GET, POST, OPTIONS')
            ->set_header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');

        // Handle OPTIONS request
        if ($CI->input->server('REQUEST_METHOD') === 'OPTIONS') {
            $CI->output
                ->set_status_header(200)
                ->set_output('');
            exit;
        }
    }
}