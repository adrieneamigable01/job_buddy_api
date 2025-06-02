<?php
/**
 * @author  Your Name
 * @version 0.1.0
 */

class Subscription extends CI_Controller {

    private $clientId = 'test';
    private $secret = 'tes';
    private $baseUrl = 'https://api-m.sandbox.paypal.com'; // Change to live for production

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('SubscriptionModel'); // Make sure this model exists
        $this->load->model('UserActivityLogModel'); // Make sure this model exists
        $this->load->model('UserModel'); // Make sure this model exists
        $this->load->library('Response', NULL, 'response');
        // $this->load->library('Stripe_lib');


    }

    public function checkToken() {
        $return = array(
            'isError' => false,
            'message' => 'Valid Token',
        );
        $this->response->output($return);
    }

    private function getAccessToken() {
        $ch = curl_init("{$this->baseUrl}/v1/oauth2/token");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->clientId}:{$this->secret}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "Accept-Language: en_US"
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);
        return $data['access_token'] ?? null;
    }

    public function createPaypalSubscription() {
        header('Content-Type: application/json');
        $planId = $this->input->get("plan_id");
        $userId = $this->input->get("user_id");
        $accessToken = $this->getAccessToken();
     
        $data = [
            "plan_id" => $planId,
            "custom_id" => "user_$userId",
            "application_context" => [
                "brand_name" => "JobBuddy",
                "locale" => "en-US",
                "user_action" => "SUBSCRIBE_NOW",
                "payment_method" => [
                    "payer_selected" => "PAYPAL",
                    "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED"
                ],
                "return_url" => base_url()."paypal/subscription/success?plan_id=$planId&user_id=$userId",
                "cancel_url" => base_url()."paypal/subscription/cancel"
            ]
        ];

        $ch = curl_init("{$this->baseUrl}/v1/billing/subscriptions");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$accessToken}"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);

        $result = curl_exec($ch);
        curl_close($ch);

        echo $result;
    }



    public function subscriptionSuccess() {
        
        $transQuery = array();

        $plan_id = $this->input->get("plan_id");
        $uid = $this->input->get("user_id");
        
        // Basic validation
        if (empty($plan_id)) {
            $return = array('isError' => true, 'message' => 'Plan ID is required');
        } else {
            try {
                
                $existing = $this->SubscriptionModel->getActiveSubscription($uid);
            
                if ($existing) {
                    $invalidate = $this->SubscriptionModel->deactivateSubscription($existing->id);
                    array_push($transQuery, $invalidate);
                }

                $user = $this->UserModel->getUser($uid)[0];

                // Get duration from plan
                $plan = $this->SubscriptionModel->getPlan($plan_id);
                if (!$plan) {
                    throw new Exception("Subscription plan not found.");
                }

                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d', strtotime("+{$plan->duration_days} days"));

                $payload = array(
                    'user_id' => $user->user_id,
                    'plan_id' => $plan_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'is_active' => 1,
                );

                $query = $this->SubscriptionModel->add($payload);
                array_push($transQuery, $query);



                $user_activity_data = array(
                    'user_id' => $uid,
                    'activity_type' => 'subscription',
                    'activity_details' => "User {$user->lastname} {$user->firstname} added new subscription plan $plan->name for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => $plan_id,
                    'related_table' => 'subscriptions',
                    'created_at' => date("Y-m-d H:i:s")
                );

                $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
                array_push($transQuery, $user_activity_query);

             
                $result = array_filter($transQuery);
                $res = $this->response->mysqlTQ($result);

                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Subscription created successfully',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Failed to create subscription'
                    );
                }
            } catch (Exception $e) {
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage()
                );
            }
        }

        $this->response->output($return);
    }
    public function subscribe_free() {
        
        $transQuery = array();

        $headers = $this->input->request_headers();
        error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting

        // Retrieve the token from Authorization header
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;


        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        $decoded = decode_jwt($token, $this->config->item('jwt_key'));
        $user_id = $decoded->data->id;
        $uid = $decoded->data->user_id;

        $data = json_decode(file_get_contents("php://input"), true);
        $plan_id = $data['plan_id'] ?? '';
        

        // Basic validation
        if (empty($plan_id)) {
            $return = array('isError' => true, 'message' => 'Plan ID is required');
        } else {
            try {

                $existing = $this->SubscriptionModel->getActiveSubscription($user_id);
               
                if ($existing) {
                    $invalidate = $this->SubscriptionModel->deactivateSubscription($existing->id);
                    array_push($transQuery, $invalidate);
                }

                // Get duration from plan
                $plan = $this->SubscriptionModel->getPlan($plan_id);
                if (!$plan) {
                    throw new Exception("Subscription plan not found.");
                }

                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d', strtotime("+{$plan->duration_days} days"));

                $payload = array(
                    'user_id' => $uid,
                    'plan_id' => $plan_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'is_active' => 1,
                );

                $query = $this->SubscriptionModel->add($payload);
                array_push($transQuery, $query);

                $user_activity_data = array(
                    'user_id' => $uid,
                    'activity_type' => 'subscription',
                    'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} added new subscription plan $plan->name for the date of ".date("Y-m-d H:i:s"),
                    'related_id' => $plan_id,
                    'related_table' => 'subscriptions',
                    'created_at' => date("Y-m-d H:i:s")
                );

                $user_activity_query = $this->UserActivityLogModel->add($user_activity_data);
                array_push($transQuery, $user_activity_query);


                $result = array_filter($transQuery);
                $res = $this->response->mysqlTQ($result);

                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Subscription created successfully',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Failed to create subscription'
                    );
                }
            } catch (Exception $e) {
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage()
                );
            }
        }

        $this->response->output($return);
    }

    public function get() {
        try {
            $user_id = $this->input->get("user_id");
            $is_active = $this->input->get("is_active");

            $payload = array();
            if (!empty($user_id)) {
                $payload['subscriptions.user_id'] = $user_id;
            }
            if (isset($is_active)) {
                $payload['subscriptions.is_active'] = $is_active;
            }

            $data = $this->SubscriptionModel->get($payload);

            $return = array(
                'isError' => false,
                'message' => 'Success',
                'data' => $data
            );
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage()
            );
        }

        $this->response->output($return);
    }

    public function cancel() {
        $transQuery = array();
        $subscription_id = $this->input->post('subscription_id');


        if (empty($subscription_id)) {
            $return = array('isError' => true, 'message' => 'Subscription ID is required');
        } else {
            try {
                $payload = array(
                    'is_active' => 0,
                    'updated_at' => date('Y-m-d')
                );

                $where = array('id' => $subscription_id);

                $query = $this->SubscriptionModel->update($payload, $where);
                array_push($transQuery, $query);
                $result = array_filter($transQuery);
                $res = $this->response->mysqlTQ($result);

                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Subscription canceled successfully',
                        'data' => $payload
                    );
                } else {
                    $return = array('isError' => true, 'message' => 'Failed to cancel subscription');
                }
            } catch (Exception $e) {
                $return = array('isError' => true, 'message' => $e->getMessage());
            }
        }

        $this->response->output($return);
    }
}
?>
