<?php
/**
 * SubscriptionPlan Controller
 * Author: Adriene Carre Amigable
 * Date: 2025-04-23
 * Version: 0.1.0
 */

class SubscriptionPlan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('SubscriptionPlanModel');
        $this->load->library('Response', NULL, 'response');
    }

    public function get() {
        try {
            $id = $this->input->get("id");

            $payload = array();
            if (!empty($id)) {
                $payload['id'] = $id;
            }

            $result = $this->SubscriptionPlanModel->get($payload);

            $return = array(
                'isError' => false,
                'message' => 'Success',
                'data' => $result
            );
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage()
            );
        }

        $this->response->output($return);
    }

    public function create() {
        $transQuery = array();

        $name = $this->input->post('name');
        $description = $this->input->post('description');
        $price = $this->input->post('price');
        $duration_days = $this->input->post('duration_days');
        $max_companies = $this->input->post('max_companies');
        $max_posts = $this->input->post('max_posts');

        if (empty($name) || empty($price) || empty($duration_days)) {
            $return = array(
                'isError' => true,
                'message' => 'Name, price, and duration are required.'
            );
        } else {
            try {
                $payload = array(
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'duration_days' => $duration_days,
                    'max_companies' => $max_companies,
                    'max_posts' => $max_posts
                );

                $query = $this->SubscriptionPlanModel->add($payload);
                array_push($transQuery, $query);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully created subscription plan.',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Failed to create subscription plan.'
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

    public function update() {
        $transQuery = array();

        $id = $this->input->post('id');
        $payload = $this->input->post();

        if (empty($id)) {
            $return = array(
                'isError' => true,
                'message' => 'Subscription plan ID is required.'
            );
        } else {
            try {
                $where = array('id' => $id);
                $query = $this->SubscriptionPlanModel->update($payload, $where);
                array_push($transQuery, $query);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Subscription plan updated successfully.',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Failed to update subscription plan.'
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
}
?>
