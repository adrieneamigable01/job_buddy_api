<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Uuid extends CI_Controller{
    public function __construct() {
        $this->CI =& get_instance();
    }

    function generate_uuid_v4() {
        $data = random_bytes(16);
        // Set version to 4 (UUIDv4) and set the variant to 2 (RFC 4122)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    function generate_unique_id() {
        do {
            $id = $this->generate_uuid_v4();
        } while ($this->check_id_exists($id));
        return $id;
    }

    public function check_id_exists($id){
        $this->CI->db->where('stockid', $id);
        $query = $this->CI->db->get('stocks');
        return $query->num_rows() > 0;
    }
    
}

?>