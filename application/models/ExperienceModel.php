
<?php
/**
 * Course Model
 * Author: Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
 * Date Created: 3/12/2025
 * Version: 1.0.0
 */
class ExperienceModel extends CI_Model {

    // Get courses based on filters
    public function get($payload) {
        // Select the fields you need from the courses table.
        $this->db->select('*');
        $this->db->from('experience'); // Table name 'courses'
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result(); // Returns the results from the database query.
    }
}
?>
