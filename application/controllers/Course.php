<?php

    class Course extends CI_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
            parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->model('CourseModel');
            $this->load->library('Response', NULL, 'response');
        }

        // Check if the token is valid
        public function checkToken() {
            $return = array(
                'isError' => false,
                'message'   => 'Valid Token',
            );
            $this->response->output($return); // Return the JSON encoded data
        }

        // Batch query execution for multiple queries
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

        // Get course data
        public function get() {
            try {
                $course_id = $this->input->get("course_id");
                $is_active = $this->input->get("is_active");
                $payload = array(
                    'is_active' => isset($is_active) ? $is_active : 1
                );

                if (!empty($course_id)) {
                    $payload['course_id'] = $course_id;
                }

                $request = $this->CourseModel->get($payload);

                $return = array(
                    'isError' => false,
                    'message'  => 'Success',
                    'data'     => $request,
                );
            } catch (Exception $e) {
                $return = array(
                    'isError' => true,
                    'message'  => $e->getMessage(),
                );
            }
            $this->response->output($return); // Return the JSON encoded data
        }

        // Create a new course
        public function create() {
            $transQuery = array();

            // Retrieve form data for the course
            $courses = $this->input->post('courses');

            // Validation checks for course data
            if (empty($courses)) {
                $return = array(
                    'isError' => true,
                    'message'   => 'Course name is required',
                );
            } else {
                try {
                    // Prepare the payload for the new course data
                    $payload = array(
                        'courses'    => $courses,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"),
                    );

                    // Call model function to add the new course
                    $response = $this->CourseModel->add($payload);
                    array_push($transQuery, $response);

                    // Handle the transaction result
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);

                    // Success response
                    if ($res) {
                        $return = array(
                            'isError' => false,
                            'message'   => 'Successfully added new course',
                            'data'     => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message'   => 'Error adding course',
                        );
                    }

                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        'isError' => true,
                        'message'   => $e->getMessage(),
                    );
                }
            }

            // Output the response
            $this->response->output($return);
        }

        // Update an existing course
        public function update() {
            $transQuery = array();

            // Retrieve form data
            $course_id = $this->input->post('course_id');
            $courses = $this->input->post('courses');

            // Validation checks for course data
            if (empty($course_id)) {
                $return = array(
                    'isError' => true,
                    'message'   => 'Course ID is required',
                );
            } else if (empty($courses)) {
                $return = array(
                    'isError' => true,
                    'message'   => 'Course name is required',
                );
            } else {
                try {
                    // Prepare the payload for updating the course data
                    $payload = array(
                        'courses'    => $courses,
                        'updated_at' => date("Y-m-d H:i:s"),
                    );

                    // Set the condition to update the specific course
                    $where = array(
                        'course_id' => $course_id
                    );

                    // Call model function to update the course
                    $response = $this->CourseModel->update($payload, $where);
                    array_push($transQuery, $response);

                    // Handle the transaction result
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);

                    // Success response
                    if ($res) {
                        $return = array(
                            'isError' => false,
                            'message'   => 'Successfully updated course',
                            'data'     => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message'   => 'Error updating course',
                        );
                    }

                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        'isError' => true,
                        'message'   => $e->getMessage(),
                    );
                }
            }

            // Output the response
            $this->response->output($return);
        }

        // Soft delete a course
        public function void() {
            $transQuery = array();

            // Retrieve form data for soft delete
            $course_id = $this->input->post('course_id');

            // Validation checks
            if (empty($course_id)) {
                $return = array(
                    'isError' => true,
                    'message'   => 'Course ID is required',
                );
            } else {
                try {
                    // Prepare the payload for soft delete (set deleted_at)
                    $payload = array(
                        'deleted_at' => date("Y-m-d H:i:s"),
                        'is_active'   => 0,
                    );
                    $where = array(
                        'course_id' => $course_id
                    );

                    // Call model function to soft delete the course
                    $response = $this->CourseModel->update($payload, $where);
                    array_push($transQuery, $response);

                    // Handle the transaction result
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);

                    // Success response
                    if ($res) {
                        $return = array(
                            'isError' => false,
                            'message'   => 'Successfully voided course',
                            'data'     => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message'   => 'Error voiding course',
                        );
                    }

                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        'isError' => true,
                        'message'   => $e->getMessage(),
                    );
                }
            }

            // Output the response
            $this->response->output($return);
        }

        // Activate a course
        public function activate() {
            $transQuery = array();

            // Retrieve form data to activate a course
            $course_id = $this->input->post('course_id');

            // Validation checks
            if (empty($course_id)) {
                $return = array(
                    'isError' => true,
                    'message'   => 'Course ID is required',
                );
            } else {
                try {
                    // Prepare the payload for reactivation
                    $payload = array(
                        'is_active'  => 1,
                        'deleted_at' => null,
                        'updated_at' => date("Y-m-d H:i:s"),
                    );
                    $where = array(
                        'course_id' => $course_id
                    );

                    // Call model function to activate the course
                    $response = $this->CourseModel->update($payload, $where);
                    array_push($transQuery, $response);

                    // Handle the transaction result
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);

                    // Success response
                    if ($res) {
                        $return = array(
                            'isError' => false,
                            'message'   => 'Successfully activated course',
                            'data'     => $payload
                        );
                    } else {
                        $return = array(
                            'isError' => true,
                            'message'   => 'Error activating course',
                        );
                    }

                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        'isError' => true,
                        'message'   => $e->getMessage(),
                    );
                }
            }

            // Output the response
            $this->response->output($return);
        }
    }
?>
