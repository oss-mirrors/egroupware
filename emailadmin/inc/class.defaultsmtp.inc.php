<?php
/**
 * EGroupware EMailAdmin: generic base class for SMTP
 *
 * @link http://www.egroupware.org
 * @package emailadmin
 * @author Lars Kneschke <lkneschke@linux-at-work.de>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License Version 2+
 * @version $Id$
 */

/**
 * EMailAdmin generic base class for SMTP
 */
class defaultsmtp
{
	/**
	 * Capabilities of this class (pipe-separated): default, forward
	 */
	const CAPABILITIES = 'default';

	/**
	 * SmtpServerId
	 *
	 * @var int
	 */
	var $SmtpServerId;

	var $smtpAuth = false;

	var $editForwardingAddress = false;

	var $host;

	var $port;

	var $username;

	var $password;

	var $defaultDomain;

	/**
	 * Constructor
	 *
	 * @param string $defaultDomain=null
	 */
	function __construct($defaultDomain=null)
	{
		$this->defaultDomain = $defaultDomain ? $defaultDomain : $GLOBALS['egw_info']['server']['mail_suffix'];
	}

	/**
	 * Hook called on account creation
	 *
	 * @param array $_hookValues values for keys 'account_email', 'account_firstname', 'account_lastname', 'account_lid'
	 * @return boolean true on success, false on error writing to ldap
	 */
	function addAccount($_hookValues)
	{
		return true;
	}

	/**
	 * Hook called on account deletion
	 *
	 * @param array $_hookValues values for keys 'account_lid', 'account_id'
	 * @return boolean true on success, false on error writing to ldap
	 */
	function deleteAccount($_hookValues)
	{
		return true;
	}

	/**
	 * Get all email addresses of an account
	 *
	 * @param string $_accountName
	 * @return array
	 */
	function getAccountEmailAddress($_accountName)
	{
		$accountID = $GLOBALS['egw']->accounts->name2id($_accountName);
		$emailAddress = $GLOBALS['egw']->accounts->id2name($accountID,'account_email');
		if(empty($emailAddress))
			$emailAddress = $_accountName.'@'.$this->defaultDomain;

		$realName = trim($GLOBALS['egw_info']['user']['account_firstname'] . (!empty($GLOBALS['egw_info']['user']['account_firstname']) ? ' ' : '') . $GLOBALS['egw_info']['user']['account_lastname']);

		return array(
			array(
				'name'		=> $realName,
				'address'	=> $emailAddress,
				'type'		=> 'default'
			)
		);
	}

	/**
	 * Get the data of a given user
	 *
	 * @param int|string $user numerical account-id, account-name or email address
	 * @return array with values for keys 'mailLocalAddress', 'mailAlternateAddress' (array), 'mailForwardingAddress' (array),
	 * 	'accountStatus' ("active"), 'quotaLimit' and 'deliveryMode' ("forwardOnly")
	 */
	function getUserData($_uidnumber)
	{
		$userData = array();

		return $userData;
	}

	/**
	 * Saves the forwarding information
	 *
	 * @param int $_accountID
	 * @param string $_forwardingAddress
	 * @param string $_keepLocalCopy 'yes'
	 * @return boolean true on success, false on error writing to ldap
	 */
	function saveSMTPForwarding($_accountID, $_forwardingAddress, $_keepLocalCopy)
	{
		return true;
	}

	/**
	 * Set the data of a given user
	 *
	 * @param int $_uidnumber numerical user-id
	 * @param array $_mailAlternateAddress
	 * @param array $_mailForwardingAddress
	 * @param string $_deliveryMode
	 * @param string $_accountStatus
	 * @param string $_mailLocalAddress
	 * @param int $_quota in MB
	 * @return boolean true on success, false on error writing to ldap
	 */
	function setUserData($_uidnumber, $_mailAlternateAddress, $_mailForwardingAddress, $_deliveryMode, $_accountStatus, $_mailLocalAddress, $_quota)
	{
		return true;
	}

	/**
	 * Hook called on account update
	 *
	 * @param array $_hookValues values for keys 'account_email', 'account_firstname', 'account_lastname', 'account_lid', 'account_id'
	 * @return boolean true on success, false on error writing to ldap
	 */
	function updateAccount($_hookValues)
	{
		return true;
	}
}
