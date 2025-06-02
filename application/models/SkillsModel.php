<?php
/**
 * skills Model
 * Author: Adriene Carre Llanos Amigable
 * Date Created: 4/27/2025
 * Version: 0.1.0
 */
class SkillsModel extends CI_Model {

    /**
     * Add a new skills to the database.
     * 
     * @param array $payload Data to insert into the skills table.
     * @return int|bool Returns the inserted skills ID or false on failure.
     */
    public function add($payload) {
        $this->db->insert('skills', $payload);
        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update an existing skills in the database.
     * 
     * @param int $skills_id The ID of the skills to update.
     * @param array $payload Data to update in the skills table.
     * @return bool Returns true on success, false otherwise.
     */
    public function update($skills_id, $payload) {
        $this->db->where('id', $skills_id);
        return $this->db->update('skills', $payload);
    }

    /**
     * Delete a skills from the database.
     * 
     * @param int $skills_id The ID of the skills to delete.
     * @return bool Returns true on success, false otherwise.
     */
    public function delete($skills_id) {
        $this->db->where('id', $skills_id);
        return $this->db->delete('skills');
    }

    /**
     * Retrieve all skills from the database.
     * 
     * @return array Returns an array of skills.
     */
    public function get_all() {
        $this->db->select('id, name, description, created_at, updated_at');
        $this->db->from('skills');
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Retrieve a single skills by ID.
     * 
     * @param int $skills_id The ID of the skills.
     * @return object|null Returns the skills object or null if not found.
     */
    public function get_by_id($skills_id) {
        $this->db->select('id, name, description, created_at, updated_at');
        $this->db->from('skills');
        $this->db->where('id', $skills_id);
        $query = $this->db->get();
        return $query->row();
    }
}
?>
