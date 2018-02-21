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

// module configs
$cfg = loadConfig('paypal');
if(!is_array($cfg)) throw new Exception(lang('error_66'));

$PayPal = new PayPal();
$packagesList = $PayPal->getPackagesList();

if(is_array($packagesList)) {
	
	echo '<table class="table table-striped">';
	echo '<thead>';
		echo '<tr>';
			echo '<th>'.lang('shop_credits_txt_1').'</th>';
			echo '<th>'.lang('shop_credits_txt_2').'</th>';
			echo '<th></th>';
		echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach($packagesList as $package) {
		$packageTitle = lang($package['title']) != 'ERROR' ? lang($package['title']) : $package['title'];
		$packageCost = number_format($package['cost'], 2);
		
		if($cfg['sandbox']) {
			echo '<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">';
		} else {
			echo '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">';
		}
			echo '<input type="hidden" name="cmd" value="_xclick">';
			echo '<input type="hidden" name="business" value="'.$cfg['seller_email'].'">';
			echo '<input type="hidden" name="item_name" value="'.$packageTitle.'">';
			echo '<input type="hidden" name="currency_code" value="'.$cfg['currency'].'">';
			echo '<input type="hidden" name="amount" value="'.$packageCost.'">';
			echo '<input type="hidden" name="notify_url" value="'.__BASE_URL__.'api/paypal.php">';
			echo '<input type="hidden" name="return" value="'.Handler::websiteLink().'">';
			echo '<input type="hidden" name="cancel_return" value="'.Handler::websiteLink().'">';
			echo '<input type="hidden" name="no_shipping" value="1">';
			echo '<input type="hidden" name="shipping" value="0.00">';
			echo '<input type="hidden" name="no_note" value="1">';
			echo '<input type="hidden" name="tax" value="0.00">';
			echo '<input type="hidden" name="custom" value="'.$package['id'].','.$_SESSION['userid'].'">';
			
			echo '<tr>';
				echo '<td>'.$packageTitle.'</td>';
				echo '<td>$'.$packageCost.' '.$cfg['currency'].'</td>';
				echo '<td class="text-right"><input type="image" name="submit" src="'.$cfg['button_image_url'].'" alt="Buy now with PayPal" style="cursor:pointer;"></td>';
			echo '</tr>';
		echo '</form>';
	}
	echo '</tbody>';
	echo '</table>';
	
} else {
	message('warning', lang('error_250'));
}