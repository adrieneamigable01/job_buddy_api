<?php
class Skills extends CI_Controller {
    /**
     * Class constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->load->model('SkillsModel'); // Ensure you have created the SkillsModel
        $this->load->library('Response', NULL, 'response');
    }

    /**
     * Get all categories.
     */
    public function get() {
        try {
            $categories = $this->SkillsModel->get_all();
            $return = array(
                'isError' => false,
                'message' => 'Success',
                'data' => $categories,
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
     * Create a new category.
     */
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';

        if (empty($name)) {
            $return = array(
                'isError' => true,
                'message' => 'Category name is required',
            );
        } else {
            try {
                $payload = array(
                    'name' => $name,
                    'description' => $description,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                );

                $category_id = $this->SkillsModel->add($payload);

                if ($category_id) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully created category',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error creating category',
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
     * Update an existing category.
     */
    public function update() {
        $data = json_decode(file_get_contents("php://input"), true);
        $category_id = $data['id'] ?? '';
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';

        if (empty($category_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Category ID is required',
            );
        } else if (empty($name)) {
            $return = array(
                'isError' => true,
                'message' => 'Category name is required',
            );
        } else {
            try {
                $payload = array(
                    'name' => $name,
                    'description' => $description,
                    'updated_at' => date('Y-m-d H:i:s'),
                );

                $response = $this->SkillsModel->update($category_id, $payload);

                if ($response) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully updated category',
                        'data' => $payload
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error updating category',
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
     * Delete a category.
     */
    public function delete() {
        $data = json_decode(file_get_contents("php://input"), true);
        $category_id = $data['id'] ?? '';

        if (empty($category_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Category ID is required',
            );
        } else {
            try {
                $response = $this->SkillsModel->delete($category_id);

                if ($response) {
                    $return = array(
                        'isError' => false,
                        'message' => 'Successfully deleted category',
                    );
                } else {
                    $return = array(
                        'isError' => true,
                        'message' => 'Error deleting category',
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
