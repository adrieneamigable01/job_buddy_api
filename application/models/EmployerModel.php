<?php
/**
 * Employer Model
 * Author: Adriene Carre Amigable
 * Date Created: 5/10/2020
 * Version: 0.0.1
 */
class EmployerModel extends CI_Model {

    public function add($payload){
        // Insert employer data into the 'employer' table
        return $this->db->set($payload)->get_compiled_insert('employer');
    }

    public function update($payload, $where){
        // Update employer data based on the given 'where' condition
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('employer');
    }

    public function get($payload){
        $this->db->select('
            employer.employer_id,
            employer.lastname,
            employer.firstname,
            employer.middlename,
            employer.user_id,
            employer.email,
            employer.phone,
            employer.address,
            employer.birthdate,
            employer.gender,
            employer.status,
            employer.is_active,
            employer.created_at,
            employer.updated_at,
            employer.deleted_at
        ');
        $this->db->from('employer');
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result();
    }

    public function getById($employer_id)
    {
        return $this->db->get_where('employer', ['employer_id ' => $employer_id])->row();
    }

    function generateEmployerID($prefix = 'EMP') {
        // Generate a unique employer ID based on timestamp and random number
        $timestamp = time();
        $randomNumber = rand(1000, 9999);
        $employer_id = $prefix . '-' . $timestamp . '-' . $randomNumber;
        return $employer_id;
    }

    public function isEmployerIDExists($employer_id, $user_id = "") {
        $this->db->select('employer_id');
        $this->db->from('employer');
        $this->db->where('employer_id', $employer_id);
        if (!empty($user_id)) {
            $this->db->where('user_id !=', $user_id);
        }
        $query = $this->db->get();

        // If any rows are returned, the employer_id exists
        return ($query->num_rows() > 0);
    }
}
?>
