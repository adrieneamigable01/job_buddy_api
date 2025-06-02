<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class UserModel extends CI_Model{

    public function add($payload){
        return $this->db->set($payload)->get_compiled_insert('users');
    }

    public function updateUser($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('users');
    }

    public function approveUser($user_id,$table) {
        $payload = array('status' => 'Validated');
        $where = array('user_id'=>$user_id);
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update($table);
    }

    public function authenticate($payload) {


        $sql = "SELECT 
                users.id, 
                users.user_id, 
                users.user_type, 
                users.username, 
                users.password,
                users.created_at, 
                users.updated_at, 
                CASE 
                    WHEN users.user_type = 'student' THEN students.first_name
                    WHEN users.user_type = 'admin' THEN admins.first_name
                    WHEN users.user_type = 'teacher' THEN teachers.first_name
                    ELSE NULL 
                END AS first_name,
                CASE 
                    WHEN users.user_type = 'student' THEN students.middle_name
                    WHEN users.user_type = 'admin' THEN admins.middle_name
                    WHEN users.user_type = 'teacher' THEN teachers.middle_name
                    ELSE NULL 
                END AS middle_name,
                CASE 
                    WHEN users.user_type = 'student' THEN students.last_name
                    WHEN users.user_type = 'admin' THEN admins.last_name
                    WHEN users.user_type = 'teacher' THEN teachers.last_name
                    ELSE NULL 
                END AS last_name
            FROM users
            LEFT JOIN students ON students.user_id = users.user_id AND users.user_type = 'student'
            LEFT JOIN admins ON admins.user_id = users.user_id AND users.user_type = 'admin'
            LEFT JOIN teachers ON teachers.user_id = users.user_id AND users.user_type = 'teacher'
            WHERE users.user_id = ?";

        // Execute the query with the payload's values (email and password)
        $query = $this->db->query($sql, array($payload['user_id']));
        // Return the result as an array
        return $query->result();
    }
    function generateUserID($prefix = 'USER') {
        // Get the current timestamp (uniqueness based on time)
        $timestamp = time();
        
        // Generate a random 4-digit number
        $randomNumber = rand(1000, 9999);
        
        // Combine prefix, timestamp, and random number to create a unique ID
        $studentID = $prefix . '-' . $timestamp . '-' . $randomNumber;
        
        return $studentID;
    }
    public function isUserIDExists($user_id) {
        $this->db->select('user_id');
        $this->db->from('users');  // Assuming the student data is in a table named 'students'
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
    
        // If any rows are returned, the student_id exists
        return ($query->num_rows() > 0);
    }
    
    
    public function getUser($user_id){

       

        $sql = "SELECT 
                users.id, 
                users.user_id, 
                users.user_id, 
                users.user_type, 
                users.username, 
                users.password,
                users.created_at, 
                users.updated_at, 

                CASE 
                    WHEN users.user_type = 'student' THEN students.firstname
                    WHEN users.user_type = 'employer' THEN employer.firstname
                    WHEN users.user_type = 'admin' THEN admins.firstname
                    ELSE NULL 
                END AS firstname,
                CASE 
                    WHEN users.user_type = 'student' THEN students.middlename
                    WHEN users.user_type = 'employer' THEN employer.middlename
                    WHEN users.user_type = 'admin' THEN admins.middlename
                    ELSE NULL 
                END AS middlename,
                CASE 
                    WHEN users.user_type = 'student' THEN students.lastname
                    WHEN users.user_type = 'employer' THEN employer.lastname
                    WHEN users.user_type = 'admin' THEN admins.lastname
                    ELSE NULL 
                END AS lastname,
                CASE 
                    WHEN users.user_type = 'student' THEN students.status
                    WHEN users.user_type = 'employer' THEN employer.status
                    WHEN users.user_type = 'admin' THEN 'Validated'
                    ELSE NULL 
                END AS validation_status,
                validation_logs.validation_logs_id,
                validation_logs.validation_logs,
                validation_logs.date_added,
                validation_logs.is_active,
                user_documents.document_type,
                user_documents.document_path
            FROM users
            LEFT JOIN students ON students.user_id = users.user_id AND users.user_type = 'student'
            LEFT JOIN employer ON employer.user_id = users.user_id AND users.user_type = 'employer'
            LEFT JOIN admins ON admins.user_id = users.user_id AND users.user_type = 'admin'
            LEFT JOIN validation_logs ON validation_logs.user_id = users.user_id
                AND validation_logs.validation_logs_id = (
                    SELECT MAX(vl.validation_logs_id)
                    FROM validation_logs vl
                    WHERE vl.user_id = users.user_id
                )

            LEFT JOIN user_documents ON user_documents.user_id = users.user_id
                AND user_documents.document_id = (
                    SELECT MAX(ud.document_id)
                    FROM user_documents ud
                    WHERE ud.user_id = users.user_id
                )
            WHERE `users`.is_active = 1 AND users.user_id = '$user_id'";
        
        
        if(isset($payload['user_id'])){
            $user_id = !empty($payload['user_id']) ? $payload['user_id']: "All";
            if($user_id != "All"){
                $sql .= " AND `users`.user_id = {$user_id}";
            }
        }

        if(isset($payload['notuser_id'])){
            $notuser_id =  implode(', ', $payload['notuser_id']);;
            $sql .= " AND users.user_id NOT IN($notuser_id)";
        }


        return  $this->db->query($sql)->result();
    }

    public function changePassword($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('users');
    }

    function generateuser_id($prefix = 'USER') {
        // Get the current timestamp (uniqueness based on time)
        $timestamp = time();
        
        // Generate a random 4-digit number
        $randomNumber = rand(1000, 9999);
        
        // Combine prefix, timestamp, and random number to create a unique ID
        $studentID = $prefix . '-' . $timestamp . '-' . $randomNumber;
        
        return $studentID;
    }

    public function isuser_idExists($user_id) {
        $this->db->select('user_id');
        $this->db->from('users');  // Assuming the student data is in a table named 'students'
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
    
        // If any rows are returned, the student_id exists
        return ($query->num_rows() > 0);
    }
   
 }
?>