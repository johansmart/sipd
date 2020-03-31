<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Item_data extends CI_Controller {

	public function __construct() {
	
		parent::__construct();
		
			$this->load->model('all_model','',TRUE);
			
	}
	
	
	public function index() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	if($this->session->userdata('level') != 'administrator'){redirect('login');}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Item Data';
		
		if($this->input->post('submit')){
		
		$cek1 = $this->all_model->get_data_by_jenis_tahun($this->input->post('id_jenis_data'));
		foreach($cek1 as $cek1){
		
			$cek2 = $this->all_model->cek_item_data($cek1->id_data,$this->input->post('tahun'));
			
			if($cek2 == 0){
				
				$data = array(
					'id_data' => $cek1->id_data,
					'tahun' => $this->input->post('tahun')
				);
				
				$this->db->insert('item_data',$data);
			
			}
		
		}
		
		
		$id_jenis = array(
					'id_jenis'      => $this->input->post('id_jenis_data'),
					'tahun_jenis'      => $this->input->post('tahun'),
					
				);
			
				$this->session->set_flashdata($id_jenis);
		
		$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Tambah Item Data 
							 </div>');
							 
				redirect('item-data');
		
		}

	
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		
		//$contents['jenis_data'] = $this->all_model->jenis_data_all();
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		
		$template['content'] = $this->load->view('item_data',$contents,TRUE);
		
		$this->load->view('template',$template);
		
	}
	
	
	
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */