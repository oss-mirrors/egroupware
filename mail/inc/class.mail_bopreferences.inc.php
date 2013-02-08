<?php
/**
 * Mail - worker class for preferences and mailprofiles
 *
 * @link http://www.egroupware.org
 * @package mail
 * @author Klaus Leithoff [kl@stylite.de]
 * @copyright (c) 2013 by Klaus Leithoff <kl-AT-stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

class mail_bopreferences extends mail_sopreferences
{
	/**
	 * Methods callable via menuaction
	 *
	 * @var array
	 */
	var $public_functions = array
	(
		'getPreferences'	=> True,
	);

	/**
	 * profileData - stores the users Profile Data
	 *
	 * @var array
	 */
	var $profileData;

	/**
	 * session Data
	 *
	 * @var array
	 */
	var $sessionData;

	/**
	 * Instance of emailadmin
	 *
	 * @var array
	 */
	var $boemailadmin;

	/**
	 * constructor
	 *
	 * @param boolean $_restoreSession=true

	 */
	function __construct($_restoreSession = true)
	{
		//error_log(__METHOD__." called ".print_r($_restoreSession,true).function_backtrace());
		parent::__construct();
		$this->boemailadmin = new emailadmin_bo(false,$_restoreSession); // does read all profiles, no profile?
		if ($_restoreSession && !(is_array($this->sessionData) && (count($this->sessionData)>0))  ) $this->restoreSessionData();
		if ($_restoreSession===false && (is_array($this->sessionData) && (count($this->sessionData)>0))  )
		{
			//error_log(__METHOD__." Unset Session ".function_backtrace());
			//make sure session data will be reset
			$this->sessionData = array();
			$this->profileData = array();
			$this->saveSessionData();
		}
		//error_log(__METHOD__.print_r($this->sessionData,true));
		if (isset($this->sessionData['profileData']) && ($this->sessionData['profileData'] instanceof ea_preferences))
		{
			//error_log(__METHOD__." Restore Session ".function_backtrace());
			$this->profileData = $this->sessionData['profileData'];
		}
	}

	/**
	 * restoreSessionData
	 * populates class var sessionData
	 */
	function restoreSessionData()
	{
		//error_log(__METHOD__." Session restore ".function_backtrace());
		// set an own autoload function, search emailadmin for missing classes
		$GLOBALS['egw_info']['flags']['autoload'] = array(__CLASS__,'autoload');

		$this->sessionData = (array) unserialize($GLOBALS['egw']->session->appsession('mail_preferences','mail'));
	}

	/**
	 * saveSessionData
	 * save class var sessionData to appsession
	 */
	function saveSessionData()
	{
		$GLOBALS['egw']->session->appsession('mail_preferences','mail',serialize($this->sessionData));
	}

	/**
	 * getAccountData
	 * get the first active user defined account
	 * @param array &$_profileData, reference, not altered; used to validate $_profileData
	 * @param int $_identityID=NULL
	 * @return array of objects (icServer, ogServer, identities)
	 */
	function getAccountData(&$_profileData, $_identityID=NULL)
	{
		#echo "<p>backtrace: ".function_backtrace()."</p>\n";
		if(!($_profileData instanceof ea_preferences))
			die(__FILE__.': '.__LINE__);
		$accountData = parent::getAccountData($GLOBALS['egw_info']['user']['account_id'],$_identityID);

		// currently we use only the first profile available
		$accountData = array_shift($accountData);
		//_debug_array($accountData);

		$icServer = CreateObject('emailadmin.defaultimap');
		$icServer->ImapServerId	= $accountData['id'];
		$icServer->encryption	= isset($accountData['ic_encryption']) ? $accountData['ic_encryption'] : 1;
		$icServer->host		= $accountData['ic_hostname'];
		$icServer->port 	= isset($accountData['ic_port']) ? $accountData['ic_port'] : 143;
		$icServer->validatecert	= isset($accountData['ic_validatecertificate']) ? (bool)$accountData['ic_validatecertificate'] : 1;
		$icServer->username 	= $accountData['ic_username'];
		$icServer->loginName 	= $accountData['ic_username'];
		$icServer->password	= $accountData['ic_password'];
		$icServer->enableSieve	= isset($accountData['ic_enable_sieve']) ? (bool)$accountData['ic_enable_sieve'] : 1;
		$icServer->sieveHost	= $accountData['ic_sieve_server'];
		$icServer->sievePort	= isset($accountData['ic_sieve_port']) ? $accountData['ic_sieve_port'] : 2000;
		if ($accountData['ic_folderstoshowinhome']) $icServer->folderstoshowinhome	= $accountData['ic_folderstoshowinhome'];
		if ($accountData['ic_trashfolder']) $icServer->trashfolder = $accountData['ic_trashfolder'];
		if ($accountData['ic_sentfolder']) $icServer->sentfolder = $accountData['ic_sentfolder'];
		if ($accountData['ic_draftfolder']) $icServer->draftfolder = $accountData['ic_draftfolder'];
		if ($accountData['ic_templatefolder']) $icServer->templatefolder = $accountData['ic_templatefolder'];

		$ogServer = new emailadmin_smtp();
		$ogServer->SmtpServerId	= $accountData['id'];
		$ogServer->host		= $accountData['og_hostname'];
		$ogServer->port		= isset($accountData['og_port']) ? $accountData['og_port'] : 25;
		$ogServer->smtpAuth	= (bool)$accountData['og_smtpauth'];
		if($ogServer->smtpAuth) {
			$ogServer->username 	= $accountData['og_username'];
			$ogServer->password 	= $accountData['og_password'];
		}

		$identity = CreateObject('emailadmin.ea_identity');
		$identity->emailAddress	= $accountData['emailaddress'];
		$identity->realName	= $accountData['realname'];
		//$identity->default	= true;
		$identity->default = (bool)$accountData['active'];
		$identity->organization	= $accountData['organization'];
		$identity->signature = $accountData['signatureid'];
		$identity->id  = $accountData['id'];

		$isActive = (bool)$accountData['active'];

		return array('icServer' => $icServer, 'ogServer' => $ogServer, 'identity' => $identity, 'active' => $isActive);
	}

	/**
	 * getAllAccountData
	 * get the first active user defined account
	 * @param array &$_profileData, reference, not altered; used to validate $_profileData
	 * @return array of array of objects (icServer, ogServer, identities)
	 */
	function getAllAccountData(&$_profileData)
	{
		if(!($_profileData instanceof ea_preferences))
			die(__FILE__.': '.__LINE__);
		$AllAccountData = parent::getAccountData($GLOBALS['egw_info']['user']['account_id'],'all');
		#_debug_array($accountData);
		foreach ($AllAccountData as $key => $accountData)
		{
			$icServer = CreateObject('emailadmin.defaultimap');
			$icServer->ImapServerId	= $accountData['id'];
			$icServer->encryption	= isset($accountData['ic_encryption']) ? $accountData['ic_encryption'] : 1;
			$icServer->host		= $accountData['ic_hostname'];
			$icServer->port 	= isset($accountData['ic_port']) ? $accountData['ic_port'] : 143;
			$icServer->validatecert	= isset($accountData['ic_validatecertificate']) ? (bool)$accountData['ic_validatecertificate'] : 1;
			$icServer->username 	= $accountData['ic_username'];
			$icServer->loginName 	= $accountData['ic_username'];
			$icServer->password	= $accountData['ic_password'];
			$icServer->enableSieve	= isset($accountData['ic_enable_sieve']) ? (bool)$accountData['ic_enable_sieve'] : 1;
			$icServer->sieveHost	= $accountData['ic_sieve_server'];
			$icServer->sievePort	= isset($accountData['ic_sieve_port']) ? $accountData['ic_sieve_port'] : 2000;
			if ($accountData['ic_folderstoshowinhome']) $icServer->folderstoshowinhome = $accountData['ic_folderstoshowinhome'];
			if ($accountData['ic_trashfolder']) $icServer->trashfolder = $accountData['ic_trashfolder'];
			if ($accountData['ic_sentfolder']) $icServer->sentfolder = $accountData['ic_sentfolder'];
			if ($accountData['ic_draftfolder']) $icServer->draftfolder = $accountData['ic_draftfolder'];
			if ($accountData['ic_templatefolder']) $icServer->templatefolder = $accountData['ic_templatefolder'];

			$ogServer = new emailadmin_smtp();
			$ogServer->SmtpServerId	= $accountData['id'];
			$ogServer->host		= $accountData['og_hostname'];
			$ogServer->port		= isset($accountData['og_port']) ? $accountData['og_port'] : 25;
			$ogServer->smtpAuth	= (bool)$accountData['og_smtpauth'];
			if($ogServer->smtpAuth) {
				$ogServer->username 	= $accountData['og_username'];
				$ogServer->password 	= $accountData['og_password'];
			}

			$identity = CreateObject('emailadmin.ea_identity');
			$identity->emailAddress	= $accountData['emailaddress'];
			$identity->realName	= $accountData['realname'];
			//$identity->default	= true;
			$identity->default = (bool)$accountData['active'];
			$identity->organization	= $accountData['organization'];
			$identity->signature = $accountData['signatureid'];
			$identity->id  = $accountData['id'];
			$isActive = (bool)$accountData['active'];
			$out[$accountData['id']] = array('icServer' => $icServer, 'ogServer' => $ogServer, 'identity' => $identity, 'active' => $isActive);
		}
		return $out;
	}

	function getUserDefinedIdentities()
	{
		$profileID = emailadmin_bo::getUserDefaultProfileID();
		$profileData        = $this->boemailadmin->getUserProfile('mail');
		if(!($profileData instanceof ea_preferences) || !($profileData->ic_server[$profileID] instanceof defaultimap)) {
			return false;
		}
		if($profileData->userDefinedAccounts || $profileData->userDefinedIdentities)
		{
			// get user defined accounts
			$allAccountData = $this->getAllAccountData($profileData);
			if ($allAccountData)
			{
				foreach ($allAccountData as $tmpkey => $accountData)
				{
					$accountArray[] = $accountData['identity'];
				}
				return $accountArray;
			}
		}
		return array();
	}

	/**
	 * getPreferences - fetches the active profile for a user
	 *
	 * @param boolean $getUserDefinedProfiles
	 * @param int $_profileID - use this profile to be set its prefs as active profile (0)
	 * @param string $_appName - the app the profile is fetched for
	 * @param int $_singleProfileToFetch - single Profile to fetch no merging of profileData; emailadminprofiles only; for Administrative use only (by now)
	 * @return object ea_preferences object with the active emailprofile set to ID = 0
	 */
	function getPreferences($getUserDefinedProfiles=true,$_profileID=0,$_appName='mail',$_singleProfileToFetch=0)
	{
		if (isset($this->sessionData['profileData']) && ($this->sessionData['profileData'] instanceof ea_preferences))
		{
			$this->profileData = $this->sessionData['profileData'];
		}

		if((!($this->profileData instanceof ea_preferences) && $_singleProfileToFetch==0) || ($_singleProfileToFetch!=0 && !isset($this->profileData->icServer[$_singleProfileToFetch])))
		{
			$GLOBALS['egw']->preferences->read_repository();
			$userPreferences = $GLOBALS['egw_info']['user']['preferences']['mail'];

			$imapServerTypes	= $this->boemailadmin->getIMAPServerTypes();
			$profileData = $this->boemailadmin->getUserProfile($_appName,'',($_singleProfileToFetch<0?-$_singleProfileToFetch:'')); // by now we assume only one profile to be returned
			$icServerKeys = array_keys((array)$profileData->ic_server);
			$icProfileID = array_shift($icServerKeys);
			$ogServerKeys = array_keys((array)$profileData->og_server);
			$ogProfileID = array_shift($ogServerKeys);
			//error_log(__METHOD__.__LINE__.' ServerProfile(s)Fetched->'.array2string(count($profileData->ic_server)));
			//may be needed later on, as it may hold users Identities connected to MailAlternateAdresses
			$IdIsDefault = 0;
			$rememberIdentities = $profileData->identities;
			foreach ($rememberIdentities as $adkey => $ident)
			{
				if ($ident->default) $IdIsDefault = $ident->id;
				$profileData->identities[$adkey]->default = false;
			}

			if(!($profileData instanceof ea_preferences) || !($profileData->ic_server[$icProfileID] instanceof defaultimap))
			{
				return false;
			}
			// set the emailadminprofile as profile 0; it will be assumed the active one (if no other profiles are active)
			$profileData->setIncomingServer($profileData->ic_server[$icProfileID],0);
			$profileID = $icProfileID;
			$profileData->setOutgoingServer($profileData->og_server[$ogProfileID],0);
			$profileData->setIdentity($profileData->identities[$icProfileID],0);
			$userPrefs = $this->mergeUserAndProfilePrefs($userPreferences,$profileData,$icProfileID);
			$rememberID = array(); // there may be more ids to be rememered
			$maxId = $icProfileID>0?$icProfileID:0;
			$minId = $icProfileID<0?$icProfileID:0;
			//$profileData->setPreferences($userPrefs,0);
			if($profileData->userDefinedAccounts && $GLOBALS['egw_info']['user']['apps']['mail'] && $getUserDefinedProfiles)
			{
				// get user defined accounts (only fetch the active one(s), as we call it without second parameter)
				// we assume only one account may be active at once
				$allAccountData = $this->getAllAccountData($profileData);
				foreach ((array)$allAccountData as $k => $accountData)
				{
					// set defined IMAP server
					if(($accountData['icServer'] instanceof defaultimap))
					{
						$profileData->setIncomingServer($accountData['icServer'],$k);
						$userPrefs = $this->mergeUserAndProfilePrefs($userPreferences,$profileData,$k);
						//$profileData->setPreferences($userPrefs,$k);
					}
					// set defined SMTP Server
					if(($accountData['ogServer'] instanceof emailadmin_smtp))
						$profileData->setOutgoingServer($accountData['ogServer'],$k);

					if(($accountData['identity'] instanceof ea_identity))
					{
						$profileData->setIdentity($accountData['identity'],$k);
						$rememberID[] = $k; // remember Identity as already added
						if ($k>0 && $k>$maxId) $maxId = $k;
						if ($k<0 && $k<$minId) $minId = $k;
					}

					if (empty($_profileID))
					{
						$setAsActive = $accountData['active'];
						//if($setAsActive) error_log(__METHOD__.__LINE__." Setting Profile with ID=$k (using Active Info) for ActiveProfile");
					}
					else
					{
						$setAsActive = ($_profileID==$k);
						//if($setAsActive) error_log(__METHOD__.__LINE__." Setting Profile with ID=$_profileID for ActiveProfile");
					}
					if($setAsActive)
					{
						// replace the global defined IMAP Server
						if(($accountData['icServer'] instanceof defaultimap))
						{
							$profileID = $k;
							$profileData->setIncomingServer($accountData['icServer'],0);
							$userPrefs = $this->mergeUserAndProfilePrefs($userPreferences,$profileData,$k);
							//$profileData->setPreferences($userPrefs,0);
						}

						// replace the global defined SMTP Server
						if(($accountData['ogServer'] instanceof emailadmin_smtp))
							$profileData->setOutgoingServer($accountData['ogServer'],0);

						// replace the global defined identity
						if(($accountData['identity'] instanceof ea_identity)) {
							//_debug_array($profileData);
							$profileData->setIdentity($accountData['identity'],0);
							$profileData->identities[0]->default = true;
							$rememberID[] = $IdIsDefault = $accountData['identity']->id;
						}
					}
				}
			}
			if($profileData->userDefinedIdentities && $GLOBALS['egw_info']['user']['apps']['mail'])
			{
				$allUserIdentities = $this->getUserDefinedIdentities();
				if (is_array($allUserIdentities))
				{
					$i=$maxId+1;
					$y=$minId-1;
					foreach ($allUserIdentities as $tmpkey => $id)
					{
						if (!in_array($id->id,$rememberID))
						{
							$profileData->setIdentity($id,$i);
							$i++;
						}
					}
				}
			}
			// make sure there is one profile marked as default (either 0 or the one found)
			$profileData->identities[$IdIsDefault]->default = true;

			$userPrefs = $this->mergeUserAndProfilePrefs($userPreferences,$profileData,$profileID);
			$profileData->setPreferences($userPrefs);

			//_debug_array($profileData);#exit;
			$this->sessionData['profileData'] = $this->profileData = $profileData;
			$this->saveSessionData();
			//_debug_array($this->profileData);
		}
		return $this->profileData;
	}

	function mergeUserAndProfilePrefs($userPrefs, &$profileData, $profileID)
	{
		// echo "<p>backtrace: ".function_backtrace()."</p>\n";
		if (is_array($profileData->ic_server[$profileID]->folderstoshowinhome) && !empty($profileData->ic_server[$profileID]->folderstoshowinhome[0]))
		{
			$userPrefs['mainscreen_showfolders'] = implode(',',$profileData->ic_server[$profileID]->folderstoshowinhome);
		}
		if (!empty($profileData->ic_server[$profileID]->sentfolder)) $userPrefs['sentFolder'] = $profileData->ic_server[$profileID]->sentfolder;
		if (!empty($profileData->ic_server[$profileID]->trashfolder)) $userPrefs['trashFolder'] = $profileData->ic_server[$profileID]->trashfolder;
		if (!empty($profileData->ic_server[$profileID]->draftfolder)) $userPrefs['draftFolder'] = $profileData->ic_server[$profileID]->draftfolder;
		if (!empty($profileData->ic_server[$profileID]->templatefolder)) $userPrefs['templateFolder'] = $profileData->ic_server[$profileID]->templatefolder;
		if(empty($userPrefs['deleteOptions']))
			$userPrefs['deleteOptions'] = 'mark_as_deleted';

		if (!empty($userPrefs['trash_folder']))
			$userPrefs['move_to_trash'] 	= True;
		if (!empty($userPrefs['sent_folder']))
		{
			if (!isset($userPrefs['sendOptions']) || empty($userPrefs['sendOptions'])) $userPrefs['sendOptions'] = 'move_to_sent';
		}

		if (!empty($userPrefs['email_sig'])) $userPrefs['signature'] = $userPrefs['email_sig'];

		unset($userPrefs['email_sig']);
		return $userPrefs;
	}

	function saveAccountData($_icServer, $_ogServer, $_identity)
	{
		if(is_object($_icServer) && !isset($_icServer->validatecert)) {
			$_icServer->validatecert = true;
		}
		if(isset($_icServer->host)) {
			$_icServer->sieveHost = $_icServer->host;
		}
		// unset the session data
		$this->sessionData = array();
		$this->saveSessionData();
		//error_log(__METHOD__.__LINE__.array2string($_icServer));
		emailadmin_bo::unsetCachedObjects($_identity->id);

		return parent::saveAccountData($GLOBALS['egw_info']['user']['account_id'], $_icServer, $_ogServer, $_identity);
	}

	function deleteAccountData($_identity)
	{
		if (is_array($_identity)) {
			foreach ($_identity as $tmpkey => $id)
			{
				if ($id->id) {
					$identity[] = $id->id;
				} else {
					$identity[] = $id;
				}
			}
		} else {
			$identity = $_identity;
		}
		$this->sessionData = array();
		$this->saveSessionData();
		parent::deleteAccountData($GLOBALS['egw_info']['user']['account_id'], $identity);
	}

	function setProfileActive($_status, $_identity=NULL)
	{
		$this->sessionData = array();
		$this->saveSessionData();
		if (!empty($_identity) && $_status == true)
		{
			//error_log(__METHOD__.__LINE__.' change status of Profile '.$_identity.' to '.$_status);
			// globals preferences add appname varname value
			$GLOBALS['egw']->preferences->add('mail','ActiveProfileID',$_identity,'user');
			// save prefs
			$GLOBALS['egw']->preferences->save_repository(true);
			egw_cache::setSession('mail','activeProfileID',$_identity);
		}
		parent::setProfileActive($GLOBALS['egw_info']['user']['account_id'], $_status, $_identity);
	}
}
