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
 *
 * Class can be switched to use exceptions by calling
 *
 * 	PEAR::setErrorHandling(PEAR_ERROR_EXCEPTION);
 *
 * In which case constructor and setters will throw exceptions for connection, login or other errors.
 *
 * retriveRules and getters will not throw an exception, if there's no script currently.
 *
 * Most methods incl. constructor accept a script-name, but by default current active script is used
 * and if theres no script emailadmin_sieve::DEFAULT_SCRIPT_NAME.
 */
class emailadmin_sieve extends Net_Sieve
{
	/**
	 * reference to emailadmin_imap object
	 *
	 * @var emailadmin_imap
	 */
	var $icServer;

	/**
	* @var string name of active script queried from Sieve server
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
	var $_timeout = 10;

	/**
	 * Switch on some error_log debug messages
	 *
	 * @var boolean
	 */
	var $debug = false;

	/**
	 * Default script name used if no active script found on server
	 */
	const DEFAULT_SCRIPT_NAME = 'mail';

	/**
	 * Constructor
	 *
	 * @param emailadmin_imap $_icServer
	 * @param string $_euser effictive user, if given the Cyrus admin account is used to login on behalf of $euser
	 * @param string $_scriptName
	 */
	function __construct(emailadmin_imap $_icServer=null, $_euser='', $_scriptName=null)
	{
		parent::Net_Sieve();

		if ($_scriptName) $this->scriptName = $_scriptName;

		// TODO: since we seem to have major problems authenticating via DIGEST-MD5 and CRAM-MD5 in SIEVE, we skip MD5-METHODS for now
		if (!is_null($_icServer))
		{
			$_icServer->supportedAuthMethods = array('PLAIN' , 'LOGIN');
			$_icServer->supportedSASLAuthMethods=array();
		}
		else
		{
			$this->supportedAuthMethods = array('PLAIN' , 'LOGIN');
			$this->supportedSASLAuthMethods=array();
		}

		$this->displayCharset	= translation::charset();

		if (!is_null($_icServer) && $this->_connect($_icServer, $_euser) === 'die') {
			die('Sieve not activated');
		}
	}

	/**
	 * Open connection to the sieve server
	 *
	 * @param emailadmin_imap $_icServer
	 * @param string $euser effictive user, if given the Cyrus admin account is used to login on behalf of $euser
	 * @return mixed 'die' = sieve not enabled, false=connect or login failure, true=success
	 */
	function _connect(emailadmin_imap $_icServer, $euser='')
	{
		static $isConError = null;
		static $sieveAuthMethods = null;
		$_icServerID = $_icServer->acc_id;
		if (is_null($isConError))
		{
			$isConError =  egw_cache::getCache(egw_cache::INSTANCE, 'email', 'icServerSIEVE_connectionError' . trim($GLOBALS['egw_info']['user']['account_id']));
		}
		if ( isset($isConError[$_icServerID]) )
		{
			$this->error = new PEAR_Error($isConError[$_icServerID]);
			return false;
		}

		if ($this->debug)
		{
			error_log(__METHOD__ . array2string($euser));
		}
		if($_icServer->acc_sieve_enabled)
		{
			if ($_icServer->acc_sieve_host)
			{
				$sieveHost = $_icServer->acc_sieve_host;
			}
			else
			{
				$sieveHost = $_icServer->acc_imap_host;
			}
			//error_log(__METHOD__.__LINE__.'->'.$sieveHost);
			$sievePort		= $_icServer->acc_sieve_port;

			$useTLS = false;

			switch($_icServer->acc_sieve_ssl & ~emailadmin_account::SSL_VERIFY)
			{
				case emailadmin_account::SSL_SSL:
					$sieveHost = 'ssl://'.$sieveHost;
					break;
				case emailadmin_account::SSL_TLS:
					$sieveHost = 'tls://'.$sieveHost;
					break;
				case emailadmin_account::SSL_STARTTLS:
					$useTLS = true;
			}
			// disable certificate validation, if not explicitly enabled (not possible in current UI, as not supported by Horde_Imap_Client)
			$options = array(
				'ssl' => array(
					'verify_peer' => (bool)($_icServer->acc_sieve_ssl & emailadmin_account::SSL_VERIFY),
					'verify_peer_name' => (bool)($_icServer->acc_sieve_ssl & emailadmin_account::SSL_VERIFY),
				),
			);

			if ($euser)
			{
				$username = $_icServer->acc_imap_admin_username;
				$password = $_icServer->acc_imap_admin_password;
			}
			else
			{
				$username = $_icServer->acc_imap_username;
				$password = $_icServer->acc_imap_password;
			}
			$this->icServer = $_icServer;
		}
		else
		{
			egw_cache::setCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$isConError,$expiration=60*15);
			return 'die';
		}
		$this->_timeout = 10; // socket::connect sets the/this timeout on connection
		$timeout = emailadmin_imap::getTimeOut('SIEVE');
		if ($timeout > $this->_timeout)
		{
			$this->_timeout = $timeout;
		}

		if(self::isError($this->error = $this->connect($sieveHost , $sievePort, $options, $useTLS) ) )
		{
			if ($this->debug)
			{
				error_log(__METHOD__ . ": error in connect($sieveHost,$sievePort, " . array2string($options) . ", $useTLS): " . $this->error->getMessage());
			}
			$isConError[$_icServerID] = $this->error->getMessage();
			egw_cache::setCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$isConError,$expiration=60*15);
			return false;
		}
		// we cache the supported AuthMethods during session, to be able to speed up login.
		if (is_null($sieveAuthMethods))
		{
			$sieveAuthMethods = & egw_cache::getSession('email', 'sieve_supportedAuthMethods');
		}
		if (isset($sieveAuthMethods[$_icServerID]))
		{
			$this->supportedAuthMethods = $sieveAuthMethods[$_icServerID];
		}

		if(self::isError($this->error = $this->login($username, $password, 'LOGIN', $euser) ) )
		{
			if ($this->debug)
			{
				error_log(__METHOD__ . ": error in login($username,$password,null,$euser): " . $this->error->getMessage());
			}
			$isConError[$_icServerID] = $this->error->getMessage();
			egw_cache::setCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$isConError,$expiration=60*15);
			return false;
		}

		// query active script from Sieve server
		if (empty($this->scriptName))
		{
			try {
				$this->scriptName = $this->getActive();
			}
			catch(Exception $e) {
				unset($e);	// ignore NOTEXISTS exception
			}
			if (empty($this->scriptName))
			{
				$this->scriptName = self::DEFAULT_SCRIPT_NAME;
			}
		}

		//error_log(__METHOD__.__LINE__.array2string($this->_capability));
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
        if ($this->debug)
		{
			error_log(__METHOD__ . __LINE__ . "$host, $port, " . array2string($options) . ", $useTLS");
		}
		$this->_data['host'] = $host;
        $this->_data['port'] = $port;
        $this->_useTLS       = $useTLS;
        if (is_array($options)) {
            $this->_options = array_merge((array)$this->_options, $options);
        }

        if (NET_SIEVE_STATE_DISCONNECTED != $this->_state) {
            return PEAR::raiseError('Not currently in DISCONNECTED state', 1);
        }

		if (self::isError($res = $this->_sock->connect($host, $port, false, ($this->_timeout?$this->_timeout:10), $options))) {
            return $res;
        }

        if ($this->_bypassAuth) {
            $this->_state = NET_SIEVE_STATE_TRANSACTION;
        } else {
            $this->_state = NET_SIEVE_STATE_AUTHORISATION;
            if (self::isError($res = $this->_doCmd())) {
                return $res;
            }
        }

        // Explicitly ask for the capabilities in case the connection is
        // picked up from an existing connection.
        if (self::isError($res = $this->_cmdCapability())) {
            return PEAR::raiseError(
                'Failed to connect, server said: ' . $res->getMessage(), 2
            );
        }

        // Check if we can enable TLS via STARTTLS.
        if ($useTLS && !empty($this->_capability['starttls'])
            && function_exists('stream_socket_enable_crypto')
        ) {
            if (self::isError($res = $this->_startTLS())) {
                return $res;
            }
        }

        return true;
    }

	/**
	* Own _getBestAuthMethod as Net/Sieve.php assumes SASLMethods to be working
	* Returns the name of the best authentication method that the server
	* has advertised.
	*
	* @param string if !=null,check this one first if reported as serverMethod.
	*                  if so, return as bestauthmethod
	* @return mixed    Returns a string containing the name of the best
	*                  supported authentication method or a PEAR_Error object
	*                  if a failure condition is encountered.
	*/
	function _getBestAuthMethod($userMethod = null)
	{
		//error_log(__METHOD__.__LINE__.'->'.$userMethod.'<->'.array2string($this->_capability['sasl']));
		if( isset($this->_capability['sasl']) ){
			$serverMethods=$this->_capability['sasl'];
		}else{
			// if the server don't send an sasl capability fallback to login auth
			//return 'LOGIN';
			return PEAR::raiseError("This server don't support any Auth methods SASL problem?");
		}
		$methods = array();
		if($userMethod != null ){
			$methods[] = $userMethod;
			foreach ( $this->supportedAuthMethods as $method ) {
				$methods[]=$method;
			}
		}else{
			$methods = $this->supportedAuthMethods;
		}
		if( ($methods != null) && ($serverMethods != null)){
			foreach ( $methods as $method ) {
				if ( in_array( $method , $serverMethods ) ) {
					return $method;
				}
			}
			$serverMethods=implode(',' , $serverMethods );
			$myMethods=implode(',' ,$this->supportedAuthMethods);
			return PEAR::raiseError("$method NOT supported authentication method!. This server " .
				"supports these methods= $serverMethods, but I support $myMethods");
		}else{
			return PEAR::raiseError("This server don't support any Auth methods");
		}
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
        if ( self::isError( $method = $this->_getBestAuthMethod($userMethod) ) ) {
            return $method;
        }
        //error_log(__METHOD__.__LINE__.' using AuthMethod: '.$method);
        switch ($method) {
            case 'DIGEST-MD5':
                $result = $this->_authDigestMD5( $uid , $pwd , $euser );
                if (!self::isError($result))
				{
					break;
				}
				$res = $this->_doCmd();
                unset($this->_error);
                $this->supportedAuthMethods = array_diff($this->supportedAuthMethods,array($method,'CRAM-MD5'));
                return $this->_cmdAuthenticate($uid , $pwd, null, $euser);
            case 'CRAM-MD5':
                $result = $this->_authCRAMMD5( $uid , $pwd, $euser);
                if (!self::isError($result))
				{
					break;
				}
				$res = $this->_doCmd();
                unset($this->_error);
                $this->supportedAuthMethods = array_diff($this->supportedAuthMethods,array($method,'DIGEST-MD5'));
                return $this->_cmdAuthenticate($uid , $pwd, null, $euser);
            case 'LOGIN':
                $result = $this->_authLOGIN( $uid , $pwd , $euser );
                if (!self::isError($result))
				{
					break;
				}
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
        if (self::isError($result))
		{
			return $result;
		}
		if (self::isError($res = $this->_doCmd())) {
            return $res;
        }

        // Query the server capabilities again now that we are authenticated.
        if (self::isError($res = $this->_cmdCapability())) {
            return PEAR::raiseError(
                'Failed to connect, server said: ' . $res->getMessage(), 2
            );
        }

        return $result;
    }

    /**
     * Send a command and retrieves a response from the server.
     *
     * @param string $cmd   The command to send.
     * @param boolean $auth Whether this is an authentication command.
     *
     * @return string|PEAR_Error  Reponse string if an OK response, PEAR_Error
     *                            if a NO response.
     */
    function _doCmd($cmd = '', $auth = false)
    {
        $referralCount = 0;
        while ($referralCount < $this->_maxReferralCount) {
            if (strlen($cmd)) {
                $error = $this->_sendCmd($cmd);
                if (is_a($error, 'PEAR_Error')) {
                    return $error;
                }
            }

            $response = '';
            while (true) {
                $line = $this->_recvLn();

                if (is_a($line, 'PEAR_Error')) {
                    return $line;
                }

                if (preg_match('/^(OK|NO)/i', $line, $tag)) {
                    // Check for string literal message.
                    // ServerResponse may send {nm} (nm representing a number)
                    // dbmail (in some versions) sends: {nm+} thus breaking RFC5804 rules (Section 4 Formal Syntax)
                    // {nm+} may only be used in communicating from client TO server; (not Server to Client)
                    // we work around this bug (allowing +) using a patch introduced with roundcube 2 years ago.
                    //if (preg_match('/{([0-9]+)}$/', $line, $matches)) { //original
                    if (preg_match('/{([0-9]+)\+?}$/', $line, $matches)) { //patched to cope with dbmail
                        $line = substr($line, 0, -(strlen($matches[1]) + 2))
                            . str_replace(
                                "\r\n", ' ', $this->_recvBytes($matches[1] + 2)
                            );
                    }

                    if ('OK' == $this->_toUpper($tag[1])) {
                        $response .= $line;
                        return rtrim($response);
                    }

                    return $this->_pear->raiseError(trim($response . substr($line, 2)), 3);
                }

                if (preg_match('/^BYE/i', $line)) {
                    $error = $this->disconnect(false);
                    if (is_a($error, 'PEAR_Error')) {
                        return $this->_pear->raiseError(
                            'Cannot handle BYE, the error was: '
                            . $error->getMessage(),
                            4
                        );
                    }
                    // Check for referral, then follow it.  Otherwise, carp an
                    // error.
                    if (preg_match('/^bye \(referral "(sieve:\/\/)?([^"]+)/i', $line, $matches)) {
                        // Replace the old host with the referral host
                        // preserving any protocol prefix.
                        $this->_data['host'] = preg_replace(
                            '/\w+(?!(\w|\:\/\/)).*/', $matches[2],
                            $this->_data['host']
                        );
                        $error = $this->_handleConnectAndLogin();
                        if (is_a($error, 'PEAR_Error')) {
                            return $this->_pear->raiseError(
                                'Cannot follow referral to '
                                . $this->_data['host'] . ', the error was: '
                                . $error->getMessage(),
                                5
                            );
                        }
                        break;
                    }
                    return $this->_pear->raiseError(trim($response . $line), 6);
                }

                // ServerResponse may send {nm} (nm representing a number)
                // dbmail (in some versions) sends: {nm+} thus breaking RFC5804 rules (Section 4 Formal Syntax)
                // {nm+} may only be used in communicating from client TO server; (not Server to Client)
                // we work around this bug (allowing +) using a patch introduced with roundcube 2 years ago.
                // although roundcube suggested only the change in line
                //if (preg_match('/^{([0-9]+)}/', $line, $matches)) { //original
                if (preg_match('/^{([0-9]+)\+?}/', $line, $matches)) { //patched to cope with dbmail
                    // Matches literal string responses.
                    $line = $this->_recvBytes($matches[1] + 2);
                    if (!$auth) {
                        // Receive the pending OK only if we aren't
                        // authenticating since string responses during
                        // authentication don't need an OK.
                        $this->_recvLn();
                    }
                    return $line;
                }

                if ($auth) {
                    // String responses during authentication don't need an
                    // OK.
                    $response .= $line;
                    return rtrim($response);
                }

                $response .= $line . "\r\n";
                $referralCount++;
            }
        }

        return $this->_pear->raiseError('Max referral count (' . $referralCount . ') reached. Cyrus murder loop error?', 7);
    }

	function getRules()
	{
		if (!isset($this->rules)) $this->retrieveRules();

		return $this->rules;
	}

	function getVacation()
	{
		if (!isset($this->rules)) $this->retrieveRules();

		return $this->vacation;
	}

	function getEmailNotification()
	{
		if (!isset($this->rules)) $this->retrieveRules();

		return $this->emailNotification;
	}

	/**
	 * Set email notifications
	 *
	 * @param array $_rules
	 * @param string $_scriptName
	 * @param boolean $utf7imap_fileinto =false true: encode foldernames with utf7imap, default utf8
	 */
	function setRules(array $_rules, $_scriptName=null, $utf7imap_fileinto=false)
	{
		$script = $this->retrieveRules($_scriptName);
		$script->debug = $this->debug;
		$script->rules = $_rules;
		$ret = $script->updateScript($this, $utf7imap_fileinto);
		$this->error = $script->errstr;
		return $ret;
	}

	/**
	 * Set email notifications
	 *
	 * @param array $_vacation
	 * @param string $_scriptName
	 */
	function setVacation(array $_vacation, $_scriptName=null)
	{
		if ($this->debug)
		{
			error_log(__METHOD__ . "($_scriptName," . print_r($_vacation, true) . ')');
		}
		$script = $this->retrieveRules($_scriptName);
		$script->debug = $this->debug;
		$script->vacation = $_vacation;
		$ret = $script->updateScript($this);
		$this->error = $script->errstr;
		return $ret;
	}

	/**
	 * Set email notifications
	 *
	 * @param array $_emailNotification
	 * @param string $_scriptName
	 * @return emailadmin_script
	 */
	function setEmailNotification(array $_emailNotification, $_scriptName=null)
	{
		if ($_emailNotification['externalEmail'] == '' || !preg_match("/\@/",$_emailNotification['externalEmail'])) {
    		$_emailNotification['status'] = 'off';
    		$_emailNotification['externalEmail'] = '';
    	}

    	$script = $this->retrieveRules($_scriptName);
   		$script->emailNotification = $_emailNotification;
		$ret = $script->updateScript($this);
		$this->error = $script->errstr;
		return $ret;
	}

	/**
	 * Retrive rules, vacation, notifications and return emailadmin_script object to update them
	 *
	 * @param string $_scriptName
	 * @return emailadmin_script
	 */
	function retrieveRules($_scriptName=null)
	{
		if (!$_scriptName)
		{
			$_scriptName = $this->scriptName;
		}
		$script = new emailadmin_script($_scriptName);

		try {
			$script->retrieveRules($this);
		}
		catch (Exception $e) {
			unset($e);	// ignore not found script exception
		}
		$this->rules =& $script->rules;
		$this->vacation =& $script->vacation;
		$this->emailNotification =& $script->emailNotification; // Added email notifications

		return $script;
	}

	/**
	 * Tell whether a value is a PEAR error.
	 *
	 * Implemented here to get arround: PHP Deprecated:  Non-static method self::isError() should not be called statically
	 *
	 * @param   mixed $data   the value to test
	 * @param   int   $code   if $data is an error object, return true
	 *                        only if $code is a string and
	 *                        $obj->getMessage() == $code or
	 *                        $code is an integer and $obj->getCode() == $code
	 * @access  public
	 * @return  bool    true if parameter is an error
	 */
	protected static function isError($data, $code = null)
	{
		static $pear=null;
		if (!isset($pear)) $pear = new PEAR();

		return $pear->isError($data, $code);
	}
}
