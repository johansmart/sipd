	<div class="page-head">
				<h2>Tambah Sumber Data</h2>
			
			</div>		
		<div class="cl-mcont">
			<div class="row">
				
				
				<div class="col-sm-6 col-md-12">

			     <div class="block-flat">
       
          <div class="content">

		    <?php
						  if($this->session->flashdata('alert')){echo $this->session->flashdata('alert');}
						  ?>
						  
          <form action="" parsley-validate novalidate method="post"> 
            <div class="form-group">
              <label>Nama Sumber Data</label> <input type="text" name="sumber_data" parsley-trigger="change" required placeholder="Isikan Nama Sumber Data" class="form-control" value="" >
            </div>
          
		   <div class="form-group">
              <label>Nomor Telepon</label> <input type="text" name="telepon" parsley-trigger="change" placeholder="Isikan Nomor Telepon Sumber Data" class="form-control" value="" >
            </div>
			
			 <div class="form-group">
              <label>Alamat</label> <input type="text" name="alamat" parsley-trigger="change" placeholder="Isikan Alamat Sumber Data" class="form-control" value="" >
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