<?php

   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class Auth extends CI_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->helper('jwt');
            $this->load->model('AuthModel');
            $this->load->model('StudentModel');
            $this->load->model('EmployerModel');
            $this->load->model('UserModel');
            $this->load->model('SubscriptionModel');
            $this->load->model('UserActivityLogModel');
            $this->load->model('UserLogsModel');
            $this->load->library('Response',NULL,'response');
           
        }
        /**
            * Generate a key
            * 
            *
            * @return string return a string use to be the accessKey 
        */
        private function keygen($length=10)
        {
            $key = '';
            list($usec, $sec) = explode(' ', microtime());
            mt_srand((int) $sec + ((int) $usec * 100000));
            
            $inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

            for($i=0; $i<$length; $i++)
            {
                $key .= $inputs[mt_rand(0,61)];
            }
            return $key;
        }
        public function testpass(){
            $hashedPassword = password_hash('admin', PASSWORD_BCRYPT);
            print_r( $hashedPassword );
        }
        /**
            * Authenticate a user
            * 
            *
            * @return array return the data info of a user
        */
        public function login(){

            /**
             * @var string post data $username
             * @var string post data $password
             * @var array  data $return
            */
            $data = json_decode(file_get_contents("php://input"), true);
        

            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            $return   = array();

            // conditions
            //this will filter so that no php error will found
            if(empty($username)){ //check if the username and password is not empty
                $return = array(
                    'isError' =>true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Empty username',
                );
            }else if(empty($password)){ //check if the username and password is not empty
                $return = array(
                    'isError' =>true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Empty password',
                );
            }
            else{
               //set payload
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                // print_r($hashedPassword);return false;
                $payload = array('username' => $username);
                /** 
                    * Call the auth  model
                    * then call the authenticate method
                    * @param array $payload.
                */
                $authenticate = $this->AuthModel->authenticate($payload);

                  
                try{
                    if(count($authenticate) > 0){

                        if (password_verify($password, $authenticate[0]->password)) {

                            $data = array(
                                'id'            => $authenticate[0]->id,
                                'user_id'      => $authenticate[0]->user_id,
                                'full_name'    => $authenticate[0]->lastname.', '.$authenticate[0]->firstname,
                                'firstname'    => $authenticate[0]->firstname,
                                'lastname'     => $authenticate[0]->lastname,
                                'username'     => $authenticate[0]->username,
                                'user_type'    => $authenticate[0]->user_type,
                                'created_at'   => $authenticate[0]->created_at,
                                'updated_at'   => $authenticate[0]->updated_at,
                            );
                       
                            
                         
                           
                           

                            
    
                            if($authenticate[0]->user_type == "student"){
                                $student_id = $this->input->get("student_id");
                                $student_payload = array(
                                    'students.is_active' => 1,
                                    'students.user_id' => $authenticate[0]->user_id
                                );
                
                                $student_data = $this->StudentModel->get($student_payload);
    
                                $data['user_information'] = $student_data;
                            }
    
                            if($authenticate[0]->user_type == "employer"){
                                
                                $employer_payload = array(
                                    'employer.is_active' => 1,
                                    'employer.user_id' => $authenticate[0]->user_id
                                );
                
                                $employer_data = $this->EmployerModel->get($employer_payload);
                                $data['user_information'] = $employer_data;
                            }


                            $jwtpayload = array(
                                "iss" => "job_buddy_api",
                                "aud" => "jobbuddy-api",
                                "iat" => time(),
                                "exp" => time() + (60 * 60),  // Token expires in 1 hour
                                "data" => $data
                            );
    
                            $jwt = generate_jwt($jwtpayload, $this->config->item('jwt_key'));

                            $data['token'] = $jwt;

                            if($authenticate[0]->user_type == "admin"){
                                $payload = array(
                                    'user_id'  => $authenticate[0]->user_id,
                                    'log_time' => date("Y-m-d H:i:s"),
                                    'log_type' => 'login',
                                    'token'    => $jwt
                                );
                
                                // Call the model method to insert the log
                                $response = $this->UserLogsModel->add($payload);
                            }

                            $return = array(
                                'isError'     => false,
                                // 'code'      =>http_response_code(),
                                'message'       =>'Login successfuly',
                                'data'         => $data,
                            );
                        } else {

                            

                            $return = array(
                                'isError' => true,
                                'data'=> $payload,
                                // 'code'     =>http_response_code(),
                                'message'   =>'Invalid login credentials',
                            );
                        }

                       

                    }else{
                        $return = array(
                            'isError' => true,
                            'data'=> $payload,
                            // 'code'     =>http_response_code(),
                            'message'   =>'Invalid login credentials',
                        );
                    }
                }catch (Exception $e) {
                    //set the server error
                    $return = array(
                        'isError' => true,
                        // 'code'     =>http_response_code(),
                        'message'   => $e->getMessage(),
                    );
                }
            }
            $this->response->output($return,1); //return the json encoded data
        }
        public function register() {
          
            $transQuery = array();
            $pref = "AD";
            
            $data = json_decode(file_get_contents("php://input"), true);
       
            $user_type   = $data['user_type'] ?? null;
            $user_id     = $this->UserModel->generateUserID();
            $first_name  = $data['first_name'] ?? null;
            $middle_name = $data['middle_name'] ?? null;
            $last_name   = $data['last_name'] ?? null;
            $email       = $data['email'] ?? null;
            $password    = $data['password'] ?? null;
            $phone       = $data['phone'] ?? null;
            $address     = $data['address'] ?? null;
            $birthdate   = $data['birthdate'] ?? null;
            $gender      = $data['gender'] ?? null;
            $dateCreated = date("Y-m-d");
        
            // Student-specific
            $skills                  = $data['skills'] ?? [];
            $course_id               = $data['course_id'] ?? null;
            $status                  = $data['status'] ?? null;
            $preferred_available_time = $data['preferred_available_time'] ?? null;
        
            if (empty($user_type)) {
                $return = ['isError' => true, 'message' => 'User type is required'];
            } else if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
                $return = ['isError' => true, 'message' => 'Required fields are missing'];
            } else {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $pref = $user_type == 'student' ? 'STUD' : ($user_type == 'employer' ? 'EMP' : 'AD');
                    while ($this->UserModel->isUserIDExists($user_id)) {
                        $user_id = $this->UserModel->generateUserID($pref);
                    }
        
                    $payload_user = [
                        'user_id'    => $user_id,
                        'user_type'  => $user_type,
                        'username'   => $email,
                        'password'   => $hashedPassword,
                        'created_at' => $dateCreated,
                    ];
                    $response_user = $this->UserModel->add($payload_user);
                    array_push($transQuery, $response_user);
        
                    if ($user_type == 'student') {
                        if (empty($course_id)) {
                            $return = ['isError' => true, 'message' => 'Course ID is required for students'];
                            $this->response->output($return);
                            return false;
                        }
        
                        $student_payload = [
                            'user_id'    => $user_id,
                            'firstname'  => $first_name,
                            'middlename' => $middle_name,
                            'lastname'   => $last_name,
                            'email'      => $email,
                            'phone'      => $phone,
                            'address'    => $address,
                            'birthdate'  => $birthdate,
                            'gender'     => $gender,
                            'is_active'  => 1,
                            'course_id'  => $course_id,
                            'prefere_available_time' => $preferred_available_time,
                            'created_at' => $dateCreated,
                        ];
        
                        if (!empty($skills)) {
                            $student_payload['skills'] = json_encode($skills);
                        }
        
                        $response_student = $this->StudentModel->add($student_payload);
                        array_push($transQuery, $response_student);
        
                    } else if ($user_type == 'employer') {
                        $employer_payload = [
                            'user_id'    => $user_id,
                            'firstname'  => $first_name,
                            'middlename' => $middle_name,
                            'lastname'   => $last_name,
                            'email'      => $email,
                            'phone'      => $phone,
                            'address'    => $address,
                            'birthdate'  => $birthdate,
                            'gender'     => $gender,
                            'is_active'  => 1,
                            'created_at' => $dateCreated,
                        ];
                        $response_employer = $this->EmployerModel->add($employer_payload);
                        array_push($transQuery, $response_employer);
                    }
                    

                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d', strtotime("+30 days"));

                $payload = array(
                    'user_id' => $user_id,
                    'plan_id' => 'P-Free',
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'is_active' => 1,
                );

                $query = $this->SubscriptionModel->add($payload);
                array_push($transQuery, $query);

                $user_activity_data = array(
                    'user_id' => $user_id,
                    'activity_type' => 'subscription',
                    'activity_details' => "User {$first_name} {$last_name} added new subscription plan Free for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => 'P-Free',
                    'related_table' => 'subscriptions',
                    'created_at' => date("Y-m-d H:i:s")
                );

                $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
                array_push($transQuery, $user_activity_query);
        
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    $return = $res
                        ? ['isError' => false, 'message' => 'Successfully added new user', 'data' => $payload_user]
                        : ['isError' => true, 'message' => 'Error adding user'];
                } catch (Exception $e) {
                    $return = ['isError' => true, 'message' => $e->getMessage()];
                }
            }
        
            $this->response->output($return);
        }
        
        
        
        
        /* Logout user */
        public function logout(){
            $transQuery      = array();
            $headers = $this->input->request_headers();
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            $return   = array();
           
            $payload = array(
                'token' => $token,
            );
            $decodedToken = decode_jwt($token,$this->config->item('jwt_key'));

            if (!$decodedToken || !is_object($decodedToken) || !isset($decodedToken->data) || !is_object($decodedToken->data)) {
                error_log('Invalid token structure: ' . print_r($decodedToken, true));
                $return = array(
                    'isError' => true,
                    'message'   =>'Invalid or Expired Token',
                );
                $this->response->output($return);return false;
            }

            
            $response = $this->AuthModel->addBlackListToken($payload);
            array_push($transQuery, $response);
            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
           
            if($res){
                $payload = array(
                    'user_id'  => $decodedToken->data->user_id,
                    'log_time' => date("Y-m-d H:i:s"),
                    'log_type' => 'logout',
                    'token'    => $token
                );

                // Call the model method to insert the log
                $response = $this->UserLogsModel->add($payload);
                $return = array(
                    'isError' => false,
                    'message'   =>'Success',
                );
            }else{
                $return = array(
                    'isError' => true,
                    'message'   =>'Error',
                );
            }
            $this->response->output($return);
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
    }
    
?>