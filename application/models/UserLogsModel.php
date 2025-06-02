<?php
/**
 * User Logs Model
 * Author: Your Name
 * Date Created: 3/12/2025
 * Version: 1.0.0
 */
class UserLogsModel extends CI_Model {

    // Add a new log entry
    public function add($payload) {
        // Ensure all required fields are included in the payload before inserting.
        return $this->db->insert('user_logs',$payload); // Table name 'user_logs'
    }
    // Get logs based on filters
    public function get($payload) {
        // Select the fields you need from the user_logs table.
        $this->db->select("
            user_logs.log_id,
            user_logs.user_id,
            user_logs.log_time,
            user_logs.log_type,
            user_logs.token,
            CONCAT(admins.firstname, ' ', admins.middlename, ' ', admins.lastname) as full_name
        ");
        $this->db->from('user_logs'); // Table name 'user_logs'
        $this->db->join('users', 'users.user_id = user_logs.user_id', 'left'); // Left join to fetch admin details
        $this->db->join('admins', 'users.user_id = users.user_id', 'left'); // Left join to fetch admin details
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result(); // Returns the results from the database query.
    }
}
?>
