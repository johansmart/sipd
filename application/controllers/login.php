<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct() {
	
		parent::__construct();
		
			$this->load->model('all_model','',TRUE);
			
	}
	
	
	public function index() {
	
	if($this->session->userdata('login_user')){redirect('home');}
	
	if($this->input->post('submit')){
	
	$cek = $this->all_model->cek_login($this->input->post('username'),md5($this->input->post('password')));
	
		if($cek > 0){
			
		
	
			$detail = $this->all_model->row_login($this->input->post('username'));
			
			
				$data = array(
					'id_login'      => $detail->id_login,
					'login_user'    => $detail->username,
					'nama_user'     => $detail->nama,
					'level'         => $detail->level
				);
			
				$this->session->set_userdata($data);
				
		
					$up_lg = array(
						'last_login' => date('Y-m-d H:i:s')
					);
					$this->db->where('id_login',$detail->id_login);
					$this->db->update('login',$up_lg);
					
				redirect('home');
			
			
			
		} else {
		
		$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Login Gagal ! </strong> Username / Password Anda Salah.
							 </div>');
			
		redirect('login');
		
		}
	
	} 
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Login';
	
		$this->load->view('login',$contents);
		
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */