<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

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
		
		$contents['page'] = 'User';
		

		if($this->uri->segment(3) == 'search'){
		
		$keyword = 'user';
		
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
		
		$config['base_url']        = site_url('user/index/search');
		$config['total_rows'] 	   = $this->all_model->user_num_rows_search($type,$key);
		$config['per_page'] 	   = 10;
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
		
		$contents['row']	        = $this->all_model->user_paging_search($type,$key,$config['per_page'],$this->uri->segment(4));
		$contents['paging']        		= $this->pagination->create_links();
	
		$contents['sumber_data_total']   = $config['total_rows'] ;
		
		
		} else {
		
		$config['base_url']        = site_url('user/index/');
		$config['total_rows'] 	   = $this->all_model->user_num_rows();
		$config['per_page'] 	   = 10;
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
		
		$contents['row']	       = $this->all_model->user_paging($config['per_page'],$this->uri->segment(3));
		$contents['paging']            = $this->pagination->create_links();
	
		$contents['sumber_data_total']    = $config['total_rows'];
		
		}
	
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		$template['content'] = $this->load->view('user',$contents,TRUE);
		
		$this->load->view('template',$template);
		
	}
	
	
	public function tambah() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	if($this->session->userdata('level') != 'administrator'){redirect('login');}
	
	
	if($this->input->post('submit')){
	
	$cek = $this->all_model->cek_username($this->input->post('username'));
	
	if($cek == 1){
	
	$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Username telah dipakai
							 </div>');
							 
							 redirect('user');
	
	} else {
	
	$row_menu = count($this->input->post('menu'));
	
	$menus = $this->input->post('menu');
	
	$menu = '';
	for($i=0;$i<$row_menu;$i++){
	$j = 1 + $i;
	if($j == $row_menu){
	$menu .= $menus[$i];
	} else {
	$menu .= $menus[$i].',';
	}
	}
	
	$data = array(
	'username' => $this->input->post('username'),
	'password' => md5($this->input->post('password')),
	'nama' => $this->input->post('nama'),
	'level' => 'user',
	'menu' => $menu,
        'last_login' => date('Y-m-d H:i:s')
	);
	
	$this->db->insert('login',$data);
	
	$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Tambah Data
							 </div>');
	
	redirect('user');
	
	
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
	if($this->session->userdata('level') != 'administrator'){redirect('login');}

	
	if($this->input->post('submit')){
	
	if($this->uri->segment(3) != ''){
	$id = $this->uri->segment(3);
	} else {
	$id='';
	}
	
	$cek = $this->all_model->cek_edit_username($this->input->post('username'),$this->input->post('id_edit'));
	
	if($cek == 1){
	
	$this->session->set_flashdata('alert','<div class="alert alert-danger">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-times-circle sign"></i><strong>Maaf ! </strong> Username telah dipakai
							 </div>');
							 
							 redirect('user');
	
	} else {
	
	$row_menu = count($this->input->post('menu'));
	
	$menus = $this->input->post('menu');
	
	$menu = '';
	for($i=0;$i<$row_menu;$i++){
	$j = 1 + $i;
	if($j == $row_menu){
	$menu .= $menus[$i];
	} else {
	$menu .= $menus[$i].',';
	}
	}
	
	if($this->input->post('password')){
	$data['password'] = md5($this->input->post('password'));
	}
	
	$data['username'] = $this->input->post('username');
	$data['nama']     = $this->input->post('nama');
	$data['menu']     = $menu;
	
	
	
	$this->db->where('id_login',$this->input->post('id_edit'));
	$this->db->update('login',$data);
	
	$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Edit Data
							 </div>');
	
	redirect('user/index/'.$id);
	
	}
	
	}
	
		$title   = 'Sistem Informasi Pembangunan Daerah';
		$own_url = '';
	
		$contents['public_url'] = base_url().'public/';
		$contents['title'] = ''.$title;
		
		$contents['own_url'] = base_url().''.$own_url.'';
		
		$contents['page'] = 'User';
		
		$contents['profilku'] = $detail = $this->all_model->row_login($this->session->userdata('login_user'));
		
	
	
		$contents['row'] = $this->all_model->detail_sumber_data_by_id($id);
		
	
		$contents['kelompok_data'] = $this->all_model->kelompok_data_all();
		$template['content'] = $this->load->view('edit_sumber_data',$contents,TRUE);
		
		$this->load->view('template',$template);
		
	}
	
	
	public function hapus() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	if($this->session->userdata('level') != 'administrator'){redirect('login');}
	
	
    //$id = $this->uri->segment(3);
	//$id2 = $this->uri->segment(4);
	
	if($this->input->post('submit')){
	$id = $this->input->post('id_delete');
	$id2 = $this->uri->segment(3);
	
	$this->db->where('id_login',$id);
	$this->db->delete('login');
	

	
	$this->session->set_flashdata('alert','<div class="alert alert-success">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								<i class="fa fa-check sign"></i><strong>Selamat ! </strong> Berhasil Hapus Data
							 </div>');
	
	redirect('user/index/'.$id2);
	} else {
	redirect('user');
	}
	
		
		
	}
	
	public function chained_menu() {
	
	if(!$this->session->userdata('login_user')){redirect('login');}
	if($this->session->userdata('level') != 'administrator'){redirect('login');}

	$menu = $this->input->post('menu');
	

	$pecah_menu = explode(',',$menu);
	

	?>
	<table>
	<tr><td></td><td></td></tr>
	<?php
	$menu2 = $this->all_model->kelompok_data_all();
		   foreach($menu2  as $menu2 ){
		   
		
		   $submenu = $this->all_model->jenis_data_by_id_kel_data($menu2->id_kelompok_data);
			?>
			<tr><td>-</td><td  align="left"> &nbsp; <?php echo $menu2->kelompok_data;?> </td></tr>
		 
		   
			<?php
		   foreach($submenu as $submenu){
		   
		      if (in_array($submenu->id_jenis_data, $pecah_menu))
  {
  $checked = 'checked="checked"';
  } else {
  $checked = '';
  }
  
		   ?>
		  <tr><td></td><td align="left"> <input <?php echo $checked;?> class="checked-menu" type="checkbox" name="menu[]" id="menu[]" value="<?php echo $submenu->id_jenis_data;?>"> &nbsp; <?php echo $submenu->jenis_data;?> </td></tr>
		   
		 
		   
		   
		   <?php
		   }
		   
		   }
		   ?>
		   
		   </table>
		   <?php
	
	}
	
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */