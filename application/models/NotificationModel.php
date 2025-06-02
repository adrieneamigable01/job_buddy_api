<?php
/**
 * notifications Model
 * Author: Adriene Carre Llanos Amigable
 * Date Created: 5/15/2025
 * Version: 0.2.0
 */
class NotificationModel extends CI_Model {

    public function add($payload) {
        $this->db->insert('notifications', $payload);
        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function get_by_user($user_id) {
        $this->db->select('
            n.id,
            n.receive_by,
            n.created_by,
            n.company_id,
            c.company_name,
            n.title,
            n.message,
            n.is_read,
            n.created_at,
            
            -- Sender full name
            CASE 
                WHEN su.user_type = "student" THEN CONCAT(ss.firstname, " ", ss.middlename, " ", ss.lastname)
                WHEN su.user_type = "employer" THEN CONCAT(se.firstname, " ", se.middlename, " ", se.lastname)
                ELSE "Unknown"
            END AS sender_name,
    
            -- Receiver full name
            CASE 
                WHEN ru.user_type = "student" THEN CONCAT(rs.firstname, " ", rs.middlename, " ", rs.lastname)
                WHEN ru.user_type = "employer" THEN CONCAT(re.firstname, " ", re.middlename, " ", re.lastname)
                ELSE "Unknown"
            END AS receiver_name
        ');
    
        $this->db->from('notifications n');
    
        // Join company info
        $this->db->join('company c', 'n.company_id = c.company_id', 'left');
    
        // Sender info
        $this->db->join('users su', 'su.user_id = n.created_by', 'left');
        $this->db->join('students ss', 'ss.user_id = su.user_id', 'left');
        $this->db->join('employer se', 'se.user_id = su.user_id', 'left');
    
        // Receiver info
        $this->db->join('users ru', 'ru.user_id = n.receive_by', 'left');
        $this->db->join('students rs', 'rs.user_id = ru.user_id', 'left');
        $this->db->join('employer re', 're.user_id = ru.user_id', 'left');
    
        // WHERE clause: user is either the sender or receiver
        $this->db->where("(n.receive_by = '$user_id' OR n.created_by = '$user_id')", null, false);
    
        $this->db->order_by('n.created_at', 'DESC');
    
        $query = $this->db->get();
        return $query->result();
    }
    
    

    public function get_by_receiver($receive_by) {
        $this->db->select('id, receive_by, created_by, company_id, title, message, is_read, created_at');
        $this->db->from('notifications');
        $this->db->where('receive_by', $receive_by);
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function mark_as_read($id) {
        $this->db->where('id', $id);
        return $this->db->update('notifications', ['is_read' => 1]);
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('notifications');
    }
}
?>
