<?php
class Course extends CI_Controller {
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->load->model('CourseModel'); // Make sure to create CourseModel
        $this->load->library('Response', NULL, 'response');
    }

    /**
     * Get all courses.
     */
    public function get() {
        try {
            $courses = $this->CourseModel->get_all();
            $return = array(
                'isError' => false,
                'message' => 'Success',
                'data' => $courses,
            );
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage(),
            );
        }
        $this->response->output($return);
    }

    /**
     * Create a new course.
     */
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        $course_name = $data['courses'] ?? '';
        $is_active = $data['is_active'] ?? 1;

        if (empty($course_name)) {
            $return = array(
                'isError' => true,
                'message' => 'Course name is required',
            );
        } else {
            try {
                $payload = array(
                    'courses'    => $course_name,
                    'is_active'  => $is_active,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                );

                $course_id = $this->CourseModel->add($payload);

                if ($course_id) {
                    $payload['course_id'] = $course_id;
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully created course',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error creating course',
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

    /**
     * Update an existing course.
     */
    public function update() {
        $data = json_decode(file_get_contents("php://input"), true);
        $course_id = $data['course_id'] ?? '';
        $course_name = $data['courses'] ?? '';
        $is_active = $data['is_active'] ?? 1;

        if (empty($course_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Course ID is required',
            );
        } else if (empty($course_name)) {
            $return = array(
                'isError' => true,
                'message' => 'Course name is required',
            );
        } else {
            try {
                $payload = array(
                    'courses'    => $course_name,
                    'is_active'  => $is_active,
                    'updated_at' => date('Y-m-d H:i:s'),
                );

                $response = $this->CourseModel->update($course_id, $payload);

                if ($response) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully updated course',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error updating course',
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

    /**
     * Delete a course (soft delete using deleted_at).
     */
    public function delete() {
        $data = json_decode(file_get_contents("php://input"), true);
        $course_id = $data['course_id'] ?? '';

        if (empty($course_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Course ID is required',
            );
        } else {
            try {
                $payload = array(
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'is_active'  => 0
                );

                $response = $this->CourseModel->update($course_id, $payload);

                if ($response) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully deleted course',
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error deleting course',
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
