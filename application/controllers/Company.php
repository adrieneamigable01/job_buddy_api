<?php
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class Company extends MY_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->model('CompanyModel');
            $this->load->model('UserModel');
            $this->load->model('UserActivityLogModel');
            $this->load->library('Response',NULL,'response');
        }
        public function checkToken(){
            $return = array(
                'isError'      => false,
                'message'        =>'Valid Token',
            );
            $this->response->output($return); //return the json encoded data
        }
        public function mysqlTQ($arrQuery){
            $arrayIds = array();
            if (!empty($arrQuery)) {
                $this->db->trans_start();
                foreach ($arrQuery as $value) {
                    $this->db->query($value);
                    $last_id = $this->db->insert_id();
                    array_push($arrayIds,$last_id);
                }
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                } else {
                    $this->db->trans_commit();
                   
                    return $arrayIds;
                }
            }
        }
        /**
            * Get all active suppliers
            * 
            *
            * @return array Returns the isError define if the request is error or not.
            * message indicated the system message 
            * data where the data object has 
        */
        public function get(){
            /**
             * @var string post data $key
             * @var string session data $accessKey
            */
            try{
               
                /** 
                    * Call the supploer model
                    * then call the getUser method
                    * @param array $payload.
                */
                $company_id = $this->input->get("company_id");
                $is_active = $this->input->get("is_active");
                $payload = array(
                    'company.is_active' => isset($is_active) ? $is_active : 1
                );
          
               
                
               

                if(!empty($company_id)){
                    $payload['company_id'] = $company_id;
                }

                $request = $this->CompanyModel->get($payload);

                $return = array(
                    'isError'      => false,
                    'message'        =>'Success',
                    'data'          => $request,
                );
            }catch (Exception $e) {
                //set server error
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   => $e->getMessage(),
                );
            }
            $this->response->output($return); //return the json encoded data
        }
        

        public function create() {
            
            $transQuery = array();
        
            // Retrieve form data for the company\

            $headers = $this->input->request_headers();
            error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting
    
            // Retrieve the token from Authorization header
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
    
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            $decoded = decode_jwt($token, $this->config->item('jwt_key'));
            if(!empty($decoded->data->user_information)){
                $employer_id = $decoded->data->user_information[0]->employer_id;
                $user_id = $decoded->data->user_information[0]->user_id;
            }



            $data = json_decode(file_get_contents("php://input"), true);

            $company_name = $data['company_name'] ?? null;
            $company_address = $data['company_address'] ?? null;
            $contact_number = $data['contact_number'] ?? null;
            $email = $data['email'] ?? null;
            $established_date = $data['established_date'] ?? null;
            $company_logo = $data['company_logo'] ?? null;

           
        
            // Validation checks for company data
            if (empty($employer_id)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Employer ID is required',
                );
            }
            else if (empty($company_name)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Company name is required',
                );
            } else if (empty($company_address)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Company address is required',
                );
            } else if (empty($contact_number)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Contact number is required',
                );
            } else if (empty($email)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Email is required',
                );
            } else if (empty($established_date)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Established date is required',
                );
            } else {
                try {
                    // Payload array for new company data
                    $payload = array(
                        'employer_id'        => $employer_id,
                        'company_name'       => $company_name,
                        'company_address'    => $company_address,
                        'contact_number'     => $contact_number,
                        'email'              => $email,
                        'established_date'   => $established_date,
                        'company_logo'       => $company_logo,
                        'created_at'         => date("Y-m-d"),
                    );
        
                    // Call model function to add the company
                    $response = $this->CompanyModel->add($payload);
                    array_push($transQuery, $response);


                    $user_activity_data = array(
                        'user_id' => $user_id,
                        'activity_type' => 'company',
                        'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} created new company $company_name located at $company_address for the date of ".date("Y-m-d H:i:s"),
                        'related_id' => $employer_id,
                        'related_table' => 'company',
                        'created_at' => date("Y-m-d H:i:s")
                    );
    
                    $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
                    array_push($transQuery, $user_activity_query);
                   
        
                    // Handle the transaction result (if any)
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
                    // Success response
                    if ($res) {
                        $return = array(
                            'isError' => false,
                            'message' => 'Successfully added new company',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message' => 'Error adding company',
                        );
                    }
        
                } catch (Exception $e) {
                    $errorLog = "[" . date('Y-m-d H:i:s') . "] Company creation failed: " . $e->getMessage() . "\n";
                    file_put_contents(APPPATH . 'logs/custom_error_log.txt', $errorLog, FILE_APPEND);
                    // Handle exception and return error response
                    $return = array(
                        'isError' => true,
                        'message' => $e->getMessage(),
                    );
                }
            }
        
            // Output the response
            $this->response->output($return);
        }
        
        
        public function update() {
            $transQuery = array();
            
            // Retrieve form data using the 'name' attributes from the HTML form
            $company_id = $this->input->post('company_id');
            $company_name = $this->input->post('company_name');
            $company_address = $this->input->post('company_address');
            $contact_number = $this->input->post('contact_number');
            $email = $this->input->post('email');
            $established_date = $this->input->post('established_date');
            $is_active = $this->input->post('is_active');
            $company_logo = $this->input->post('company_logo'); // If you're storing an image or file
            
            // Validation checks
            if (empty($company_id)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Company ID is required',
                );
            } else if (empty($company_name)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Company name is required',
                );
            } else if (empty($company_address)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Company address is required',
                );
            } else if (empty($contact_number)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Contact number is required',
                );
            } else if (empty($email)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Email is required',
                );
            } else if (empty($established_date)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Established date is required',
                );
            } else {
                try {
                    // Payload array for company data to be updated
                    $payload = array(
                        'company_name'        => $company_name,
                        'company_address'     => $company_address,
                        'contact_number'      => $contact_number,
                        'email'               => $email,
                        'established_date'    => $established_date,
                        'is_active'           => $is_active,
                        'company_logo'       => $company_logo, // If an image is being uploaded
                        'updated_at'          => date("Y-m-d"),
                    );
                    
                    // The condition to find the company record to update
                    $where = array(
                        'company_id' => $company_id
                    );
                    
                    // Call model function to update the company
                    $response = $this->CompanyModel->update($payload, $where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            'isError' => false,
                            'message' => 'Successfully updated company',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message' => 'Error updating company',
                        );
                    }
        
                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        'isError' => true,
                        'message' => $e->getMessage(),
                    );
                }
            }
        
            // Output the response
            $this->response->output($return);
        }
        
        
        public function void() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
            $company_id = $this->input->post('company_id');
        
            // Validation checks
            if (empty($company_id)) {
                $return = array(
                    'isError' => true,
                    'message' => 'company id is required',
                );
            }else {
                try {
        
                    // Payload array for new user data
                    $payload = array(
                        'is_active'   => 0,
                        'deleted_at'  => date("Y-m-d"),
                    );
                    $where = array(
                        'company_id' => $company_id
                    );
                    // Call model function to add user
                    $response = $this->CompanyModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            'isError' => false,
                            'message' => 'Successfully void company',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message' => 'Error void company',
                        );
                    }
        
                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        'isError' => true,
                        'message' => $e->getMessage(),
                    );
                }
            }
        
            // Output the response
            $this->response->output($return);
        }
        public function activate() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
            $company_id = $this->input->post('company_id');
        
            // Validation checks
            if (empty($company_id)) {
                $return = array(
                    'isError' => true,
                    'message' => 'company id is required',
                );
            }else {
                try {
        
                    // Payload array for new user data
                    $payload = array(
                        'is_active'   => 1,
                        'deleted_at'  => null,
                        'updated_at'  => date("Y-m-d"),
                    );
                    $where = array(
                        'company_id' => $company_id
                    );
                    // Call model function to add user
                    $response = $this->CompanyModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            'isError' => false,
                            'message' => 'Successfully activate company',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message' => 'Error activating company',
                        );
                    }
        
                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        'isError' => true,
                        'message' => $e->getMessage(),
                    );
                }
            }
        
            // Output the response
            $this->response->output($return);
        }
    }
?>