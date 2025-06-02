<?php
/**
 * @author Adriene Care Llanos Amigable
 * @version 0.1.0
 */

class AppReview extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('AppReviewModel');
        $this->load->model('UserActivityLogModel');
        $this->load->library('Response', NULL, 'response');
    }

    public function get() {
        try {
            $payload = array();
            $headers = $this->input->request_headers();
            error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting
    
            // Retrieve the token from Authorization header
            $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
    
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            $decoded = decode_jwt($token, $this->config->item('jwt_key'));
       
            if(!empty($decoded->data->user_information)){
                $user_id = $decoded->data->user_information[0]->user_id;
                if($decoded->data->user_type != "admin"){
                    $payload['user_id'] = $user_id;
                }
             
            }
            // user_id
            $reviews = $this->AppReviewModel->get_all($payload);
            $return = array(
                'isError' => false,
                'message' => 'Success',
                'data' => $reviews
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
       
        $headers = $this->input->request_headers();
        error_log(print_r($headers, true)); // Debugging: Log headers for troubleshooting

        // Retrieve the token from Authorization header
        $token = $headers['Authorization'] ?? $headers['authorization'] ?? null;


        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        $decoded = decode_jwt($token, $this->config->item('jwt_key'));

        if(!empty($decoded->data->user_information)){
            $user_id = $decoded->data->user_information[0]->user_id;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $rating = $data['rating'] ?? 0;
        $comment = $data['comment'] ?? '';

        if ($rating <= 0 || $rating > 5) {
            $return = array('isError' => true, 'message' => 'Invalid rating value.');
        } else {
            try {
                $payload = array(
                    'user_id' => $user_id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'created_at' => date('Y-m-d H:i:s')
                );

                $review_id = $this->AppReviewModel->add($payload);
                if ($review_id) {

                    $user_activity_data = array(
                        'user_id' => $user_id,
                        'activity_type' => 'app_review',
                        'activity_details' => "User {$decoded->data->lastname} {$decoded->data->firstname} added rating of $rating stars for the date of ".date("Y-m-d H:i:s"),
                        'related_id' => $review_id,
                        'related_table' => 'app_reviews',
                        'created_at' => date("Y-m-d H:i:s")
                    );
    
                    $user_activity_query = $this->UserActivityLogModel->direct_add($user_activity_data);
             
                    $return = array(
                        'isError' => false,
                        'message' => 'Review submitted successfully.',
                        'data' => $payload
                    );
                } else {
                    $return = array('isError' => true, 'message' => 'Failed to submit review.');
                }
            } catch (Exception $e) {
                $return = array('isError' => true, 'message' => $e->getMessage());
            }
        }

        $this->response->output($return);
    }

    public function update() {
        $data = $this->input->post();
        $id = $data['id'] ?? null;
        $rating = $data['rating'] ?? 0;
        $comment = $data['comment'] ?? '';

        if (empty($id)) {
            $return = array('isError' => true, 'message' => 'Review ID is required.');
        } else {
            try {
                $payload = array(
                    'rating' => $rating,
                    'comment' => $comment
                );
                $response = $this->AppReviewModel->update($id, $payload);
                $return = array(
                    'isError' => !$response,
                    'message' => $response ? 'Review updated successfully.' : 'Failed to update review.',
                    'data' => $payload
                );
            } catch (Exception $e) {
                $return = array('isError' => true, 'message' => $e->getMessage());
            }
        }

        $this->response->output($return);
    }

    public function delete() {
        $id = $this->input->post('id');

        if (empty($id)) {
            $return = array('isError' => true, 'message' => 'Review ID is required.');
        } else {
            try {
                $response = $this->AppReviewModel->delete($id);
                $return = array(
                    'isError' => !$response,
                    'message' => $response ? 'Review deleted successfully.' : 'Failed to delete review.'
                );
            } catch (Exception $e) {
                $return = array('isError' => true, 'message' => $e->getMessage());
            }
        }

        $this->response->output($return);
    }
}
?>
