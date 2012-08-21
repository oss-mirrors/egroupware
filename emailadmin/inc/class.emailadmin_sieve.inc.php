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
	* @var $rules containing the rules
	*/
	var $rules;

	/**
	* @var $vacation containing the vacation
	*/
	var $vacation;

	/**
	* @var $emailNotification containing the emailNotification
	*/
	var $emailNotification;

	/**
	* @var object $error the last PEAR error object
	*/
	var $error;

	/**
	 * The timeout for the connection to the SIEVE server.
	 * @var int
	 */
	var $_timeout = null;

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

		// TODO: since we seem to have major problems authenticating via DIGEST-MD5 and CRAM-MD5 in SIEVE, we skip MD5-METHODS for now
		if (!is_null($_icServer))
		{
			$_icServer->supportedAuthMethods = array('PLAIN' , 'LOGIN');
		}
		else
		{
			$this->supportedAuthMethods = array('PLAIN' , 'LOGIN');
		}

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
		static $sieveAuthMethods;
		$_icServerID = $_icServer->ImapServerId;
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
		$this->_timeout = 10; // socket::connect sets the/this timeout on connection
		$timeout = felamimail_bo::getTimeOut('SIEVE');
		if ($timeout>$this->_timeout) $this->_timeout = $timeout;

		if(PEAR::isError($this->error = $this->connect($sieveHost , $sievePort, null, $useTLS) ) ){
			if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.": error in connect($sieveHost,$sievePort): ".$this->error->getMessage());
			$isConError[$_icServerID] = "SIEVE: error in connect($sieveHost,$sievePort): ".$this->error->getMessage();
			egw_cache::setCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$isConError,$expiration=60*15);
			return false;
		}
		// we cache the supported AuthMethods during session, to be able to speed up login.
		if (is_null($sieveAuthMethods)) $sieveAuthMethods =& egw_cache::getSession('email','sieve_supportedAuthMethods');
		if (isset($sieveAuthMethods[$_icServerID])) $this->supportedAuthMethods = $sieveAuthMethods[$_icServerID];

		if(PEAR::isError($this->error = $this->login($username, $password, null, $euser) ) ){
			if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.array2string($this->icServer));
			if ($this->debug) error_log(__CLASS__.'::'.__METHOD__.": error in login($username,$password,null,$euser): ".$this->error->getMessage());
			$isConError[$_icServerID] = "SIEVE: error in login($username,$password,null,$euser): ".$this->error->getMessage();
			egw_cache::setCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$isConError,$expiration=60*15);
			return false;
		}
		return true;
	}

    /**
     * Handles connecting to the server and checks the response validity.
     * overwritten function from Net_Sieve to respect timeout
     *
     * @param string  $host    Hostname of server.
     * @param string  $port    Port of server.
     * @param array   $options List of options to pass to
     *                         stream_context_create().
     * @param boolean $useTLS  Use TLS if available.
     *
     * @return boolean  True on success, PEAR_Error otherwise.
     */
    function connect($host, $port, $options = null, $useTLS = true)
    {
        //error_log(__METHOD__.__LINE__."$host, $port, ".array2string($options).", $useTLS");
        $this->_data['host'] = $host;
        $this->_data['port'] = $port;
        $this->_useTLS       = $useTLS;
        if (is_array($options)) {
            $this->_options = array_merge($this->_options, $options);
        }

        if (NET_SIEVE_STATE_DISCONNECTED != $this->_state) {
            return PEAR::raiseError('Not currently in DISCONNECTED state', 1);
        }

        if (PEAR::isError($res = $this->_sock->connect($host, $port, false, ($this->_timeout?$this->_timeout:10), $options))) {
            return $res;
        }

        if ($this->_bypassAuth) {
            $this->_state = NET_SIEVE_STATE_TRANSACTION;
        } else {
            $this->_state = NET_SIEVE_STATE_AUTHORISATION;
            if (PEAR::isError($res = $this->_doCmd())) {
                return $res;
            }
        }

        // Explicitly ask for the capabilities in case the connection is
        // picked up from an existing connection.
        if (PEAR::isError($res = $this->_cmdCapability())) {
            return PEAR::raiseError(
                'Failed to connect, server said: ' . $res->getMessage(), 2
            );
        }

        // Check if we can enable TLS via STARTTLS.
        if ($useTLS && !empty($this->_capability['starttls'])
            && function_exists('stream_socket_enable_crypto')
        ) {
            if (PEAR::isError($res = $this->_startTLS())) {
                return $res;
            }
        }

        return true;
    }

    /**
     * Handles the authentication using any known method
     * overwritten function from Net_Sieve to support fallback
     *
     * @param string $uid The userid to authenticate as.
     * @param string $pwd The password to authenticate with.
     * @param string $userMethod The method to use ( if $userMethod == '' then the class chooses the best method (the stronger is the best ) )
     * @param string $euser The effective uid to authenticate as.
     *
     * @return mixed  string or PEAR_Error
     *
     * @access private
     * @since  1.0
     */
    function _cmdAuthenticate($uid , $pwd , $userMethod = null , $euser = '' )
    {
        if ( PEAR::isError( $method = $this->_getBestAuthMethod($userMethod) ) ) {
            return $method;
        }
        //error_log(__METHOD__.__LINE__.' using AuthMethod: '.$method);
        switch ($method) {
            case 'DIGEST-MD5':
                $result = $this->_authDigest_MD5( $uid , $pwd , $euser );
                if ( !PEAR::isError($result)) break;
                $res = $this->_doCmd();
                unset($this->_error);
                $this->supportedAuthMethods = array_diff($this->supportedAuthMethods,array($method,'CRAM-MD5'));
                return $this->_cmdAuthenticate($uid , $pwd, null, $euser);
            case 'CRAM-MD5':
                $result = $this->_authCRAM_MD5( $uid , $pwd, $euser);
                if ( !PEAR::isError($result)) break;
                $res = $this->_doCmd();
                unset($this->_error);
                $this->supportedAuthMethods = array_diff($this->supportedAuthMethods,array($method,'DIGEST-MD5'));
                return $this->_cmdAuthenticate($uid , $pwd, null, $euser);
            case 'LOGIN':
                $result = $this->_authLOGIN( $uid , $pwd , $euser );
                if ( !PEAR::isError($result)) break;
                $res = $this->_doCmd();
                unset($this->_error);
                $this->supportedAuthMethods = array_diff($this->supportedAuthMethods,array($method));
                return $this->_cmdAuthenticate($uid , $pwd, null, $euser);
            case 'PLAIN':
                $result = $this->_authPLAIN( $uid , $pwd , $euser );
                break;
            default :
                $result = new PEAR_Error( "$method is not a supported authentication method" );
                break;
        }
        if (PEAR::isError($result)) return $result;
        if (PEAR::isError($res = $this->_doCmd())) {
            return $res;
        }

        // Query the server capabilities again now that we are authenticated.
        if (PEAR::isError($res = $this->_cmdCapability())) {
            return PEAR::raiseError(
                'Failed to connect, server said: ' . $res->getMessage(), 2
            );
        }

        return $result;
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

	function retrieveRules($_scriptName, $returnRules = false) {
		if (!$_scriptName) $_scriptName = $this->scriptName;
		$script = new emailadmin_script($_scriptName);

		if($script->retrieveRules($this)) {
			$this->rules = $script->rules;
			$this->vacation = $script->vacation;
			$this->emailNotification = $script->emailNotification; // Added email notifications
			if ($returnRules) return array('rules'=>$this->rules,'vacation'=>$this->vacation,'emailNotification'=>$this->emailNotification);
			return true;
		}

		return false;
	}
}
