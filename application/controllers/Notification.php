<?php
/**
 * Notification Controller
 * Author: Adriene Carre Llanos Amigable
 * Version: 0.2.0
 */

class Notification extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('NotificationModel');
        $this->load->library('Response', NULL, 'response');
    }

    public function get() {
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
    
        try {
            $decoded = decode_jwt($token, $this->config->item('jwt_key'));
    
            if (!empty($decoded->data->user_information)) {
                $user_id = $decoded->data->user_information[0]->user_id;
    
                // Fetch notifications where user is either the sender or receiver
                $notifications = $this->NotificationModel->get_by_user($user_id);
    
                $return = [
                    'isError' => false,
                    'message' => 'Success',
                    'data' => $notifications
                ];
            } else {
                throw new Exception('Invalid user token');
            }
    
        } catch (Exception $e) {
            $return = [
                'isError' => true,
                'message' => $e->getMessage()
            ];
        }
    
        $this->response->output($return);
    }

    
    public function get_by_receiver($receive_by) {
        try {
            $notifications = $this->NotificationModel->get_by_receiver($receive_by);
            $return = [
                'isError' => false,
                'message' => 'Success',
                'data' => $notifications
            ];
        } catch (Exception $e) {
            $return = [
                'isError' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->response->output($return);
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        
     
        $receive_by = $data['receive_by'] ?? null;
        $created_by = $data['created_by'] ?? null;
        $company_id = $data['company_id'] ?? null;
        $title = $data['title'] ?? '';
        $message = $data['message'] ?? '';
      
        if (empty($receive_by) || empty($created_by) || empty($company_id) || empty($title) || empty($message)) {
            $return = [
                'isError' => true,
                'message' => 'All fields are required (receive_by, created_by, company_id, title, message)'
            ];
        } else {
            try {
                $payload = [
                    'receive_by' => $receive_by,
                    'created_by' => $created_by,
                    'company_id' => $company_id,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $notif_id = $this->NotificationModel->add($payload);

                $return = $notif_id
                    ? ['isError' => false, 'message' => 'Notification created successfully', 'data' => $payload]
                    : ['isError' => true, 'message' => 'Failed to create notification'];
            } catch (Exception $e) {
                $return = ['isError' => true, 'message' => $e->getMessage()];
            }
        }

        $this->response->output($return);
    }

    public function mark_as_read() {
        $id = $this->input->post('id');

        if (empty($id)) {
            $return = ['isError' => true, 'message' => 'Notification ID is required'];
        } else {
            try {
                $updated = $this->NotificationModel->mark_as_read($id);
                $return = $updated
                    ? ['isError' => false, 'message' => 'Notification marked as read']
                    : ['isError' => true, 'message' => 'Failed to update notification'];
            } catch (Exception $e) {
                $return = ['isError' => true, 'message' => $e->getMessage()];
            }
        }

        $this->response->output($return);
    }

    public function delete() {
        $id = $this->input->post('id');

        if (empty($id)) {
            $return = ['isError' => true, 'message' => 'Notification ID is required'];
        } else {
            try {
                $deleted = $this->NotificationModel->delete($id);
                $return = $deleted
                    ? ['isError' => false, 'message' => 'Notification deleted successfully']
                    : ['isError' => true, 'message' => 'Failed to delete notification'];
            } catch (Exception $e) {
                $return = ['isError' => true, 'message' => $e->getMessage()];
            }
        }

        $this->response->output($return);
    }
}
?>
