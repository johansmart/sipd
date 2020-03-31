	<div class="page-head">
				<h2>Tambah Item Data Untuk Tahun .... </h2>
			
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
              <label>Jenis Data</label> <select id="id_jenis_data" name="id_jenis_data" class="select2">
			  <?php
			  foreach($kelompok_data as $kelompok_data){
			  ?>
			  <optgroup label="<?php echo $kelompok_data->kelompok_data?>">
                         <?php
						 
						 $jenis_data = $this->all_model->jenis_data_by_id_kel_data($kelompok_data->id_kelompok_data);
			  foreach($jenis_data as $jd){
			  
			  if($this->session->flashdata('id_jenis') == $jd->id_jenis_data){
			  $selected = 'selected';
			  } else {
			  $selected = '';
			  }
			  
			  ?>
			  <option value="<?php echo $jd->id_jenis_data;?>" <?php echo $selected;?>><?php echo $jd->jenis_data;?></option>
			  <?php
			  }
			  ?>
                     </optgroup>
					 <?php
					 }
					 ?>
			
			  </select>
            </div>
          
		   <div class="form-group">
              <label>Tahun</label> 
			  <select id="tahun" name="tahun" class="select2">
			  <?php
			  for($i=date('Y');$i>=2000;$i--){
			  
			  
			  if($this->session->flashdata('tahun_jenis') == $i){
			  $selected2 = 'selected';
			  } else {
			  $selected2 = '';
			  }
			  
			  ?>
			  <option value="<?php echo $i;?>" <?php echo $selected2;?>><?php echo $i;?></option>
			  <?php
			  }
			  ?>
			  </select>
			  
            </div>
			
			
       
	   <!--
              <button class="btn btn-primary" type="submit">Submit</button>
              <button class="btn btn-default">Cancel</button>-->
			  <input class="btn btn-primary" type="submit" name="submit" id="submit" value="Tambah">
			  <input class="btn btn-default" type="reset" value="Clear">
            </form>
          
          </div>
        </div>	
					
				
				</div>			
			</div>
			
		
			
		  </div>