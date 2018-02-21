<?php
/**
 * WebEngine CMS
 * https://webenginecms.org/
 * 
 * @version 2.0.0
 * @author Lautaro Angelico <https://lautaroangelico.com/>
 * @copyright (c) 2013-2018 Lautaro Angelico, All Rights Reserved
 * 
 * Licensed under the MIT license
 * https://opensource.org/licenses/MIT
 */

$PayPal = new PayPal();
$creditSystem = new CreditSystem();

echo '<div class="row">';
	echo '<div class="col-sm-12 col-md-10 col-lg-8">';
		echo '<div class="card">';
			echo '<div class="header">PayPal Packages List</div>';
			echo '<div class="content table-responsive table-full-width">';
				
				$packagesList = $PayPal->getPackagesList();
				if(is_array($packagesList)) {
					echo '<table class="table table-hover table-striped">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Id</th>';
							echo '<th>Title / Phrase</th>';
							echo '<th>Credit Config</th>';
							echo '<th>Credits</th>';
							echo '<th>Cost</th>';
							echo '<th class="text-right">Actions</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					foreach($packagesList as $package) {
						
						$creditSystem->setConfigId($package['config']);
						$configData = $creditSystem->showConfigs(true);
						
						echo '<tr>';
							echo '<td>'.$package['id'].'</td>';
							echo '<td>'.(lang($package['title']) != 'ERROR' ? lang($package['title']).' ('.$package['title'].')' : $package['title']).'</td>';
							echo '<td>'.$configData['config_title'].'</td>';
							echo '<td>'.number_format($package['credits']).'</td>';
							echo '<td>$'.number_format($package['cost'], 2).'</td>';
							echo '<td class="td-actions text-right">';
								echo '<a href="'.admincp_base('paypal/edit/package/'.$package['id']).'" rel="tooltip" title="" class="btn btn-warning btn-simple btn-xs" data-original-title="Edit"><i class="fa fa-edit"></i></a>';
								echo '<a href="#" onclick="confirmationMessage(\''.admincp_base('paypal/delete/package/'.$package['id']).'\', \'Are you sure?\', \'This action will permanently delete the paypal package from the database.\', \'Confirm\', \'Cancel\')" rel="tooltip" title="" class="btn btn-danger btn-simple btn-xs" data-original-title="Delete"><i class="fa fa-times"></i></a>';
							echo '</td>';
						echo '</tr>';
					}
					echo '</tbody>';
					echo '</table>';
				} else {
					message('warning', 'There are no paypal packages in the database.');
				}
				
			echo '</div>';
		echo '</div>';
		
		echo '<a href="'.admincp_base('paypal/new').'" class="btn btn-primary">New Package</a>';
		
	echo '</div>';
echo '</div>';