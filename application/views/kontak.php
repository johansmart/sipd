	<div class="page-head">
				<h2>Edit Kontak</h2>
			
			</div>		
		<div class="cl-mcont">
			<div class="row">
				
				
				<div class="col-sm-6 col-md-12">

			     <div class="block-flat">
       
          <div class="content">

		    <?php
						  if($this->session->flashdata('alert')){echo $this->session->flashdata('alert');}
						  ?>
						  
          <form action="" parsley-validate novalidate method="post" enctype="multipart/form-data"> 
            <div class="form-group">
              <label>Nama</label> <input type="text" name="nama" parsley-trigger="change" required placeholder="Enter user name" class="form-control" value="<?php echo $profilku->nama;?>" readonly="readonly">
            </div>
      
	  
	  <div class="form-group">
              <label>Alamat</label> <input type="text" name="alamat" id="alamat" parsley-trigger="change" required placeholder="Enter user name" class="form-control" value="<?php echo $kontak->alamat;?>">
            </div>
			
			<div class="form-group">
              <label>No Telepon</label> <input type="text" name="telepon" id="telepon" parsley-trigger="change" required placeholder="Enter user name" class="form-control" value="<?php echo $kontak->telepon;?>">
            </div>
			
			
			<div class="form-group">
              <label>Fax</label> <input type="text" name="fax" id="fax" parsley-trigger="change" required placeholder="Enter user name" class="form-control" value="<?php echo $kontak->fax;?>">
            </div>
			
			<div class="form-group">
              <label>Email</label> <input type="text" name="email" id="email" parsley-type="email" required placeholder="Enter user name" class="form-control" value="<?php echo $kontak->email;?>">
            </div>
			
			<div class="form-group">
              <label>Website</label> <input type="text" name="website" id="website" parsley-trigger="change" required placeholder="Enter user name" class="form-control" value="<?php echo $kontak->website;?>">
            </div>
			
			<div class="form-group">
              <label>Latitude, Longitude</label> <input type="text" name="latitude_longitude" id="latitude_longitude" parsley-trigger="change" required placeholder="Enter user name" class="form-control" value="<?php echo $kontak->latitude_longitude;?>">
            </div>
			
			<div class="form-group">
              <label>Ket. Peta</label> <input type="text" name="ket_peta" id="ket_peta" parsley-trigger="change" required placeholder="Enter user name" class="form-control" value="<?php echo $kontak->ket_peta;?>">
            </div>
			
			
			 <div class="form-group">
                                      <label>Ganti Foto</label>
                                     
									
                                  
											 
											<input type="file" class="default" name="userfile" />
                                      
                                  </div>
       
	   <!--
              <button class="btn btn-primary" type="submit">Submit</button>
              <button class="btn btn-default">Cancel</button>-->
			  <input class="btn btn-primary" type="submit" name="submit" id="submit" value="Simpan">
			  <input class="btn btn-default" type="reset" value="Clear">
            </form>
          
          </div>
        </div>	
					
				
				</div>			
			</div>
			
		
			
		  </div>