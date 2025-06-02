<?php
/**
 * Auth Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/3/2020
 * Version: 0.0.1
 */
 class AuthModel extends CI_Model{
    /**
     * This will authenticate the user
     * @param array payload 
    */
    
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
                END AS lastname
            FROM users
            LEFT JOIN students ON students.user_id = users.user_id AND users.user_type = 'student'
            LEFT JOIN employer ON employer.user_id = users.user_id AND users.user_type = 'employer'
            LEFT JOIN admins ON admins.user_id = users.user_id AND users.user_type = 'admin'
            WHERE users.username = ?";

        // Execute the query with the payload's values (email and password)
        $query = $this->db->query($sql, array($payload['username']));
        // Return the result as an array
        return $query->result();
    }
    
    
    
    
    
    public function addBlackListToken($payload){
        return $this->db->set($payload)->get_compiled_insert('blacklist_token');
    }
 }
?>