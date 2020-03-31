	<div class="page-head">
				<h2>Ganti Password</h2>
			
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
              <label>Username</label> <input type="text" name="username" parsley-trigger="change" required placeholder="Enter user name" class="form-control" value="<?php echo $profilku->username;?>" readonly="readonly">
            </div>
            <div class="form-group">
              <label>Password Lama</label> <input type="password" name="pwdlama" id="pwdlama" parsley-trigger="change" required placeholder="Isikan Password Lama" class="form-control">
            </div>
            <div class="form-group"> 
              <label>Password Baru</label> <input id="password" type="password" name="password" placeholder="Isikan Password Baru" required class="form-control">
            </div> 
            <div class="form-group"> 
              <label>Konfirmasi Password</label> <input parsley-equalto="#password" type="password" id="confirm_password" name="confirm_password" required placeholder="Password" class="form-control">
            </div> 
       
	   <!--
              <button class="btn btn-primary" type="submit">Submit</button>
              <button class="btn btn-default">Cancel</button>-->
			  <input class="btn btn-primary" type="submit" name="submit" id="submit" value="Ganti">
			  <input class="btn btn-default" type="reset" value="Clear">
            </form>
          
          </div>
        </div>	
					
				
				</div>			
			</div>
			
		
			
		  </div>