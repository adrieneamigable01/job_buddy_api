<?php
/**
 * User Documents Controller
 * Author: Your Name
 * Date Created: 3/12/2025
 * Version: 1.0.0
 */

class UserDocuments extends MY_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('UserDocumentsModel');
        $this->load->model('EmployerModel');
        $this->load->model('ValidationLogsModel');
        $this->load->model('UserModel');
        $this->load->model('UserActivityLogModel');
        $this->load->library('Response', NULL, 'response');
    }

    // Add a new document entry
    public function create() {
        $transQuery = array();

        $headers = $this->input->request_headers();
        error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting

        // Retrieve the token from Authorization header
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;


        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
        if(!empty($decoded->data->user_information)){
            $user_type = $decoded->data->user_type == "employer" ? "Employer" : "Student";
            $user_id = $decoded->data->user_information[0]->user_id;
        }

       


        $data = json_decode(file_get_contents("php://input"), true);

        try {
            // Retrieve POST data
            $document_type = $data['document_type'];
            $document_path = $data['document_path'];  // Path to the document
            $base64Selfie = $data['base64Selfie'];  // Path to the document
            $uploaded_at = date('Y-m-d H:i:s');  // Current timestamp for upload time

            // Validation checks
            if (empty($user_id)) {
                $return = array(
                    'isError' => true,
                    'message' => 'User ID is required',
                );
                $this->response->output($return);return;
            } else if (empty($document_type)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Document type is required',
                );
                $this->response->output($return);return;
            } 
            else if (empty($document_path)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Document path is required',
                );
                $this->response->output($return);return;
            } 
            else if (empty($base64Selfie)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Selfie is required',
                );
                $this->response->output($return);return;
            } 
            else {
                // Payload for inserting into the user_documents table
                $payload = array(
                    'user_id' => $user_id,
                    'user_type' => $user_type,
                    'document_type' => $document_type,
                    'document_path' => $document_path,
                    'base64Selfie' => $base64Selfie,
                    'uploaded_at' => $uploaded_at
                );
                // Call the model method to insert the document
                $response = $this->UserDocumentsModel->add($payload);
                array_push($transQuery, $response);

                $where = array(
                    'user_id' => $user_id,
                );

                $update_employer = array(
                    'status' => 'Need Validate',
                );

                // Call the employer method to update the status
                $response_employer = $this->EmployerModel->update($update_employer,$where);
                array_push($transQuery, $response_employer);


                $validation_logs_payload = [
                    'user_id' => $user_id,
                    'validation_logs' => 'Uploaded document',
                    'date_added' => date("Y-m-d")
                ];

                $validation_logs_response = $this->ValidationLogsModel->add_query($validation_logs_payload);
                array_push($transQuery, $validation_logs_response);

                $user = $this->UserModel->getUser($user_id);
                $user = $user[0];

                $user_activity_data = array(
                    'user_id' => $user_id,
                    'activity_type' => 'user',
                    'activity_details' => "User {$user->lastname} {$user->firstname} uploaded validation documents with document type of {$document_type}  for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => $user_id,
                    'related_table' => 'users',
                    'created_at' => date("Y-m-d H:i:s")
                );
    
                $user_query = $this->UserActivityLogModel->add($user_activity_data);
                array_push($transQuery, $user_query);


                $result = array_filter($transQuery);
                $res = $this->response->mysqlTQ($result);

                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Document successfully uploaded',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Failed to upload document',
                    );
                }
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

    // Get user documents
    public function get() {
        try {
            // Get parameters
            $user_id = $this->input->get('user_id');
            $document_type = $this->input->get('document_type');
            
            // Create payload for filtering the documents
            $payload = array();
            if (!empty($user_id)) {
                $payload['user_id'] = $user_id;
            }
            if (!empty($document_type)) {
                $payload['document_type'] = $document_type;
            }

            // Call the model method to get documents
            $documents = $this->UserDocumentsModel->get($payload);
            
            if ($documents) {
                $return = array(
                    'isError' => false,
                    'message' => 'Documents fetched successfully',
                    'data' => $documents
                );
            } else {
                $return = array(
                    'isError' => true,
                    'message' => 'No documents found',
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
