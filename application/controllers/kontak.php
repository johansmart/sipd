<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Kontak extends CI_Controller {

	public function __construct() {
	
		parent::__construct();
		
			$this->load->model('all_model','',TRUE);
			
	}
	
	
	public function index() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
		if($this->input->post('submit')){
		
		$config['upload_path']   = base_url().'public/images/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$this->load->library('upload', $config);
			
		if(empty($_FILES['userfile']['name'])){

		$data = array(
		'alamat' => $this->input->post('alamat'),
		'telepon' => $this->input->post('telepon'),
		'fax' => $this->input->post('fax'),
		'email' => $this->input->post('email'),
		'website' => $this->input->post('website'),
		'latitude_longitude' => $this->input->post('latitude_longitude'),
		'ket_peta' => $this->input->post('ket_peta')
		);
		
		$this->db->update('kontak',$data);
		
		$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Kontak Berhasil Diubah
							 </div>');
		redirect (site_url('kontak'));
		}
		else {
		
			if ( ! $this->upload->do_upload()){
				
				
				$contens['alert'] = '<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> File Foto Salah.
							 </div>';
				
			}else{		

		$kontak = $this->all_model->kontak();
	
	
		if(!empty($kontak->foto)){
		unlink('public/images/'.$kontak->foto);
		}
		
		
		
		
		$data = array(
		'alamat' => $this->input->post('alamat'),
		'telepon' => $this->input->post('telepon'),
		'fax' => $this->input->post('fax'),
		'email' => $this->input->post('email'),
		'website' => $this->input->post('website'),
		'latitude_longitude' => $this->input->post('latitude_longitude'),
		'ket_peta' => $this->input->post('ket_peta'),
		'foto' => $this->upload->file_name
		);
		
		$this->db->update('kontak',$data);
	
			
				
				$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Kontak Berhasil Diubah
							 </div>');
				redirect (site_url('kontak'));
				
			}	
		}		
	
		
		
		}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Kontak';
		
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		$contents['kontak'] = $this->all_model->kontak();
	
		
		$template['content'] = $this->load->view('kontak',$contents,TRUE);
		
		$this->load->view('template',$template);

		
		
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */