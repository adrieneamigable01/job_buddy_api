<?php
/**
 * User Logs Controller
 * Author: Your Name
 * Date Created: 3/12/2025
 * Version: 1.0.0
 */

class UserActivityLog extends MY_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('UserActivityLogModel');
        $this->load->library('Response', NULL, 'response');
    }

    
    // Get user Logs
    public function get() {
        try {
            // Get parameters
            $user_id = $this->input->get('user_id');
            
            // Create payload for filtering the Logs
            $payload = array();
            if (!empty($user_id)) {
                $payload['user_id'] = $user_id;
            }

            // Call the model method to get Logs
            $activity_logs = $this->UserActivityLogModel->get($payload);
            
            if ($activity_logs) {
                $return = array(
                    'isError' => false,
                    'message' => 'Logs fetched successfully',
                    'data' => $activity_logs
                );
            } else {
                $return = array(
                    'isError' => true,
                    'message' => 'No Logs found',
                );
            }
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage(),
            );
        }

        // Output the response
        $this->response->output($return);
    }
}
?>
