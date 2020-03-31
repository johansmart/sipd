	<div class="page-head">
				<h2>Nilai Profil Tabular</h2>
			
			</div>		
		<div class="cl-mcont">
			<div class="row">
				
				
				<div class="col-sm-6 col-md-12">

					<div class="block-flat">
					
						<div class="content">
							<table>
								<thead>
									<tr style="background:#2E2E2E;color:white">
										<th style="width:10px;">No</th>
										<th>Kelompok Data</th>
										
									</tr>
								</thead>
								<tbody>
								<?php
								$no_kd = 0;
								$no_jd = 0;
								
								$no_untuk_user = 0;
								
								foreach($kelompok_data as $kd2){
								$no_kd++;
								
								$detail_login2 = $this->all_model->detail_login_by_id($this->session->userdata('id_login'));
								if($detail_login2->level == 'administrator'){
								?>
									<tr style="background:white">
										<td valign="top" align="center"><?php echo $no_kd;?></td>
										<td><?php echo $kd2->kelompok_data;?><br /> Jenis Data :
										
										
										
										<div class="content">
											<table>
												
												<thead>
												
												<?php
												$jenis_data2 = $this->all_model->jenis_data_by_id_kel_data($kd2->id_kelompok_data);
												foreach($jenis_data2 as $jd2){
												$no_jd++;
												?><tr>
													<th style="width:30px;"><?php echo Romawi($no_jd);?></th>
													<th>
													<?php
													/*
													?>
													<a href="<?php echo $own_url;?>data-tabular/index/<?php echo $jd2->id_jenis_data;?>/<?php echo date('Y');?>"><?php echo $jd2->jenis_data;?></a>
													<?php
													*/
													?>
													<a href="#" id_jenis_data="<?php echo $jd2->id_jenis_data;?>" tahun="<?php echo date('Y');?>" id="data_tabular_halaman"><?php echo $jd2->jenis_data;?></a>
													
													</th>
													</tr>
												<?php
												}
												?>
												</thead>
											</table>
										</div>
										
										
										</td>
										
									</tr>
								<?php
									} else {
									
									$pecah_menu2 = explode(',',$detail_login2->menu);
				
				
				
				
				
				
						$jenis_data2 = $this->all_model->jenis_data_by_id_kel_data($kd2->id_kelompok_data);
												
												$cek_jenis_data2 = $this->all_model->jenis_data_by_id_kel_data($kd2->id_kelompok_data);
				
				$nombor2 = 0;
				foreach($cek_jenis_data2 as $cjd2){
				
					if (in_array($cjd2->id_jenis_data, $pecah_menu2)) {
					$nombor2++;
					}
				
				}
				
				if($nombor2>0){
				
				$no_untuk_user++;
									
									?>
									<tr style="background:white">
										<td valign="top" align="center"><?php echo $no_untuk_user;?></td>
										<td><?php echo $kd2->kelompok_data;?><br /> Jenis Data :
										
										
										
										<div class="content">
											<table>
												<?php
												
										
				
				?>
												<thead>
												
												<?php
												
				
												foreach($jenis_data2 as $jd2){
												
												if (in_array($jd2->id_jenis_data, $pecah_menu2)) {
												
												$no_jd++;
												?><tr>
													<th style="width:30px;"><?php echo Romawi($no_jd);?></th>
													<th>
													
													<!--
													<a href="<?php echo $own_url;?>data-tabular/index/<?php echo $jd2->id_jenis_data;?>/<?php echo date('Y');?>"><?php echo $jd2->jenis_data;?></a>-->
													
													<a href="#" id_jenis_data="<?php echo $jd2->id_jenis_data;?>" tahun="<?php echo date('Y');?>" id="data_tabular_halaman"><?php echo $jd2->jenis_data;?></a>
													
													</th>
													</tr>
												<?php
												}
												
												}
												
												
												?>
												</thead>
												
											</table>
										</div>
										
										
										</td>
										
									</tr>
									
									<?php
									}
									  
									}
								 
								}
								?>
								</tbody>
							</table>						
						</div>
					</div>
					
					
				
				</div>			
			</div>
			
		
			
		  </div>