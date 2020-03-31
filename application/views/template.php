<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="shortcut icon" href="images/favicon.png">

	<title><?php echo $title;?></title>
	<link href='<?php echo $public_url;?>css/style_laoding.css' rel='stylesheet' type='text/css'>
	<link href='<?php echo $public_url;?>css/font1.css' rel='stylesheet' type='text/css'>
	<link href='<?php echo $public_url;?>css/font1.css' rel='stylesheet' type='text/css'>

	<!-- Bootstrap core CSS -->
	<link href="<?php echo $public_url;?>js/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo $public_url;?>js/jquery.gritter/css/jquery.gritter.css" />
	<link rel="stylesheet" href="<?php echo $public_url;?>fonts/font-awesome-4/css/font-awesome.min.css">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script src="../../assets/js/html5shiv.js"></script>
	  <script src="../../assets/js/respond.min.js"></script>
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="<?php echo $public_url;?>js/jquery.nanoscroller/nanoscroller.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $public_url;?>js/jquery.easypiechart/jquery.easy-pie-chart.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $public_url;?>js/bootstrap.switch/bootstrap-switch.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $public_url;?>js/bootstrap.datetimepicker/css/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $public_url;?>js/jquery.select2/select2.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $public_url;?>js/bootstrap.slider/css/slider.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $public_url;?>js/jquery.niftymodals/css/component.css" />
	<!-- Custom styles for this template -->
	<link href="<?php echo $public_url;?>css/style.css" rel="stylesheet" />

</head>

<body>

  <!-- Fixed navbar -->
  <div id="head-nav" class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="fa fa-gear"></span>
        </button>
        <a class="navbar-brand" href="#"><span>SIPD</span></a>
      </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
		<?php
		
		$ac_np = '';
		$ac_sd = '';
		$ac_ek = '';
		$ac_us = '';
		$ac_id = '';
	

		
		// jika halaman sesuai makan akan ditambahkan id dan class 
		switch($page){
		case "Home";
		$ac_np = 'class="active"';
		break;
		case "Sumber Data";
		$ac_sd = 'class="active"';
		break;
		case "Kontak";
		$ac_ek = 'class="active"';
		break;
		case "User";
		$ac_us = 'class="active"';
		break;
		case "Item Data";
		$ac_id = 'class="active"';
		break;
	}
		
		?>
          <li <?php echo $ac_np;?>><a href="<?php echo $own_url;?>home">Nilai Profil</a></li>
          <li <?php echo $ac_sd;?>><a href="<?php echo $own_url;?>sumber-data">Sumber Data</a></li>
		  <li <?php echo $ac_ek;?>><a href="<?php echo $own_url;?>kontak">Edit Kontak</a></li>
		  <?php
		  if($this->session->userdata('level') == 'administrator'){
		  ?>
		  <li <?php echo $ac_us;?>><a href="<?php echo $own_url;?>user">User</a></li>
		  <li <?php echo $ac_id;?>><a href="<?php echo $own_url;?>item-data">Item Data</a></li>
		  <?php
		  }
		  ?>
         <!--<li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Contact <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="#">Action</a></li>
              <li><a href="#">Another action</a></li>
              <li><a href="#">Something else here</a></li>
      <li class="dropdown-submenu"><a href="#">Sub menu</a>
        <ul class="dropdown-menu">
          <li><a href="#">Action</a></li>
          <li><a href="#">Another action</a></li>
          <li><a href="#">Something else here</a></li>
          </ul>
      </li>              
      </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Large menu <b class="caret"></b></a>
      <ul class="dropdown-menu col-menu-2">
        <li class="col-sm-6 no-padding">
          <ul>
          <li class="dropdown-header"><i class="fa fa-group"></i>Users</li>
          <li><a href="#">Action</a></li>
          <li><a href="#">Another action</a></li>
          <li><a href="#">Something else here</a></li>
          <li class="dropdown-header"><i class="fa fa-gear"></i>Config</li>
          <li><a href="#">Action</a></li>
          <li><a href="#">Another action</a></li>
          <li><a href="#">Something else here</a></li> 
          </ul>
        </li>
        <li  class="col-sm-6 no-padding">
          <ul>
          <li class="dropdown-header"><i class="fa fa-legal"></i>Sales</li>
          <li><a href="#">New sale</a></li>
          <li><a href="#">Register a product</a></li>
          <li><a href="#">Register a client</a></li> 
          <li><a href="#">Month sales</a></li>
          <li><a href="#">Delivered orders</a></li>
          </ul>
        </li>
      </ul>
          </li>-->
        </ul>
    <ul class="nav navbar-nav navbar-right user-nav">
      <li class="dropdown profile_menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img alt="Avatar" src="<?php echo $public_url;?>images/user.png" /><?php echo $profilku->nama;?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
          <li><a href="<?php echo $own_url;?>password">Ganti Password</a></li>
          <!--<li><a href="#">Profile</a></li>
          <li><a href="#">Messages</a></li>
          <li class="divider"></li>-->
          <li><a href="<?php echo $own_url;?>logout">Sign Out</a></li>
        </ul>
      </li>
    </ul>		
<!--	
    <ul class="nav navbar-nav navbar-right not-nav">
      <li class="button dropdown">
        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class=" fa fa-comments"></i></a>
        <ul class="dropdown-menu messages">
          <li>
            <div class="nano nscroller">
              <div class="content">
                <ul>
                  <li>
                    <a href="#">
                      <img src="images/avatar2.jpg" alt="avatar" /><span class="date pull-right">13 Sept.</span> <span class="name">Daniel</span> I'm following you, and I want your money! 
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <img src="images/avatar_50.jpg" alt="avatar" /><span class="date pull-right">20 Oct.</span><span class="name">Adam</span> is now following you 
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <img src="images/avatar4_50.jpg" alt="avatar" /><span class="date pull-right">2 Nov.</span><span class="name">Michael</span> is now following you 
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <img src="images/avatar3_50.jpg" alt="avatar" /><span class="date pull-right">2 Nov.</span><span class="name">Lucy</span> is now following you 
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            <ul class="foot"><li><a href="#">View all messages </a></li></ul>           
          </li>
        </ul>
      </li>
      <li class="button dropdown">
        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-globe"></i><span class="bubble">2</span></a>
        <ul class="dropdown-menu">
          <li>
            <div class="nano nscroller">
              <div class="content">
                <ul>
                  <li><a href="#"><i class="fa fa-cloud-upload info"></i><b>Daniel</b> is now following you <span class="date">2 minutes ago.</span></a></li>
                  <li><a href="#"><i class="fa fa-male success"></i> <b>Michael</b> is now following you <span class="date">15 minutes ago.</span></a></li>
                  <li><a href="#"><i class="fa fa-bug warning"></i> <b>Mia</b> commented on post <span class="date">30 minutes ago.</span></a></li>
                  <li><a href="#"><i class="fa fa-credit-card danger"></i> <b>Andrew</b> killed someone <span class="date">1 hour ago.</span></a></li>
                </ul>
              </div>
            </div>
            <ul class="foot"><li><a href="#">View all activity </a></li></ul>           
          </li>
        </ul>
      </li>
      <li class="button"><a href="javascript:;"><i class="fa fa-microphone"></i></a></li>				
    </ul>
-->
      </div><!--/.nav-collapse -->
    </div>
  </div>
	
<div id="cl-wrapper">
<?php
if($page == "Home"){
?>
		<div class="cl-sidebar">
			<div class="cl-toggle"><i class="fa fa-bars"></i></div>
			<div class="cl-navblock">
				<ul class="cl-vnavigation">
				<?php
				/*
				foreach($kelompok_data as $kd){
				
				$jenis_data = $this->all_model->jenis_data_by_id_kel_data($kd->id_kelompok_data);
				
				$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
				
				if($detail_login->level == 'administrator'){
				?>
					<li><a href="#"><?php echo $kd->kelompok_data;?></a>
						<ul class="sub-menu">
						<?php
						foreach($jenis_data as $jd){
						?>
							<li><a href="<?php echo $own_url;?>data-tabular/index/<?php echo $jd->id_jenis_data;?>/<?php echo date('Y');?>"><?php echo $jd->jenis_data;?></a></li>
						<?php
						}
						?>
							
						</ul>
					</li>
				<?php
				*/
				
				foreach($kelompok_data as $kd){
				
				$jenis_data = $this->all_model->jenis_data_by_id_kel_data($kd->id_kelompok_data);
				
				$detail_login = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
				
				if($detail_login->level == 'administrator'){
				?>
					<li><a href="#" id="menu_<?php echo $kd->id_kelompok_data;?>"><?php echo $kd->kelompok_data;?></a>
						<ul class="sub-menu">
						<?php
						foreach($jenis_data as $jd){
						?>
							<li><a href="#" id_kelompok_data="<?php echo $jd->id_kelompok_data;?>" id_jenis_data="<?php echo $jd->id_jenis_data;?>" tahun="<?php echo date('Y');?>" id="data_tabular_halaman"><?php echo $jd->jenis_data;?></a></li>
						<?php
						}
						?>
							
						</ul>
					</li>
				<?php
				
				} else if($detail_login->level == 'user'){
				
				$pecah_menu = explode(',',$detail_login->menu);
				
				
				$cek_jenis_data = $this->all_model->jenis_data_by_id_kel_data($kd->id_kelompok_data);
				
				$nombor = 0;
				foreach($cek_jenis_data as $cjd){
				
					if (in_array($cjd->id_jenis_data, $pecah_menu)) {
					$nombor++;
					}
				
				}
				
				if($nombor>0){
				?>
				
				<li><a href="#" id="menu_<?php echo $kd->id_kelompok_data;?>"><?php echo $kd->kelompok_data;?></a>
						<ul class="sub-menu">
						<?php
						foreach($jenis_data as $jd){
						
						if (in_array($jd->id_jenis_data, $pecah_menu)) {
						
						?>
							<!--<li><a href="<?php echo $own_url;?>data-tabular/index/<?php echo $jd->id_jenis_data;?>/<?php echo date('Y');?>"><?php echo $jd->jenis_data;?></a></li>-->
							
							<li><a href="#" id_kelompok_data="<?php echo $jd->id_kelompok_data;?>" id_jenis_data="<?php echo $jd->id_jenis_data;?>" tahun="<?php echo date('Y');?>" id="data_tabular_halaman"><?php echo $jd->jenis_data;?></a></li>
						<?php
							}
							
						}
						?>
							
						</ul>
					</li>
					
					
				<?php
				}
				
				}
				
				
				
				}
				?>
				</ul>
			</div>
		</div>
	<?php
	}
	?>
	<div class="container-fluid" id="pcont">
	

						
	<div id="halaman_konten">
	
		
	
	<?php
		  if(isset($content)){echo $content;}
		  ?>
		  </div>
		  
		  
	</div> 
	
</div>



	<script src="<?php echo $public_url;?>js/jquery.js"></script>
	
	 <script>
	 
	 /*
	$(document).ready(function() {
    //$('#upload_file').submit(function(e) {
	
	$(document).on('submit','#upload_file',function(e){
	
	 $.isLoading({ text: "Loading" });
	 
	 var dataString    		  = { id_jenis_data  : $("#id_jenis_data_abis_load").val() ,  tahun : $("#pilih_tahun").val() };
	
	
	
        e.preventDefault();
        $.ajaxFileUpload({
	   //$.ajax({
            url             :'<?php echo $own_url;?>data-tabular/import-excel-ajax/',
            secureuri       :false,
            fileElementId   :'userfile',
			//dataType 	    : 'html',
            data            : dataString,
			dataType        : 'json',
            success : function (html)
            {
			
			// $.isLoading("hide");
			
			//alert(html.id);
		
               
			   
			  
			   
			   	if(html.status == "gagal"){
				$('#halaman_konten').html('<input type="button" id="pindah_halaman_gagal" id_jenis_data="'+ $("#id_jenis_data_abis_load").val()+'" tahun="'+$("#pilih_tahun").val()+'" style="display:none">');
			    $("#pindah_halaman_gagal").click();
				} else if(html.status == "berhasil"){
				 $('#halaman_konten').html('<input type="button" id="pindah_halaman_berhasil" id_jenis_data="'+ $("#id_jenis_data_abis_load").val()+'" tahun="'+$("#pilih_tahun").val()+'" style="display:none">');
				$("#pindah_halaman_berhasil").click();
				}
				 
				 //$(".nilai_hidden").hide();
	//$(".tombol-simpan-table").hide();
	//$(".sumber_data").hide();
	//$(".ketersediaan_data").attr('disabled',true);
	
            }
        });
		
		
		//self.location.replace('home');
		
		/*
	var dataString2    		  = { id_jenis_data  : $("#id_jenis_data_abis_load").val() ,  tahun : $("#pilih_tahun").val() };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/load-page",
				data: dataString2,
				cache: false,
		
				success: function(html2){
	//alert(html2);
				 $('#halaman_konten').html(html2);
				 
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	
	 $.isLoading("hide");
	//;
				 
			}
		});
		*/
		
		/*
        return false;
    });
	
	
	

		
		
		
});
*/
	</script>
    <script src="<?php echo $public_url?>js/ajax_upload/ajaxfileupload.js"></script>

	<script src="<?php echo $public_url;?>js/jquery.parsley/parsley.js" type="text/javascript"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.sparkline/jquery.sparkline.min.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.easypiechart/jquery.easy-pie-chart.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/behaviour/general.js"></script>
  <script type="text/javascript" src="<?php echo $public_url;?>js/jquery.ui/jquery-ui.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.nestable/jquery.nestable.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/bootstrap.switch/bootstrap-switch.min.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.select2/select2.min.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/bootstrap.slider/js/bootstrap-slider.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.gritter/js/jquery.gritter.js"></script>
  <script type="text/javascript" src="<?php echo $public_url;?>js/jquery.niftymodals/js/jquery.modalEffects.js"></script>   
  <script type="text/javascript">
    $(document).ready(function(){
      //initialize the javascript
      App.init();
      $('.md-trigger').modalEffects();
    });
  </script>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
  <script src="<?php echo $public_url;?>js/behaviour/voice-commands.js"></script>
  <script src="<?php echo $public_url;?>js/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.flot/jquery.flot.js"></script>
<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.flot/jquery.flot.pie.js"></script>
<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.flot/jquery.flot.resize.js"></script>
<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.flot/jquery.flot.labels.js"></script>

<script src="<?php echo $public_url;?>js/jquery.isloading/jquery.isloading.js" type="text/javascript"></script>
	
		
</body>
</html>


	  <script>
 

	$(document).ready(function() {
	
	$(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	


		$(document).on("click", ".delete", function(){

			var id      = $(this).attr("id");
			$("#id_delete").val(id);

			return false;

		});
		
		$(document).on("click", ".tambah", function(){

		
		$("#id_sumber_data").val("");
			$("#sumber_data").val("");
			$("#telepon").val("");
			$("#alamat").val("");

			$( '#form_tambah_sumber_data' ).parsley('reset');
			
			return false;

		});
		
		$(document).on("click", ".edit", function(){



			var id_edit      = $(this).attr("id_edit");
			var sumber_data      = $(this).attr("sumber_data");
			var telepon      = $(this).attr("telepon");
			var alamat      = $(this).attr("alamat");
		
			
			$("#id_edit").val(id_edit);
			$("#id_sumber_data_edit").val(id_edit);
			$("#sumber_data_edit").val(sumber_data);
			$("#telepon_edit").val(telepon);
			$("#alamat_edit").val(alamat);

			$( '#form_edit_sumber_data' ).parsley('reset');
			
			return false;

		});
		
		
		$(document).on("click", "#pindah_halaman", function(){
		
		 $.isLoading({ text: "Loading" });

		//self.location.replace("<?php echo $own_url;?>data-tabular/index/<?php echo $this->uri->segment(3);?>/"+$("#pilih_tahun").val());

		var id_jenis_data      = $("#pindah_halaman").attr("id_jenis_data");
			var tahun      = $("#pindah_halaman").attr("tahun");
		
		
	
		
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/load-page",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				 $('#halaman_konten').html(html);
				 
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	 $.isLoading("hide");
	
				 
			}
		});


		});
		
		
		$(document).on("click", "#pindah_halaman_gagal", function(){
		
		 $.isLoading({ text: "Loading" });

		//self.location.replace("<?php echo $own_url;?>data-tabular/index/<?php echo $this->uri->segment(3);?>/"+$("#pilih_tahun").val());

		var id_jenis_data      = $("#pindah_halaman_gagal").attr("id_jenis_data");
			var tahun      = $("#pindah_halaman_gagal").attr("tahun");
		
		
	
		
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/load-page-gagal",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				 $('#halaman_konten').html(html);
				 
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	 $.isLoading("hide");
	
				 
			}
		});


		});
		
		
		$(document).on("click", "#pindah_halaman_berhasil", function(){
		
		 $.isLoading({ text: "Loading" });

		//self.location.replace("<?php echo $own_url;?>data-tabular/index/<?php echo $this->uri->segment(3);?>/"+$("#pilih_tahun").val());

		var id_jenis_data      = $("#pindah_halaman_berhasil").attr("id_jenis_data");
			var tahun      = $("#pindah_halaman_berhasil").attr("tahun");
		
		
	
		
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/load-page-berhasil",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				 $('#halaman_konten').html(html);
				 
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	 $.isLoading("hide");
	
				 
			}
		});


		});
		
		
		// redirect halaman
		
		$(document).on("change", "#pilih_tahun", function(){
		
		 $.isLoading({ text: "Loading" });

		//self.location.replace("<?php echo $own_url;?>data-tabular/index/<?php echo $this->uri->segment(3);?>/"+$("#pilih_tahun").val());

		var id_jenis_data      = $("#id_jenis_data_abis_load").attr("id_jenis_data_abis_load");
			var tahun      = $("#pilih_tahun").val();
		
		
	
		
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/load-page",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				 $('#halaman_konten').html(html);
				 
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	 $.isLoading("hide");
	
				 
			}
		});


		});
		
		
		// submit import table
		
		$(document).on("change", "#userfile", function(){
		

		
			 $("#submit").click();
			

		});
		
		
		// klik import table
		
		$(document).on("click", "#span_import_excel", function(){

		
			$("#userfile").click();
			

		});
		
		
		// klik edit table
		
		$(document).on("click", "#edit_table", function(){

		$(".tombol-simpan-table").show();
		$(".tombol-edit-table").hide();
		
			$(".nilai_hidden").show();
			$(".nilai_span").hide();
			
			$(".sumber_data").show();
			$(".sumber_data_span").hide();
			
			$(".ketersediaan_data").removeAttr('disabled');

		});
		
		
		// klik simpan table
		
		$(document).on("click", "#simpan_table", function(){

		
			//$("#submit_edit_data").click();
			
				
 $.isLoading({ text: "Loading" });
	
		var id_item_data = new Array();
		var nilai = new Array();
		var ketersediaan_data = new Array();
		var sumber_data = new Array();
		
		var jumlah_looping_data = $('#jumlah_looping_data').val();
		
		for (i=0; i<jumlah_looping_data; i++){
		id_item_data[i] = $('#id_item_data'+i).val();
		
		nilai[i] = $('#nilai'+i).val();
		
		if($('#ketersediaan_data'+i).is(':checked')){
		ketersediaan_data[i] = $('#ketersediaan_data'+i).val();
		} else {
		ketersediaan_data[i] = "";
		}
		
		sumber_data[i] = $('#sumber_data'+i).val();
	}
		
	
var id_jenis_data      = $("#id_jenis_data_abis_load").attr("id_jenis_data_abis_load");
			var tahun      = $("#pilih_tahun").val();

		
      var dataString    		  = { id_item_data : id_item_data , nilai:nilai,ketersediaan_data:ketersediaan_data,sumber_data:sumber_data,id_jenis_data  : id_jenis_data ,  tahun : tahun,jumlah_looping_data:jumlah_looping_data };
	
	
	$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/edit-data-ajax",
				data: dataString,
				cache: false,
		
				success: function(html){

				$('#halaman_konten').html(html);
				
				 $(".nilai_hidden").hide();
				 
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
		 $.isLoading("hide");
			}
		});
		

		});
		
		// klik hapus user
		
		$(document).on("click", ".delete_user", function(){

			var id      = $(this).attr("id");
			$("#id_delete").val(id);

			return false;

		});
		
		// klik tambah user
		
		$(document).on("click", ".tambah_user", function(){

		
			$("#username").val("");
			$("#password").val("");
			$("#nama").val("");
			$(".checked-menu").attr('checked', false); 

			$( '#form_tambah_sumber_data' ).parsley('reset');
			
			return false;

		});
		
		// show sub menu edit user
		
		$(document).on("click", ".edit_user", function(){



			var id_edit      = $(this).attr("id_edit");
			var menu      = $(this).attr("menu");
			var username      = $(this).attr("username");
			var nama      = $(this).attr("nama");
			
		
			
			$("#id_edit").val(id_edit);
			$("#username_edit").val(username);
			$("#nama_edit").val(nama);
		
		
		var dataString    		  = { menu  : menu  };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>user/chained-menu",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				 $('#area_menu').html(html);
				 
			}
		});

			$( '#form_edit_sumber_data' ).parsley('reset');
			
			return false;

		});
		
		
		
		// load ajax page data tabular
		$(document).on("click", "#data_tabular_halaman", function(){

		
 $.isLoading({ text: "Loading" });

	 //$('#halaman_konten').html("Loading dub");
			var id_jenis_data      = $(this).attr("id_jenis_data");
			var tahun      = $(this).attr("tahun");
		var id_kelompok_data      = $(this).attr("id_kelompok_data");
		
	
		
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/load-page",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				 $('#halaman_konten').html(html);
				 
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	
	 $.isLoading("hide");
	 $("#menu_"+id_kelompok_data).click();
	
				 
			}
		});

			

		});
		
		
		// salin data ajax
		
		$(document).on("click", "#salin_data_ajax", function(){

		//alert('aaa');
 $.isLoading({ text: "Loading" });
//$('#halaman_konten').html("Loading dub");

			var id_jenis_data      = $("#id_jenis_data_abis_load").val();
			var tahun      = $("#pilih_tahun").val();
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/salin-data-ajax",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				$('#halaman_konten').html(html);
				  
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	$.isLoading("hide");
	
			}
		});
		
		
	

			

		});
	
	// salin ketersediaan data ajax
	$(document).on("click", "#salin_ketersediaan_ajax", function(){

		
 $.isLoading({ text: "Loading" });


			var id_jenis_data      = $("#id_jenis_data_abis_load").attr("id_jenis_data_abis_load");
			var tahun      = $("#pilih_tahun").val();
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/salin-ketersediaan-data-ajax",
				data: dataString,
				cache: false,
		
				success: function(html){

				$('#halaman_konten').html(html);
				
				 $(".nilai_hidden").hide();
				 
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
		 $.isLoading("hide");
			}
		});
		
		

			

		});
		
	});
			
	</script>
	
	<?php
	if($this->session->flashdata('halaman_load')){
	
		if($this->session->flashdata('halaman_load') == "gagal"){
	?>
		  <script>
 

	$(document).ready(function() {
	

		
		
	
		
		 $.isLoading({ text: "Loading" });

		
		var id_jenis_data      = "<?php echo $this->session->flashdata('id_load');?>";
			var tahun      = "<?php echo $this->session->flashdata('tahun_load');?>";
		
		
	
		
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/load-page-gagal",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				 $('#halaman_konten').html(html);
				 
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	 $.isLoading("hide");
	
				 
			}
		});


	
		
		



	

		
	});
			
	</script>
	
	<?php
		} else if($this->session->flashdata('halaman_load') == "berhasil"){
	?>
	
	
	<script>
 

	$(document).ready(function() {
	


		
		
		 $.isLoading({ text: "Loading" });

	
			var id_jenis_data      = "<?php echo $this->session->flashdata('id_load');?>";
			var tahun      = "<?php echo $this->session->flashdata('tahun_load');?>";
		
		
	
		
		
		var dataString    		  = { id_jenis_data  : id_jenis_data ,  tahun : tahun };
	
	
		$.ajax({
		
				type: "POST",
				url: "<?php echo $own_url; ?>data-tabular/load-page-berhasil",
				data: dataString,
				cache: false,
		
				success: function(html){
	
				 $('#halaman_konten').html(html);
				 
				 $(".nilai_hidden").hide();
	$(".tombol-simpan-table").hide();
	$(".sumber_data").hide();
	$(".ketersediaan_data").attr('disabled',true);
	 $.isLoading("hide");
	
				 
			}
		});



		
		


	

		
	});
			
	</script>
	
	<?php
	
		}
	}
	?>


	