<?php
/**
 * AppReview Model
 * Author: Adriene Care Llanos Amigable
 * Version: 1.0.0
 */
class AppReviewModel extends CI_Model {

    public function add($payload) {
        $this->db->insert('app_reviews', $payload);
        return $this->db->insert_id();
    }

    public function update($id, $payload) {
        $this->db->where('id', $id);
        return $this->db->update('app_reviews', $payload);
    }

    public function delete($id) {
        return $this->db->delete('app_reviews', array('id' => $id));
    }

    public function get_all($payload) {
        $this->db->select('id, user_id, rating, comment, created_at');
        $this->db->from('app_reviews');
        $this->db->where($payload);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_by_user($user_id) {
        $this->db->where('user_id', $user_id);
        return $this->db->get('app_reviews')->result_array();
    }
}
?>
