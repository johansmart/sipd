<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class All_model extends CI_Model {
	
	function __construct() {
		parent::__construct();
	}	
	
	function cek_login($username,$password){
		return $q = $this->db->select('*')->from('login')->where('username',$username)->where('password',$password)->get()->num_rows();
	}
	
	function detail_login_by_id($id){
		return $q = $this->db->select('*')->from('login')->where('id_login',$id)->get()->row();
	}
	
	function row_login($username){
		return $q = $this->db->select('*')->from('login')->where('username',$username)->get()->row();
	}
	
	function kelompok_data_all(){
		return $q = $this->db->select('*')->from('kelompok_data')->order_by('urutan','ASC')->get()->result();
	}
	
	function jenis_data_by_id_kel_data($id){
		return $q = $this->db->select('*')->from('jenis_data')->where('id_kelompok_data',$id)->order_by('urutan','ASC')->get()->result();
	}
	
	function jenis_data_all(){
		return $q = $this->db->select('*')->from('jenis_data')->get()->result();
	}
	
	function kontak(){
		return $q = $this->db->select('*')->from('kontak')->get()->row();
	}
	
	
	function sumber_data_paging($limit, $offset){
		return $q = $this->db->select('*')->from('sumber_data')->order_by('id_sumber_data','ASC')->limit($limit, $offset)->get()->result();
	}
	
	function sumber_data_num_rows(){
		return $q = $this->db->select('*')->from('sumber_data')->get()->num_rows();
	}
	
	function sumber_data_paging_search($type,$key,$limit, $offset){
		return $q = $this->db->select('*')->from('sumber_data')->like($type,$key)->order_by('id_sumber_data','ASC')->limit($limit, $offset)->get()->result();
	}
	
	function sumber_data_num_rows_search($type,$key){
		return $q = $this->db->select('*')->from('sumber_data')->like($type,$key)->get()->num_rows();
	}
	
	function cek_sumber_data_by_id($id){
		return $q = $this->db->select('*')->from('sumber_data')->where('id_sumber_data',$id)->get()->num_rows();
	}
	
	function detail_sumber_data_by_id($id){
		return $q = $this->db->select('*')->from('sumber_data')->where('id_sumber_data',$id)->get()->row();
	}
	
	function sumber_data_all(){
		return $q = $this->db->select('*')->from('sumber_data')->order_by('id_sumber_data','DESC')->get()->result();
	}
	
	function data_tabular($id,$tahun){
		return $q = $this->db->select('*,data.sumber_data as data_sumber_data')->from('data')->join('sumber_data','sumber_data.id_sumber_data = data.sumber_data','left')->where('id_jenis_data',$id)->where('tahun',$tahun)->order_by('id_data','ASC')->get()->result();
	}
	
	function jenis_data_detail($id){
		return $q = $this->db->select('*')->from('jenis_data')->where('id_jenis_data',$id)->get()->row();
	}
	
	function cek_bintang_data_tabular($id){
		return $q = $this->db->select('*')->from('data')->like('data','*')->where('id_data',$id)->get()->num_rows();
	}
	
	function user_paging($limit, $offset){
		return $q = $this->db->select('*')->from('login')->order_by('id_login','DESC')->limit($limit, $offset)->get()->result();
	}
	
	function user_num_rows(){
		return $q = $this->db->select('*')->from('login')->get()->num_rows();
	}
	
	function user_paging_search($type,$key,$limit, $offset){
		return $q = $this->db->select('*')->from('login')->like($type,$key)->order_by('id_login','DESC')->limit($limit, $offset)->get()->result();
	}
	
	function user_num_rows_search($type,$key){
		return $q = $this->db->select('*')->from('login')->like($type,$key)->get()->num_rows();
	}
	
	function cek_username($username){
		return $q = $this->db->select('*')->from('login')->where('username',$username)->get()->num_rows();
	}
	
	function cek_edit_username($username,$id){
		return $q = $this->db->select('*')->from('login')->where('username',$username)->where('id_login !=',$id)->get()->num_rows();
	}
	
	function data_tabular_semua($id,$tahun){
		return $q = $this->db->select('*,data.id_data as id_data,item_data.sumber_data as data_sumber_data')->from('item_data')->join('data','item_data.id_data = data.id_data','left')->join('sumber_data','sumber_data.id_sumber_data = item_data.sumber_data','left')->where('data.id_jenis_data',$id)->where('item_data.tahun',$tahun)->order_by('data.urutan_semua','ASC')->get()->result();
	}
	
	function data_tabular_semua_desc($id,$tahun){
		return $q = $this->db->select('*,data.id_data as id_data,item_data.sumber_data as data_sumber_data')->from('item_data')->join('data','item_data.id_data = data.id_data','left')->join('sumber_data','sumber_data.id_sumber_data = item_data.sumber_data','left')->where('data.id_jenis_data',$id)->where('item_data.tahun',$tahun)->order_by('data.urutan_semua','DESC')->get()->result();
	}
	
	function data_tabular_semua_num_rows($id,$tahun){
		return $q = $this->db->select('*,data.id_data as id_data,item_data.sumber_data as data_sumber_data')->from('item_data')->join('data','item_data.id_data = data.id_data','left')->join('sumber_data','sumber_data.id_sumber_data = item_data.sumber_data','left')->where('data.id_jenis_data',$id)->where('item_data.tahun',$tahun)->order_by('data.urutan_semua','ASC')->get()->num_rows();
	}
	
	function data_tabular_1($id,$tahun){
		return $q = $this->db->select('*,data.id_data as id_data,item_data.sumber_data as data_sumber_data')->from('data')->join('item_data','item_data.id_data = data.id_data','left')->join('sumber_data','sumber_data.id_sumber_data = item_data.sumber_data','left')->where('data.id_jenis_data',$id)->where('id_parent',NULL)->where('tahun',$tahun)->order_by('data.urutan','ASC')->get()->result();
	}
	
	
	function data_tabular_2($id,$tahun){
		return $q = $this->db->select('*,data.id_data as id_data,item_data.sumber_data as data_sumber_data')->from('data')->join('item_data','item_data.id_data = data.id_data','left')->join('sumber_data','sumber_data.id_sumber_data = item_data.sumber_data','left')->where('data.id_parent',$id)->where('tahun',$tahun)->order_by('data.urutan','ASC')->get()->result();
	}
	
	function get_data_by_jenis_tahun($id_jenis){
		return $q = $this->db->select('*')->from('data')->where('id_jenis_data',$id_jenis)->get()->result();
	}
	
	function cek_item_data($id_data,$tahun){
		return $q = $this->db->select('*')->from('item_data')->where('id_data',$id_data)->where('tahun',$tahun)->get()->num_rows();
	}
	
	function sum_bintang_dua($id_parent,$tahun){
		return $q = $this->db->select('SUM(item_data.nilai) AS total_nilai')->from('data')->join('item_data','item_data.id_data = data.id_data','left')->where('id_parent',$id_parent)->where('tahun',$tahun)->get()->row();
	}
	
	function dapat_bintang_dua($id_parent,$tahun){
		return $q = $this->db->select('*')->from('data')->join('item_data','item_data.id_data = data.id_data','left')->where('id_parent',$id_parent)->where('tahun',$tahun)->get()->result();
	}
	
	function sum_bintang_tiga_count($id_parent,$tahun){
		return $q = $this->db->select('*')->from('data')->join('item_data','item_data.id_data = data.id_data','left')->where('id_parent',$id_parent)->where('tahun',$tahun)->get()->num_rows();
	}
	
	function row_item_data($id_data,$tahun){
		return $q = $this->db->select('*')->from('item_data')->where('id_data',$id_data)->where('tahun',$tahun)->get()->row();
	}
	
	function data_by_jenis_order_id($jenis){
		return $q = $this->db->select('*')->from('data')->where('id_jenis_data',$jenis)->order_by('id_asal','ASC')->get()->result();
	}
	
	function data_join_item_by_urutansemua_tahun_idjenis($jenis,$tahun,$urutan){
		return $q = $this->db->select('*')->from('item_data')->join('data','item_data.id_data = data.id_data')->where('id_jenis_data',$jenis)->where('tahun',$tahun)->where('urutan_semua',$urutan)->get()->row();
	}
}