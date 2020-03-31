	<script>
	function warning() {
	return confirm('Yakin Hapus Data?');
}

	</script>
	
	
	<div class="page-head">
				<h2>User</h2>
			
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
									
									<a href="#" data-toggle="modal" data-target="#mod-info" class="tambah_user"><button type="button" class="btn btn-success"><i class="fa fa-plus"></i> Tambah Data</button></a>
									
									
									
					
				
					</div>
					<div style="float:right">
 <form class="form-inline" action="<?php echo $own_url;?>user/index/search" method="post">
 <select name="type" id="type"  class="form-control" style="width:40%">
          <option value="username">Username</option>
          
		  <option value="nama">Nama</option>
	
		
          
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
										<th>Username</th>
										<th>Nama</th>
								
										
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
								
								  ?>
								
								
								<tr>
									
									
										<td valign="top" align="center"><?php echo $no_sd;?></td>
										<td>
										
											
									
									
										<a style="margin-right:3px;" data-toggle="modal" data-target="#mod-edit" class="edit_user label label-success" href="#"  username="<?php echo $row->username;?>" nama="<?php echo $row->nama;?>"  id_edit="<?php echo $row->id_login;?>" menu="<?php echo $row->menu;?>"><i data-placement="top" data-toggle="tooltip" data-original-title="Edit" class="fa fa-pencil"></i>
										</a>
										
									
								
										
										<a data-toggle="modal" data-target="#mod-warning"  class="delete_user label label-danger" href="#" id="<?php echo $row->id_login;?>"><i data-placement="top" data-toggle="tooltip" data-original-title="Hapus" class="fa fa-times"></i></a>
										
										
											
										
										</td>
										<td><?php echo $row->username;?></td>
										<td><?php echo $row->nama;?></td>
									
										
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
		   <form action="<?php echo $own_url;?>user/hapus/<?php echo $this->uri->segment(3);?>" method="post" >
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
					  <form action="<?php echo $own_url;?>user/tambah" parsley-validate novalidate method="post" id="form_tambah_sumber_data"> 
							  <div class="modal fade colored-header" id="mod-info" tabindex="-1" role="dialog">
								<div class="modal-dialog">
								   <div class="md-content">
                      <div class="modal-header">
                        <h3>Tambah User</h3>
                        <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      </div>
                      <div class="modal-body">
                        <div class="text-center">
                          <!--<div class="i-circle primary"><i class="fa fa-check"></i></div>-->
                          <div class="form-group">
              <label>Username</label> <input type="text" name="username" id="username" parsley-trigger="change" required placeholder="Isikan Username" class="form-control" value="" >
            </div>
			
			<div class="form-group">
			  <label>Password</label> <input type="password" name="password" id="password" parsley-trigger="change" required placeholder="Isikan Password" class="form-control" value="" >
            </div>
          
		  <div class="form-group">
		   <label>Nama</label> <input type="text" name="nama" id="nama" parsley-trigger="change" required placeholder="Isikan Nama" class="form-control" value="" >
            </div>
			
			
			 <div class="form-group">
		   <label>Menu</label>  <br />
		   <table>
		   <tr><td></td><td  align="left"> </td></tr>
		   <?php
		   $menu = $this->all_model->kelompok_data_all();
		   foreach($menu  as $menu ){
		   
			$submenu = $this->all_model->jenis_data_by_id_kel_data($menu->id_kelompok_data);
			?>
			<tr><td>-</td><td  align="left"> &nbsp; <?php echo $menu->kelompok_data;?> </td></tr>
		 
		   
			<?php
		   foreach($submenu as $submenu){
		   ?>
		  <tr><td></td><td align="left"> <input class="checked-menu" type="checkbox" name="menu[]" id="menu[]" value="<?php echo $submenu->id_jenis_data;?>"> &nbsp; <?php echo $submenu->jenis_data;?> </td></tr>
		   
		 
		   
		   
		   <?php
		   }
		   
		   }
		   ?>
		   </table>
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
					  <form action="<?php echo $own_url;?>user/edit/<?php echo $this->uri->segment(3);?>" parsley-validate novalidate method="post" id="form_edit_sumber_data"> 
							  <div class="modal fade colored-header" id="mod-edit" tabindex="-1" role="dialog">
								<div class="modal-dialog">
								   <div class="md-content">
                      <div class="modal-header">
                        <h3>Edit User</h3>
                        <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      </div>
                      <div class="modal-body">
                        <div class="text-center">
                          <!--<div class="i-circle primary"><i class="fa fa-check"></i></div>-->
                                    <div class="form-group">
              <label>Username</label> <input type="text" name="username" id="username_edit" parsley-trigger="change" required placeholder="Isikan Username" class="form-control" value="" >
            </div>
			
			<div class="form-group">
			  <label>Password</label> <input type="password" name="password" id="password_edit" parsley-trigger="change" placeholder="Kosongi Password Jika Tidak Ingin Mengganti" class="form-control" value="" >
            </div>
          
		  <div class="form-group">
		   <label>Nama</label> <input type="text" name="nama" id="nama_edit" parsley-trigger="change" required placeholder="Isikan Nama" class="form-control" value="" >
            </div>
			
			
			 <div class="form-group">
		   <label>Menu</label>  <br />
		   <span id="area_menu">
		   <?php
		   /*
		   $menu2 = $this->all_model->kelompok_data_all();
		   foreach($menu2  as $menu2 ){
		   ?>
		   <span style="float:left"><input class="checked-menu" type="checkbox" name="menu[]" id="menu[]" value="<?php echo $menu2->id_kelompok_data;?>"> &nbsp; <?php echo $menu2->kelompok_data;?> </span><div style="clear:both"></div>
		   <?php
		   }
		   */
		   ?>
		   </span>
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