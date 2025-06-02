<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends MY_Controller {
    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('ChatModel');
        $this->load->library('Response', NULL, 'response');
    }

    public function createThread() {
        $headers = $this->input->request_headers();
        error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting

        // Retrieve the token from Authorization header
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        $decoded = decode_jwt($token, $this->config->item('jwt_key'));

        $created_by = null;
        if (!empty($decoded->data->user_information)) {
            $created_by = $decoded->data->user_information[0]->user_id ?? null; // Adjust based on your actual JWT structure
        }

        // Get the rest of the posted data
        $data = json_decode(file_get_contents("php://input"), true);
        $user1_id = $created_by ?? null;
        $user2_id = $data['user2_id'] ?? null;
        $title    = $data['title'] ?? null;
        $company_id    = $data['company_id'] ?? null;

        if (empty($user1_id) || empty($user2_id || empty($title))) {
            $return = ['isError' => true, 'message' => 'Both user IDs and title by are required'];
        }elseif (empty($company_id)) {
            $return = ['isError' => true, 'message' => 'Company id is required'];
        } elseif (!$this->ChatModel->isValidUser($user1_id) || !$this->ChatModel->isValidUser($user2_id)) {
            $return = ['isError' => true, 'message' => 'One or both user IDs are invalid'];
        } else {
            $data = [
                'user1_id' => $user1_id,
                'user2_id' => $user2_id,
                'created_by' => $created_by,
                'title' => $title,
                'company_id' => $company_id
            ];
            $thread_id = $this->ChatModel->createThread($data);
            $return = ['isError' => false, 'message' => 'Thread created', 'data' => ['thread_id' => $thread_id]];
        }

        $this->response->output($return);
    }

    public function sendMessage() {
        $headers = $this->input->request_headers();
        error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting
        
        // Retrieve the token from Authorization header
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
        
        $sender_id = null;
        if (!empty($decoded->data->user_information)) {
            $sender_id = $decoded->data->user_information[0]->user_id ?? null; // Adjust field as needed
        }
        
        // Get the rest of the posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        $message     = $data['message'] ?? null;
        $thread_id   = $data['thread_id'] ?? null;

        if (empty($sender_id) || empty($message) || empty($thread_id)) {
            $return = ['isError' => true, 'message' => 'All fields are required'];
        } elseif (!$this->ChatModel->isValidUser($sender_id)) {
            $return = ['isError' => true, 'message' => 'Invalid sender'];
        } else {
            $data = [
                'sender_id'   => $sender_id,
                'message'     => $message,
                'thread_id'   => $thread_id,
                'created_at'   => date("Y-m-d H:i:s")
            ];
            $this->ChatModel->sendMessage($data);
            $return = ['isError' => false, 'message' => 'Message sent'];
        }

        $this->response->output($return);
    }

    public function getThreads() {
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
    
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
    
        if (!empty($decoded->data->user_information)) {
            $user_id = $decoded->data->user_id;
        }
    
        if (empty($user_id)) {
            $return = ['isError' => true, 'message' => 'User ID is required'];
        } else {
            $threads = $this->ChatModel->getThreads($user_id);
    
            // Add participants for each thread
            foreach ($threads as &$thread) {
                $user_ids = [$thread['user1_id'], $thread['user2_id']];
                $participants = $this->ChatModel->getParticipantsInfo($user_ids);
                $thread['participants'] = $participants;
            }
    
            $return = ['isError' => false, 'message' => 'Threads retrieved', 'data' => $threads];
        }
    
        $this->response->output($return);
    }
    

    public function getMessagesByThread() {
        $data = json_decode(file_get_contents("php://input"), true);
        $thread_id = $data['thread_id'] ?? null;
        if (empty($thread_id)) {
            $return = ['isError' => true, 'message' => 'Thread ID is required'];
        } else {
            $messages = $this->ChatModel->getMessagesByThread($thread_id);
            $return = ['isError' => false, 'message' => 'Messages retrieved', 'data' => $messages];
        }
        $this->response->output($return);
    }

    public function markAsRead() {

        $headers = $this->input->request_headers();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
    
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
    
        if (!empty($decoded->data->user_information)) {
            $user_id = $decoded->data->user_id;
        }

       

        $data = json_decode(file_get_contents("php://input"), true);
        $message_id = $data['message_id'] ?? null;
    
        if (empty($message_id) || empty($user_id)) {
            $return = ['isError' => true, 'message' => 'Message ID and User ID are required'];
        } else {
            $this->load->model('ChatModel');
            $this->ChatModel->markMessageAsRead($message_id, $user_id);
            $return = ['isError' => false, 'message' => 'Message marked as read'];
        }
    
        $this->response->output($return);
    }
    
    public function markAllAsRead() {

        $headers = $this->input->request_headers();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
    
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
    
        if (!empty($decoded->data->user_information)) {
            $user_id = $decoded->data->user_id;
        }
    
        $data = json_decode(file_get_contents("php://input"), true);
        $thread_id = $data['thread_id'] ?? null;
    
        if (empty($thread_id) || empty($user_id)) {
            $return = ['isError' => true, 'message' => 'Thread ID and User ID are required'];
        } else {
            // Load the model for accessing messages in the thread
    
            // Mark all messages in the thread as read for the user
            $isMarked = $this->ChatModel->markAllMessagesAsRead($thread_id, $user_id);
    
            if ($isMarked) {
                $return = ['isError' => false, 'message' => 'All messages in the thread marked as read'];
            } else {
                $return = ['isError' => true, 'message' => 'No messages found in this thread or update failed'];
            }
        }
    
        $this->response->output($return);
    }

    
}
