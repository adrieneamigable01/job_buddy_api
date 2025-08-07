<?php

class CourseModel extends CI_Model {

    // Add a new course
    public function add($payload) {
        // Ensure all required fields are included in the payload before inserting.
        return $this->db->set($payload)->get_compiled_insert('courses'); // Table name 'courses'
    }

    // Update an existing course
    public function update($payload, $where) {
        // Ensure the fields to be updated are in the payload.
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('courses'); // Table name 'courses'
    }

    // Get courses based on filters
    public function get($payload) {
        // Select the fields you need from the courses table.
        $this->db->select('
            courses.course_id,
            courses.courses,
            courses.created_at,
            courses.updated_at,
            courses.deleted_at,
            courses.is_active
        ');
        $this->db->from('courses'); // Table name 'courses'
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result(); // Returns the results from the database query.
    }
}
?>
