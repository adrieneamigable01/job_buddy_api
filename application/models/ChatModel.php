<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ChatModel extends CI_Model {

    public function createThread($data) {
        $this->db->insert('chat_threads', [
            'user1_id' => $data['user1_id'],
            'user2_id' => $data['user2_id'],
            'created_by' => $data['created_by'],
            'title' => $data['title'],
            'company_id' => $data['company_id'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert_id();
    }

    public function sendMessage($data) {
        $this->db->insert('chat_messages', [
            'thread_id'   => $data['thread_id'],
            'sender_id'   => $data['sender_id'],
            'message'     => $data['message'],
            'created_at'   => $data['created_at']
        ]);
    }

    public function getParticipantsInfo($user_ids) {
        if (empty($user_ids)) return [];
    
        $this->db->select('
            u.user_id,
            u.username,
            u.user_type,
            CASE
                WHEN u.user_type = "student" THEN CONCAT(s.firstname, " ", s.middlename, " ", s.lastname)
                WHEN u.user_type = "employer" THEN CONCAT(e.firstname, " ", e.middlename, " ", e.lastname)
                ELSE "Unknown"
            END AS full_name,
            s.student_id,
            e.employer_id
        ');
        $this->db->from('users u');
        $this->db->join('students s', 's.user_id = u.user_id', 'left');
        $this->db->join('employer e', 'e.user_id = u.user_id', 'left');
        $this->db->where_in('u.user_id', $user_ids);
    
        $query = $this->db->get();
        $results = $query->result_array();
    
        // Optional: Clean up unused fields depending on user type
        foreach ($results as &$user) {
            if ($user['user_type'] === 'student') {
                unset($user['employer_id']);
            } elseif ($user['user_type'] === 'employer') {
                unset($user['student_id']);
            } else {
                unset($user['student_id'], $user['employer_id']);
            }
        }
    
        return $results;
    }
    

    public function getThreads($user_id) {
        $this->db->select('
            ct.thread_id,
            ct.user1_id,
            ct.user2_id,
            ct.title,
            ct.company_id,
            ct.created_at AS thread_created_at,
            ct.created_by,  
            u.username AS created_by_username, 
            cm.message AS last_message,
            cm.created_at AS last_message_time,
            cm.is_read AS last_message_read,
            cm.sender_id AS last_message_sender,
            GROUP_CONCAT(DISTINCT cmr.user_id) AS read_by_users,  -- Collect users who have read the message
            -- Now get the name from either the students or employer tables
            CASE
                WHEN u.user_type = "student" THEN CONCAT(s.firstname, " ", s.middlename, " ", s.lastname)
                WHEN u.user_type = "employer" THEN CONCAT(e.firstname, " ", e.middlename, " ", e.lastname)
                ELSE "Unknown"
            END AS created_by_name
        ');
    
        $this->db->from('chat_threads ct');
        
        // Join to get the most recent message per thread
        $this->db->join('(SELECT m1.*
                         FROM chat_messages m1
                         INNER JOIN (
                             SELECT thread_id, MAX(created_at) AS latest
                             FROM chat_messages
                             GROUP BY thread_id
                         ) m2 ON m1.thread_id = m2.thread_id AND m1.created_at = m2.latest
                        ) cm', 'cm.thread_id = ct.thread_id', 'left');
    
        // Join to get user data and determine which table (students or employer) to join
        $this->db->join('users u', 'u.user_id = ct.created_by', 'left');
    
        // Conditional joins for students and employers based on user_type
        $this->db->join('students s', 's.user_id = u.user_id', 'left');  // Join with students table
        $this->db->join('employer e', 'e.user_id = u.user_id', 'left');  // Join with employer_table
    
        // Join chat_message_reads to get users who have read the message
        $this->db->join('chat_message_reads cmr', 'cmr.message_id = cm.message_id', 'left');
    
        // Filter threads by user1 or user2 being the current user
        $this->db->where('ct.user1_id', $user_id);
        $this->db->or_where('ct.user2_id', $user_id);
    
        $this->db->group_by('cm.message_id');
        $this->db->order_by('cm.created_at', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    
    
    

    public function getMessagesByThread($thread_id) {
        $sql = "
            SELECT 
                m.*,
                CASE 
                    WHEN s.user_id IS NOT NULL THEN CONCAT_WS(' ', s.lastname, s.firstname, s.middlename)
                    WHEN e.user_id IS NOT NULL THEN CONCAT_WS(' ', e.lastname, e.firstname, e.middlename)
                    ELSE NULL
                END AS senderName,
                GROUP_CONCAT(DISTINCT r.user_id) AS read_by_users
            FROM chat_messages m
            LEFT JOIN students s ON m.sender_id = s.user_id
            LEFT JOIN employer e ON m.sender_id = e.user_id
            LEFT JOIN chat_message_reads r ON r.message_id = m.message_id
            WHERE m.thread_id = ?
            GROUP BY m.message_id
            ORDER BY m.created_at ASC
        ";
    
        $query = $this->db->query($sql, [$thread_id]);
        return $query->result_array();
    }
    
    

    public function isValidUser($user_id) {
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('users');
        return $query->num_rows() > 0;
    }

    public function getUserType($user_id) {
        $this->db->select('user_type');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('users');
        return $query->row('user_type');
    }

    public function markMessageAsRead($message_id, $user_id) {
        // Check if already marked as read
        $exists = $this->db->get_where('chat_message_reads', [
            'message_id' => $message_id,
            'user_id' => $user_id
        ])->row();
    
        if (!$exists) {
            $this->db->insert('chat_message_reads', [
                'message_id' => $message_id,
                'user_id' => $user_id,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function markAllMessagesAsRead($thread_id, $user_id) {
        // Get all messages in the thread
        $this->db->select('message_id');
        $this->db->from('chat_messages');
        $this->db->where('thread_id', $thread_id);
        $query = $this->db->get();
    
        $messages = $query->result();
    
        foreach ($messages as $message) {
            // Check if the user has already marked this message as read
            $this->db->select('id');
            $this->db->from('chat_message_reads');
            $this->db->where('message_id', $message->message_id);
            $this->db->where('user_id', $user_id);
            $readQuery = $this->db->get();
    
            // If no record exists, insert the read record
            if ($readQuery->num_rows() === 0) {
                // Insert the record into chat_message_reads
                $this->db->insert('chat_message_reads', [
                    'message_id' => $message->message_id,
                    'user_id' => $user_id,
                    'read_at' => date('Y-m-d H:i:s') // Current timestamp
                ]);
            }
        }
    
        // Return success
        return true;
    }
    
    
    
    
    
}
