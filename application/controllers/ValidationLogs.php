<?php
/**
 * Validation Logs Controller
 * Author: Your Name
 * Date Created: 3/12/2025
 * Version: 1.0.0
 */

class ValidationLogs extends MY_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('ValidationLogsModel');
        $this->load->model('UserActivityLogModel');
        $this->load->library('Response', NULL, 'response');
    }

    // Add a new validation log entry
    public function create() {
        try {
            $user_id = $this->input->post('user_id');
            $validation_logs = $this->input->post('validation_logs');
            $date_added = date('Y-m-d H:i:s');

            if (empty($user_id)) {
                $return = ['isError' => true, 'message' => 'User ID is required'];
            } else {
                $payload = [
                    'user_id' => $user_id,
                    'validation_logs' => $validation_logs,
                    'date_added' => $date_added
                ];

           

                $response = $this->ValidationLogsModel->add($payload);
                if ($response) {
                    $return = ['isError' => false, 'message' => 'Validation log added successfully', 'data' => $payload];
                } else {
                    $return = ['isError' => true, 'message' => 'Failed to add validation log'];
                }
            }
        } catch (Exception $e) {
            $return = ['isError' => true, 'message' => $e->getMessage()];
        }

        $this->response->output($return);
    }

    // Get validation logs
    public function get() {
        try {
            $user_id = $this->input->get('user_id');
            $is_active = $this->input->get('is_active');

            $filters = [];
            if (!empty($user_id)) {
                $filters['user_id'] = $user_id;
            }
            if (!empty($is_active)) {
                $filters['is_active'] = $is_active;
            }

            $logs = $this->ValidationLogsModel->get($filters);

            if ($logs) {
                $return = ['isError' => false, 'message' => 'Validation logs fetched successfully', 'data' => $logs];
            } else {
                $return = ['isError' => true, 'message' => 'No validation logs found','data'=>[]];
            }
        } catch (Exception $e) {
            $return = ['isError' => true, 'message' => $e->getMessage()];
        }

        $this->response->output($return);
    }
}
