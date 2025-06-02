<?php
/**
 * Validation Logs Model
 * Author: Your Name
 * Date Created: 3/12/2025
 * Version: 1.0.0
 */
class ValidationLogsModel extends CI_Model {

    // Add a new validation log entry
    public function add($payload) {
        return $this->db->insert('validation_logs',$payload); // Table name 'validation_logs'
    }

    public function add_query($payload) {
        return $this->db->set($payload)->get_compiled_insert('validation_logs');// Table name 'validation_logs'
    }


    
    public function addQuery($payload) {
        return $this->db->set($payload)->get_compiled_insert('validation_logs');
    }

    // Update an existing validation log entry
    public function update($payload, $where) {
        $this->db->where($where);
        return $this->db->update('validation_logs', $payload); // Table name 'validation_logs'
    }

    // Get validation logs based on filters
    public function get($filters = []) {
        $this->db->select('*');
        $this->db->from('validation_logs');
        if (!empty($filters)) {
            $this->db->where($filters);
        }
        $query = $this->db->get();
        return $query->result();
    }
}
