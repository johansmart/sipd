
	
	<div class="page-head">
				<h2>Nilai Profil Tabular</h2>
			
			</div>		
		<div class="cl-mcont">
			<div class="row">
				
				
				<div class="col-sm-6 col-md-12">

					<div class="block-flat">
					
						<?php
						  if($this->session->flashdata('alert')){echo $this->session->flashdata('alert');}
						  ?>
						  
					
				<table style="border:none" width="100">
					<tr style="border:none">
					
					
						<td style="border:none"><a href="<?php echo $own_url;?>data-tabular/excel/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>"><button type="button" class="btn btn-info"><i class="fa fa-save"></i> Download Format Excel</button></a></td>
					
					
						<td style="border:none">
						<form action="<?php echo $own_url;?>data-tabular/import-excel/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>" method="post" enctype="multipart/form-data">
						<input type="file" name="userfile" id="userfile" style="display:none">
						
						<!--
						<span class="button" id="upload">upload file</span>
<span id="filename"></span>-->

<span class="btn btn-success" id="span_import_excel"><i class="fa fa-plus"></i> Import Excel</span>


						<input type="submit" name="submit" id="submit" value="Import" style="display:none">
						</form>
						</td>
						
						<td style="border:none">		
						<select name="pilih_tahun" id="pilih_tahun" class="form-control">
						<?php
						for($th=2000;$th<=date('Y');$th++){
						
						if($this->uri->segment(4) == $th){
						$selected ='selected';
						} else {
						$selected = '';
						}
						?>
						<option value="<?php  echo $th;?>" <?php echo $selected;?>><?php  echo $th;?></option>
						<?php
						}
						?>
						</select></td>
						
						<td style="border:none">	
						<a data-toggle="modal" data-target="#mod-warning"  href="#" ><button type="button" class="btn btn-warning btn-rad">Salin Data</button></a>
							
						</td>
						
						
						<td style="border:none">	
					
								<a data-toggle="modal" data-target="#mod-warning2"  href="#" ><button type="button" class="btn btn-danger btn-rad">Salin Ketersediaan Data</button></a>
						</td>
						
						<td style="border:none"><span class="tombol-edit-table btn btn-primary btn-rad" id="edit_table">Edit Table</span>
						<span class="tombol-simpan-table btn btn-primary btn-rad" id="simpan_table">Simpan</span>
						</td>
					</tr>
				</table>
					
			
					
									
									
					
						
				
						
						<div class="content">
						<form action="" method="post" id="form_edit_data">
							<table>
								<thead>
									<tr style="background:#2E2E2E;color:white">
										<th style="width:10px;">No</th>
										<th>Nama</th>
										<th>Nilai</th>
										<th>Satuan</th>
										<th>Ketersediaan Data</th>
										<th>Sumber Data</th>
										
									</tr>
								</thead>
								<tbody>
								<?php
								$no_d = 0;
								$no_cb = 0;
								
								foreach($data_tabular as $data_tabular){
								$no_d++;
								?>
									<tr style="background:white">
										<td valign="" align="center"><?php echo $no_d;?></td>
										<td valign="" align="" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo str_replace(' ','&nbsp;',$data_tabular->data);?></td>
										<td valign="" align="right">
										
										<?php
										if($data_tabular->tipe_elemen == '*'){
										
										?>
										
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										
										<?php
										} else if($data_tabular->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular->id_data,$this->uri->segment(4));
										
										
										if($total_nilai->total_nilai > 0){
										$broken_number = explode('.',$total_nilai->total_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
	
										}
										
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular->id_data,$this->uri->segment(4));
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($data_tabular->id_data,$this->uri->segment(4));
										
										
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
										$broken_number = explode('.',$total_nilai->total_nilai/$banyak_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular->tipe_elemen == NULL){
										?>
										
										
										<?php 
										
										if($data_tabular->nilai == NULL){
										$nilai_dt = 'n/a';
										} else {
										
										$broken_number = explode('.',$data_tabular->nilai);
    
										if($broken_number[1]==0){
											$nilai_dt =  number_format($broken_number[0],0,'','.');
										}else{
											$nilai_dt = number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										
										}
										
										echo '<span class="nilai_span">'.$nilai_dt.'</span>';
										?>
										<input class="nilai_hidden form-control" type="text" name="nilai[]" id="nilai[]" value="<?php echo $nilai_dt;?>">
										<?php
										 
										?>
									
									
										<?php
										}
										?>
										</td>
										<td valign="" align="left"><?php echo $data_tabular->satuan;?></td>
										<td valign="" align="left"><?php 
										if($data_tabular->ketersediaan_data == 1){
										$checked='checked="checked"';
										} else {
										$checked='';
										} echo '<input type="checkbox" '.$checked.' disabled value="Ada" name="ketersediaan_data['.$no_cb.']" id="ketersediaan_data['.$no_cb.']" class="ketersediaan_data icheck">&nbsp;Ada';
										
										?></td>
										<td valign="" align="">
										
										<?php 
										
										if($data_tabular->tipe_elemen == NULL){
									
									$list_sumber_data = $this->all_model->sumber_data_all();
									
										?>
										<select name="sumber_data[]" id="sumber_data[]" class="sumber_data form-control">
										<?php
										foreach($list_sumber_data as $lsd){
										
										if($data_tabular->sumber_data == NULL){
										
											if($lsd->nilai_default == 1){
											$selected = 'selected';
											} else {
											$selected = '';
											}
										
										} else {
										
											if($data_tabular->data_sumber_data == $lsd->id_sumber_data){
											$selected = 'selected';
											} else {
											$selected = '';
											}
											
										}
										
										?>
										<option value="<?php echo $lsd->id_sumber_data;?>" <?php echo $selected;?>><?php echo $lsd->sumber_data;?></option>
										<?php
										}
										?>
										
										</select>
										<?php
										} else {
										?>
										<input type="hidden" name="sumber_data[]" id="sumber_data[]" value="">
										<?php
										}
										
										?>
										
										<?php echo '<span class="sumber_data_span">'.$data_tabular->sumber_data.'</span>';?>
										
										
										<input type="hidden" name="id_item_data[]" id="id_item_data[]" value="<?php echo $data_tabular->id_item_data;?>">
										
										</td>
								
										
									</tr>
									
									<?php
								$no_cb++;
								}
								
								/*
								foreach($data_tabular as $data_tabular){
								$no_d++;
								
								
								?>
									<tr style="background:white">
										<td valign="" align="center"><?php echo $no_d;?></td>
										<td valign="" align="" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo str_replace(' ','&nbsp;',$data_tabular->data);?></td>
										<td valign="" align="right">
										
										<?php
										if($data_tabular->tipe_elemen == '*'){
										
										?>
										
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										
										<?php
										} else if($data_tabular->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular->id_data,$this->uri->segment(4));
										
										
										if($total_nilai->total_nilai > 0){
										$broken_number = explode('.',$total_nilai->total_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
	
										}
										
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular->id_data,$this->uri->segment(4));
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($data_tabular->id_data,$this->uri->segment(4));
										
										
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
										$broken_number = explode('.',$total_nilai->total_nilai/$banyak_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular->tipe_elemen == NULL){
										?>
										
										
										<?php 
										
										if($data_tabular->nilai == NULL){
										$nilai_dt = 'n/a';
										} else {
										
										$broken_number = explode('.',$data_tabular->nilai);
    
										if($broken_number[1]==0){
											$nilai_dt =  number_format($broken_number[0],0,'','.');
										}else{
											$nilai_dt = number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										
										}
										
										echo '<span class="nilai_span">'.$nilai_dt.'</span>';
										?>
										<input class="nilai_hidden form-control" type="text" name="nilai[]" id="nilai[]" value="<?php echo $nilai_dt;?>">
										<?php
										 
										?>
									
									
										<?php
										}
										?>
										</td>
										<td valign="" align="left"><?php echo $data_tabular->satuan;?></td>
										<td valign="" align="left"><?php 
										if($data_tabular->ketersediaan_data == 1){
										$checked='checked="checked"';
										} else {
										$checked='';
										} echo '<input type="checkbox" '.$checked.' disabled value="Ada" name="ketersediaan_data['.$no_cb.']" id="ketersediaan_data['.$no_cb.']" class="ketersediaan_data icheck">&nbsp;Ada';
										
										?></td>
										<td valign="" align="">
										
										<?php 
										
										if($data_tabular->tipe_elemen == NULL){
									
									$list_sumber_data = $this->all_model->sumber_data_all();
									
										?>
										<select name="sumber_data[]" id="sumber_data[]" class="sumber_data form-control">
										<?php
										foreach($list_sumber_data as $lsd){
										
										if($data_tabular->sumber_data == NULL){
										
											if($lsd->nilai_default == 1){
											$selected = 'selected';
											} else {
											$selected = '';
											}
										
										} else {
										
											if($data_tabular->data_sumber_data == $lsd->id_sumber_data){
											$selected = 'selected';
											} else {
											$selected = '';
											}
											
										}
										
										?>
										<option value="<?php echo $lsd->id_sumber_data;?>" <?php echo $selected;?>><?php echo $lsd->sumber_data;?></option>
										<?php
										}
										?>
										
										</select>
										<?php
										} else {
										?>
										<input type="hidden" name="sumber_data[]" id="sumber_data[]" value="">
										<?php
										}
										
										?>
										
										<?php echo '<span class="sumber_data_span">'.$data_tabular->sumber_data.'</span>';?>
										
										
										<input type="hidden" name="id_item_data[]" id="id_item_data[]" value="<?php echo $data_tabular->id_item_data;?>">
										
										</td>
								
										
									</tr>
									
									<?php
									
									
									$dt2 = $this->all_model->data_tabular_2($data_tabular->id_data,$this->uri->segment(4));
									
									// data tabular kedua 22222222222222222222222222222222222222222222222222222222222222222222222222222222222222
									foreach($dt2 as $data_tabular2 ){
									$no_d++;
									$no_cb++;
									?>
									
									<tr style="background:white">
										<td valign="" align="center"><?php echo $no_d;?></td>
										<td valign="" align="" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo str_replace(' ','&nbsp;',$data_tabular2->data);?></td>
										<td valign="" align="right">
										
										<?php
										if($data_tabular2->tipe_elemen == '*'){
										
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular2->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular2->id_data,$this->uri->segment(4));
										
										
										if($total_nilai->total_nilai > 0){
										$broken_number = explode('.',$total_nilai->total_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										}
	
										
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular2->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular2->id_data,$this->uri->segment(4));
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($data_tabular2->id_data,$this->uri->segment(4));
										
										
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										$broken_number = explode('.',$total_nilai->total_nilai/$banyak_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular2->tipe_elemen == NULL){
										?>
										
										
										<?php 
										
										if($data_tabular2->nilai == NULL){
										$nilai_dt = 'n/a';
										} else {
										
										$broken_number = explode('.',$data_tabular2->nilai);
    
										if($broken_number[1]==0){
											$nilai_dt =  number_format($broken_number[0],0,'','.');
										}else{
											$nilai_dt = number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										
										}
										
										echo '<span class="nilai_span">'.$nilai_dt.'</span>';
										?>
										<input class="nilai_hidden form-control" type="text" name="nilai[]" id="nilai[]" value="<?php echo $nilai_dt;?>">
										<?php
										 
										?>
									
										<?php
										}
										
										?>
										
										</td>
										<td valign="" align="left"><?php echo $data_tabular2->satuan;?></td>
										<td valign="" align="left"><?php 
										if($data_tabular2->ketersediaan_data == 1){
										$checked='checked="checked"';
										} else {
										$checked='';
										} echo '<input type="checkbox" '.$checked.' disabled value="Ada" name="ketersediaan_data['.$no_cb.']" id="ketersediaan_data['.$no_cb.']" class="ketersediaan_data icheck">&nbsp;Ada';
										
										?></td>
										<td valign="" align="">
										
										<?php 
										
										if($data_tabular2->tipe_elemen == NULL){
									
									$list_sumber_data = $this->all_model->sumber_data_all();
									
										?>
										<select name="sumber_data[]" id="sumber_data[]" class="sumber_data form-control">
										<?php
										foreach($list_sumber_data as $lsd){
										
										if($data_tabular2->sumber_data == NULL){
										
											if($lsd->nilai_default == 1){
											$selected = 'selected';
											} else {
											$selected = '';
											}
										
										} else {
										
											if($data_tabular2->data_sumber_data == $lsd->id_sumber_data){
											$selected = 'selected';
											} else {
											$selected = '';
											}
											
										}
										
										?>
										<option value="<?php echo $lsd->id_sumber_data;?>" <?php echo $selected;?>><?php echo $lsd->sumber_data;?></option>
										<?php
										}
										?>
										
										</select>
										<?php
										} else {
										?>
										<input type="hidden" name="sumber_data[]" id="sumber_data[]" value="">
										<?php
										}
										
										?>
										
										<?php echo '<span class="sumber_data_span">'.$data_tabular2->sumber_data.'</span>';?>
										
										
										<input type="hidden" name="id_item_data[]" id="id_item_data[]" value="<?php echo $data_tabular2->id_item_data;?>">
										
										</td>
								
										
									</tr>
									
									
									
									
									
									
									<?php
									
									$dt3 = $this->all_model->data_tabular_2($data_tabular2->id_data,$this->uri->segment(4));
									
									// data tabular kedua 33333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333
									foreach($dt3 as $data_tabular3 ){
									$no_d++;
									$no_cb++;
									?>
									
									<tr style="background:white">
										<td valign="" align="center"><?php echo $no_d;?></td>
										<td valign="" align="" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo str_replace(' ','&nbsp;',$data_tabular3->data);?></td>
										<td valign="" align="right">
										
										<?php
										if($data_tabular3->tipe_elemen == '*'){
										
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular3->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular3->id_data,$this->uri->segment(4));
										
										
										if($total_nilai->total_nilai > 0){
										$broken_number = explode('.',$total_nilai->total_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
	
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular3->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular3->id_data,$this->uri->segment(4));
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($data_tabular3->id_data,$this->uri->segment(4));
										
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
										$broken_number = explode('.',$total_nilai->total_nilai/$banyak_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular3->tipe_elemen == NULL){
										?>
										
										
										<?php 
										
										if($data_tabular3->nilai == NULL){
										$nilai_dt = 'n/a';
										} else {
										$broken_number = explode('.',$data_tabular3->nilai);
    
										if($broken_number[1]==0){
											$nilai_dt =  number_format($broken_number[0],0,'','.');
										}else{
											$nilai_dt = number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										}
										
										echo '<span class="nilai_span">'.$nilai_dt.'</span>';
										?>
										<input class="nilai_hidden form-control" type="text" name="nilai[]" id="nilai[]" value="<?php echo $nilai_dt;?>">
										<?php
										 }
										?>
									
									
										</td>
										<td valign="" align="left"><?php echo $data_tabular3->satuan;?></td>
										<td valign="" align="left"><?php 
										if($data_tabular3->ketersediaan_data == 1){
										$checked='checked="checked"';
										} else {
										$checked='';
										} echo '<input type="checkbox" '.$checked.' disabled value="Ada" name="ketersediaan_data['.$no_cb.']" id="ketersediaan_data['.$no_cb.']" class="ketersediaan_data icheck">&nbsp;Ada';
										
										?></td>
										<td valign="" align="">
										
										<?php 
										
										if($data_tabular3->tipe_elemen == NULL){
									
									$list_sumber_data = $this->all_model->sumber_data_all();
									
										?>
										<select name="sumber_data[]" id="sumber_data[]" class="sumber_data form-control">
										<?php
										foreach($list_sumber_data as $lsd){
										
										if($data_tabular3->sumber_data == NULL){
										
											if($lsd->nilai_default == 1){
											$selected = 'selected';
											} else {
											$selected = '';
											}
										
										} else {
										
											if($data_tabular3->data_sumber_data == $lsd->id_sumber_data){
											$selected = 'selected';
											} else {
											$selected = '';
											}
											
										}
										
										?>
										<option value="<?php echo $lsd->id_sumber_data;?>" <?php echo $selected;?>><?php echo $lsd->sumber_data;?></option>
										<?php
										}
										?>
										
										</select>
										<?php
										} else {
										?>
										<input type="hidden" name="sumber_data[]" id="sumber_data[]" value="">
										<?php
										}
										
										?>
										
										<?php echo '<span class="sumber_data_span">'.$data_tabular3->sumber_data.'</span>';?>
										
										
										<input type="hidden" name="id_item_data[]" id="id_item_data[]" value="<?php echo $data_tabular3->id_item_data;?>">
										
										</td>
								
										
									</tr>
									
									
									
									
									
									
										
									<?php
									
									$dt4 = $this->all_model->data_tabular_2($data_tabular3->id_data,$this->uri->segment(4));
									
									// data tabular kedua 444444444444444444444444444444444444444444444444444444444444444444444444444444444444444444444
									foreach($dt4 as $data_tabular4 ){
									$no_d++;
									$no_cb++;
									?>
									
									<tr style="background:white">
										<td valign="" align="center"><?php echo $no_d;?></td>
										<td valign="" align="" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo str_replace(' ','&nbsp;',$data_tabular4->data);?></td>
										<td valign="" align="right">
										
										<?php
										if($data_tabular4->tipe_elemen == '*'){
										
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular4->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular4->id_data,$this->uri->segment(4));
										
										
										if($total_nilai->total_nilai > 0){
										$broken_number = explode('.',$total_nilai->total_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
	
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular4->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular4->id_data,$this->uri->segment(4));
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($data_tabular4->id_data,$this->uri->segment(4));
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
										$broken_number = explode('.',$total_nilai->total_nilai/$banyak_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular4->tipe_elemen == NULL){
										?>
										
										
										<?php 
										
										
										if($data_tabular4->nilai == NULL){
										$nilai_dt = 'n/a';
										} else {
										$broken_number = explode('.',$data_tabular4->nilai);
    
										if($broken_number[1]==0){
											$nilai_dt =  number_format($broken_number[0],0,'','.');
										}else{
											$nilai_dt = number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										}
										
										echo '<span class="nilai_span">'.$nilai_dt.'</span>';
										?>
										<input class="nilai_hidden form-control" type="text" name="nilai[]" id="nilai[]" value="<?php echo $nilai_dt;?>">
										<?php
										 }
										?>
									
										</td>
										<td valign="" align="left"><?php echo $data_tabular4->satuan;?></td>
										<td valign="" align="left"><?php 
										if($data_tabular4->ketersediaan_data == 1){
										$checked='checked="checked"';
										} else {
										$checked='';
										} echo '<input type="checkbox" '.$checked.' disabled value="Ada" name="ketersediaan_data['.$no_cb.']" id="ketersediaan_data['.$no_cb.']" class="ketersediaan_data icheck">&nbsp;Ada';
										
										?></td>
										<td valign="" align="">
										
										<?php 
										
										if($data_tabular4->tipe_elemen == NULL){
									
									$list_sumber_data = $this->all_model->sumber_data_all();
									
										?>
										<select name="sumber_data[]" id="sumber_data[]" class="sumber_data form-control">
										<?php
										foreach($list_sumber_data as $lsd){
										
										if($data_tabular4->sumber_data == NULL){
										
											if($lsd->nilai_default == 1){
											$selected = 'selected';
											} else {
											$selected = '';
											}
										
										} else {
										
											if($data_tabular4->data_sumber_data == $lsd->id_sumber_data){
											$selected = 'selected';
											} else {
											$selected = '';
											}
											
										}
										
										?>
										<option value="<?php echo $lsd->id_sumber_data;?>" <?php echo $selected;?>><?php echo $lsd->sumber_data;?></option>
										<?php
										}
										?>
										
										</select>
										<?php
										} else {
										?>
										<input type="hidden" name="sumber_data[]" id="sumber_data[]" value="">
										<?php
										}
										
										?>
										
										<?php echo '<span class="sumber_data_span">'.$data_tabular4->sumber_data.'</span>';?>
										
										
										<input type="hidden" name="id_item_data[]" id="id_item_data[]" value="<?php echo $data_tabular4->id_item_data;?>">
										
										</td>
								
										
									</tr>
									
									
									<?php
									
									$dt5 = $this->all_model->data_tabular_2($data_tabular4->id_data,$this->uri->segment(4));
									
									// data tabular kedua 555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555555
									foreach($dt5 as $data_tabular5 ){
									$no_d++;
									$no_cb++;
									?>
									
									<tr style="background:white">
										<td valign="" align="center"><?php echo $no_d;?></td>
										<td valign="" align="" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo str_replace(' ','&nbsp;',$data_tabular5->data);?></td>
										<td valign="" align="right">
										
										<?php
										if($data_tabular5->tipe_elemen == '*'){
										
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular5->tipe_elemen == '**'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular5->id_data,$this->uri->segment(4));
										
										
										if($total_nilai->total_nilai > 0){
										$broken_number = explode('.',$total_nilai->total_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
	
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular5->tipe_elemen == '***'){
										
										$total_nilai = $this->all_model->sum_bintang_dua($data_tabular5->id_data,$this->uri->segment(4));
										$banyak_nilai = $this->all_model->sum_bintang_tiga_count($data_tabular5->id_data,$this->uri->segment(4));
										
										if($total_nilai->total_nilai > 0 AND $banyak_nilai > 0){
										
										$broken_number = explode('.',$total_nilai->total_nilai/$banyak_nilai);
    
										if($broken_number[1]==0){
											echo number_format($broken_number[0],0,'','.');
										}else{
											echo number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										}
										
										?>
										<input type="hidden" name="nilai[]" id="nilai[]" value="">
										<?php
										} else if($data_tabular5->tipe_elemen == NULL){
										?>
										
										
										<?php 
										
										
										if($data_tabular5->nilai == NULL){
										$nilai_dt = 'n/a';
										} else {
										$broken_number = explode('.',$data_tabular5->nilai);
    
										if($broken_number[1]==0){
											$nilai_dt =  number_format($broken_number[0],0,'','.');
										}else{
											$nilai_dt = number_format($broken_number[0],0,'','.').','.$broken_number[1];
										};
										
										}
										
										echo '<span class="nilai_span">'.$nilai_dt.'</span>';
										?>
										<input class="nilai_hidden form-control" type="text" name="nilai[]" id="nilai[]" value="<?php echo $nilai_dt;?>">
										<?php
										 }
										?>
									
										</td>
										<td valign="" align="left"><?php echo $data_tabular5->satuan;?></td>
										<td valign="" align="left"><?php 
										if($data_tabular5->ketersediaan_data == 1){
										$checked='checked="checked"';
										} else {
										$checked='';
										} echo '<input type="checkbox" '.$checked.' disabled value="Ada" name="ketersediaan_data['.$no_cb.']" id="ketersediaan_data['.$no_cb.']" class="ketersediaan_data icheck">&nbsp;Ada';
										
										?></td>
										<td valign="" align="">
										
										<?php 
										
										if($data_tabular5->tipe_elemen == NULL){
									
									$list_sumber_data = $this->all_model->sumber_data_all();
									
										?>
										<select name="sumber_data[]" id="sumber_data[]" class="sumber_data form-control">
										<?php
										foreach($list_sumber_data as $lsd){
										
										if($data_tabular5->sumber_data == NULL){
										
											if($lsd->nilai_default == 1){
											$selected = 'selected';
											} else {
											$selected = '';
											}
										
										} else {
										
											if($data_tabular5->data_sumber_data == $lsd->id_sumber_data){
											$selected = 'selected';
											} else {
											$selected = '';
											}
											
										}
										
										?>
										<option value="<?php echo $lsd->id_sumber_data;?>" <?php echo $selected;?>><?php echo $lsd->sumber_data;?></option>
										<?php
										}
										?>
										
										</select>
										<?php
										} else {
										?>
										<input type="hidden" name="sumber_data[]" id="sumber_data[]" value="">
										<?php
										}
										
										?>
										
										<?php echo '<span class="sumber_data_span">'.$data_tabular5->sumber_data.'</span>';?>
										
										
										<input type="hidden" name="id_item_data[]" id="id_item_data[]" value="<?php echo $data_tabular5->id_item_data;?>">
										
										</td>
								
										
									</tr>
									
									
									<?php
									
									}
									?>
									
									<?php
									
									}
									?>
									
									
									<?php
								
									}
									?>
									
									
									<?php
									
									}
									
									
									?>
									
									
									
								<?php
								
								
								$no_cb++;
								}
								*/
								?>
								</tbody>
							</table>	
							
							<input type="submit" name="submit_edit_data" id="submit_edit_data" value="Simpan" style="display:none">
</form>							
						</div>
						
						<br />
						
						Keterangan : <br /> 
1). tanda(*) merupakan judul dari element data yang tidak ada nilainya. <br />
2). tanda(**) merupakan parent element data yang nilainya didapat dari akumulasi nilai sub-sub nya. <br />
3). tanda(***) merupakan parent element data yang nilainya didapat dari rata -rata nilai sub-sub nya. <br />
4). Apabila memiliki Elemen Data namun belum mempunyai nilai, maka kolom nilai dikosongkan dan tetap di ceklist ketersediaan datanya.  <br />
						
					</div>
					
					
				
				</div>			
			</div>
			
	
			
		  </div>
		  
		  
		  
		  
		  
		  
		  
		  
		     <!-- Modal -->
		   <form action="<?php echo $own_url;?>data-tabular/salin-data/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>" method="post" >
							  <div class="modal fade" id="mod-warning" tabindex="-1" role="dialog">
								<div class="modal-dialog">
								  <div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									</div>
									<div class="modal-body">
										<div class="text-center">
											<div class="i-circle warning"><i class="fa fa-warning"></i></div>
											<h4>Perhatian !</h4>
											<p>Yakin Salin Data Dari Tahun Sebelumnya ?</p>
										</div>
									</div>
									<div class="modal-footer">
									  <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
									  <!--<button type="button" class="btn btn-warning" data-dismiss="modal">Hapus</button>-->
									  <input type="submit" name="submit" id="submit" class="btn btn-warning" value="Salin">
									 
									</div>
								  </div><!-- /.modal-content -->
								</div><!-- /.modal-dialog -->
							  </div><!-- /.modal -->
							  </form>
							  
							  
							  
							       <!-- Modal -->
		   <form action="<?php echo $own_url;?>data-tabular/salin-ketersediaan-data/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>" method="post" >
							  <div class="modal fade" id="mod-warning2" tabindex="-1" role="dialog">
								<div class="modal-dialog">
								  <div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									</div>
									<div class="modal-body">
										<div class="text-center">
											<div class="i-circle warning"><i class="fa fa-warning"></i></div>
											<h4>Perhatian !</h4>
											<p>Yakin Salin Ketersediaan Data Dari Tahun Sebelumnya ?</p>
										</div>
									</div>
									<div class="modal-footer">
									  <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
									  <!--<button type="button" class="btn btn-warning" data-dismiss="modal">Hapus</button>-->
									  <input type="submit" name="submit" id="submit" class="btn btn-warning" value="Salin">
									 
									</div>
								  </div><!-- /.modal-content -->
								</div><!-- /.modal-dialog -->
							  </div><!-- /.modal -->
							  </form>