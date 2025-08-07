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
            // Retrieve data from the SkillsModel
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
        $this->response->output($return); // Return the JSON encoded data
    }

    /**
     * Create a new category.
     */
    public function create() {
        // Retrieve data from request
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';

        // Validation checks
        if (empty($name)) {
            $return = array(
                'isError' => true,
                'message' => 'Category name is required',
            );
        } else {
            try {
                // Prepare data for inserting a new category
                $payload = array(
                    'name' => $name,
                    'description' => $description,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                );

                // Call the model's add method to insert the category
                $category_id = $this->SkillsModel->add($payload);

                // Return success or failure
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

        $this->response->output($return); // Return the JSON encoded data
    }

    /**
     * Update an existing category.
     */
    public function update() {
        // Retrieve data from request
        $category_id = $this->input->post('id');
        $name = $this->input->post('name');
        $description = $this->input->post('description');

        // Validation checks
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
                // Prepare data for updating the category
                $payload = array(
                    'name' => $name,
                    'description' => $description,
                    'updated_at' => date('Y-m-d H:i:s'),
                );

                // Update the category using the model
                $response = $this->SkillsModel->update($category_id, $payload);

                // Return success or failure
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

        $this->response->output($return); // Return the JSON encoded data
    }

    /**
     * Delete a category.
     */
    public function delete() {
        $category_id = $this->input->post('id');

        // Validation checks
        if (empty($category_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Category ID is required',
            );
        } else {
            try {
                // Call the model to delete the category
                $response = $this->SkillsModel->delete($category_id);

                // Return success or failure
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

        $this->response->output($return); // Return the JSON encoded data
    }
}
?>
