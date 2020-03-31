	<script>
	function warning() {
	return confirm('Yakin Hapus Data?');
}

	</script>
	
	
	<div class="page-head">
				<h2>Referensi Sumber Data</h2>
			
			</div>		
		<div class="cl-mcont">
			<div class="row">
				
				
				<div class="col-sm-6 col-md-12">

					<div class="block-flat">
					
					<?php
						  if($this->session->flashdata('alert')){echo $this->session->flashdata('alert');}
						  ?>
						  
				
					
					
					 		  	<div>
								
<div style="float:left">
<!--
									<a href="<?php echo $own_url;?>sumber-data/tambah"><button type="button" class="btn btn-success"><i class="fa fa-plus"></i> Tambah Data</button></a>-->
									
									<a href="#" data-toggle="modal" data-target="#mod-info" class="tambah"><button type="button" class="btn btn-success"><i class="fa fa-plus"></i> Tambah Data</button></a>
									
									
									
					
					<a href="<?php echo $own_url;?>sumber-data/excel"><button type="button" class="btn btn-info"><i class="fa fa-save"></i> Convert To Excel</button></a>
					</div>
					<div style="float:right">
 <form class="form-inline" action="<?php echo $own_url;?>sumber-data/index/search" method="post">
 <select name="type" id="type"  class="form-control" style="width:40%">
          <option value="sumber_data">Nama Sumber Data</option>
          
		  <option value="telepon">Telepon Sumber Data</option>
		  <option value="alamat">Alamat Sumber Data</option>
		
          
    </select>
                    <input class="form-control"  style="width:40%" placeholder="Ketikkan Kata Kunci" name="search" id="appendedInputButton" type="text">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Cari</button>
                </form>
				
				</div>
				
				
				</div>
				<br /><br />
			  
						<div class="content">
							<table>
								<thead>
									<tr style="background:#2E2E2E;color:white">
										<th style="width:10px;">No</th>
										<th>Aksi</th>
										<th>Kode</th>
										<th>Nama Sumber Data</th>
										<th>No Telepon</th>
										<th>Alamat</th>
										
									</tr>
								</thead>
								<tbody>
								<?php
								
								
								  	   if($this->uri->segment(3) == 'search' ){
								  $no_sd = 0 + $this->uri->segment(4);
								  } else {
								  $no_sd = 0 + $this->uri->segment(3);
								  }
								  
								  
								foreach($row as $row){
								$no_sd++;
								
								 if($row->nilai_default == 1){ ?>
								
									<tr style="background:#D0F5A9">
									
									<?php } else { ?>
<tr>
									<?php } ?>
									
										<td valign="top" align="center"><?php echo $no_sd;?></td>
										
										<td>
										
											
										<!--
										<a data-placement="top" data-toggle="tooltip" data-original-title="Edit" class="label label-success" href="<?php echo $own_url;?>sumber-data/edit/<?php echo $row->id_sumber_data;?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-pencil"></i></a> -->
									
										<a style="margin-right:3px;" data-toggle="modal" data-target="#mod-edit" class="edit label label-success" href="#"  sumber_data="<?php echo $row->sumber_data;?>" telepon="<?php echo $row->telepon;?>" alamat="<?php echo $row->alamat;?>" id_edit="<?php echo $row->id_sumber_data;?>"><i data-placement="top" data-toggle="tooltip" data-original-title="Edit" class="fa fa-pencil"></i>
										</a>
										
										<?php if($row->nilai_default == 0){ ?>
										<a data-placement="top" data-toggle="tooltip" data-original-title="Set Default" class="label label-info" href="<?php echo $own_url;?>sumber-data/set-default/<?php echo $row->id_sumber_data;?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-check"></i></a> 
										<?php } ?>
										
										<!--
										<a onClick="return warning()" data-placement="top" data-toggle="tooltip" data-original-title="Hapus" class="label label-danger" href="<?php echo $own_url;?>sumber-data/hapus/<?php echo $row->id_sumber_data;?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-times"></i></a>
										-->
										
										<a data-toggle="modal" data-target="#mod-warning"  class="delete label label-danger" href="#" id="<?php echo $row->id_sumber_data;?>"><i data-placement="top" data-toggle="tooltip" data-original-title="Hapus" class="fa fa-times"></i></a>
										
										
											
										
										</td>
										<td><?php echo $row->id_sumber_data;?></td>
										<td><?php echo $row->sumber_data;?></td>
										<td><?php echo $row->telepon;?></td>
										<td><?php echo $row->alamat;?></td>
										
									</tr>
								<?php
								
							
								
								}
								?>
								</tbody>
							</table>	

				
						</div>
						
						<div>
						<?php
if(isset($paging)){echo $paging;}
?>			

</div> <div style="clear:both"></div>
					</div>
					
					
				
				</div>			
			</div>
			
		
			
		  </div>
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		   <!-- Modal -->
		   <form action="<?php echo $own_url;?>sumber-data/hapus/<?php echo $this->uri->segment(3);?>" method="post" >
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
											<p>Yakin Hapus Data ?</p>
										</div>
									</div>
									<div class="modal-footer">
									  <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
									  <!--<button type="button" class="btn btn-warning" data-dismiss="modal">Hapus</button>-->
									  <input type="submit" name="submit" id="submit" class="btn btn-warning" value="Hapus">
									  <input type="hidden" name="id_delete" id="id_delete">
									</div>
								  </div><!-- /.modal-content -->
								</div><!-- /.modal-dialog -->
							  </div><!-- /.modal -->
							  </form>
							  
							  
				   	  <!-- Modal -->
					  <form action="<?php echo $own_url;?>sumber-data/tambah" parsley-validate novalidate method="post" id="form_tambah_sumber_data"> 
							  <div class="modal fade colored-header" id="mod-info" tabindex="-1" role="dialog">
								<div class="modal-dialog">
								   <div class="md-content">
                      <div class="modal-header">
                        <h3>Tambah Sumber Data</h3>
                        <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      </div>
                      <div class="modal-body">
                        <div class="text-center">
                          <!--<div class="i-circle primary"><i class="fa fa-check"></i></div>-->
                          <div class="form-group">
						  
						   <div class="form-group">
              <label>Kode</label> <input type="text" name="id_sumber_data" id="id_sumber_data" parsley-trigger="change" required placeholder="Isikan Kode Sumber Data" class="form-control" >
            </div>
			
              <label>Nama Sumber Data</label> <input type="text" name="sumber_data" id="sumber_data" parsley-trigger="change" required placeholder="Isikan Nama Sumber Data" class="form-control" value="" >
            </div>
          
		   <div class="form-group">
              <label>Nomor Telepon</label> <input type="text" name="telepon" id="telepon" parsley-trigger="change" placeholder="Isikan Nomor Telepon Sumber Data" class="form-control" value="" >
            </div>
			
			 <div class="form-group">
              <label>Alamat</label> <input type="text" name="alamat" id="alamat" parsley-trigger="change" placeholder="Isikan Alamat Sumber Data" class="form-control" value="" >
            </div>
                        </div>
                      </div>
                      <div class="modal-footer"><!--
                        <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary btn-flat md-close" data-dismiss="modal">Proceed</button>-->
						<input class="btn btn-primary" type="submit" name="submit" id="submit" value="Simpan">
			  <input class="btn btn-default" type="reset" value="Clear">
                      </div>
                    </div><!-- /.modal-content -->
								</div><!-- /.modal-dialog -->
							  </div><!-- /.modal -->
							  </form>
							  
							  
							  
							  
							    	  <!-- Modal -->
					  <form action="<?php echo $own_url;?>sumber-data/edit/<?php echo $this->uri->segment(3);?>" parsley-validate novalidate method="post" id="form_edit_sumber_data"> 
							  <div class="modal fade colored-header" id="mod-edit" tabindex="-1" role="dialog">
								<div class="modal-dialog">
								   <div class="md-content">
                      <div class="modal-header">
                        <h3>Edit Sumber Data</h3>
                        <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      </div>
                      <div class="modal-body">
                        <div class="text-center">
                          <!--<div class="i-circle primary"><i class="fa fa-check"></i></div>-->
						  
						   <div class="form-group">
              <label>Kode</label> <input type="text" name="id_sumber_data" id="id_sumber_data_edit" parsley-trigger="change" required placeholder="Isikan Kode Sumber Data" class="form-control" >
            </div>
			
			
                          <div class="form-group">
              <label>Nama Sumber Data</label> <input type="text" name="sumber_data" id="sumber_data_edit" parsley-trigger="change" required placeholder="Isikan Nama Sumber Data" class="form-control" >
            </div>
          
		   <div class="form-group">
              <label>Nomor Telepon</label> <input type="text" name="telepon" id="telepon_edit" parsley-trigger="change" placeholder="Isikan Nomor Telepon Sumber Data" class="form-control"  >
            </div>
			
			 <div class="form-group">
              <label>Alamat</label> <input type="text" name="alamat" id="alamat_edit" parsley-trigger="change" placeholder="Isikan Alamat Sumber Data" class="form-control" >
            </div>
                        </div>
                      </div>
                      <div class="modal-footer"><!--
                        <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary btn-flat md-close" data-dismiss="modal">Proceed</button>-->
						<input class="btn btn-primary" type="submit" name="submit" id="submit" value="Simpan">
						<input type="hidden" name="id_edit" id="id_edit">
			<!--  <input class="btn btn-default" type="reset" value="Clear">-->
                      </div>
                    </div><!-- /.modal-content -->
								</div><!-- /.modal-dialog -->
							  </div><!-- /.modal -->
							  </form>