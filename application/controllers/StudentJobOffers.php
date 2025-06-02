<?php
/**
 * StudentJobOffers Controller
 * Author: Adriene Care Llanos Amigable
 * Version: 0.1.0
 */

class StudentJobOffers extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('StudentJobOfferModel');
        $this->load->library('Response', NULL, 'response');
    }

    public function get() {
        try {
            $offers = $this->StudentJobOfferModel->get_all();
            $return = array(
                'isError' => false,
                'message' => 'Success',
                'data' => $offers,
            );
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage(),
            );
        }
        $this->response->output($return);
    }

    public function sendOffer() {
        $data = json_decode(file_get_contents("php://input"), true);
        $student_id = $data['student_id'] ?? '';
        $job_offer_id = $data['job_offer_id'] ?? '';
    
        if (empty($student_id) || empty($job_offer_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Student ID and Job Offer ID are required',
            );
        } else {
            try {
                // Check if offer already exists
                $exists = $this->StudentJobOfferModel->check_if_exists($student_id, $job_offer_id);
                if ($exists) {
                    $return = array(
                        'isError' => true,
                        'message' => 'Job offer already sent to this student.',
                    );
                } else {
                    $payload = array(
                        'student_id' => $student_id,
                        'job_offer_id' => $job_offer_id,
                        'date_offered' => date('Y-m-d H:i:s'),
                        'status' => 'sent',
                    );
    
                    $inserted = $this->StudentJobOfferModel->add($payload);
                    if ($inserted) {
                        $return = array(
                            'isError' => false,
                            'message' => 'Job offer successfully sent',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message' => 'Failed to send job offer',
                        );
                    }
                }
            } catch (Exception $e) {
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }
    
        $this->response->output($return);
    }
    

    public function update() {
        $id = $this->input->post('id');
        $status = $this->input->post('status');

        if (empty($id) || empty($status)) {
            $return = array(
                'isError' => true,
                'message' => 'ID and Status are required',
            );
        } else {
            try {
                $payload = array(
                    'status' => $status,
                    'date_responded' => date('Y-m-d H:i:s'),
                );

                $response = $this->StudentJobOfferModel->update($id, $payload);

                if ($response) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully updated student job offer',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error updating student job offer',
                    );
                }
            } catch (Exception $e) {
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        $this->response->output($return);
    }

    public function delete() {
        $id = $this->input->post('id');

        if (empty($id)) {
            $return = array(
                'isError' => true,
                'message' => 'ID is required',
            );
        } else {
            try {
                $response = $this->StudentJobOfferModel->delete($id);

                if ($response) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully deleted student job offer',
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error deleting student job offer',
                    );
                }
            } catch (Exception $e) {
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        $this->response->output($return);
    }

    
}
?>
