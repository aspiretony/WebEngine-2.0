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

class AccountLogin extends Account {
	
	private $_configurationFile = 'login';
	private $_maxFailedLogins = 5;
	private $_failedLoginTimeout = 900;
	private $_ipAddress;
	private $_failedAttempts = 0;
	private $_unlockTime;
	private $_accountRecoveryModule = 'recovery/password';
	private $_maxFailedAttemtpsNotificationEnabled = true;
	
	function __construct() {
		parent::__construct();
		
		$cfg = loadModuleConfig($this->_configurationFile);
		if(!is_array($cfg)) throw new Exception(lang('error_66'));
		
		// set configs
		$this->_maxFailedLogins = check($cfg['max_login_attempts']) ? $cfg['max_login_attempts'] : 5;
		$this->_failedLoginTimeout = check($cfg['failed_login_timeout']) ? $cfg['failed_login_timeout'] : 900;
		$this->_maxFailedAttemtpsNotificationEnabled = check($cfg['max_failed_attempts_notification']) ? $cfg['max_failed_attempts_notification'] : true;
		
		// check ip address
		if(!Validator::Ip(Handler::userIP())) throw new Exception(lang('error_65'));
		$this->_ipAddress = Handler::userIP();
		
		// database object
		$this->we = Handler::loadDB('WebEngine');
	}
	
	/**
	 * login
	 * validates login data and creates a new session
	 */
	public function login() {
		if(!check($this->_username)) throw new Exception(lang('error_4'));
		if(!check($this->_password)) throw new Exception(lang('error_4'));
		
		// check failed logins
		$this->_loadFailedLoginData();
		if($this->_failedAttempts >= $this->_maxFailedLogins) {
			if(strtotime($this->_unlockTime) > time()) throw new Exception(lang('error_3'));
			$this->_removeFailedLogins();
		}
		
		// check credentials
		if(!$this->_validateAccount()) {
			$this->_addFailedLogin();
			throw new Exception(lang('login_txt_5', array($this->_failedAttempts+1, $this->_maxFailedLogins, $this->_maxFailedLogins)));
		}
		
		// remove failed logins
		$this->_removeFailedLogins();
		
		// account data
		$this->_loadAccountData();
		
		// initiate session
		sessionControl::newSession($this->_accountData[_CLMN_MEMBID_], $this->_accountData[_CLMN_USERNM_]);
		
	}
	
	/**
	 * facebookLogin
	 * validates login data through facebook
	 */
	public function facebookLogin() {
		if(!check($this->_facebookId)) throw new Exception(lang('error_4'));
		
		// TODO
		// check for verified email
		// check if email has changed
		
		// check facebook id
		$AccountPreferences = new AccountPreferences();
		$AccountPreferences->setFacebookId($this->_facebookId);
		$preferences = $AccountPreferences->getAccountPreferencesFromFacebookId();
		if(!is_array($preferences)) throw new Exception(lang('error_77', array(Handler::websiteLink('login'))));
		
		// set username
		$this->setUsername($preferences['username']);
		
		// check if exists
		if(!$this->usernameExists()) throw new Exception(lang('error_12'));
		
		// account data
		$this->_loadAccountData();
		if(!is_array($this->_accountData)) throw new Exception(lang('error_12'));
		
		// remove failed logins
		$this->_removeFailedLogins();
		
		// initiate session
		sessionControl::newSession($this->_accountData[_CLMN_MEMBID_], $this->_accountData[_CLMN_USERNM_]);
		
	}
	
	/**
	 * googleLogin
	 * validates login data through google
	 */
	public function googleLogin() {
		if(!check($this->_googleId)) throw new Exception(lang('error_4'));
		
		// TODO
		// check for verified email
		// check if email has changed
		
		// check google id
		$AccountPreferences = new AccountPreferences();
		$AccountPreferences->setGoogleId($this->_googleId);
		$preferences = $AccountPreferences->getAccountPreferencesFromGoogleId();
		if(!is_array($preferences)) throw new Exception(lang('error_86', array(Handler::websiteLink('login'))));
		
		// set username
		$this->setUsername($preferences['username']);
		
		// check if exists
		if(!$this->usernameExists()) throw new Exception(lang('error_12'));
		
		// account data
		$this->_loadAccountData();
		if(!is_array($this->_accountData)) throw new Exception(lang('error_12'));
		
		// remove failed logins
		$this->_removeFailedLogins();
		
		// initiate session
		sessionControl::newSession($this->_accountData[_CLMN_MEMBID_], $this->_accountData[_CLMN_USERNM_]);
		
	}
	
	/**
	 * _loadFailedLoginData
	 * loads the failed login attempts data from the database
	 */
	private function _loadFailedLoginData() {
		if(!check($this->_ipAddress)) return;
		
		$result = $this->we->queryFetchSingle("SELECT * FROM "._WE_FAILEDLOGIN_." WHERE ip_address = ?", array($this->_ipAddress));
		if(!is_array($result)) return;
		
		$this->_failedAttempts = $result['attempts'];
		if(check($result['unlock_time'])) $this->_unlockTime = $result['unlock_time'];
	}
	
	/**
	 * _addFailedLogin
	 * adds a failed login attempt to an ip address
	 */
	private function _addFailedLogin() {
		if(!check($this->_ipAddress)) return;
		if($this->_failedAttempts >= 1) {
			if(($this->_failedAttempts+1) >= $this->_maxFailedLogins) {
				$timeout = date("Y-m-d H:i:s", time()+$this->_failedLoginTimeout);
				$this->we->query("UPDATE "._WE_FAILEDLOGIN_." SET username = ?, attempts = attempts + 1, last_update = CURRENT_TIMESTAMP, unlock_time = ? WHERE ip_address = ?", array($this->_username, $timeout, $this->_ipAddress));
				
				// send email notification to username
				if($this->_maxFailedAttemtpsNotificationEnabled) $this->_sendMaximumFailedAttemptsEmail();
			} else {
				$this->we->query("UPDATE "._WE_FAILEDLOGIN_." SET username = ?, attempts = attempts + 1, last_update = CURRENT_TIMESTAMP WHERE ip_address = ?", array($this->_username, $this->_ipAddress));
			}
		} else {
			$data = array(
				$this->_username,
				$this->_ipAddress,
				1
			);
			$this->we->query("INSERT INTO "._WE_FAILEDLOGIN_." (username, ip_address, attempts, last_update) VALUES (?, ?, ?, CURRENT_TIMESTAMP)", $data);
		}
	}
	
	/**
	 * _removeFailedLogins
	 * removes all failed logins from an ip address
	 */
	private function _removeFailedLogins() {
		if(!check($this->_ipAddress)) return;
		$this->we->query("DELETE FROM "._WE_FAILEDLOGIN_." WHERE ip_address = ?", array($this->_ipAddress));
	}
	
	/**
	 * _sendMaximumFailedAttemptsEmail
	 * sends a notification email to the user regarding the failed attempts
	 */
	private function _sendMaximumFailedAttemptsEmail() {
		if(!check($this->_username)) return;
		if(!check($this->_ipAddress)) return;
		
		$accountData = $this->getAccountData();
		if(!is_array($accountData)) return;
		
		try {
			$email = new Email();
			$email->setTemplate('FAILED_LOGIN_NOTIFICATION');
			$email->addVariable('{USERNAME}', $this->_username);
			$email->addVariable('{IP_ADDRESS}', $this->_ipAddress);
			$email->addVariable('{RECOVERY_LINK}', __BASE_URL__.$this->_accountRecoveryModule);
			$email->addAddress($accountData[_CLMN_EMAIL_]);
			$email->send();
			return true;
		} catch (Exception $ex) {
			# TODO logs system
			return;
		}
	}
	
}