<?php
/**
 * User Documents Model
 * Author: Your Name
 * Date Created: 3/12/2025
 * Version: 1.0.0
 */
class UserDocumentsModel extends CI_Model {

    // Add a new document entry
    public function add($payload) {
        // Ensure all required fields are included in the payload before inserting.
        return $this->db->set($payload)->get_compiled_insert('user_documents'); // Table name 'user_documents'
    }

    // Update an existing document entry
    public function update($payload, $where) {
        // Ensure the fields to be updated are in the payload.
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('user_documents'); // Table name 'user_documents'
    }

    // Get documents based on filters
    public function get($payload) {
        // Select the fields you need from the user_documents table.
        $this->db->select("
            user_documents.document_id,
            CASE 
                WHEN users.user_type = 'student' THEN CONCAT(students.firstname,' ',students.lastname)
                WHEN users.user_type = 'employer' THEN CONCAT(employer.firstname,' ',employer.lastname)
                WHEN users.user_type = 'admins' THEN CONCAT(admins.firstname,' ',admins.lastname)
                ELSE NULL 
            END AS fullname,
            user_documents.user_id,
            user_documents.document_type,
            user_documents.status,
            user_documents.uploaded_at,
            user_documents.document_path,
        ");
        $this->db->from('user_documents'); // Table name 'user_documents'
        $this->db->join('users', 'users.user_id = user_documents.user_id', 'left'); // Left join to fetch admin details
        $this->db->join('students', 'students.user_id = users.user_id', 'left'); // Left join to fetch admin details
        $this->db->join('employer', 'employer.user_id = users.user_id', 'left'); // Left join to fetch admin details
        $this->db->join('admins', 'admins.user_id = users.user_id', 'left'); // Left join to fetch admin details
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result(); // Returns the results from the database query.
    }

}
?>
