<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logout extends CI_Controller {

	public function __construct() {
	
		parent::__construct();
		
			$this->load->model('all_model','',TRUE);
			
	}
	
	
	public function index() {
	
		$this->session->sess_destroy();
		redirect(site_url('login'));
		
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */