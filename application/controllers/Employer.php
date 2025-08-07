<?php

class Employer extends MY_Controller {
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('EmployerModel');
        $this->load->library('Response', NULL, 'response');
    }

    public function checkToken() {
        $return = array(
            'isError' => false,
            'message' => 'Valid Token',
        );
        $this->response->output($return); // return the JSON encoded data
    }

    public function mysqlTQ($arrQuery) {
        $arrayIds = array();
        if (!empty($arrQuery)) {
            $this->db->trans_start();
            foreach ($arrQuery as $value) {
                $this->db->query($value);
                $last_id = $this->db->insert_id();
                array_push($arrayIds, $last_id);
            }
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
            } else {
                $this->db->trans_commit();
                return $arrayIds;
            }
        }
    }

    /**
     * Get all active employers.
     */
    public function get() {
        try {
            $employer_id = $this->input->get("employer_id");
            $is_active = $this->input->get("is_active");
            $payload = array(
                'employer.is_active' => isset($is_active) ? $is_active : 1
            );

            if (!empty($employer_id)) {
                $payload['employer_id'] = $employer_id;
            }

            $request = $this->EmployerModel->get($payload);

            $return = array(
                'isError' => false,
                'message' => 'Success',
                'data' => $request,
            );
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage(),
            );
        }
        $this->response->output($return); // return the JSON encoded data
    }

    public function update() {
        $transQuery = array();

        // Retrieve form data for employer
        $employer_id = $this->input->post('employer_id');
        $lastname = $this->input->post('lastname');
        $firstname = $this->input->post('firstname');
        $middlename = $this->input->post('middlename');
        $phone = $this->input->post('phone');
        $address = $this->input->post('address');
        $birthdate = $this->input->post('birthdate');
        $gender = $this->input->post('gender');
        $email = $this->input->post('email');

        // Validation checks for employer data
        if (empty($employer_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Employer ID is required',
            );
        } else if (empty($lastname)) {
            $return = array(
                'isError' => true,
                'message' => 'Last name is required',
            );
        } else if (empty($firstname)) {
            $return = array(
                'isError' => true,
                'message' => 'First name is required',
            );
        } else if (empty($phone)) {
            $return = array(
                'isError' => true,
                'message' => 'Phone number is required',
            );
        } else if (empty($email)) {
            $return = array(
                'isError' => true,
                'message' => 'Email is required',
            );
        } else if (empty($birthdate)) {
            $return = array(
                'isError' => true,
                'message' => 'Birthdate is required',
            );
        } else {
            try {
                // Payload array for employer data to be updated
                $payload = array(
                    'lastname' => $lastname,
                    'firstname' => $firstname,
                    'middlename' => $middlename,
                    'phone' => $phone,
                    'address' => $address,
                    'birthdate' => $birthdate,
                    'gender' => $gender,
                    'email' => $email,
                    'updated_at' => date("Y-m-d"),
                );

                // The condition to find the employer record to update
                $where = array(
                    'employer_id' => $employer_id
                );

                // Call model function to update the employer
                $response = $this->EmployerModel->update($payload, $where);
                array_push($transQuery, $response);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                // Success response
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully updated employer',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error updating employer',
                    );
                }

            } catch (Exception $e) {
                // Handle exception and return error response
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        // Output the response
        $this->response->output($return);
    }

    public function void() {
        $transQuery = array();

        // Retrieve form data
        $employer_id = $this->input->post('employer_id');

        // Validation checks
        if (empty($employer_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Employer ID is required',
            );
        } else {
            try {
                // Payload array for updating the employer to soft-delete it
                $payload = array(
                    'is_active' => 0,
                    'deleted_at' => date("Y-m-d"),
                );

                $where = array(
                    'employer_id' => $employer_id
                );

                // Call model function to update employer status
                $response = $this->EmployerModel->update($payload, $where);
                array_push($transQuery, $response);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                // Success response
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully void employer',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error void employer',
                    );
                }

            } catch (Exception $e) {
                // Handle exception and return error response
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        // Output the response
        $this->response->output($return);
    }

    public function activate() {
        $transQuery = array();

        // Retrieve form data
        $employer_id = $this->input->post('employer_id');

        // Validation checks
        if (empty($employer_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Employer ID is required',
            );
        } else {
            try {
                // Payload array for updating the employer to activate it
                $payload = array(
                    'is_active' => 1,
                    'deleted_at' => null,
                    'updated_at' => date("Y-m-d"),
                );

                $where = array(
                    'employer_id' => $employer_id
                );

                // Call model function to update employer status
                $response = $this->EmployerModel->update($payload, $where);
                array_push($transQuery, $response);
                $result = array_filter($transQuery);
                $res = $this->mysqlTQ($result);

                // Success response
                if ($res) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully activated employer',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error activating employer',
                    );
                }

            } catch (Exception $e) {
                // Handle exception and return error response
                $return = array(
                    'isError' => true,
                    'message' => $e->getMessage(),
                );
            }
        }

        // Output the response
        $this->response->output($return);
    }
}
?>
