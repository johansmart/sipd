<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php echo $title;?></title>
	<link href='<?php echo $public_url;?>css/font1.css' rel='stylesheet' type='text/css'>
	<link href='<?php echo $public_url;?>css/font2.css' rel='stylesheet' type='text/css'>

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
	}
		
		?>
          <li <?php echo $ac_np;?>><a href="<?php echo $own_url;?>home">Nilai Profil</a></li>
          <li <?php echo $ac_sd;?>><a href="<?php echo $own_url;?>sumber-data">Sumber Data</a></li>
		  <li <?php echo $ac_ek;?>><a href="<?php echo $own_url;?>kontak">Edit Kontak</a></li>
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
		<div class="cl-sidebar">
			<div class="cl-toggle"><i class="fa fa-bars"></i></div>
			<div class="cl-navblock">
				<ul class="cl-vnavigation">
				<?php
				foreach($kelompok_data as $kd){
				
				$jenis_data = $this->all_model->jenis_data_by_id_kel_data($kd->id_kelompok_data);
				?>
					<li><a href="#"><?php echo $kd->kelompok_data;?></a>
						<ul class="sub-menu">
						<?php
						foreach($jenis_data as $jd){
						?>
							<li><a href="#"><?php echo $jd->jenis_data;?></a></li>
						<?php
						}
						?>
							
						</ul>
					</li>
				<?php
				}
				?>
				</ul>
			</div>
		</div>
		
		<div class="container-fluid" id="pcont">
		<?php
		  if(isset($content)){echo $content;}
		  ?>
		</div> 
		
	</div>

  <script src="<?php echo $public_url;?>js/jquery.js"></script>
   <script src="<?php echo $public_url;?>js/jquery.parsley/parsley.js" type="text/javascript"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.sparkline/jquery.sparkline.min.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.easypiechart/jquery.easy-pie-chart.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/behaviour/general.js"></script>
  <script src="<?php echo $public_url;?>js/jquery.ui/jquery-ui.js" type="text/javascript"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.nestable/jquery.nestable.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/bootstrap.switch/bootstrap-switch.min.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
  <script src="<?php echo $public_url;?>js/jquery.select2/select2.min.js" type="text/javascript"></script>
  <script src="<?php echo $public_url;?>js/bootstrap.slider/js/bootstrap-slider.js" type="text/javascript"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.gritter/js/jquery.gritter.min.js"></script>

    <script type="text/javascript">
      $(document).ready(function(){
        //initialize the javascript
        App.init();
      });
    </script>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo $public_url;?>js/bootstrap/dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.flot/jquery.flot.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.flot/jquery.flot.pie.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.flot/jquery.flot.resize.js"></script>
	<script type="text/javascript" src="<?php echo $public_url;?>js/jquery.flot/jquery.flot.labels.js"></script>
  </body>
</html>
