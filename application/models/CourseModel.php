<?php
/**
 * Courses Model
 * Author: Adriene Carre Llanos Amigable
 * Date Created: 08/25/2025
 * Version: 0.1.0
 */
class CourseModel extends CI_Model {

    /**
     * Add a new course to the database.
     * 
     * @param array $payload Data to insert into the courses table.
     * @return int|bool Returns the inserted course ID or false on failure.
     */
    public function add($payload) {
        $this->db->insert('courses', $payload);
        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update an existing course in the database.
     * 
     * @param int $course_id The ID of the course to update.
     * @param array $payload Data to update in the courses table.
     * @return bool Returns true on success, false otherwise.
     */
    public function update($course_id, $payload) {
        $this->db->where('course_id', $course_id);
        return $this->db->update('courses', $payload);
    }

    /**
     * Soft delete a course from the database.
     * 
     * @param int $course_id The ID of the course to delete.
     * @return bool Returns true on success, false otherwise.
     */
    public function delete($course_id) {
        $this->db->where('course_id', $course_id);
        return $this->db->update('courses', array(
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Retrieve all active courses from the database.
     * 
     * @return array Returns an array of courses.
     */
    public function get_all() {
        $this->db->select('course_id, courses, created_at, updated_at, deleted_at, is_active');
        $this->db->from('courses');
        $this->db->where('is_active', 1);
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Retrieve a single course by ID.
     * 
     * @param int $course_id The ID of the course.
     * @return object|null Returns the course object or null if not found.
     */
    public function get_by_id($course_id) {
        $this->db->select('course_id, courses, created_at, updated_at, deleted_at, is_active');
        $this->db->from('courses');
        $this->db->where('course_id', $course_id);
        $this->db->where('is_active', 1);
        $query = $this->db->get();
        return $query->row();
    }
}
?>
