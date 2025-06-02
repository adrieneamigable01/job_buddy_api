<?php
/**
 * UserActivityLog Model
 * Author: Your Name
 * Date Created: 5/26/2025
 * Version: 1.0.0
 */
class UserActivityLogModel extends CI_Model {

    protected $table = 'user_activity_log';

    public function add($payload) {
        // Insert a new activity log entry.
        return $this->db->set($payload)->get_compiled_insert($this->table);
    }
    public function direct_add($payload) {
        // Insert a new activity log entry.
        return $this->db->insert($this->table,$payload); // Table name 'validation_logs'
    }

    public function update($payload, $where) {
        // Update existing activity log(s) based on condition.
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update($this->table);
    }

    public function get($payload) {
        $this->db->select("
        user_activity_log.id,
        user_activity_log.user_id,
        user_activity_log.activity_type,
        user_activity_log.activity_details,
        user_activity_log.related_id,
        user_activity_log.related_table,
        user_activity_log.created_at,
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
    ");

    $this->db->from($this->table);

    // Join with the users table to get user_type
    $this->db->join('users', 'users.user_id = user_activity_log.user_id', 'left');

    // Conditional joins based on possible user_type
    $this->db->join('students', 'students.user_id = users.user_id', 'left');
    $this->db->join('employer', 'employer.user_id = users.user_id', 'left');
    $this->db->join('admins', 'admins.user_id = users.user_id', 'left');

    $this->db->where($payload);

    $query = $this->db->get();
    return $query->result();

    }

}
?>
