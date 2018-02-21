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
$cfg = loadModuleConfig('account.email');
if(!is_array($cfg)) throw new Exception(lang('error_66',true));

// form submit
if(check($_POST['webengineEmail_submit'])) {
	try {
		$AccountEmail = new AccountEmail();
		$AccountEmail->setUserid($_SESSION['userid']);
		$AccountEmail->setUsername($_SESSION['username']);
		$AccountEmail->setNewEmail($_POST['webengineEmail_newemail']);
		$AccountEmail->changeEmail();
		
		if($cfg['require_verification']) {
			// verification required
			message('success', lang('success_19',true));
		} else {
			// email updated
			message('success', lang('success_20',true));
		}
	} catch (Exception $ex) {
		message('error', $ex->getMessage());
	}
}

// form
echo '<div class="col-xs-8 col-xs-offset-2" style="margin-top:30px;">';
	echo '<form class="form-horizontal" action="" method="post">';
		echo '<div class="form-group">';
			echo '<label for="webengineEmail" class="col-sm-4 control-label">'.lang('changemail_txt_1',true).'</label>';
			echo '<div class="col-sm-8">';
				echo '<input type="text" class="form-control" id="webengineEmail" name="webengineEmail_newemail">';
			echo '</div>';
		echo '</div>';
		echo '<div class="form-group">';
			echo '<div class="col-sm-offset-4 col-sm-8">';
				echo '<button type="submit" name="webengineEmail_submit" value="submit" class="btn btn-primary">'.lang('changemail_txt_2',true).'</button>';
			echo '</div>';
		echo '</div>';
	echo '</form>';
echo '</div>';