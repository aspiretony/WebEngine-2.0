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

if(check($_POST['package_submit'])) {
	try {
		
		$PayPal = new PayPal();
		$PayPal->setTitle($_POST['title']);
		$PayPal->setConfig($_POST['config']);
		$PayPal->setCredits($_POST['credits']);
		$PayPal->setCost($_POST['cost']);
		$PayPal->addPackage();
		
		redirect('paypal/packages');
		
	} catch(Exception $ex) {
		message('error', $ex->getMessage());
	}
}

$creditSystem = new CreditSystem();

echo '<div class="row">';
	echo '<div class="col-sm-12 col-md-8 col-lg-6">';
		echo '<div class="card">';
			echo '<div class="header">Add New PayPal Package</div>';
			echo '<div class="content">';
				
			echo '<form action="" method="post">';
				echo '<div class="form-group">';
					echo '<label for="input_1">Title / Phrase</label>';
					echo '<input type="text" class="form-control" id="input_1" name="title" maxlength="50" required autofocus>';
				echo '</div>';
				echo '<div class="form-group">';
					echo '<label>Credits</label>';
					echo $creditSystem->buildSelectInput("config", 0, "form-control");
				echo '</div>';
				echo '<div class="form-group">';
					echo '<label for="input_2">Credits</label>';
					echo '<input type="text" class="form-control" id="input_2" name="credits" required>';
				echo '</div>';
				echo '<div class="form-group">';
					echo '<label for="input_3">Cost</label>';
					echo '<input type="text" class="form-control" id="input_3" name="cost" required>';
				echo '</div>';
				echo '<button type="submit" class="btn btn-primary" name="package_submit" value="ok">Add Package</button> ';
				echo '<a href="'.admincp_base('paypal/packages').'" class="btn btn-large btn-danger">Cancel</a>';
			echo '</form>';
				
			echo '</div>';
		echo '</div>';
	echo '</div>';
echo '</div>';