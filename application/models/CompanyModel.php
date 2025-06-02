<?php
/**
 * Company Model
 * Author: Your Name
 * Date Created : 3/12/2025
 * Version: 1.0.0
 */
class CompanyModel extends CI_Model {

    public function add($payload){
        // Ensure all required fields are included in the payload before inserting.
        return $this->db->set($payload)->get_compiled_insert('company'); // Table name 'company'
    }

    public function update($payload, $where){
        // Ensure the fields to be updated are in the payload.
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('company'); // Table name 'company'
    }

    public function get($payload){
        // Select the fields that you need from the company table.
        $this->db->select('
            company.company_id,
            company.company_name,
            company.company_address,
            company.contact_number,
            company.email,
            company.established_date,
            company.is_active,
            company.created_at,
            company.updated_at
        ');
        $this->db->from('company'); // Table name 'company'
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result(); // Returns the results from the database query.
    }

}
?>
