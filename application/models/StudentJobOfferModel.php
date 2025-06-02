<?php
/**
 * StudentJobOffers Model
 * Author: Adriene Care Llanos Amigable
 * Date Created: 5/17/2025
 * Version: 0.1.0
 */
class StudentJobOfferModel extends CI_Model {

    public function add($payload) {
        $this->db->insert('student_job_offers', $payload);
        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function updateByStudentAndOffer($student_id, $job_offer_id, $payload) {
        $this->db->where('student_id', $student_id);
        $this->db->where('job_offer_id', $job_offer_id);
        return $this->db->update('student_job_offers', $payload);
    }
    

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('student_job_offers');
    }

    public function get_all() {
        $this->db->select('sjo.id, sjo.student_id, sjo.job_offer_id, sjo.status, sjo.date_offered, sjo.date_responded, s.full_name AS student_name, j.job_title');
        $this->db->from('student_job_offers sjo');
        $this->db->join('students s', 'sjo.student_id = s.id', 'left');
        $this->db->join('job_offers j', 'sjo.job_offer_id = j.id', 'left');
        $this->db->order_by('sjo.date_offered', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_by_id($id) {
        $this->db->select('*');
        $this->db->from('student_job_offers');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function check_if_exists($student_id, $job_offer_id) {
        $this->db->where('student_id', $student_id);
        $this->db->where('job_offer_id', $job_offer_id);
        $query = $this->db->get('student_job_offers');
        return $query->num_rows() > 0;
    }
    

    public function hasBeenSent($student_id, $job_offer_ids = []) {
        if (empty($job_offer_ids)) return [];

        $this->db->select('job_offer_id');
        $this->db->from('student_job_offers');
        $this->db->where('student_id', $student_id);
        $this->db->where_in('job_offer_id', $job_offer_ids);

        $query = $this->db->get();
        $results = $query->result_array();

        return array_column($results, 'job_offer_id');
    }

    public function getOfferStatus($student_id, $job_offer_id)
    {
        return $this->db
            ->where('student_id', $student_id)
            ->where('job_offer_id', $job_offer_id)
            ->get('student_job_offers')
            ->row_array(); // returns full row with 'status' key
    }

}
?>
