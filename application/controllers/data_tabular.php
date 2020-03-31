<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data_tabular extends CI_Controller {

	public function __construct() {
	
		parent::__construct();
		
			$this->load->model('all_model','',TRUE);
			
	}
	
	
	public function index() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
	}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->uri->segment(3);
		$tahun = $this->uri->segment(4);
		
		
		$contents['id_jenis_data'] = $id ;
		$contents['tahun'] = $tahun ;
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		
		
		
		/*
		$cek1 = $this->all_model->get_data_by_jenis_tahun($id);
		foreach($cek1 as $cek1){
		
			$cek2 = $this->all_model->cek_item_data($cek1->id_data,$tahun);
			
			if($cek2 == 0){
				
				$data = array(
					'id_data' => $cek1->id_data,
					'tahun' => $tahun
				);
				
				$this->db->insert('item_data',$data);
			
			}
		
		}
		
		
		*/
		
		
		// edit data
		if($this->input->post('submit_edit_data')){
		
		$row = count($this->input->post('id_item_data'));
		
		//echo $row;
		
		$id_item_data = $this->input->post('id_item_data');
		$nilai = $this->input->post('nilai');
		$ketersediaan_data = $this->input->post('ketersediaan_data');
		$sumber_data = $this->input->post('sumber_data');
		
			for($i=0;$i<$row;$i++){
			
			if($nilai[$i] == 'n/a'){
			$nilai[$i] = NULL;
			} else if($nilai[$i] == ''){
			$nilai[$i] = NULL;
			} else {
			$nilai[$i] = $nilai[$i];
			}
			
			if(isset($ketersediaan_data[$i])){
			
			if($ketersediaan_data[$i] == 'Ada'){
			$ketersediaan_data[$i] = 1;
			} else {
			$ketersediaan_data[$i] = 0;
			}
			
			} else {
			$ketersediaan_data[$i] = NULL;
			}
			
			
			if($sumber_data[$i] == ''){
			$sumber_data[$i] = NULL;
			} else {
			$sumber_data[$i] = $sumber_data[$i];
			}
			
			$nilai[$i] = str_replace('.','',$nilai[$i]);
			$nilai[$i] = str_replace(',','.',$nilai[$i]);
			
			$data['nilai']	                 = $nilai[$i];
			$data['ketersediaan_data']		 = $ketersediaan_data[$i];
			$data['sumber_data']		     = $sumber_data[$i];
			
			$this->db->where('id_item_data',$id_item_data[$i]);
			$this->db->update('item_data',$data);
			
			
			}
			
			
		
							 $this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Edit Data 
							 </div>');
							 
				redirect('data-tabular/index/'.$id .'/'.$tahun);
		
		
		}
		
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		
		//$contents['data_tabular'] = $this->all_model->data_tabular($id,$tahun);
		//$contents['data_tabular'] = $this->all_model->data_tabular_1($id,$tahun);
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$template['content'] = $this->load->view('data_tabular',$contents,TRUE);
		
		$this->load->view('template',$template);
		
	}
	
	
	public function salin_data() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
		}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->uri->segment(3);
		$tahun = $this->uri->segment(4);
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		
		// submit request
			if($this->input->post('submit')){
			
		
		$tahun_sebelum = $tahun - 1;
			/*
		$cek1 = $this->all_model->get_data_by_jenis_tahun($id);
		foreach($cek1 as $cek1){
		
			$cek2 = $this->all_model->cek_item_data($cek1->id_data,$tahun_sebelum);
			
			if($cek2 == 0){
				
				$data = array(
					'id_data' => $cek1->id_data,
					'tahun' => $tahun
				);
				
				$this->db->insert('item_data',$data);
			
			}
		
		}
		
		*/
		
		// cek data tahun sebelumnya
		$cek = $this->all_model->data_tabular_semua_num_rows($id,$tahun_sebelum);
		
		// jika tidak ada
		if($cek == 0){
		
		$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Data Tahun Sebelumnya Belum Ada
							 </div>');
			
		redirect('data-tabular/index/'.$id .'/'.$tahun);
		
		} else {
		
		// jika ada
		$cek3 = $this->all_model->get_data_by_jenis_tahun($id);
		foreach($cek3 as $cek3){
		
			$cek4 = $this->all_model->row_item_data($cek3->id_data,$tahun_sebelum);
			
			
				
				$data = array(
					'nilai' => $cek4->nilai,
					'ketersediaan_data' => $cek4->ketersediaan_data,
					'sumber_data' => $cek4->sumber_data
					
				);
				
				$this->db->where('id_data',$cek4->id_data);
				$this->db->where('tahun',$tahun);
				$this->db->update('item_data',$data);
			
			
		
		}
		
		
	
							 $this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Salin Data
							 </div>');
							 
				redirect('data-tabular/index/'.$id .'/'.$tahun);
				
				}
		
		
		}
		
		
		
	}
	
	
	
	public function salin_ketersediaan_data() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
		}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->uri->segment(3);
		$tahun = $this->uri->segment(4);
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		// submit request
			if($this->input->post('submit')){
			
		$tahun_sebelum = $tahun - 1;
		
		/*
		$cek1 = $this->all_model->get_data_by_jenis_tahun($id);
		foreach($cek1 as $cek1){
		
			$cek2 = $this->all_model->cek_item_data($cek1->id_data,$tahun_sebelum);
			
			if($cek2 == 0){
				
				$data = array(
					'id_data' => $cek1->id_data,
					'tahun' => $tahun
				);
				
				$this->db->insert('item_data',$data);
			
			}
		
		}
		*/
		
		// cek data tahun sebelumnya
		$cek = $this->all_model->data_tabular_semua_num_rows($id,$tahun_sebelum);
		
		// jika tidak ada
		if($cek == 0){
		
		
		$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Data Tahun Sebelumnya Belum Ada
							 </div>');
			
		redirect('data-tabular/index/'.$id .'/'.$tahun);
		
		} else {
		
		// jika ada
		$cek3 = $this->all_model->get_data_by_jenis_tahun($id);
		foreach($cek3 as $cek3){
		
			$cek4 = $this->all_model->row_item_data($cek3->id_data,$tahun_sebelum);
			
			
				
				$data = array(
				
					'ketersediaan_data' => $cek4->ketersediaan_data
			
					
				);
				
				$this->db->where('id_data',$cek4->id_data);
				$this->db->where('tahun',$tahun);
				$this->db->update('item_data',$data);
			
			
		
		}
		
		
	
							 $this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Salin Ketersediaan Data
							 </div>');
							 
				redirect('data-tabular/index/'.$id .'/'.$tahun);
		
		
		
		}
		
		}
		
		
		
	}
	
	
	
	
	public function excel(){
	
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
				
	}

	$id = $this->uri->segment(3);
		$tahun = $this->uri->segment(4);
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
	ob_clean();	
    

	
	$for_user = 145;
	
    $this->load->library('PHPExcel');
    $this->load->library('PHPExcel/IOFactory');

    $objPHPExcel = new PHPExcel();

	$jenis_data = $this->all_model->jenis_data_detail($id);
	
    $objPHPExcel->getProperties()
                ->setTitle("data_tabular_".$jenis_data->jenis_data."_Kabupaten Indragiri Hilir_".$tahun)
                ->setDescription("Data Sumber Data");

    $objPHPExcel->setActiveSheetIndex(0);

	
	$objPHPExcel->getActiveSheet()->mergeCells('A1:D1');
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Sistem Informasi Pembangunan Daerah');
	$objPHPExcel->getActiveSheet()->getStyle("A1:D1")->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle("A1:D1")->getFont()->setSize(12);
	
	
	$objPHPExcel->getActiveSheet()->setCellValue('A2', 'Kabupaten');
	$objPHPExcel->getActiveSheet()->setCellValue('B2', 'Kabupten Indragiri Hilir');
	$objPHPExcel->getActiveSheet()->setCellValue('A3', 'Jenis Data');
	$objPHPExcel->getActiveSheet()->setCellValue('B3', $jenis_data->jenis_data);
	$objPHPExcel->getActiveSheet()->setCellValue('A4', 'Tahun');
	$objPHPExcel->getActiveSheet()->setCellValue('B4', $tahun);

	$objPHPExcel->getActiveSheet()->setCellValue('C4', $id.'.'.$for_user.'.'.$tahun);
	
	
	$styleArray_hidden = array(
    'font'  => array(
        'color' => array('rgb' => 'FFFFFF')
    ));

$objPHPExcel->getActiveSheet()->getStyle('C4')->applyFromArray($styleArray_hidden);


    $fields = array('Nama','Nilai','Satuan','Ketersediaan','Sumber Data');

    $col = 0;

    $row = 6;

    foreach($fields as $field)

    {
		
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $field);
        $col++;
    }

	
	$objPHPExcel->getActiveSheet()->getStyle( 'A6' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(51);
	$objPHPExcel->getActiveSheet()->getStyle( 'B6' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(29);
	$objPHPExcel->getActiveSheet()->getStyle( 'C6' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(13);
	$objPHPExcel->getActiveSheet()->getStyle( 'D6' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getStyle( 'E6' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
	
	$objPHPExcel->getActiveSheet()->getStyle('A6')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));
		
	$objPHPExcel->getActiveSheet()->getStyle('B6')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));
		
	$objPHPExcel->getActiveSheet()->getStyle('C6')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));
		
	$objPHPExcel->getActiveSheet()->getStyle('D6')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));
		
		$objPHPExcel->getActiveSheet()->getStyle('E6')->getFill()
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

$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($styleArray);


$objPHPExcel->getActiveSheet()->getStyle("A6")->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle("B6")->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle("C6")->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle("D6")->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle("E6")->getFont()->setBold(true);

   
	
	//$data_tabular = $this->all_model->data_tabular_1($id,$tahun);
	$data_tabular = $this->all_model->data_tabular_semua($id,$tahun);
	$sumber_data = $this->all_model->sumber_data_all();
	
	 $row = 7;

	 foreach($data_tabular as $dt){
	 
	 
	 
	 if($dt->tipe_elemen != NULL){
	
	
		$styleArray2 = array(
       'borders' => array(
             'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => 'E1FFFE'),
             ),
		   ),
		);
	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$row.':E'.$row)->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'E1FFFE')
        ));
		
	$objPHPExcel->getActiveSheet()->getStyle('A'.$row.':E'.$row)->applyFromArray($styleArray2);
	
	}

        
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $dt->data);

		if($dt->tipe_elemen == '*'){
		
		} else if($dt->tipe_elemen == '**'){
		
		
		//$total_nilai = $this->all_model->sum_bintang_dua($dt->id_data,$this->uri->segment(4));
		$total_nilai = $this->all_model->dapat_bintang_dua($dt->id_data,$this->uri->segment(4));
		
		$tuotual = 0;
										foreach($total_nilai as $total_nilai){
										$tuotual += $total_nilai->nilai;
										}
					
		if($tuotual > 0){
		
		$ttlnl = str_replace('.',',',$tuotual);
		
		$broken_number = explode(',',$ttlnl);
    
		if(isset($broken_number[1])){
		
			if($broken_number[1]==0){
		
				$nilai_dt = number_format($broken_number[0],0,'','.');
				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, number_format($broken_number[0],0,'','.'));
			
			} else{
		
				$nilai_dt = number_format($broken_number[0],0,'','.').','.substr($broken_number[1],0,5);
		
				//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, number_format($broken_number[0],0,'','.').','.$broken_number[1]);
		
			}
			
		} else {
		
		$nilai_dt = number_format($broken_number[0],0,'','.');
		
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, number_format($broken_number[0],0,'','.'));
		}
		
		} else {
		
		$nilai_dt = 'n/a';
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, 'n/a');
		}
		
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $nilai_dt);
										
		} else if($dt->tipe_elemen == '***'){
		
		$total_nilai = $this->all_model->sum_bintang_dua($dt->id_data,$this->uri->segment(4));
		$banyak_nilai = $this->all_model->sum_bintang_tiga_count($dt->id_data,$this->uri->segment(4));
										
			if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
				$ttlnl = str_replace('.',',',$total_nilai->total_nilai/$banyak_nilai);
										
				$broken_number = explode(',',$total_nilai->total_nilai/$ttlnl);
    
				if(isset($broken_number[1])){
				
					if($broken_number[1]==0){
											
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, number_format($broken_number[0],0,'','.'));
											
					}else{
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, number_format($broken_number[0],0,'','.').','.$broken_number[1]);
										
					}
										
				} else {
							
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, number_format($broken_number[0],0,'','.'));
									
				}
										
			} else {
										
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, 'n/a');
											
			}
										
		
		} else if($dt->tipe_elemen == NULL){
		
		
		if($dt->nilai == NULL){
										$nilai_dt = 'n/a';
										} else {
										
										$nilai_dt =$dt->nilai;
										
										$nl = str_replace('.',',',$dt->nilai);
										
										$broken_number = explode(',',$nl);
										
										
										if($dt->nilai > 0){
										if(isset($broken_number[1])){
										
											if($broken_number[1] == 0){
											
											$nilai_dt = number_format($broken_number[0],0,'','.');
											
											} else {
											$nilai_dt = number_format($broken_number[0],0,'','.').','.substr($broken_number[1],0,5);
											}
										
	
										} else {
										
										$nilai_dt = number_format($broken_number[0],0,'','.').' ';
										
										}
										
										}  else {
										$nilai_dt = 'n/a';
										}
										
										
										}
										
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $nilai_dt);
		}
		
        $objPHPExcel->getActiveSheet()->getStyle( 'B'.$row )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		

		$objPHPExcel->getActiveSheet()->getStyle( 'C'.$row  )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $dt->satuan);
        $objPHPExcel->getActiveSheet()->getStyle( 'D'.$row  )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		if($dt->ketersediaan_data == 0){
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, 'Tidak Ada');
		} else {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row, 'Ada');
		
	
		}
		
		$objPHPExcel->setActiveSheetIndex(0);
		$objValidation = $objPHPExcel->getActiveSheet()->getCell("D".$row)->getDataValidation();
		$objValidation->setFormula1('"Ada,Tidak Ada"');
		$objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
		$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
		$objValidation->setAllowBlank(false);
		$objValidation->setShowInputMessage(true);
		$objValidation->setShowErrorMessage(true);
		$objValidation->setShowDropDown(true);
		$objValidation->setPromptTitle('Pilih Ketersediaan');
		$objValidation->setPrompt('Silahkan memilih ketersediaan dari drop-down list.');
		$objValidation->setErrorTitle('Input error');
        $objValidation->setError('Value is not in list.');


	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $dt->data_sumber_data);
		
		
		$objValidation2 = $objPHPExcel->getActiveSheet()->getCell('B'.$row)->getDataValidation();
		$objValidation2->setPromptTitle('Input Nilai');
		$objValidation2->setPrompt('Contoh input nilai : Ratusan (10), Ribuan(1000), Puluh Ribuan(10000,56), Jutaan(100000000,5689)  (Maksimal 4 Angka dibelakang Koma).');
		$objValidation2->setErrorTitle('Input error');
        $objValidation2->setError('Value is not in list.');
		$objValidation2->setAllowBlank(false);
		$objValidation2->setShowInputMessage(true);
		$objValidation2->setShowErrorMessage(true);
		
		$objValidation3 = $objPHPExcel->getActiveSheet()->getCell('E'.$row)->getDataValidation();
		$objValidation3->setPromptTitle('Input Sumber Data');
		$objValidation3->setPrompt('Masukan Kode Sumber Data Yang terdapat di tabel keterangan sumber data.');
		$objValidation3->setErrorTitle('Input error');
        $objValidation3->setError('Value is not in list.');
		$objValidation3->setAllowBlank(false);
		$objValidation3->setShowInputMessage(true);
		$objValidation3->setShowErrorMessage(true);

		
		
		
		if($dt->tipe_elemen == NULL){
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $dt->id_data);
		$objPHPExcel->getActiveSheet()->getStyle('F'.$row)->applyFromArray($styleArray_hidden);
		}

		$row++;
		
	 }
	 


		$objPHPExcel->getActiveSheet()->getStyle('G6')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));

	$objPHPExcel->getActiveSheet()->mergeCells('G6:I6');
	$objPHPExcel->getActiveSheet()->setCellValue('G6', 'Keterangan Sumber Data');
	$objPHPExcel->getActiveSheet()->getStyle("G6:I6")->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('G6:I6')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle( 'G6' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
	
		$objPHPExcel->getActiveSheet()->getStyle('G7')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));

	
	$objPHPExcel->getActiveSheet()->setCellValue('G7', 'Kode');
	$objPHPExcel->getActiveSheet()->getStyle("G7")->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle( 'G7' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	
	
	$objPHPExcel->getActiveSheet()->getStyle('H7')->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => 'F2F2F2')
        ));

	$objPHPExcel->getActiveSheet()->mergeCells('H7:I7');
	$objPHPExcel->getActiveSheet()->setCellValue('H7', 'Nama');
	$objPHPExcel->getActiveSheet()->getStyle("H7:I7")->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('H7:I7')->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle( 'H7' )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	

	 $row_sd = 8; 
	foreach($sumber_data as $sd){


        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $no_sd);
		$objPHPExcel->getActiveSheet()->getStyle( 'G'.$row_sd  )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row_sd, $sd->id_sumber_data);

		$objPHPExcel->getActiveSheet()->getStyle( 'H'.$row_sd  )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $row_sd, $sd->sumber_data);

    
        $row_sd++;

    }
	
	
    $objPHPExcel->setActiveSheetIndex(0);

    $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment;filename="data_tabular_'.$jenis_data->jenis_data.'_Kabupaten Indragiri Hilir_'.$tahun.'.xls"');
    header('Cache-Control: max-age=0');
	ob_end_clean();
    $objWriter->save('php://output');
	
	}
	
	
	
	function import_excel(){
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
	}

	$id = $this->uri->segment(3);
		$tahun = $this->uri->segment(4);
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		$this->load->library('PHPExcel');
    $this->load->library('PHPExcel/IOFactory');
		
		if($this->input->post('submit')){
		
			//$config['upload_path'] = './upload/';
			//$config['allowed_types'] = 'xls';
			//$this->load->library('upload', $config);
			
			$fileName = $_FILES['userfile']['name'];

			$config['upload_path']    = './upload/';
			$config['file_name']      = $fileName;
			$config['allowed_types']  = '*';
			//$config['max_size']		  = 10000;
			
			$this->load->library('upload');
			$this->upload->initialize($config);
			
			$nama_upload = str_replace(' ','_',$fileName );
 
			if(empty($_FILES['userfile']['name'])){
			
			/*
			$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> File Upload Kosong
							 </div>');
				redirect('data-tabular/index/'.$id .'/'.$tahun);
				*/
				
				$this->session->set_flashdata('halaman_load','gagal');
				$this->session->set_flashdata('id_load',$id);
				$this->session->set_flashdata('tahun_load',$tahun);
							 
				
				redirect('home');
				
			}
				
			else {
			
			if ( ! $this->upload->do_upload()) {

			/*
				$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Format File Harus .xls
							 </div>');
				redirect('data-tabular/index/'.$id .'/'.$tahun);
				*/
				
				$this->session->set_flashdata('halaman_load','gagal');
				$this->session->set_flashdata('id_load',$id);
				$this->session->set_flashdata('tahun_load',$tahun);
							 
				
				redirect('home');
				
			}
			
			else {
		

			

			

			//if(! $this->upload->do_upload('import') )
			//	$this->upload->display_errors();

			$media = $this->upload->data('userfile');
			$inputFileName = './upload/'.$media['file_name'];

			//  Read your Excel workbook
			try {
				$inputFileType = IOFactory::identify($inputFileName);
				$objReader = IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
			} catch(Exception $e) {
				die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			//  Get worksheet dimensions
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();

			
			
			for ($rowc4 = 4; $rowc4 <= 4; $rowc4++){ 
			
			$rowData1 = $sheet->rangeToArray('C' . $rowc4 . ':C' . $rowc4,
												NULL,
												TRUE,
												FALSE);
												
			$kolom_jenis = $rowData1[0][0];
			}
			
			$pecah_id = explode('.',$kolom_jenis);
			
			if($pecah_id[0].'.'.$pecah_id[2] != $id.'.'.$tahun ){
			//if($pecah_id[0] != $id AND $pecah_id[2] != $tahun){
			
			/*
			$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Gagal Import Data
							 </div>');
				redirect('data-tabular/index/'.$id .'/'.$tahun);
				*/
				
				unlink('./upload/'.$nama_upload);
				
				$this->session->set_flashdata('halaman_load','gagal');
				$this->session->set_flashdata('id_load',$id);
				$this->session->set_flashdata('tahun_load',$tahun);
							 
				
				redirect('home');
			
			} else {
			
			//  Loop through each row of the worksheet in turn
			$urutan_semua = 0;
			$i=0;
			for ($row = 7; $row <= $highestRow; $row++){  	
			$i++;
			$urutan_semua++;
			//  Read a row of data into an array 				
                        $rowData = $sheet->rangeToArray('A' . $row . ':F' . $row,
												NULL,
												TRUE,
												FALSE);
				//  Insert row data array into your database of choice here
				
				if($rowData[0][3] == 'Ada'){
				$ketersediaan_data = 1;
				} else {
				$ketersediaan_data = 0;
				}
				
			//if($rowData[0][5] != ''){
			
			
			
			if($rowData[0][1] == 'n/a'){
			$rowData[0][1] = NULL;
			} else if($rowData[0][1] == ''){
			$rowData[0][1] = NULL;
			} else {
			$rowData[0][1] = $rowData[0][1];
			}
			
			
			$rowData[0][1] = str_replace('.','',$rowData[0][1]);
			$rowData[0][1] = str_replace(',','.',$rowData[0][1]);
				
				$get_id = $this->all_model->data_join_item_by_urutansemua_tahun_idjenis($id,$tahun,$urutan_semua);
				

				
				$broken_number[$i] = explode('.',$rowData[0][1]);
    
			if(isset($broken_number[$i][1])){
			
			
			if(strlen($broken_number[$i][1]) > 4){
			//$angka_koma = $broken_number[$i][1];
			$nilai_baru[$i] = round($rowData[0][1],4);
			} else {
			//$angka_koma = substr($broken_number[$i][1],0,5);
			$nilai_baru[$i] = $broken_number[$i][0].'.'.$broken_number[$i][1];
			}
										
										
							
				} else {
				$nilai_baru[$i] = $rowData[0][1];
				}
				
				
				$data = array(
						 'nilai' 	          => str_replace(' ','',$nilai_baru[$i]),
						 'ketersediaan_data'  => $ketersediaan_data,
						 'sumber_data'        => $rowData[0][4]
						);

				//$this->db->where('id_data',$rowData[0][5]);
				//$this->db->where('tahun',$tahun);
				//$this->db->update("item_data",$data);
				
				$this->db->where('id_item_data',$get_id->id_item_data);
				//$this->db->where('id_data',$get_id->id_data);
				//$this->db->where('tahun',$tahun);
				$this->db->update("item_data",$data);
				
				//}
				
			}
			
			
			
			
			
					
			$dt_desc = $this->all_model->data_tabular_semua_desc($id,$tahun);
	foreach($dt_desc as $dt_desc){
	
		if($dt_desc->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($dt_desc->id_data,$tahun);
										
										
										if($total_nilai->total_nilai > 0){
										
										
										$this->db->where('id_item_data',$dt_desc->id_item_data);
										$this->db->update('item_data',array('nilai'=>$total_nilai->total_nilai));
										
										} else {
										
										}
										
										
										
		} else if($dt_desc->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($dt_desc->id_data,$tahun);
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($dt_desc->id_data,$tahun);
										
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
										
										$this->db->where('id_item_data',$dt_desc->id_item_data);
										$this->db->update('item_data',array('nilai'=>$total_nilai->total_nilai/$banyak_nilai));
										
										} else {
										
										}
										
										
										
		}
	
	}
			
			unlink('./upload/'.$nama_upload);
			
			/*
			$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Import Data Excel
							 </div>');
            redirect('data-tabular/index/'.$id .'/'.$tahun);
			*/
			
			
			$this->session->set_flashdata('halaman_load','berhasil');
				$this->session->set_flashdata('id_load',$id);
				$this->session->set_flashdata('tahun_load',$tahun);
							 
				
				redirect('home');
				
				}
				
			}
			
			
			}
			
		} else {
		redirect('home');
		}
	
	}
	
	
	public function load_page() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->input->post('id_jenis_data');
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
	}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->input->post('id_jenis_data');
		$tahun = $this->input->post('tahun');
		
		$contents['id_jenis_data'] = $id;
		$contents['tahun'] = $tahun;
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		
		
		

		
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		
		
		
	}
	
	
	public function salin_data_ajax() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
		}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->input->post('id_jenis_data');
		$tahun = $this->input->post('tahun');
		$contents['id_jenis_data'] = $id;
		$contents['tahun'] = $tahun;
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		
		
			
		
		$tahun_sebelum = $tahun - 1;
	
		
		// cek data tahun sebelumnya
		$cek = $this->all_model->data_tabular_semua_num_rows($id,$tahun_sebelum);
		
		// jika tidak ada
		if($cek == 0){
		
		$contents['notif'] = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Data Tahun Sebelumnya Belum Ada</div>';
			
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		
		} else {
		
		// jika ada
		$cek3 = $this->all_model->get_data_by_jenis_tahun($id);
		foreach($cek3 as $cek3){
		
			$cek4 = $this->all_model->row_item_data($cek3->id_data,$tahun_sebelum);
			
			
				
				$data = array(
					'nilai' => $cek4->nilai,
					'ketersediaan_data' => $cek4->ketersediaan_data,
					'sumber_data' => $cek4->sumber_data
					
				);
				
				$this->db->where('id_data',$cek4->id_data);
				$this->db->where('tahun',$tahun);
				$this->db->update('item_data',$data);
			
			
		
		}
		
		
	
			$contents['notif'] = '<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Salin Data
							 </div>';
							 
				$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
				
				}
		
		
		
		
		
		
	}
	
	
	public function salin_ketersediaan_data_ajax() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
		}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->input->post('id_jenis_data');
		$tahun = $this->input->post('tahun');
		$contents['id_jenis_data'] = $id;
		$contents['tahun'] = $tahun;
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		// submit request
		
			
		$tahun_sebelum = $tahun - 1;
		
	
		
		// cek data tahun sebelumnya
		$cek = $this->all_model->data_tabular_semua_num_rows($id,$tahun_sebelum);
		
		// jika tidak ada
		if($cek == 0){
		
		
		$contents['notif'] = '<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Data Tahun Sebelumnya Belum Ada
							 </div>';
			
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		
		} else {
		
		// jika ada
		$cek3 = $this->all_model->get_data_by_jenis_tahun($id);
		foreach($cek3 as $cek3){
		
			$cek4 = $this->all_model->row_item_data($cek3->id_data,$tahun_sebelum);
			
			
				
				$data = array(
				
					'ketersediaan_data' => $cek4->ketersediaan_data
			
					
				);
				
				$this->db->where('id_data',$cek4->id_data);
				$this->db->where('tahun',$tahun);
				$this->db->update('item_data',$data);
			
			
		
		}
		
		
	
					$contents['notif'] = '<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Salin Ketersediaan Data
							 </div>';
		
		
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		}
		
		
		
		
		
	}
	
	
	public function edit_data_ajax() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
	}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->input->post('id_jenis_data');
		$tahun = $this->input->post('tahun');
		$contents['id_jenis_data'] = $id;
		$contents['tahun'] = $tahun;
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
	
		$row = $this->input->post('jumlah_looping_data');
		//$row = count($this->input->post('id_item_data'));
		
		//echo $row;
		
		$id_item_data = $this->input->post('id_item_data');
		$nilai = $this->input->post('nilai');
		$ketersediaan_data = $this->input->post('ketersediaan_data');
		$sumber_data = $this->input->post('sumber_data');
		
			for($i=0;$i<$row;$i++){
			
			
			
			if(isset($ketersediaan_data[$i])){
			
			if($ketersediaan_data[$i] == 'Ada'){
			$ketersediaan_data[$i] = 1;
			} else {
			$ketersediaan_data[$i] = 0;
			}
			
			} else {
			$ketersediaan_data[$i] = NULL;
			}
			
			
			if($sumber_data[$i] == ''){
			$sumber_data[$i] = NULL;
			} else {
			$sumber_data[$i] = $sumber_data[$i];
			}
			
				if($nilai[$i] == 'n/a'){
			$nilai_baru = NULL;
			} else if($nilai[$i] == ''){
			$nilai_baru = NULL;
			} else {
			
			
			
			
			$nilai[$i] = str_replace('.','',$nilai[$i]);
			$nilai[$i] = str_replace(',','.',$nilai[$i]);
			
			
			$broken_number[$i] = explode('.',$nilai[$i]);
    
			if(isset($broken_number[$i][1])){
			
			
			if(strlen($broken_number[$i][1]) > 4){
			//$angka_koma = $broken_number[$i][1];
			$nilai_baru[$i] = round($nilai[$i],4);
			} else {
			//$angka_koma = substr($broken_number[$i][1],0,5);
			$nilai_baru[$i] = $broken_number[$i][0].'.'.$broken_number[$i][1];
			}
										
										
							
				} else {
				$nilai_baru[$i] = $nilai[$i];
				}
			
			$data['nilai']	                 = str_replace(' ','',$nilai_baru[$i]);
			$data['ketersediaan_data']		 = $ketersediaan_data[$i];
			$data['sumber_data']		     = $sumber_data[$i];
			
			$this->db->where('id_item_data',$id_item_data[$i]);
			$this->db->update('item_data',$data);
			
			
			}
			
	}
	
	$dt_desc = $this->all_model->data_tabular_semua_desc($id,$tahun);
	foreach($dt_desc as $dt_desc){
	
		if($dt_desc->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($dt_desc->id_data,$tahun);
										
										
										if($total_nilai->total_nilai > 0){
										
										
										$this->db->where('id_item_data',$dt_desc->id_item_data);
										$this->db->update('item_data',array('nilai'=>$total_nilai->total_nilai));
										
										} else {
										
										}
										
										
										
		} else if($dt_desc->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($dt_desc->id_data,$tahun);
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($dt_desc->id_data,$tahun);
										
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
										
										$this->db->where('id_item_data',$dt_desc->id_item_data);
										$this->db->update('item_data',array('nilai'=>$total_nilai->total_nilai/$banyak_nilai));
										
										} else {
										
										}
										
										
										
		}
	
	}
	
	$contents['notif'] = '<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Edit Data 
							 </div>';
							 
		
		

		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
	
	}
	
	
		
	public function import_excel_ajax(){
	
		if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->uri->segment(3);
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
	}

	
		$id = $this->input->post('id_jenis_data');
		$tahun = $this->input->post('tahun');
		$contents['id_jenis_data'] = $id;
		$contents['tahun'] = $tahun;

	$this->load->library('PHPExcel');
    $this->load->library('PHPExcel/IOFactory');
		
	
		
			//$config['upload_path'] = './upload/';
			//$config['allowed_types'] = 'xls';
			//$this->load->library('upload', $config);
			
			$fileName = $_FILES['userfile']['name'];

			$config['upload_path']    = './upload/';
			$config['file_name']      = $fileName;
			$config['allowed_types']  = '*';
			//$config['max_size']		  = 10000;
			
			$this->load->library('upload');
			$this->upload->initialize($config);
 
			if(empty($_FILES['userfile']['name'])){
			
			/*
			$contents['notif'] = '<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Gagal Import Data
							 </div>';
			
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		*/
		
		$jsonencode = array(
            "status"     => 'gagal'
        );
				
			}
				
			else {
			
			if ( ! $this->upload->do_upload()) {

			/*
				$contents['notif'] = '<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Gagal Import Data
							 </div>';
			
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
			*/
$jsonencode = array(
            "status"     => 'gagal'
        );			
			}
			
			else {
		

			

			

			//if(! $this->upload->do_upload('import') )
			//	$this->upload->display_errors();

			$media = $this->upload->data('userfile');
			$inputFileName = './upload/'.$media['file_name'];

			//  Read your Excel workbook
			try {
				$inputFileType = IOFactory::identify($inputFileName);
				$objReader = IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
			} catch(Exception $e) {
				die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			//  Get worksheet dimensions
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();

			
			
			for ($rowc4 = 4; $rowc4 <= 4; $rowc4++){ 
			
			$rowData1 = $sheet->rangeToArray('C' . $rowc4 . ':C' . $rowc4,
												NULL,
												TRUE,
												FALSE);
												
			$kolom_jenis = $rowData1[0][0];
			}
			
			$pecah_id = explode('.',$kolom_jenis);
			
			if($pecah_id[0].'.'.$pecah_id[2] != $id.'.'.$tahun ){
			//if($kolom_jenis != '7.145.2014'){
			
			/*
			$contents['notif'] = '<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Gagal Import Data
							 </div>';
			
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		*/
		$jsonencode = array(
            "status"     => 'gagal'
        );
			
			} else {
			
			//  Loop through each row of the worksheet in turn
			for ($row = 7; $row <= $highestRow; $row++){  				//  Read a row of data into an array 				
                        $rowData = $sheet->rangeToArray('A' . $row . ':F' . $row,
												NULL,
												TRUE,
												FALSE);
				//  Insert row data array into your database of choice here
				
				if($rowData[0][3] == 'Ada'){
				$ketersediaan_data = 1;
				} else {
				$ketersediaan_data = 0;
				}
				
			if($rowData[0][5] != ''){
			
			
			
			if($rowData[0][1] == 'n/a'){
			$rowData[0][1] = NULL;
			} else if($rowData[0][1] == ''){
			$rowData[0][1] = NULL;
			} else {
			$rowData[0][1] = $rowData[0][1];
			}
			
			
			$rowData[0][1] = str_replace('.','',$rowData[0][1]);
			$rowData[0][1] = str_replace(',','.',$rowData[0][1]);
				
				$data = array(
						 'nilai' 	          => $rowData[0][1],
						 'ketersediaan_data'  => $ketersediaan_data,
						 'sumber_data'        => $rowData[0][4]
						);

				$this->db->where('id_data',$rowData[0][5]);
				$this->db->where('tahun',$tahun);
				$this->db->update("item_data",$data);
				
				}
				
			}
			
			
			
			$dt_desc = $this->all_model->data_tabular_semua_desc($id,$tahun);
	foreach($dt_desc as $dt_desc){
	
		if($dt_desc->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($dt_desc->id_data,$tahun);
										
										
										if($total_nilai->total_nilai > 0){
										
										
										$this->db->where('id_item_data',$dt_desc->id_item_data);
										$this->db->update('item_data',array('nilai'=>$total_nilai->total_nilai));
										
										} else {
										
										}
										
										
										
		} else if($dt_desc->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($dt_desc->id_data,$tahun);
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($dt_desc->id_data,$tahun);
										
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
										
										$this->db->where('id_item_data',$dt_desc->id_item_data);
										$this->db->update('item_data',array('nilai'=>$total_nilai->total_nilai/$banyak_nilai));
										
										} else {
										
										}
										
										
										
		}
	
	}
			
			$nama_upload = str_replace(' ','_',$fileName );
			
			
			
			/*
			$contents['notif'] = '<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Edit Data 
							 </div>';
							 
		
		

		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		*/
		
		$jsonencode = array(
            "status"     => 'berhasil',
			'id'		=> $id,
			'tahun'		=> $tahun
        );
		
			}
			
			}
			
			
			}
			
		//$status = "success";
                //$msg = "File successfully uploaded";
				
	//echo json_encode(array('status' => $status, 'msg' => $msg));
	unlink('./upload/'.$nama_upload);
	 echo json_encode($jsonencode);
}






	public function load_page_gagal() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->input->post('id_jenis_data');
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
	}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->input->post('id_jenis_data');
		$tahun = $this->input->post('tahun');
		
		$contents['id_jenis_data'] = $id;
		$contents['tahun'] = $tahun;
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		
		
		
$contents['notif'] = '<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Gagal Import Data
							 </div>';
		
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		
		
		
	}
	
	
	public function load_page_berhasil() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	
	// cek akses user
	if($this->session->userdata('level') != 'administrator'){
	$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
	//$detail_jenis_data = $this->all_model->jenis_data_detail($this->uri->segment(3));
	$detail_jenis_data = $this->input->post('id_jenis_data');
	
	$pecah_menu = explode(',',$detail_login->menu);
				
				if (in_array($detail_jenis_data, $pecah_menu)) {
				
				} else {
				redirect('home');
				}
	}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'Data Tabular';
		
		
		$id = $this->input->post('id_jenis_data');
		$tahun = $this->input->post('tahun');
		
		$contents['id_jenis_data'] = $id;
		$contents['tahun'] = $tahun;
		
		if(empty($id)){
		redirect('home');
		}
		
		if(empty($tahun)){
		redirect('home');
		}
		
		
		
		$contents['notif'] = '<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Edit Data 
							 </div>';

		
		$contents['data_tabular'] = $this->all_model->data_tabular_semua($id,$tahun);
	
		$this->load->view('data_tabular',$contents);
		
		
		
	}
	
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */