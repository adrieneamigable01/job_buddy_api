<?php
/**
 * Contract Model
 * Author: [Your Name]
 * Date Created: [Date]
 * Version: 1.0.0
 */
class ContractModel extends CI_Model {

    public function add($payload) {
        $this->db->insert('contracts', $payload);
        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        return false;
    }
    public function add_direct($payload) {
        return $this->db->set($payload)->get_compiled_insert('contracts');
    }

    public function update($payload, $where) {
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('contracts');
    }
    public function update_direct($payload, $where) {
        $this->db->where($where);
        return $this->db->update('contracts', $payload);
    }

    public function get($payload) {
        $this->db->select('
            contracts.contracts_id,
            contracts.job_offers_id,
            contracts.student_id,
            contracts.contract_title,
            contracts.pdf_path,
            contracts.signature,
            contracts.signed_at,
            contracts.status
        ');
        $this->db->from('contracts');
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result();
    }
}
?>
