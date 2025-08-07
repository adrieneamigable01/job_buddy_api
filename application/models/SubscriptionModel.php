<?php
/**
 * Subscription Model
 * Author: Adriene Carre Amigable
 * Date Created : 4/23/2025
 * Version: 0.0.1
 */
class SubscriptionModel extends CI_Model {

    /**
     * Add a new subscription (compiled query).
     * 
     * @param array $payload Data to insert into the subscriptions table.
     * @return string Returns the compiled insert query.
     */
    public function add($payload) {
        return $this->db->set($payload)->get_compiled_insert('subscriptions');
    }

    /**
     * Update an existing subscription (compiled query).
     * 
     * @param array $payload Data to update.
     * @param array $where Condition to find the record to update.
     * @return string Returns the compiled update query.
     */
    public function update($payload, $where) {
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('subscriptions');
    }

    /**
     * Get subscriptions based on conditions.
     * 
     * @param array $payload Filter conditions.
     * @return array Result array of subscriptions.
     */
    public function get($payload,$filterExpired = 0) {
        $this->db->select('
            subscriptions.id,
            subscriptions.user_id,
            subscriptions.plan_id,
            subscription_plans.name AS plan_name,
            subscription_plans.description,
            subscription_plans.price,
            subscription_plans.duration_days,
            subscription_plans.max_companies,
            subscription_plans.max_posts,
            subscriptions.start_date,
            subscriptions.end_date,
            subscriptions.is_active,
            subscriptions.auto_renew,
            subscriptions.created_at,
            IF(CURDATE() > subscriptions.end_date, 1, 0) AS is_expired
        ');
        $this->db->from('subscriptions');
        $this->db->join('subscription_plans', 'subscriptions.plan_id = subscription_plans.plan_id', 'left');
        if ($filterExpired == 1) {
            $this->db->where('subscriptions.end_date >=', date('Y-m-d'), false);
        }
        $this->db->where($payload); // this is fine for other key-value exact matches

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Fetch a single plan's details.
     * 
     * @param int $plan_id The ID of the subscription plan.
     * @return object|null The subscription plan details.
     */
    public function getPlan($plan_id) {
        $this->db->select('*');
        $this->db->from('subscription_plans');
        $this->db->where('plan_id', "$plan_id");
        $query = $this->db->get();
        return $query->row(); // returns one row as an object
    }

    public function getActiveSubscription($user_id) {
        return $this->db->where('user_id', $user_id)
                        ->where('is_active', 1)
                        ->get('subscriptions')
                        ->row();
    }
    
    // Deactivate a subscription by ID
    public function deactivateSubscription($subscription_id) {
        $payload = array(
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        );
        $this->db->where(array(
            'id' => "$subscription_id"
        ));
        return $this->db->set($payload)->get_compiled_update('subscriptions');
    }
}
?>
