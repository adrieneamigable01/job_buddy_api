<?php


    class User extends MY_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->model('UserModel');
            $this->load->model('UserLogsModel');
            $this->load->model('ValidationLogsModel');
            $this->load->model('UserDocumentsModel');
            $this->load->model('StudentModel');
            $this->load->model('EmployerModel');
            $this->load->model('CompanyModel');
            $this->load->model('SubscriptionModel');
            $this->load->model('EducationModel');
            $this->load->model('ExperienceModel');
            $this->load->library('Response',NULL,'response');
        }
        public function checkToken(){
            $return = array(
                'isError'      => false,
                'message'        =>'Valid Token',
            );
            $this->response->output($return); //return the json encoded data
        }
        public function change_password(){
            $transQuery = array();
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password');
            $confirm_password = $this->input->post('confirm_password');
            $user_id = $this->input->post('user_id');
            if (empty($current_password)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Current password is required',
                );
            }
            else if (empty($new_password)) {
                $return = array(
                    'isError' => true,
                    'message' => 'New password is required',
                );
            }else  if (empty($confirm_password)) {
                $return = array(
                    'isError' => true,
                    'message' => 'Confirm password is required',
                );
            }else  if (empty($user_id)) {
                $return = array(
                    'isError' => true,
                    'message' => 'User ID is required',
                );
            }else  if ($new_password != $confirm_password) {
                $return = array(
                    'isError' => true,
                    'message' => 'Password not match',
                );
            }else{
               
                // print_r($hashedPassword);return false;
                $payload = array('user_id' => $user_id);
                $authenticate = $this->UserModel->authenticate($payload);
                if(count($authenticate) > 0){

                    if (!password_verify($current_password, $authenticate[0]->password)) {
                        $return = array(
                            'isError' => true,
                            'data'=> $payload,
                            // 'code'     =>http_response_code(),
                            'message'   =>'You enter an invalid old Password',
                        );
                    }else if (password_verify($new_password, $authenticate[0]->password)) {
                        $return = array(
                            'isError' => true,
                            'data'=> $payload,
                            // 'code'     =>http_response_code(),
                            'message'   => 'Please enter a new password not your current password',
                        );
                    }  
                    else {
                        $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
                        $user_id = $this->input->post('user_id');
                        $payload = array(
                            'password' => $hashedPassword,
                        );
                        $where = array(
                            'user_id' => $user_id
                        );
                        $response = $this->UserModel->changePassword($payload,$where);
                        array_push($transQuery, $response);
                        $result = array_filter($transQuery);
                        $res = $this->mysqlTQ($result);
            
                        // Success response
                        if ($res) {
                            $return = array(
                                'isError' => false,
                                'message' => 'Successfully updated student',
                                'data' => $payload
                            );
                        } else {
                            $return = array(
                                'isError' => true,
                                'message' => 'Error updating student',
                            );
                        }
                    }

                }
            }
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
        public function getUsers(){
            /**
             * @var string post data $key
             * @var string session data $accessKey
            */

            
            try{
                $storeid  = $this->input->get("storeid");
                
                $token      = $this->input->get('token');
                $decoded    = decode_jwt($token, $this->config->item('jwt_key'));
                $reqUserId  = $userid = $decoded->data->userid;

                //set payload
                $payload = array(
                    'storeid'   => $storeid,
                    'notuserId' => array($reqUserId),
                );
                /** 
                    * Call the supploer model
                    * then call the getSuppliers method
                    * @param array $payload.
                */
                $request = $this->UserModel->getUser($payload);
                $return = array(
                    'isError'      => false,
                    // 'code'       =>http_response_code(),
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
        public function generateHashPassword($pass,$key){
            $passwithKey = $pass.'-'.$key;
            return md5($passwithKey);
        }
        public function generateUsername($fullName){
            // Remove any r prefixes and trim whitespace
            $fullName = trim($fullName);
            
            // Convert to lowercase and replace spaces and hyphens with dots
            $usernameBase = strtolower(preg_replace('/[\s-]+/', '.', $fullName));


            return $usernameBase;
        }
        public function createUser(){   

           
            $transQuery         = array();
            $firstName          = $this->input->post('firstName');
            $middleName         = $this->input->post('middleName');
            $lastName           = $this->input->post('lastName');
            $birthdate          = $this->input->post('birthdate');
            $email              = $this->input->post('email');
            $mobile           = $this->input->post('mobile');
            $userType           = $this->input->post('userType');
            $storeid            = $this->input->post('storeid');
            $role               = $this->input->post('role');
            $dateCreated        = date("Y-m-d");

            
            $username           = $this->generateUsername($firstName.' '.$lastName);
            $hashpassword       = $this->generateHashPassword($lastName, $this->config->item('password_key'));

            if(empty($role)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Firstname is required',
                );
            }
            else if(empty($lastName)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Lastname is required',
                );
            }else if(empty($birthdate)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Birthdate is required',
                );
            }else if(empty($userType)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Usertype is required',
                );
            }else if(empty($storeid)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Store is required',
                );
            }else if(empty($role)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Role is required',
                );
            }
            else{
                try{
                   
                    // $headers = $this->input->request_headers();
                    // $token = $headers['Authorization'];
                    // if (strpos($token, 'Bearer ') === 0) {
                    //     $token = substr($token, 7);
                    // }
                    // $decoded = decode_jwt($token, $this->config->item('jwt_key'));
                    
                    //set ayload
                    $payload = array(
                        'users.firstName'       => $firstName,
                        'users.middleName'      => $middleName,
                        'users.lastName'        => $lastName,
                        'users.userName'        => $username,
                        'users.password'        => $hashpassword,
                        'users.birthdate'       => $birthdate,
                        'users.email'           => $email,
                        'users.mobile'          => $mobile,
                        'users.userType'        => $userType,
                        'users.dateCreated'     => $dateCreated,
                        'users.storeid'         => $storeid,
                        'users.role'            => $role,
                    );
                    $addUserResponse = $this->UserModel->addUser($payload);
                    array_push($transQuery, $addUserResponse);
                    $result = array_filter($transQuery);
			        $res = $this->mysqlTQ($result);


                    if($res){
                        $return = array(
                            'isError'      => false,
                            // 'code'       =>http_response_code(),
                            'message'        =>'Successfuly added new user',
                            'data'          => $payload
                        );
                    }else{
                        $return = array(
                            'isError' => true,
                            // 'code'     =>http_response_code(),
                            'message'   => 'Error adding user',
                        );
                    }
                    
                }catch (Exception $e) {
                    //return the server error
                    $return = array(
                        'isError' => true,
                        // 'code'     =>http_response_code(),
                        'message'   => $e->getMessage(),
                    );
                }
                
            }
            $this->response->output($return); //echo the json encoded data
        }
        public function updateUser(){   

           
            $transQuery         = array();
            $userid             = $this->input->post('userId');
            $firstName          = $this->input->post('firstName');
            $middleName         = $this->input->post('middleName');
            $lastName           = $this->input->post('lastName');
            $birthdate          = $this->input->post('birthdate');
            $email              = $this->input->post('email');
            $mobile             = $this->input->post('mobile');
            $userType           = $this->input->post('userType');
            $storeid            = $this->input->post('storeid');
            $role               = $this->input->post('role');
            $dateCreated        = date("Y-m-d");

            
            $username           = $this->generateUsername($firstName.' '.$lastName);
            $hashpassword       = $this->generateHashPassword($lastName, $this->config->item('password_key'));

            if(empty($userid)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'User id is required',
                );
            }
            else if(empty($firstName)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Firstname is required',
                );
            }
            else if(empty($lastName)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Lastname is required',
                );
            }else if(empty($birthdate)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Birthdate is required',
                );
            }else if(empty($userType)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Usertype is required',
                );
            }else if(empty($storeid)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Store is required',
                );
            }else if(empty($role)){ //check the data is not empty
                $return = array(
                    'isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   =>'Role is required',
                );
            }
            else{
                try{
                   
                    $headers = $this->input->request_headers();
                    $token = $headers['Authorization'];
                    if (strpos($token, 'Bearer ') === 0) {
                        $token = substr($token, 7);
                    }
                    $decoded = decode_jwt($token, $this->config->item('jwt_key'));
                    
                    //set ayload
                    $payload = array(
                        'users.firstName'       => $firstName,
                        'users.middleName'      => $middleName,
                        'users.lastName'        => $lastName,
                        'users.userName'        => $username,
                        'users.password'        => $hashpassword,
                        'users.birthdate'       => $birthdate,
                        'users.email'           => $email,
                        'users.mobile'          => $mobile,
                        'users.userType'        => $userType,
                        'users.dateCreated'     => $dateCreated,
                        'users.storeid'         => $storeid,
                        'users.role'            => $role,
                    );
                    $where = array(
                        'userId' => $userid,
                    );
                    $addUserResponse = $this->UserModel->updateUser($payload,$where);
                    array_push($transQuery, $addUserResponse);
                    $result = array_filter($transQuery);
			        $res = $this->mysqlTQ($result);


                    if($res){
                        $return = array(
                            'isError'      => false,
                            // 'code'       =>http_response_code(),
                            'message'        =>'Successfuly update user',
                            'data'          => $payload
                        );
                    }else{
                        $return = array(
                            'isError' => true,
                            // 'code'     =>http_response_code(),
                            'message'   => 'Error updating user',
                        );
                    }
                    
                }catch (Exception $e) {
                    //return the server error
                    $return = array(
                        'isError' => true,
                        // 'code'     =>http_response_code(),
                        'message'   => $e->getMessage(),
                    );
                }
                
            }
            $this->response->output($return); //echo the json encoded data
        }

        //User Logs
        public function logs(){
             /**
             * @var string post data $key
             * @var string session data $accessKey
            */
           
            
            try{
                // user_logs
                $log_id  = $this->input->get("log_id");
                $user_id  = $this->input->get("user_id");
                //set payload
                $payload = array();

                if(isset($log_id)){
                    $payload['log_id'] = $log_id;
                }
                if(isset($user_id)){
                    $payload['user_id'] = $user_id;
                }
                /** 
                    * Call the supploer model
                    * then call the getSuppliers method
                    * @param array $payload.
                */
                $request = $this->UserLogsModel->get($payload);
                $return = array(
                    'isError'      => false,
                    // 'code'       =>http_response_code(),
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

        public function approve(){
            $transQuery = array();

            $data = json_decode(file_get_contents("php://input"), true);

            $document_id = $data['document_id'] ?? '';
           

           
            if (empty( $document_id )) {
                $return = array(
                    'isError' => true,
                    'message' => 'Document ID is required',
                );
                $this->response->output($return);
                return;
            }


            $document = $this->UserDocumentsModel->get(array(
                'document_id' => $document_id
            ));

            

           

            if(!$document){
                $return = array(
                    'isError' => true,
                    'message' => 'Document # not found',
                );
                $this->response->output($return);
                return;
            }

            $document = $document[0];
            $user_id = $document->user_id;
            // Fetch user details
            $user = $this->UserModel->getUser($user_id);

            if (!$user) {
                $return = array(
                    'isError' => true,
                    'message' => 'User not found',
                );
                $this->response->output($return);
                return;
            }
             
            $user  = $user[0];


            if ($user->validation_status == "Validated") {
                $return = array(
                    'isError' => true,
                    'message' => 'This user is already validated',
                );
                $this->response->output($return);
                return;
            }

            // Determine which table to update
            if ($user->user_type === 'student') {
                $table = 'students';
            } elseif ($user->user_type === 'employer') {
                $table = 'employer';
            } else {
                $return = array(
                    'isError' => true,
                    'message' => 'Invalid user type',
                );
                $this->response->output($return);
                return;
            }

            // Update status to 'Validated'
            $approveUserResponse = $this->UserModel->approveUser($user_id,$table);
            array_push($transQuery, $approveUserResponse);



            $logsPayload = array(
                'user_id' => $user_id,
                'validation_logs' => 'Approve user',
                'date_added' => date("Y-m-d")
            );

            $approveUserResponse = $this->ValidationLogsModel->addQuery($logsPayload);
            array_push($transQuery, $approveUserResponse);

            $logsPayload = array(
                'status' => 'Approve',
            );
            $where = array(
                'document_id ' => $document_id,
            );

            $changeDocymentStatusResponse = $this->UserDocumentsModel->update($logsPayload,$where);
            array_push($transQuery, $changeDocymentStatusResponse);



            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);



            if ($res) {
                $return = array(
                    'isError' => false,
                    'message' => 'User successfully validated',
                );
            } else {
                $return = array(
                    'isError' => true,
                    'message' => 'Failed to update user status',
                );
            }

            $this->response->output($return);
        }


        public function profile(){

            $headers = $this->input->request_headers();
            error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting
    
            // Retrieve the token from Authorization header
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    

            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }

            $decoded = decode_jwt($token, $this->config->item('jwt_key'));
            $user_id = $decoded->data->user_id;

            $response = $this->UserModel->getUser($user_id);

            
      

            $data = array(
                'id'      => $response[0]->id,
                'user_id'      => $response[0]->user_id,
                'full_name'    => $response[0]->lastname.', '.$response[0]->firstname,
                'firstname'    => $response[0]->firstname,
                'lastname'     => $response[0]->lastname,
                'username'     => $response[0]->username,
                'user_type'    => $response[0]->user_type,
                'created_at'   => $response[0]->created_at,
                'updated_at'   => $response[0]->updated_at,
                'validation_status'   => $response[0]->validation_status,
                'validation_logs' => array(
                    'validation_logs_id' => $response[0]->validation_logs_id,
                    'validation_logs' => $response[0]->validation_logs,
                ),
                'validation_document' => array(
                    'document_type' => $response[0]->document_type,
                    'document_path' => $response[0]->document_path
                ),
            );

            if($response[0]->user_type == "employer"){
                                
                $employer_payload = array(
                    'employer.is_active' => 1,
                    'employer.user_id' => $response[0]->user_id
                );

                $employer_data = $this->EmployerModel->get($employer_payload);
              
              
                $payload_company = array(
                    'company.is_active' =>  1,
                    'company.employer_id' => $employer_data[0]->employer_id
                );
          
               
                $company_data = $this->CompanyModel->get($payload_company);
                $data['info'] = $employer_data;
                $data['company'] = $company_data;


                $user_id = $this->input->get("user_id");
                $is_active = $this->input->get("is_active");

                $payload_subsription = array(
                    'subscriptions.user_id' => $response[0]->user_id,
                    'subscriptions.is_active' => 1,
                );

                $subscription_data = $this->SubscriptionModel->get($payload_subsription,1);
                $data['subscription'] = $subscription_data;
                
                $payload_free_subsription = array(
                    'subscriptions.user_id'   => $response[0]->user_id,
                    // 'subscriptions.is_active' => "P-Free",
                );
                $free_subscription_data = $this->SubscriptionModel->get($payload_free_subsription,0);
                $data['already_subscribe'] = sizeof($free_subscription_data) > 0 ? true : false;
            }
            if($response[0]->user_type == "student"){
                                
                $student_payload = array(
                    'students.is_active' => 1,
                    'students.user_id' => $response[0]->user_id
                );

                $student_data = $this->StudentModel->get($student_payload);
          
              
                $data['info'] = $student_data;


                $education_payload = array(
                    'education.student_id' => $data['info'][0]->student_id
                );
                $education_request = $this->EducationModel->get($education_payload);
                
                $data['education'] = $education_request;

                $experience_payload = array(
                    'experience.student_id' => $data['info'][0]->student_id
                );
                $experience_request = $this->ExperienceModel->get($experience_payload);
                
                $data['experience'] = $experience_request;

            
               
            }

            $return = array(
                'isError'      => false,
                // 'code'       =>http_response_code(),
                'message'        =>'Success',
                'data'          => $data,
            );

            $this->response->output($return); //return the json encoded data
        }

       

    }
?>