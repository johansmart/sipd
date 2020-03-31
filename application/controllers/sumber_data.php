<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sumber_data extends CI_Controller {

	public function __construct() {
	
		parent::__construct();
		
			$this->load->model('all_model','',TRUE);
			
	}
	
	
	public function index() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Sumber Data';
		

		if($this->uri->segment(3) == 'search'){
		
		$keyword = 'sumber_data';
		
		if($this->input->post('type')){
		
				$data = array(
								'search_'.$keyword	    => $this->input->post('search'),
								'type_'.$keyword	    => $this->input->post('type'),
							);
							
			$this->session->set_userdata($data);
			
			$key = $this->session->userdata('search_'.$keyword);
			$type = $this->session->userdata('type_'.$keyword);
			
			
									
		} else {
		
			$key = $this->session->userdata('search_'.$keyword);
			$type = $this->session->userdata('type_'.$keyword);
			
		}
		
		$config['base_url']        = site_url('sumber-data/index/search');
		$config['total_rows'] 	   = $this->all_model->sumber_data_num_rows_search($type,$key);
		$config['per_page'] 	   = 20;
		$config['num_links'] 	   = 10;
		$config['full_tag_open']   = '<ul class="pagination pagination-sm pull-right">';
		$config['first_link']      = '«';
		$config['first_tag_open']  = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_link']       = '»';
		$config['last_tag_open']   = '<li>';
		$config['last_tag_close']  = '</li>';
		$config['full_tag_close']  = '</ul>';
		$config['next_link']       = '&gt;';
		$config['next_tag_open']   = '<li>';
		$config['next_tag_close']  = '</li>';
		$config['prev_link']       = '&lt;';
		$config['prev_tag_open']   = '<li>';
		$config['prev_tag_close']  = '</li>';
		$config['cur_tag_open']    = '<li><a href="#"><b>';
		$config['cur_tag_close']   = '</b></a></li>';
		$config['num_tag_open']    = '<li>';
		$config['num_tag_close']   = '</li>';
		$config['uri_segment']     = 4;
		
		$this->pagination->initialize($config); 
		
		$contents['row']	        = $this->all_model->sumber_data_paging_search($type,$key,$config['per_page'],$this->uri->segment(4));
		$contents['paging']        		= $this->pagination->create_links();
	
		$contents['sumber_data_total']   = $config['total_rows'] ;
		
		
		} else {
		
		$config['base_url']        = site_url('sumber-data/index/');
		$config['total_rows'] 	   = $this->all_model->sumber_data_num_rows();
		$config['per_page'] 	   = 20;
		$config['num_links'] 	   = 10;
		$config['full_tag_open']   = '<ul class="pagination pagination-sm pull-right">';
		$config['first_link']      = '«';
		$config['first_tag_open']  = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_link']       = '»';
		$config['last_tag_open']   = '<li>';
		$config['last_tag_close']  = '</li>';
		$config['full_tag_close']  = '</ul>';
		$config['next_link']       = '&gt;';
		$config['next_tag_open']   = '<li>';
		$config['next_tag_close']  = '</li>';
		$config['prev_link']       = '&lt;';
		$config['prev_tag_open']   = '<li>';
		$config['prev_tag_close']  = '</li>';
		$config['cur_tag_open']    = '<li><a href="#"><b>';
		$config['cur_tag_close']   = '</b></a></li>';
		$config['num_tag_open']    = '<li>';
		$config['num_tag_close']   = '</li>';
		$config['uri_segment']     = 3;
		
		$this->pagination->initialize($config); 
		
		$contents['row']	       = $this->all_model->sumber_data_paging($config['per_page'],$this->uri->segment(3));
		$contents['paging']            = $this->pagination->create_links();
	
		$contents['sumber_data_total']    = $config['total_rows'];
		
		}
	
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		$template['content'] = $this->load->view('sumber_data',$contents,TRUE);
		
		$this->load->view('template',$template);
		
	}
	
	
	public function tambah() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	
	if($this->input->post('submit')){
	
	
	$cek = $this->all_model->cek_sumber_data_by_id($this->input->post('id_sumber_data'));
	
	if($cek){
	
	
		$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Kode telah dipakai
							 </div>');
							 
							 redirect('sumber-data/index/'.$id);
							 
							 
	} else {
	
	$data = array(
	'id_sumber_data' => $this->input->post('id_sumber_data'),
	'sumber_data' => $this->input->post('sumber_data'),
	'telepon' => $this->input->post('telepon'),
	'alamat' => $this->input->post('alamat'),
        'nilai_default' => 0
	);
	
	$this->db->insert('sumber_data',$data);
	
	$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Tambah Data
							 </div>');
	
	redirect('sumber-data');
	
	}
	
	}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Sumber Data';
		
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		$template['content'] = $this->load->view('tambah_sumber_data',$contents,TRUE);
		
		$this->load->view('template',$template);
		
	}
	
	
	public function edit() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}

	if($this->uri->segment(3) != ''){
	$id = $this->uri->segment(3);
	} else {
	$id='';
	}
	
	if($this->input->post('submit')){
	
	if($this->input->post('id_sumber_data') == $this->input->post('id_edit')){
	
	
	$data = array(
	'id_sumber_data' => $this->input->post('id_sumber_data'),
	'sumber_data' => $this->input->post('sumber_data'),
	'telepon' => $this->input->post('telepon'),
	'alamat' => $this->input->post('alamat')
	);
	
	$this->db->where('id_sumber_data',$this->input->post('id_edit'));
	$this->db->update('sumber_data',$data);
	
	$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Edit Data
							 </div>');
	
	
	redirect('sumber-data/index/'.$id);
	
	
	} else {
	
	
	
	$cek = $this->all_model->cek_sumber_data_by_id($this->input->post('id_sumber_data'));
	
	if($cek){
	
	
		$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Kode telah dipakai
							 </div>');
							 
							 redirect('sumber-data/index/'.$id);
							 
							 
	} else {
	
	$data = array(
	'id_sumber_data' => $this->input->post('id_sumber_data'),
	'sumber_data' => $this->input->post('sumber_data'),
	'telepon' => $this->input->post('telepon'),
	'alamat' => $this->input->post('alamat')
	);
	
	$this->db->where('id_sumber_data',$this->input->post('id_edit'));
	$this->db->update('sumber_data',$data);
	
	$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Edit Data
							 </div>');
	
	
	redirect('sumber-data/index/'.$id);
	
	}
	
	}
	
	}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Sumber Data';
		
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		
	
	
		$contents['row'] = $this->all_model->detail_sumber_data_by_id($id);
		
	
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		$template['content'] = $this->load->view('edit_sumber_data',$contents,TRUE);
		
		$this->load->view('template',$template);
		
	}
	
	
	public function hapus() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	
    //$id = $this->uri->segment(3);
	//$id2 = $this->uri->segment(4);
	
	if($this->input->post('submit')){
	$id = $this->input->post('id_delete');
	$id2 = $this->uri->segment(3);
	
	$this->db->where('id_sumber_data',$id);
	$this->db->delete('sumber_data');
	

	
	$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Hapus Data
							 </div>');
	
	redirect('sumber-data/index/'.$id2);
	} else {
	redirect('sumber-data');
	}
	
		
		
	}
	
	public function set_default() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	$this->db->update('sumber_data',array('nilai_default'=>0));
	
    $id = $this->uri->segment(3);
	$id2 = $this->uri->segment(4);
	
	$this->db->where('id_sumber_data',$id);
	$this->db->update('sumber_data',array('nilai_default'=>1));
	

	
	$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Set Default Data
							 </div>');
	
	redirect('sumber-data/index/'.$id2);
	
	
		
		
	}
	
	public function excel(){
	
	if(!$this->session->userdata('login_user')){redirect('login');}

    ob_clean();

    $this->load->library('PHPExcel');
    $this->load->library('PHPExcel/IOFactory');

    $objPHPExcel = new PHPExcel();

    $objPHPExcel->getProperties()
                ->setTitle("Sumber Data")
                ->setDescription("Data Sumber Data");

    $objPHPExcel->setActiveSheetIndex(0);

    $fields = array('No','Nama Sumber Data','No Telepon','Alamat');

    $col = 0;

    $row = 1;

    foreach($fields as $field)

    {
		
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $field);
        $col++;
    }

	
	$objPHPExcel->getActiveSheet()->getStyle( 'A1' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
	$objPHPExcel->getActiveSheet()->getStyle( 'B1' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
	$objPHPExcel->getActiveSheet()->getStyle( 'C1' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getStyle( 'D1' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
	
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));
		
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));
		
	$objPHPExcel->getActiveSheet()->getStyle('C1')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));
		
	$objPHPExcel->getActiveSheet()->getStyle('D1')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));
	
	//$objPHPExcel->getActiveSheet()->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	
	$styleArray = array(
       'borders' => array(
             'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '848484'),
             ),
       ),
);
$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);

    $row = 2;

	$sumber_data = $this->all_model->sumber_data_all();
	$no_sd = 0;
	foreach($sumber_data as $sd){
	$no_sd++;

        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $no_sd);
		$objPHPExcel->getActiveSheet()->getStyle( 'A'.$row  )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $no_sd);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $sd->sumber_data);

		$objPHPExcel->getActiveSheet()->getStyle( 'C'.$row  )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $sd->telepon);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sd->alamat);

        $row++;

    }


    $objPHPExcel->setActiveSheetIndex(0);

    $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment;filename="sumber_data_'.date('YmdHis').'.xls"');
    header('Cache-Control: max-age=0');
	ob_end_clean();
    $objWriter->save('php://output');
	
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */