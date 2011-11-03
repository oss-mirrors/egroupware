<?php
/**
 * EGroupware EMailAdmin: Support for Sieve scripts
 *
 * @link http://www.egroupware.org
 * @package emailadmin
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
 * @author Lars Kneschke
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

include_once('Net/Sieve.php');

/**
 * Support for Sieve scripts
 */
class emailadmin_sieve extends Net_Sieve 
{
	/**
	* @var object $icServer object containing the information about the imapserver
	*/
	var $icServer;

	/**
	* @var object $icServer object containing the information about the imapserver
	*/
	var $scriptName;

	/**
	* @var object $error the last PEAR error object
	*/
	var $error;
	
	/**
	 * Switch on some error_log debug messages
	 *
	 * @var boolean
	 */
	var $debug = false;

	/**
	 * Constructor
	 * 
	 * @param defaultimap $_icServer
	 */
	function __construct(defaultimap $_icServer=null)
	{
		parent::Net_Sieve();
		
		$this->scriptName = !empty($GLOBALS['egw_info']['user']['preferences']['felamimail']['sieveScriptName']) ? $GLOBALS['egw_info']['user']['preferences']['felamimail']['sieveScriptName'] : 'felamimail';

		$this->displayCharset	= $GLOBALS['egw']->translation->charset();
		
		if (!is_null($_icServer) && $this->_connect($_icServer) === 'die') {
			die('Sieve not activated');
		}
	}

	/**
	 * Open connection to the sieve server
	 *
	 * @param defaultimap $_icServer
	 * @param string $euser='' effictive user, if given the Cyrus admin account is used to login on behalf of $euser
	 * @return mixed 'die' = sieve not enabled, false=connect or login failure, true=success
	 */
	function _connect($_icServer,$euser='')
	{
		static $isConError;
		if (is_null($isConError)) $isConError =& egw_cache::getCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$callback=null,$callback_params=array(),$expiration=60*15);
		if ( isset($isConError[$_icServerID]) ) 
		{
			error_log(__METHOD__.__LINE__.' failed for Reason:'.$isConError[$_icServerID]);
			//$this->errorMessage = $isConError[$_icServerID];
			return false;			
		}

		if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.array2string($euser));
		if(($_icServer instanceof defaultimap) && $_icServer->enableSieve) {
			if (!empty($_icServer->sieveHost))
			{
				$sieveHost = $_icServer->sieveHost;
			}
			else
			{
				$sieveHost = $_icServer->host;
			}
			//error_log(__METHOD__.__LINE__.'->'.$sieveHost);
			$sievePort		= $_icServer->sievePort;
			$useTLS			= $_icServer->encryption > 0;
			if ($euser) {
				$username		= $_icServer->adminUsername;
				$password		= $_icServer->adminPassword;
			} else {
				$username		= $_icServer->loginName;
				$password		= $_icServer->password;
			}
			$this->icServer = $_icServer;
		} else {
			egw_cache::setCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$isConError,$expiration=60*15);
			return 'die';
		}

		if(PEAR::isError($this->error = $this->connect($sieveHost , $sievePort, null, $useTLS) ) ){
			if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.": error in connect($sieveHost,$sievePort): ".$this->error->getMessage());
			$isConError[$_icServerID] = "SIEVE: error in connect($sieveHost,$sievePort): ".$this->error->getMessage();
			egw_cache::setCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$isConError,$expiration=60*15);
			return false;
		}
		if(PEAR::isError($this->error = $this->login($username, $password, null, $euser) ) ){
			if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.array2string($this->icServer));
			if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.": error in login($username,$password,null,$euser): ".$this->error->getMessage());
			$isConError[$_icServerID] = "SIEVE: error in login($username,$password,null,$euser): ".$this->error->getMessage();
			egw_cache::setCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$isConError,$expiration=60*15);
			return false;
		}
		return true;
	}
	
	function getRules($_scriptName) {
		return $this->rules;
	}

	function getVacation($_scriptName) {
		return $this->vacation;
	}

	function getEmailNotification($_scriptName) {
		return $this->emailNotification;
	}

	function setRules($_scriptName, $_rules) 
	{
		if (!$_scriptName) $_scriptName = $this->scriptName;
		$script = new emailadmin_script($_scriptName);
		$script->debug = $this->debug;

		if($script->retrieveRules($this)) {
			$script->rules = $_rules;
			$script->updateScript($this);
			
			return true;
		} 

		return false;
	}

	function setVacation($_scriptName, $_vacation) 
	{
		if (!$_scriptName) $_scriptName = $this->scriptName;
		if ($this->debug) error_log(__CLASS__.'::'.__METHOD__."($_scriptName,".print_r($_vacation,true).')');
		$script = new emailadmin_script($_scriptName);
		$script->debug = $this->debug;

		if($script->retrieveRules($this)) {
			$script->vacation = $_vacation;
			$script->updateScript($this);
			/*	
			// setting up an async job to enable/disable the vacation message
			$async = new asyncservice();
			$user = $GLOBALS['egw_info']['user']['account_id'];
			$async->delete($async_id ="felamimail-vacation-$user");
			$end_date = $_vacation['end_date'] + 24*3600;	// end-date is inclusive, so we have to add 24h
			if ($_vacation['status'] == 'by_date' && time() < $end_date)
			{
				$time = time() < $_vacation['start_date'] ? $_vacation['start_date'] : $end_date;
				$async->set_timer($time,$async_id,'felamimail.bosieve.async_vacation',$_vacation+array('scriptName'=>$_scriptName),$user);
			}
			*/
			return true;
		}
		if ($this->debug) error_log(__CLASS__.'::'.__METHOD__."($_scriptName,".print_r($_vacation,true).') could not retrieve rules!');

		return false;
	}
	
	/**
	 * Set vacation with admin right for an other user, used to async enable/disable vacation
	 * 
	 * @param string $_euser
	 * @param string $_scriptName
	 * @param string $_vaction
	 * @return boolean true on success false otherwise
	 */
	function setVacationUser($_euser, $_scriptName, $_vacation)
	{
		if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.' User:'.array2string($_euser).' Scriptname:'.array2string($_scriptName).' VacationMessage:'.array2string($_vacation));
		if (!$_scriptName) $_scriptName = $this->scriptName;
		if ($this->_connect($this->icServer,$_euser) === true) {			
			$this->setVacation($_scriptName,$_vacation);
			// we need to logout, so further vacation's get processed
			$error = $this->_cmdLogout();
			if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.' logout '.(PEAR::isError($error) ? 'failed: '.$ret->getMessage() : 'successful'));
			return true;
		}
		return false;
	}
	
	function setEmailNotification($_scriptName, $_emailNotification) {
		if (!$_scriptName) $_scriptName = $this->scriptName;
    	if ($_emailNotification['externalEmail'] == '' || !preg_match("/\@/",$_emailNotification['externalEmail'])) {
    		$_emailNotification['status'] = 'off';
    		$_emailNotification['externalEmail'] = '';
    	}

    	$script = new emailadmin_script($_scriptName);
    	if ($script->retrieveRules($this)) {
    		$script->emailNotification = $_emailNotification;
    		return $script->updateScript($this);
    	}
    	return false;
	}

	function retrieveRules($_scriptName) {
		if (!$_scriptName) $_scriptName = $this->scriptName;
		$script = new emailadmin_script($_scriptName);
		
		if($script->retrieveRules($this)) {
			$this->rules = $script->rules;
			$this->vacation = $script->vacation;
			$this->emailNotification = $script->emailNotification; // Added email notifications	
			return true;
		} 
		
		return false;
	}
}
