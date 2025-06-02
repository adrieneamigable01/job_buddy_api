<?php
class Response extends CI_Controller{
    public function __construct(){
		$this->CI =& get_instance();
		$this->setAuthorization();
		$this->CI->load->helper('jwt');
		$this->CI->load->library('cors');
		$this->CI->cors->handle();
    }
	public function mysqlTQ($arrQuery){
		$arrayIds = array();
		if (!empty($arrQuery)) {
			$this->CI->db->trans_start();
			foreach ($arrQuery as $value) {
				$this->CI->db->query($value);
				$last_id =$this->CI->db->insert_id();
				array_push($arrayIds,$last_id);
			}
			if ($this->CI->db->trans_status() === FALSE) {
				$this->CI->db->trans_rollback();
			} else {
				$this->CI->db->trans_commit();
			   
				return $arrayIds;
			}
		}
	}

    public function mysqlTQWids($arrQuery){
		$arrayIds = array();
		if (!empty($arrQuery)) {
			$this->CI->db->trans_start();
			foreach ($arrQuery as $value) {
				$this->CI->db->query($value);
				$last_id =$this->CI->db->insert_id();
				array_push($arrayIds,$last_id);
			}
			if ($this->CI->db->trans_status() === FALSE) {
				$this->CI->db->trans_rollback();
			} else {
				$this->CI->db->trans_commit();
			   
				return $arrayIds;
			}
		}
	}
	private function setAuthorization(){
		$this->CI->output
		->set_header('Access-Control-Allow-Origin: *') // Adjust as needed for security
		->set_header('Access-Control-Allow-Methods: GET, POST, OPTIONS')
		->set_header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');

		if ($this->CI->input->server('REQUEST_METHOD') == 'OPTIONS') {
			$this->CI->output
			->set_status_header(200)
            ->set_output('');
			exit;
		}
	}
	protected function _check_cors()
    {
        // Convert the config items into strings
        $allowed_headers = implode(', ', $this->config->item('allowed_cors_headers'));
        $allowed_methods = implode(', ', $this->config->item('allowed_cors_methods'));

        // If we want to allow any domain to access the API
        if ($this->config->item('allow_any_cors_domain') === TRUE)
        {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: '.$allowed_headers);
            header('Access-Control-Allow-Methods: '.$allowed_methods);
        }
        else
        {
            // We're going to allow only certain domains access
            // Store the HTTP Origin header
            $origin = $this->input->server('HTTP_ORIGIN');
            if ($origin === NULL)
            {
                $origin = '';
            }

            // If the origin domain is in the allowed_cors_origins list, then add the Access Control headers
            if (in_array($origin, $this->config->item('allowed_cors_origins')))
            {
                header('Access-Control-Allow-Origin: '.$origin);
                header('Access-Control-Allow-Headers: '.$allowed_headers);
                header('Access-Control-Allow-Methods: '.$allowed_methods);
            }
        }

        // If there are headers that should be forced in the CORS check, add them now
        if (is_array($this->config->item('forced_cors_headers')))
        {
            foreach ($this->config->item('forced_cors_headers') as $header => $value)
            {
                header($header . ': ' . $value);
            }
        }

        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if ($this->input->method() === 'options')
        {
            // Load DB if needed for logging
            if (!isset($this->rest->db) && $this->config->item('rest_enable_logging'))
            {
                $this->rest->db = $this->load->database($this->config->item('rest_database_group'), TRUE);
            }
            exit;
        }
    }
    function output($data,$isAuth = 0,$code = 200){
		$this->CI->output
		->set_content_type('application/json')
		->set_status_header($code)
		->set_output(json_encode($data));

		if($code == 401){
			$this->CI->output
			->_display();
		}
    }
}
?>