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

	<link rel="stylesheet" href="<?php echo $public_url;?>fonts/font-awesome-4/css/font-awesome.min.css">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script src="../../assets/js/html5shiv.js"></script>
	  <script src="../../assets/js/respond.min.js"></script>
	<![endif]-->

	<!-- Custom styles for this template -->
	<link href="<?php echo $public_url;?>css/style.css" rel="stylesheet" />	

</head>

<body class="texture">

<div id="cl-wrapper" class="login-container">

	<div class="middle-login">
		<div class="block-flat">
			<div class="header">							
				<h3 class="text-center"><!--<img class="logo-img" src="<?php echo $public_url;?>images/logo.png" alt="logo"/>-->SISTEM INFORMASI PEMBANGUNAN DAERAH</h3>
			</div>
			<div>
				<form style="margin-bottom: 0px !important;" class="form-horizontal" action="" method="post" parsley-validate novalidate>
					<div class="content">
					
						<?php
		if($this->session->flashdata('alert')){echo $this->session->flashdata('alert');}
		?>
							 
						<h4 class="title">LOGIN SIPD</h4>
							<div class="form-group">
								<div class="col-sm-12">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-user"></i></span>
										<input type="text" required placeholder="Masukkan Username Anda" id="username" name="username" class="form-control">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-lock"></i></span>
										<input type="password" required placeholder="Masukkan Password Anda" id="password" name="password" class="form-control">
									</div>
								</div>
							</div>
							
					</div>
					<div class="foot">
						<!--<button class="btn btn-default" data-dismiss="modal" type="button">Register</button>-->
						<!--<button class="btn btn-primary" data-dismiss="modal" type="submit">Log me in</button>-->
						<input class="btn btn-primary" type="submit" name="submit" id="submit" value="LOGIN">
					</div>
				</form>
			</div>
		</div>
		<div class="text-center out-links"><a href="#">&copy; 2014 <?php echo $title;?></a></div>
	</div> 
	
</div>

<script src="<?php echo $public_url;?>js/jquery.js"></script>
<script src="<?php echo $public_url;?>js/jquery.parsley/parsley.js" type="text/javascript"></script>
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
