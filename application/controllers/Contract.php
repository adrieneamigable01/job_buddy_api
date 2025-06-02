<?php
/**
 * @author  
 * @version 0.1.0
 */
class Contract extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('ContractModel');
        $this->load->library('Response', NULL, 'response');
    }

    public function checkToken() {
        $return = array(
            'isError' => false,
            'message' => 'Valid Token',
        );
        $this->response->output($return);
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

    public function get() {
        try {
            $contract_id = $this->input->get("contracts_id");
            $payload = [];

            if (!empty($contract_id)) {
                $payload['contracts_id'] = $contract_id;
            }

            $request = $this->ContractModel->get($payload);

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

        $this->response->output($return);
    }

    public function upload() {
        $transQuery = array();
    
        $data = json_decode(file_get_contents("php://input"), true);

        // Set default values
        $payload = array(
            'job_offers_id'   => $data['job_offers_id'] ?? '',
            'student_id'      => $data['student_id'] ?? '',
            'contract_title'  => $data['contract_title'] ?? '',
            'pdf_path'        => $data['pdf_path'] ?? '',
            'signature'       => $data['signature'] ?? '',
            'signed_at'       => date("Y-m-d H:i:s"),
            'status'          => $data['status'] ?? 'pending'
        );
        
        // Define required fields
        $requiredFields = ['job_offers_id', 'student_id', 'contract_title', 'pdf_path'];
        
        // Validate required fields
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (empty($payload[$field])) {
                $missingFields[] = $field;
            }
        }

        
        // If there are missing fields, return an error response
        if (!empty($missingFields)) {
 
            $return = array(
                'isError' => true,
                'message' => 'Successfully added contract',
                'data' =>  'Missing or empty required fields: ' . implode(', ', $missingFields),
            );
            $this->response->output($return);return;
        }

    
        try {
            $response = $this->ContractModel->add($payload);
            array_push($transQuery, $response);
    
            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
    
            if ($res) {
                $return = array(
                    'isError' => false,
                    'message' => 'Successfully added contract',
                    'data' => $payload
                );
            } else {
                $return = array(
                    'isError' => true,
                    'message' => 'Failed to add contract',
                );
            }
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage(),
            );
        }
    
        $this->response->output($return);
    }
    
    public function update() {
        $transQuery = array();
    
        $data = json_decode(file_get_contents("php://input"), true);
        $contracts_id = $data['contracts_id'] ?? null;
    
        if (empty($contracts_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Contract ID is required',
            );
            $this->response->output($return);
            return;
        }
    
        $payload = array(
            'contract_title' => $data['contract_title'] ?? '',
            'pdf_path'       => $data['pdf_path'] ?? '',
            'signature'      => $data['signature'] ?? '',
            'status'         => $data['status'] ?? '',
            'signed_at'      => date("Y-m-d H:i:s")
        );
    
        try {
            $where = ['contracts_id' => $contracts_id];
            $response = $this->ContractModel->update($payload, $where);
            array_push($transQuery, $response);
    
            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
    
            if ($res) {
                $return = array(
                    'isError' => false,
                    'message' => 'Successfully updated contract',
                    'data' => $payload
                );
            } else {
                $return = array(
                    'isError' => true,
                    'message' => 'Failed to update contract',
                );
            }
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage(),
            );
        }
    
        $this->response->output($return);
    }
    
    public function void() {
        $transQuery = array();
    
        $data = json_decode(file_get_contents("php://input"), true);
        $contracts_id = $data['contracts_id'] ?? null;
    
        if (empty($contracts_id)) {
            $return = array(
                'isError' => true,
                'message' => 'Contract ID is required',
            );
            $this->response->output($return);
            return;
        }
    
        try {
            $payload = array(
                'status' => 'voided'
            );
            $where = ['contracts_id' => $contracts_id];
            $response = $this->ContractModel->update($payload, $where);
            array_push($transQuery, $response);
    
            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
    
            if ($res) {
                $return = array(
                    'isError' => false,
                    'message' => 'Successfully voided contract',
                );
            } else {
                $return = array(
                    'isError' => true,
                    'message' => 'Failed to void contract',
                );
            }
        } catch (Exception $e) {
            $return = array(
                'isError' => true,
                'message' => $e->getMessage(),
            );
        }
    
        $this->response->output($return);
    }
    
}
?>
