<?php
/**
 * Student Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
class StudentModel extends CI_Model {

    public function add($payload){
        // Ensure all required fields are included in the payload.
        return $this->db->set($payload)->get_compiled_insert('students');
    }

    public function update($payload, $where){
        // Ensure the fields to be updated are in the payload.
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('students');
    }
   

    public function get($payload){
        $this->db->select('
            students.student_id,
            students.lastname,
            students.firstname,
            students.middlename,
            students.user_id,
            students.email,
            students.phone,
            students.address,
            students.birthdate,
            students.gender,
            students.is_active,
            students.created_at,
            students.updated_at,
            students.deleted_at,
            students.skills,
            students.prefere_available_time,
            students.employment_type,
            students.course_id,
            students.status,
            courses.courses,    
        ');
        $this->db->from('students')
        ->join('courses', 'students.course_id = courses.course_id', 'left');
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result();
    }

    public function getStudentById($student_id)
    {
        return $this->db->where('student_id', $student_id)
                        ->where('deleted_at', null)
                        ->get('students')
                        ->row();
    }


    function generateStudentID($prefix = 'STU') {
        // Get the current timestamp (uniqueness based on time)
        $timestamp = time();
        
        // Generate a random 4-digit number
        $randomNumber = rand(1000, 9999);
        
        // Combine prefix, timestamp, and random number to create a unique ID
        $studentID = $prefix . '-' . $timestamp . '-' . $randomNumber;
        
        return $studentID;
    }

    public function isStudentIDExists($student_id, $user_id = "") {
        try {
            $this->db->select('student_id');
            $this->db->from('students');
            $this->db->where('student_id', $student_id);
    
            // Exclude the specific user_id if it's provided
            if (!empty($user_id)) {
                $this->db->where('user_id !=', $user_id);
            }
    
            $query = $this->db->get();
    
            // Check if there are any rows returned
            return ($query->num_rows() > 0);
        } catch (Exception $e) {
            // Log the error or handle it as needed
            log_message('error', 'Error in isStudentIDExists method: ' . $e->getMessage());
            return false;  // Return false in case of an error
        }
    }


    public function insertEducation($payload){
        // Ensure all required fields are included in the payload.
        return $this->db->set($payload)->get_compiled_insert('education');
    }
    public function updateEducation($payload, $where){
        // Ensure the fields to be updated are in the payload.
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('education');
    }
    public function insertExperience($payload){
        // Ensure all required fields are included in the payload.
        return $this->db->set($payload)->get_compiled_insert('experience');
    }
    public function updateExperience($payload, $where){
        // Ensure the fields to be updated are in the payload.
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('experience');
    }



    

}
?>
