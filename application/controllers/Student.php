<?php
/**
 * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
 * @version 0.1.0
 */

class Student extends MY_Controller {
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('StudentModel'); // Make sure to create the StudentModel
        $this->load->model('EducationModel'); // Make sure to create the StudentModel
        $this->load->model('UserActivityLogModel'); // Make sure to create the StudentModel
        $this->load->library('Response', NULL, 'response');
    }

    public function checkToken() {
        $return = array(
            'isError' => false,
            'message' => 'Valid Token',
        );
        $this->response->output($return); // return the JSON encoded data
    }

    public function mysqlTQ($arrQuery) {
        $arrayIds = array();
        if (!empty($arrQuery)) {
            $this->db->trans_start();
            foreach ($arrQuery as $value) {
                $this->db->query($value);
                $last_id = $this->db->insert_id();
                array_push($arrayIds, $last_id);
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
     * Get all active students.
     */
    public function get() {
        try {
            $student_id = $this->input->get("student_id");
            $is_active = $this->input->get("is_active");
            $payload = array(
                'students.is_active' => isset($is_active) ? $is_active : 1
            );

            if (!empty($student_id)) {
                $payload['student_id'] = $student_id;
            }

            $request = $this->StudentModel->get($payload);

           
            

            $return = array(
                'isError' => false,
                'message' => 'Success',
                'data' => $request,
            );
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage(),
            );
        }
        $this->response->output($return); // return the JSON encoded data
    }

    public function update() {
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
            $student_id = $decoded->data->user_information[0]->student_id;
            $user_id = $decoded->data->user_information[0]->user_id;
        }


        $data = json_decode(file_get_contents("php://input"), true);
       
        $employment_type   = $data['employment_type'] ?? null;
        $firstname  = $data['first_name'] ?? null;
        $middlename = $data['middle_name'] ?? null;
        $lastname   = $data['last_name'] ?? null;
        $email       = $data['email'] ?? null;
        $password    = $data['password'] ?? null;
        $phone       = $data['phone'] ?? null;
        $address     = $data['address'] ?? null;
        $birthdate   = $data['birthdate'] ?? null;
        $gender      = $data['gender'] ?? null;
        $skills      = $data['skills'] ?? null;
        $course_id      = $data['course_id'] ?? null;
        $preferred_available_time      = $data['preferred_available_time'] ?? null;
        // Validation checks for student data
        if (empty($student_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Student ID is required',
            );
        } else if (empty($lastname)) {
            $return = array(
                'isError' => true,
                'message' => 'Last name is required',
            );
        } else if (empty($firstname)) {
            $return = array(
                'isError' => true,
                'message' => 'First name is required',
            );
        } else if (empty($phone)) {
            $return = array(
                'isError' => true,
                'message' => 'Phone number is required',
            );
        } else if (empty($email)) {
            $return = array(
                'isError' => true,
                'message' => 'Email is required',
            );
        } else if (empty($birthdate)) {
            $return = array(
                'isError' => true,
                'message' => 'Birthdate is required',
            );
        }
        else if (empty($employment_type)) {
            $return = array(
                'isError' => true,
                'message' => 'Employment type is required',
            );
        }
        else if (empty($skills)) {
            $return = array(
                'isError' => true,
                'message' => 'Skills is required',
            );
        }
        else if (empty($course_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Course is required',
            );
        }
        else if (empty($preferred_available_time)) {
            $return = array(
                'isError' => true,
                'message' => 'Prefered Available Time is required',
            );
        }
        
        else {
            try {
                // Payload array for student data to be updated
                $payload = array(
                    'lastname' => $lastname,
                    'firstname' => $firstname,
                    'middlename' => $middlename,
                    'phone' => $phone,
                    'address' => $address,
                    'birthdate' => $birthdate,
                    'gender' => $gender,
                    'email' => $email,
                    'skills' => $skills, // Include skills
                    'employment_type' => $employment_type, // Include skills
                    'skills' => $skills, // Include skills
                    'course_id' => $course_id, // Include skills
                    'prefere_available_time' => $preferred_available_time, // Include skills
                    'updated_at' => date("Y-m-d"),
                );

                // The condition to find the student record to update
                $where = array(
                    'student_id' => $student_id
                );

                // Call model function to update the student
                $response = $this->StudentModel->update($payload, $where);
                array_push($transQuery, $response);

                $user_activity_data = array(
                    'user_id' => $user_id,
                    'activity_type' => 'user',
                    'activity_details' => "User {$lastname} {$firstname} updated his/her profile for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => $student_id,
                    'related_table' => 'students',
                    'created_at' => date("Y-m-d H:i:s")
                );

                $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
                array_push($transQuery, $user_activity_query);

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

        // Retrieve form data
        $student_id = $this->input->post('student_id');

        // Validation checks
        if (empty($student_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Student ID is required',
            );
        } else {
            try {
                // Payload array for updating the student to soft-delete it
                $payload = array(
                    'is_active' => 0,
                    'deleted_at' => date("Y-m-d"),
                );

                $where = array(
                    'student_id' => $student_id
                );

                // Call model function to update student status
                $response = $this->StudentModel->update($payload, $where);
                array_push($transQuery, $response);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                // Success response
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully void student',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error void student',
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

        // Retrieve form data
        $student_id = $this->input->post('student_id');

        // Validation checks
        if (empty($student_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Student ID is required',
            );
        } else {
            try {
                // Payload array for updating the student to activate it
                $payload = array(
                    'is_active' => 1,
                    'deleted_at' => null,
                    'updated_at' => date("Y-m-d"),
                );

                $where = array(
                    'student_id' => $student_id
                );

                // Call model function to update student status
                $response = $this->StudentModel->update($payload, $where);
                array_push($transQuery, $response);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                // Success response
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully activated student',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error activating student',
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


    public function addEducation() {
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
            $student_id = $decoded->data->user_information[0]->student_id;
            $user_id = $decoded->data->user_information[0]->user_id;
        }



        // Decode JSON input
        $data = json_decode(file_get_contents("php://input"), true);
    
        // Validate required fields
        $requiredFields = [
            'course_id', 'school_name', 'degree',
            'field_of_study', 'start_year', 'end_year'
        ];
    
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->response->output([
                    'isError' => true,
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required',
                ]);
                return;
            }
        }
    
        // Prepare payload
        $payload = [
            'course_id'      => $data['course_id'],
            'student_id'     => $student_id,
            'school_name'    => $data['school_name'],
            'degree'         => $data['degree'],
            'field_of_study' => $data['field_of_study'],
            'start_year'     => $data['start_year'],
            'end_year'       => $data['end_year'],
            'grade'          => $data['grade'] ?? null,
            'activities'     => $data['activities'] ?? null,
            'description'    => $data['description'] ?? null,
        ];
    
        try {
            // Insert into the database
            $insertEducationQuery = $this->StudentModel->insertEducation($payload);

            array_push($transQuery, $insertEducationQuery);

            $user_activity_data = array(
                'user_id' => $user_id,
                'activity_type' => 'user',
                'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} added new education for the date of ".date("Y-m-d H:i:s"),
                'related_id' => $student_id,
                'related_table' => 'students',
                'created_at' => date("Y-m-d H:i:s")
            );

            $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
            array_push($transQuery, $user_activity_query);

            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
    
            if ($res) {
                $this->response->output([
                    'isError' => false,
                    'message' => 'Education record added successfully',
                    'data'    => $res,
                ]);
            } else {
                $this->response->output([
                    'isError' => true,
                    'message' => 'Failed to add education record',
                ]);
            }
        } catch (Exception $e) {
            $this->response->output([
                'isError' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function updateEducation() {
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
            $student_id = $decoded->data->user_information[0]->student_id;
            $user_id = $decoded->data->user_information[0]->user_id;
        }



        // Decode JSON input
        $data = json_decode(file_get_contents("php://input"), true);
    
        // Validate required fields
        $requiredFields = [
            'education_id','course_id', 'school_name', 'degree',
            'field_of_study', 'start_year', 'end_year'
        ];
    
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->response->output([
                    'isError' => true,
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required',
                ]);
                return;
            }
        }
    
        // Prepare payload
        $payload = [
            'course_id'      => $data['course_id'],
            'student_id'     => $student_id,
            'school_name'    => $data['school_name'],
            'degree'         => $data['degree'],
            'field_of_study' => $data['field_of_study'],
            'start_year'     => $data['start_year'],
            'end_year'       => $data['end_year'],
            'grade'          => $data['grade'] ?? null,
            'activities'     => $data['activities'] ?? null,
            'description'    => $data['description'] ?? null,
        ];
    
        try {
            // Insert into the database
            $where = array(
                'id' => $data['education_id'],
                'student_id' => $student_id,
            );
            $insertEducationQuery = $this->StudentModel->updateEducation($payload,$where);

            array_push($transQuery, $insertEducationQuery);

            $user_activity_data = array(
                'user_id' => $user_id,
                'activity_type' => 'user',
                'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} updated his/her education for the date of ".date("Y-m-d H:i:s"),
                'related_id' => $student_id,
                'related_table' => 'students',
                'created_at' => date("Y-m-d H:i:s")
            );

            $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
            array_push($transQuery, $user_activity_query);


            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
    
            if ($res) {
                $this->response->output([
                    'isError' => false,
                    'message' => 'Education record updated successfully',
                    'data'    => $res,
                ]);
            } else {
                $this->response->output([
                    'isError' => true,
                    'message' => 'Failed to updated education record',
                ]);
            }
        } catch (Exception $e) {
            $this->response->output([
                'isError' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }


    public function addExperience() {
        $transQuery = array();
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
    
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
        if (!empty($decoded->data->user_information)) {
            $student_id = $decoded->data->user_information[0]->student_id;
            $user_id = $decoded->data->user_information[0]->user_id;
        }
    
        $data = json_decode(file_get_contents("php://input"), true);
    
        $requiredFields = ['company_name', 'position_title', 'start_date'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->response->output([
                    'isError' => true,
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required',
                ]);
                return;
            }
        }
    
        $payload = [
            'student_id'     => $student_id,
            'company_name'   => $data['company_name'],
            'position_title' => $data['position_title'],
            'location'       => $data['location'] ?? null,
            'skills'         => $data['skills'] ?? null,
            'start_date'     => $data['start_date'],
            'end_date'       => $data['end_date'] ?? null,
            'is_current'     => $data['is_current'] ?? false,
            'description'    => $data['description'] ?? null,
        ];
    
        try {
            $insertExperienceQuery = $this->StudentModel->insertExperience($payload);
            array_push($transQuery, $insertExperienceQuery);

            $user_activity_data = array(
                'user_id' => $user_id,
                'activity_type' => 'user',
                'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} added new experience for the date of ".date("Y-m-d H:i:s"),
                'related_id' => $student_id,
                'related_table' => 'students',
                'created_at' => date("Y-m-d H:i:s")
            );

            $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
            array_push($transQuery, $user_activity_query);

            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
    
            if ($res) {
                $this->response->output([
                    'isError' => false,
                    'message' => 'Experience record added successfully',
                    'data'    => $res,
                ]);
            } else {
                $this->response->output([
                    'isError' => true,
                    'message' => 'Failed to add experience record',
                ]);
            }
        } catch (Exception $e) {
            $this->response->output([
                'isError' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    public function updateExperience() {
        $transQuery = array();
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
    
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
        if (!empty($decoded->data->user_information)) {
            $student_id = $decoded->data->user_information[0]->student_id;
            $user_id = $decoded->data->user_information[0]->user_id;
        }
    
        $data = json_decode(file_get_contents("php://input"), true);
    
        $requiredFields = ['experience_id', 'company_name', 'position_title', 'start_date'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->response->output([
                    'isError' => true,
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required',
                ]);
                return;
            }
        }
    
        $payload = [
            'company_name'   => $data['company_name'],
            'position_title' => $data['position_title'],
            'location'       => $data['location'] ?? null,
            'skills'         => $data['skills'] ?? null,
            'start_date'     => $data['start_date'],
            'end_date'       => $data['end_date'] ?? null,
            'is_current'     => $data['is_current'] ?? false,
            'description'    => $data['description'] ?? null,
        ];
    
        try {
            $where = [
                'experience_id' => $data['experience_id'],
                'student_id'    => $student_id,
            ];
            $updateExperienceQuery = $this->StudentModel->updateExperience($payload, $where);
            array_push($transQuery, $updateExperienceQuery);

            $user_activity_data = array(
                'user_id' => $user_id,
                'activity_type' => 'user',
                'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} updated his/her experience for the date of ".date("Y-m-d H:i:s"),
                'related_id' => $student_id,
                'related_table' => 'students',
                'created_at' => date("Y-m-d H:i:s")
            );

            $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
            array_push($transQuery, $user_activity_query);

            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
    
            if ($res) {
                $this->response->output([
                    'isError' => false,
                    'message' => 'Experience record updated successfully',
                    'data'    => $res,
                ]);
            } else {
                $this->response->output([
                    'isError' => true,
                    'message' => 'Failed to update experience record',
                ]);
            }
        } catch (Exception $e) {
            $this->response->output([
                'isError' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    
}
?>
