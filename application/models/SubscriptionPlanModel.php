<?php
/**
 * Subscription Plan Model
 * Author: Adriene Carre Amigable
 * Date: 2025-04-23
 * Version: 0.1.0
 */

class SubscriptionPlanModel extends CI_Model {

    public function add($payload) {
        return $this->db->set($payload)->get_compiled_insert('subscription_plans');
    }

    public function update($payload, $where) {
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('subscription_plans');
    }

    public function get($payload) {
        $this->db->select('*');
        $this->db->from('subscription_plans');
        if (!empty($payload)) {
            $this->db->where($payload);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function isNameExists($name, $exclude_id = null) {
        $this->db->select('id');
        $this->db->from('subscription_plans');
        $this->db->where('name', $name);
        if (!empty($exclude_id)) {
            $this->db->where('id !=', $exclude_id);
        }
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }
}
?>
