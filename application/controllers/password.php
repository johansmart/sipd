<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Password extends CI_Controller {

	public function __construct() {
	
		parent::__construct();
		
			$this->load->model('all_model','',TRUE);
			
	}
	
	
	public function index() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
		if($this->input->post('submit')){
		
		$username     = $this->session->userdata('login_user');
		$pwdlama      = md5($this->input->post('pwdlama'));
		$pwd1         = $this->input->post('password');
		$pwd2         = $this->input->post('confirm_password');
		
		$cek_login = $this->all_model->row_login($username);
	
			if($pwdlama != $cek_login->password){
		
				$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Password Lama Anda Salah.
							 </div>');
		
			} else if($pwd1 != $pwd2){
		
				$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Isian Password Anda Tidak Sama.
							 </div>');
		
			} else {
			
				$data = array(
					'password' => md5($pwd1)
				);
			
				$this->db->where('id_login',$cek_login->id_login);
				$this->db->update('login',$data);
			
				$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Password Berhasil Diubah
							 </div>');
			
			}
		
		redirect('password');
		
		}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Password';
		
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
	
		
		$template['content'] = $this->load->view('password',$contents,TRUE);
		
		$this->load->view('template',$template);

		
		
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */