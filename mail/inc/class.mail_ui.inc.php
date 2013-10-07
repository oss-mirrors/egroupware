<?php
/**
 * EGroupware - Mail - interface class
 *
 * @link http://www.egroupware.org
 * @package mail
 * @author Klaus Leithoff [kl@stylite.de]
 * @copyright (c) 2013 by Klaus Leithoff <kl-AT-stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

include_once(EGW_INCLUDE_ROOT.'/etemplate/inc/class.etemplate.inc.php');

/**
 * Mail Interface class
 */
class mail_ui
{
	/**
	 * Methods callable via menuaction
	 *
	 * @var array
	 */
	var $public_functions = array
	(
		'index' => True,
		'displayHeader'	=> True,
		'displayMessage'	=> True,
		'displayImage'		=> True,
		'getAttachment'		=> True,
		'saveMessage'	=> True,
		'vfsSaveAttachment' => True,
		'vfsSaveMessage' => True,
		'loadEmailBody'	=> True,
		'importMessage'	=> True,
		'TestConnection' => True,
	);

	/**
	 * current icServerID
	 *
	 * @var int
	 */
	static $icServerID;

	/**
	 * delimiter - used to separate profileID from foldertreestructure, and separate keyinformation in rowids
	 *
	 * @var string
	 */
	static $delimiter = '::';

	/**
	 * nextMatch name for index
	 *
	 * @var string
	 */
	static $nm_index = 'nm';

	/**
	 * instance of mail_bo
	 *
	 * @var object
	 */
	var $mail_bo;

	/**
	 * definition of available / supported search types
	 *
	 * @var array
	 */
	var $searchTypes = array(
		'quick'		=> 'quicksearch',	// lang('quicksearch')
		'subject'	=> 'subject',		// lang('subject')
		'body'		=> 'message body',	// lang('message body')
		'from'		=> 'from',			// lang('from')
		'to'		=> 'to',			// lang('to')
		'cc'		=> 'cc',			// lang('cc')
	);

	/**
	 * definition of available / supported status types
	 *
	 * @var array
	 */
	var $statusTypes = array(
		'any'		=> 'any status',// lang('any status')
		'flagged'	=> 'flagged',	// lang('flagged')	
		'unseen'	=> 'unread',	// lang('unread')
		'answered'	=> 'replied',	// lang('replied')
		'seen'		=> 'read',		// lang('read')
		'deleted'	=> 'deleted',	// lang('deleted')
	);

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		if (!isset($GLOBALS['egw_info']['flags']['js_link_registry']))
		{
			//error_log(__METHOD__.__LINE__.' js_link_registry not set, force it:'.array2string($GLOBALS['egw_info']['flags']['js_link_registry']));
			$GLOBALS['egw_info']['flags']['js_link_registry']=true;
		}
		// no autohide of the sidebox, as we use it for folderlist now.
		unset($GLOBALS['egw_info']['user']['preferences']['common']['auto_hide_sidebox']);
		if (!empty($_GET["resetConnection"])) $connectionReset = html::purify($_GET["resetConnection"]);
		unset($_GET["resetConnection"]);

		//$icServerID =& egw_cache::getSession('mail','activeProfileID');
		if (isset($GLOBALS['egw_info']['user']['preferences']['mail']['ActiveProfileID']) && !empty($GLOBALS['egw_info']['user']['preferences']['mail']['ActiveProfileID']))
		{
			self::$icServerID = (int)$GLOBALS['egw_info']['user']['preferences']['mail']['ActiveProfileID'];
		}
		if ($connectionReset)
		{
			if (mail_bo::$debug) error_log(__METHOD__.__LINE__.' Connection Reset triggered:'.$connectionReset.' for Profile with ID:'.self::$icServerID);
			emailadmin_bo::unsetCachedObjects(self::$icServerID);
		}

		$this->mail_bo = mail_bo::getInstance(false,self::$icServerID);
		if (mail_bo::$debug) error_log(__METHOD__.__LINE__.' Fetched IC Server:'.self::$icServerID.'/'.$this->mail_bo->profileID.':'.function_backtrace());
		// no icServer Object: something failed big time
		if (!isset($this->mail_bo->icServer)) exit; // ToDo: Exception or the dialog for setting up a server config
		if (!($this->mail_bo->icServer->_connected == 1)) $this->mail_bo->openConnection(self::$icServerID);
	}

	/**
	 * changeProfile
	 *
	 * @param int $icServerID
	 */
	function changeProfile($_icServerID,$unsetCache=false)
	{
		//if (self::$icServerID != $_icServerID) $unsetCache = true;
		if (mail_bo::$debug) error_log(__METHOD__.__LINE__.'->'.self::$icServerID.'<->'.$_icServerID);
		self::$icServerID = $_icServerID;
		if ($unsetCache) emailadmin_bo::unsetCachedObjects(self::$icServerID);
		$this->mail_bo = mail_bo::getInstance(false,self::$icServerID);
		if (mail_bo::$debug) error_log(__METHOD__.__LINE__.' Fetched IC Server:'.self::$icServerID.'/'.$this->mail_bo->profileID.':'.function_backtrace());
		// no icServer Object: something failed big time
		if (!isset($this->mail_bo->icServer)) exit; // ToDo: Exception or the dialog for setting up a server config
		/*if (!($this->mail_bo->icServer->_connected == 1))*/ $this->mail_bo->openConnection(self::$icServerID);
		// save session varchar
		$oldicServerID =& egw_cache::getSession('mail','activeProfileID');
		$oldicServerID = self::$icServerID;
		// save pref
		$GLOBALS['egw']->preferences->add('mail','ActiveProfileID',self::$icServerID,'user');
		$GLOBALS['egw']->preferences->save_repository(true);
		$GLOBALS['egw_info']['user']['preferences']['mail']['ActiveProfileID'] = self::$icServerID;
	}

	/**
	 * Main mail page
	 *
	 * @param array $content=null
	 * @param string $msg=null
	 */
	function index(array $content=null,$msg=null)
	{
		$this->mail_bo->restoreSessionData();
		$sessionFolder = $this->mail_bo->sessionData['maibox'];
		if ($this->mail_bo->folderExists($sessionFolder))
		{
			$this->mail_bo->reopen($sessionFolder); // needed to fetch full set of capabilities
		}
		//_debug_array($content);
		if (!is_array($content))
		{
			$content = array(
				self::$nm_index => egw_session::appsession('index','mail'),
			);
			if (!is_array($content[self::$nm_index]))
			{
				$content[self::$nm_index] = array(
					'get_rows'       =>	'mail.mail_ui.get_rows',	// I  method/callback to request the data for the rows eg. 'notes.bo.get_rows'
					'filter'         => 'any',	// filter is used to choose the mailbox
					'no_filter2'     => false,	// I  disable the 2. filter (params are the same as for filter)
					'no_cat'         => true,	// I  disable the cat-selectbox
					//'cat_is_select'	 => 'no_lang', // true or no_lang
					'lettersearch'   => false,	// I  show a lettersearch
					'searchletter'   =>	false,	// I0 active letter of the lettersearch or false for [all]
					'start'          =>	0,		// IO position in list
					'order'          =>	'date',	// IO name of the column to sort after (optional for the sortheaders)
					'sort'           =>	'DESC',	// IO direction of the sort: 'ASC' or 'DESC'
					'default_cols'   => 'status,attachments,subject,fromaddress,date,size',	// I  columns to use if there's no user or default pref (! as first char uses all but the named columns), default all columns
					'csv_fields'     =>	false, // I  false=disable csv export, true or unset=enable it with auto-detected fieldnames,
									//or array with name=>label or name=>array('label'=>label,'type'=>type) pairs (type is a eT widget-type)
					'actions'        => self::get_actions(),
					'row_id'         => 'row_id', // is a concatenation of trim($GLOBALS['egw_info']['user']['account_id']):profileID:base64_encode(FOLDERNAME):uid
					'placeholder_actions' => array('composeasnew')
				);
				//$content[self::$nm_index]['path'] = self::get_home_dir();
			}
		}
		$content[self::$nm_index]['csv_fields'] = false;
		if ($msg)
		{
			$content['msg'] = $msg;
		}
		else
		{
			unset($msg);
			unset($content['msg']);
		}
		//$content['preview'] = "<html><body style='background-color: pink;'/></html>";

		// filter is used to choose the mailbox
		//if (!isset($content[self::$nm_index]['foldertree'])) // maybe we fetch the folder here
		/*
		$sel_options[self::$nm_index]['foldertree'] =  array('id' => 0, 'item' => array(
			array('id' => '/INBOX', 'text' => 'INBOX', 'im0' => 'kfm_home.png', 'child' => '1', 'item' => array(
				array('id' => '/INBOX/sub', 'text' => 'sub'),
				array('id' => '/INBOX/sub2', 'text' => 'sub2'),
			)),
			array('id' => '/user', 'text' => 'user', 'child' => '1', 'item' => array(
				array('id' => '/user/birgit', 'text' => 'birgit'),
			)),
		));

		$content[self::$nm_index]['foldertree'] = '/INBOX/sub';
		*/

		if($this->mail_bo->connectionStatus !== false) {
			$quota = $this->mail_bo->getQuotaRoot();
		} else {
			$quota['limit'] = 'NOT SET';
		}

		if($quota !== false && $quota['limit'] != 'NOT SET') {
			$quotainfo = $this->quotaDisplay($quota['usage'], $quota['limit']);
			$content[self::$nm_index]['quota'] = $sel_options[self::$nm_index]['quota'] = $quotainfo['text'];
			$content[self::$nm_index]['quotainpercent'] = $sel_options[self::$nm_index]['quotainpercent'] =  (string)$quotainfo['percent'];
			$content[self::$nm_index]['quotaclass'] = $sel_options[self::$nm_index]['quotaclass'] = $quotainfo['class'];
			$content[self::$nm_index]['quotanotsupported'] = $sel_options[self::$nm_index]['quotanotsupported'] = "";
		} else {
			$content[self::$nm_index]['quota'] = $sel_options[self::$nm_index]['quota'] = lang("Quota not provided by server");
			$content[self::$nm_index]['quotaclass'] = $sel_options[self::$nm_index]['quotaclass'] = "mail-index_quotaDisplayNone";
			$content[self::$nm_index]['quotanotsupported'] = $sel_options[self::$nm_index]['quotanotsupported'] = "mail-index_quotaDisplayNone";
		}

		$sel_options[self::$nm_index]['foldertree'] = $this->getFolderTree(false);

		//$sessionFolder = $this->mail_bo->sessionData['maibox'];// already set and tested this earlier
		if ($this->mail_bo->folderExists($sessionFolder))
		{
			$content[self::$nm_index]['selectedFolder'] = $this->mail_bo->profileID.self::$delimiter.$this->mail_bo->sessionData['maibox'];
			//$this->mail_bo->reopen($sessionFolder); // needed to fetch full set of capabilities: but did that earlier
		}
		// since we are connected,(and selected the folder) we check for capabilities SUPPORTS_KEYWORDS to eventually add the keyword filters
		if ($this->mail_bo->icServer->_connected == 1 && $this->mail_bo->icServer->hasCapability('SUPPORTS_KEYWORDS'))
		{
			$this->statusTypes = array_merge($this->statusTypes,array(
				'keyword1'	=> 'urgent',//lang('urgent'),
				'keyword2'	=> 'job',	//lang('job'),
				'keyword3'	=> 'personal',//lang('personal'),
				'keyword4'	=> 'to do',	//lang('to do'),
				'keyword5'	=> 'later',	//lang('later'),
			));
		}

		if (!isset($content[self::$nm_index]['foldertree'])) $content[self::$nm_index]['foldertree'] = $this->mail_bo->profileID.self::$delimiter.'INBOX';
		if (!isset($content[self::$nm_index]['selectedFolder'])) $content[self::$nm_index]['selectedFolder'] = $this->mail_bo->profileID.self::$delimiter.'INBOX';
		$content[self::$nm_index]['foldertree'] = $content[self::$nm_index]['selectedFolder'];
		//$sel_options['cat_id'] = array(1=>'none');
		$sel_options['filter2'] = $this->searchTypes;
		$sel_options['filter'] = $this->statusTypes;
		//if (!isset($content[self::$nm_index]['cat_id'])) $content[self::$nm_index]['cat_id'] = 'All';

		$etpl = new etemplate_new('mail.index');

		// Set tree actions
		$etpl->setElementAttribute(self::$nm_index.'[foldertree]','actions', array(
			'drop_move_mail' => array(
				'type' => 'drop',
				'acceptedTypes' => 'mail',
				'icon' => 'move',
				'caption' => 'Move to',
				'onExecute' => 'javaScript:app.mail.mail_move'
			),
			'drop_copy_mail' => array(
				'type' => 'drop',
				'acceptedTypes' => 'mail',
				'icon' => 'copy',
				'caption' => 'Copy to',
				'onExecute' => 'javaScript:app.mail.mail_copy'
			),
			'drop_cancel' => array(
				'caption' => 'Cancel',
				'acceptedTypes' => 'mail',
				'type' => 'drop',
			),
			// Tree does support this one
			'add' => array(
				'caption' => 'Add Folder',
				'onExecute' => 'javaScript:app.mail.mail_AddFolder'
			),
			'edit' => array(
				'caption' => 'Rename Folder',
				'onExecute' => 'javaScript:app.mail.mail_RenameFolder'
			),
			'delete' => array(
				'caption' => 'Delete Folder',
				'onExecute' => 'javaScript:app.mail.mail_DeleteFolder'
			)
		));
//error_log(__METHOD__.__LINE__.array2string($content));
		if (empty($content[self::$nm_index]['filter2']) || empty($content[self::$nm_index]['search'])) $content[self::$nm_index]['filter2']='quick';
		$readonlys = $preserv = $sel_options;
		return $etpl->exec('mail.mail_ui.index',$content,$sel_options,$readonlys,$preserv);
	}

	/**
	 * Test Connection
	 * Simple Test, resets the active connections cachedObjects / ImapServer
	 */
	function TestConnection ()
	{
		// load translations
		translation::add_app('mail');

		common::egw_header();
		parse_navbar();
		//$GLOBALS['egw']->framework->sidebox();
		$preferences	=& $this->mail_bo->mailPreferences;

		if ($preferences->preferences['prefcontroltestconnection'] == 'none') die('You should not be here!');

		if (isset($GLOBALS['egw_info']['user']['preferences']['mail']['ActiveProfileID']))
			$icServerID = (int)$GLOBALS['egw_info']['user']['preferences']['mail']['ActiveProfileID'];
		//_debug_array($this->mail_bo->mailPreferences);
		if (is_object($preferences)) $imapServer = $preferences->getIncomingServer($icServerID);
		if (isset($imapServer->ImapServerId) && !empty($imapServer->ImapServerId))
		{
			$icServerID = $GLOBALS['egw_info']['user']['preferences']['mail']['ActiveProfileID'] = $imapServer->ImapServerId;
		}
		echo "<h2>".lang('Test Connection and display basic information about the selected profile')."</h2>";

		_debug_array('Connection Reset triggered:'.$connectionReset.' for Profile with ID:'.$icServerID);
		emailadmin_bo::unsetCachedObjects($icServerID);
/*
		if (mail_bo::$idna2)
		{
			_debug_array('Umlautdomains supported (see Example below)');
			$dom = 'füßler.com';
			$encDom = mail_bo::$idna2->encode($dom);
			_debug_array(array('source'=>$dom,'result'=>array('encoded'=>$encDom,'decoded'=>mail_bo::$idna2->decode($encDom))));
		}
*/
		if ($preferences->preferences['prefcontroltestconnection'] == 'reset') exit;

		echo "<hr /><h3 style='color:red'>".lang('IMAP Server')."</h3>";
		if($imapServer->_connectionErrorObject)
		{
			$eO = $imapServer->_connectionErrorObject;
		}
		else
		{
			$this->mail_bo->reopen('INBOX');
		}
		unset($imapServer->_connectionErrorObject);
		$sieveServer = clone $imapServer;
		if (!empty($imapServer->adminPassword)) $imapServer->adminPassword='**********************';
		if ($preferences->preferences['prefcontroltestconnection'] == 'nopasswords' || $preferences->preferences['prefcontroltestconnection'] == 'nocredentials')
		{
			if (!empty($imapServer->password)) $imapServer->password='**********************';
		}
		if ($preferences->preferences['prefcontroltestconnection'] == 'nocredentials')
		{
			if (!empty($imapServer->adminUsername)) $imapServer->adminUsername='++++++++++++++++++++++';
			if (!empty($imapServer->username)) $imapServer->username='++++++++++++++++++++++';
			if (!empty($imapServer->loginName)) $imapServer->loginName='++++++++++++++++++++++';
		}
		if ($preferences->preferences['prefcontroltestconnection'] <> 'basic')
		{
			_debug_array($imapServer);
		}
		else
		{
			_debug_array(array('ImapServerId' =>$imapServer->ImapServerId,
				'host'=>$imapServer->host,
				'port'=>$imapServer->port,
				'validatecert'=>$imapServer->validatecert));
		}

		echo "<h4 style='color:red'>".lang('Connection Status')."</h4>";
		$lE = false;
		if ($eO && $eO->message)
		{
			_debug_array($eO->message);
			$lE = true;
		}
		$isError = egw_cache::getCache(egw_cache::INSTANCE,'email','icServerIMAP_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),null,array(),$expiration=60*5);
		if ($isError[$icServerID]) {
			_debug_array($isError[$icServerID]);
			$lE = true;
		}
		_debug_array(($lE?'':lang('Successfully connected')));

		$suF = $this->mail_bo->getSpecialUseFolders();
		if (is_array($suF) && !empty($suF)) _debug_array(array(lang('Server supports Special-Use Folders')=>$suF));

		if(($sieveServer instanceof defaultimap) && $sieveServer->enableSieve) {
			$scriptName = (!empty($GLOBALS['egw_info']['user']['preferences']['mail']['sieveScriptName'])) ? $GLOBALS['egw_info']['user']['preferences']['mail']['sieveScriptName'] : 'mail';
			$sieveServer->getScript($scriptName);
			$rules = $sieveServer->retrieveRules($sieveServer->scriptName,true);
			$vacation = $sieveServer->getVacation($sieveServer->scriptName);
			echo "<h4 style='color:red'>".lang('Sieve Connection Status')."</h4>";
			$isSieveError = egw_cache::getCache(egw_cache::INSTANCE,'email','icServerSIEVE_connectionError'.trim($GLOBALS['egw_info']['user']['account_id']),$callback=null,$callback_params=array(),$expiration=60*15);
			if ($isSieveError[$icServerID])
			{
				_debug_array($isSieveError[$icServerID]);
			}
			else
			{
				_debug_array(array(lang('Successfully connected'),$rules));
			}
		}
		echo "<hr /><h3 style='color:red'>".lang('Preferences')."</h3>";
		_debug_array($preferences->preferences);
		//error_log(__METHOD__.__LINE__.' ImapServerId:'.$imapServer->ImapServerId.' Prefs:'.array2string($preferences->preferences));
		//error_log(__METHOD__.__LINE__.' ImapServerObject:'.array2string($imapServer));
		if (is_object($preferences)) $activeIdentity =& $preferences->getIdentity($icServerID, true);
		//_debug_array($activeIdentity);
		$maxMessages	=  50;
		$userPreferences	=&  $GLOBALS['egw_info']['user']['preferences']['mail'];

		// retrieve data for/from user defined accounts
		$selectedID = 0;
		if (count($preferences->ic_server)) {
			foreach ($preferences->ic_server as $tmpkey => $accountData)
			{
				if ($tmpkey==0) continue;
				$identity =& $preferences->identities[$tmpkey];
				$icServer =& $accountData;
				//_debug_array($identity);
				//_debug_array($icServer);
				//error_log(__METHOD__.__LINE__.' Userdefined Profiles ImapServerId:'.$icServer->ImapServerId);
				if (empty($icServer->host)) continue;
				$identities[$identity->id]=$identity->realName.' '.$identity->organization.' &lt;'.$identity->emailAddress.'&gt;';
				if (!empty($identity->default)) $identities[$identity->id] = $identities[$identity->id].'<b>('.lang('selected').')</b>';
			}
		}
		if (count($identities)>0)
		{
			echo "<hr /><h3 style='color:red'>".lang('available personal EMail-Accounts/Profiles')."</h3>";
			_debug_array($identities);
		}

		if (empty($imapServer->host) && count($identities)==0 && $preferences->userDefinedAccounts)
		{
			// redirect to new personal account
			egw::redirect_link('/index.php',array('menuaction'=>'felamimail.uipreferences.editAccountData',
				'accountID'=>"new",
				'msg'	=> lang("There is no IMAP Server configured.")." - ".lang("Please configure access to an existing individual IMAP account."),
			));
		}
		common::egw_footer();
	}

	/**
	 * Ajax callback to fetch folders for given profile
	 *
	 * We currently load all folders of a given profile, tree can also load parts of a tree.
	 *
	 * @param string $_GET[id] if of node whos children are requested
	 */
	public function ajax_foldertree($_nodeID = null)
	{
		$nodeID = $_GET['id'];
		if (!is_null($_nodeID)) $nodeID = $_nodeID;
		//error_log(__METHOD__.__LINE__.'->'.array2string($_REQUEST));
		//error_log(__METHOD__.__LINE__.'->'.array2string($_GET));
		$fetchCounters = !is_null($_nodeID);
		list($_profileID,$_folderName) = explode(self::$delimiter,$nodeID,2);
		if (!empty($_folderName)) $fetchCounters = true;
		$data = $this->getFolderTree($fetchCounters, $nodeID);
		//error_log(__METHOD__.__LINE__.':'.$nodeID.'->'.array2string($data));
		if (!is_null($_nodeID)) return $data;
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		common::egw_exit();
	}

	/**
	 * getFolderTree, get folders from server and prepare the folder tree
	 * @param bool $_fetchCounters, wether to fetch extended information on folders
	 * @param string $_nodeID, nodeID to fetch and return
	 * @return array something like that: array('id'=>0,
	 * 		'item'=>array(
	 *			'text'=>'INBOX',
	 *			'tooltip'=>'INBOX'.' '.lang('(not connected)'),
	 *			'im0'=>'kfm_home.png'
	 *			'item'=>array($MORE_ITEMS)
	 *		)
	 *	);
	 */
	function getFolderTree($_fetchCounters=false, $_nodeID=null)
	{
		if (!is_null($_nodeID) && $_nodeID !=0)
		{
			list($_profileID,$_folderName) = explode(self::$delimiter,$_nodeID,2);
			if (is_numeric($_profileID))
			{
				if ($_profileID && $_profileID != $this->mail_bo->profileID)
				{
					//error_log(__METHOD__.__LINE__.' change Profile to ->'.$_profileID);
					$this->changeProfile($_profileID);
				}
			}
		}
		$folderObjects = $this->mail_bo->getFolderObjects(true,false,true);
		$trashFolder = $this->mail_bo->getTrashFolder();
		$templateFolder = $this->mail_bo->getTemplateFolder();
		$draftFolder = $this->mail_bo->getDraftFolder();
		$sentFolder = $this->mail_bo->getSentFolder();
		$userDefinedFunctionFolders = array();
		if (isset($trashFolder) && $trashFolder != 'none') $userDefinedFunctionFolders['Trash'] = $trashFolder;
		if (isset($sentFolder) && $sentFolder != 'none') $userDefinedFunctionFolders['Sent'] = $sentFolder;
		if (isset($draftFolder) && $draftFolder != 'none') $userDefinedFunctionFolders['Drafts'] = $draftFolder;
		if (isset($templateFolder) && $templateFolder != 'none') $userDefinedFunctionFolders['Templates'] = $templateFolder;
		$out = array('id' => 0);
		//_debug_array($this->mail_bo->mailPreferences);
		//if($this->mail_bo->mailPreferences->userDefinedAccounts) $allAccountData = $this->mail_bo->bopreferences->getAllAccountData($this->mail_bo->mailPreferences);
		//$starttime = microtime(true);
		if (count($this->mail_bo->mailPreferences->ic_server)) {
			foreach ($this->mail_bo->mailPreferences->ic_server as $tmpkey => $accountData)
			{
				if ($tmpkey==0) continue;
				$identity =& $this->mail_bo->mailPreferences->identities[$tmpkey];
				$icServer =& $accountData;
				//_debug_array($identity);
				//_debug_array($icServer);
				if ($_profileID && $icServer->ImapServerId<>$_profileID) continue;
				//error_log(__METHOD__.__LINE__.' Userdefined Profiles ImapServerId:'.$icServer->ImapServerId);
				if (empty($icServer->host)) continue;
				$identities[$icServer->ImapServerId]=$identity->realName.' '.$identity->organization.' &lt;'.$identity->emailAddress.'&gt;';
				$oA = array('id'=>$icServer->ImapServerId,
					'text'=>$identities[$icServer->ImapServerId], //$this->mail_bo->profileID,
					'tooltip'=>'('.$icServer->ImapServerId.') '.htmlspecialchars_decode($identities[$icServer->ImapServerId]),
					'im0' => 'thunderbird.png',
					'im1' => 'thunderbird.png',
					'im2' => 'thunderbird.png',
					'path'=> array($icServer->ImapServerId),
					'child'=> 1, // dynamic loading on unfold
					'parent' => ''
				);
				$this->setOutStructure($oA,$out,self::$delimiter);
			}
		}
		//$endtime = microtime(true) - $starttime;
		//error_log(__METHOD__.__LINE__.' Fetching identities Took:'.$endtime);

		//error_log(__METHOD__.__LINE__.array2string($folderObjects));
		$c = 0;
		foreach($folderObjects as $key => $obj)
		{
			$fS = $this->mail_bo->getFolderStatus($key,false,($_fetchCounters?false:true));
			//_debug_array($fS);
			//error_log(__METHOD__.__LINE__.array2string($fS));
			$fFP = $folderParts = explode($obj->delimiter, $key);

			//get rightmost folderpart
			$shortName = array_pop($folderParts);

			// the rest of the array is the name of the parent
			$parentName = implode((array)$folderParts,$obj->delimiter);
			$parentName = $this->mail_bo->profileID.self::$delimiter.$parentName;
			$oA =array('text'=> $obj->shortDisplayName, 'tooltip'=> $obj->displayName);
			array_unshift($fFP,$this->mail_bo->profileID);
			$oA['path'] = $fFP;
			$path = $key; //$obj->folderName; //$obj->delimiter
			if ($fS['unseen']) $oA['text'] = '<b>'.$oA['text'].' ('.$fS['unseen'].')</b>';
			if ($path=='INBOX')
			{
				$oA['im0'] = $oA['im1']= $oA['im2'] = "kfm_home.png";
			}
			elseif (in_array($obj->shortFolderName,mail_bo::$autoFolders))
			{
				//echo $obj->shortFolderName.'<br>';
				$oA['im0'] = $oA['im1']= $oA['im2'] = "MailFolder".$obj->shortFolderName.".png";
				//$image2 = "'MailFolderPlain.png'";
				//$image3 = "'MailFolderPlain.png'";
			}
			elseif (in_array($key,$userDefinedFunctionFolders))
			{
				$_key = array_search($key,$userDefinedFunctionFolders);
				$oA['im0'] = $oA['im1']= $oA['im2'] = "MailFolder".$_key.".png";
			}
			else
			{
				$oA['im0'] =  "MailFolderPlain.png"; // one Level
				$oA['im1'] = "folderOpen.gif";
				$oA['im2'] = "MailFolderClosed.png"; // has Children
			}
			$path = $this->mail_bo->profileID.self::$delimiter.$key; //$obj->folderName; //$obj->delimiter
			$oA['id'] = $path; // ID holds the PATH
			if (!empty($fS['attributes']) && stripos(array2string($fS['attributes']),'\noselect')!== false)
			{
				$oA['im0'] = "folderNoSelectClosed.gif"; // one Level
				$oA['im1'] = "folderNoSelectOpen.gif";
				$oA['im2'] = "folderNoSelectClosed.gif"; // has Children
			}
			if (!empty($fS['attributes']) && stripos(array2string($fS['attributes']),'\hasnochildren')=== false)
			{
				$oA['child']=1; // translates to: hasChildren -> dynamicLoading
			}
			$oA['parent'] = $parentName;
//_debug_array($oA);
			$this->setOutStructure($oA,$out,$obj->delimiter);
			$c++;
		}
		if (!is_null($_nodeID) && $_nodeID !=0)
		{
			$node = self::findNode($out,$_nodeID);
			//error_log(__METHOD__.__LINE__.':'.$_nodeID.'->'.array2string($node));
			return $node;
		}
		return ($c?$out:array('id'=>0, 'item'=>array('text'=>'INBOX','tooltip'=>'INBOX'.' '.lang('(not connected)'),'im0'=>'kfm_home.png')));
	}

	/**
	 * findNode - helper function to return only a branch of the tree
	 *
	 * @param array $out, out array (to be searched)
	 * @param string $_nodeID, node to search for
	 * @param boolean $childElements=true return node itself, or only its child items
	 * @return array structured subtree
	 */
	static function findNode($_out, $_nodeID, $childElements = false)
	{
		foreach($_out['item'] as $node)
		{
			if (strcmp($node['id'],$_nodeID)===0)
			{
				//error_log(__METHOD__.__LINE__.':'.$_nodeID.'->'.$node['id']);
				return ($childElements?$node['item']:$node);
			}
			elseif (is_array($node['item']) && strncmp($node['id'],$_nodeID,strlen($node['id']))===0 && strlen($_nodeID)>strlen($node['id']))
			{
				//error_log(__METHOD__.__LINE__.' descend into '.$node['id']);
				return self::findNode($node,$_nodeID,$childElements);
			}
		}
	}

	/**
	 * setOutStructure - helper function to transform the folderObjectList to dhtmlXTreeObject requirements
	 *
	 * @param array $data, data to be processed
	 * @param array &$out, out array
	 * @param string $del='.', needed as glue for parent/child operation / comparsion
	 * @param boolean $createMissingParents=true create a missing parent, instead of throwing an exception
	 * @return void
	 */
	function setOutStructure($data, &$out, $del='.', $createMissingParents=true)
	{
		//error_log(__METHOD__."(".array2string($data).', '.array2string($out).", '$del')");
		$components = $data['path'];
		array_pop($components);	// remove own name

		$insert = &$out;
		$parents = array();
		foreach($components as $component)
		{
			if (count($parents)>1)
			{
				$helper = array_slice($parents,1,null,true);
				$parent = $parents[0].self::$delimiter.implode($del, $helper);
				if ($parent) $parent .= $del;
			}
			else
			{
				$parent = implode(self::$delimiter, $parents);
				if ($parent) $parent .= self::$delimiter;
			}

			if (!is_array($insert) || !isset($insert['item']))
			{
				// throwing an exeption here seems to be unrecoverable, even if the cause is a something that can be handeled by the mailserver
				error_log(__METHOD__.':'.__LINE__." id=$data[id]: Parent '$parent' of '$component' not found!");
				break;
				//throw new egw_exception_assertion_failed(__METHOD__.':'.__LINE__." id=$data[id]: Parent '$parent' '$component' not found! out=".array2string($out));
			}
			foreach($insert['item'] as &$item)
			{
				if ($item['id'] == $parent.$component)
				{
					$insert =& $item;
					break;
				}
			}
			if ($item['id'] != $parent.$component)
			{
				if ($createMissingParents)
				{
					unset($item);
					$item = array('id' => $parent.$component, 'text' => $component, 'im0' => "folderNoSelectClosed.gif",'im1' => "folderNoSelectOpen.gif",'im2' => "folderNoSelectClosed.gif",'tooltip' => '**missing**');
					$insert['item'][] =& $item;
					$insert =& $item;
				}
				else
				{
					throw new egw_exception_assertion_failed(__METHOD__.':'.__LINE__.": id=$data[id]: Parent '$parent' '$component' not found!");
				}
			}
			$parents[] = $component;
		}
		unset($data['path']);
		$insert['item'][] = $data;
		//error_log(__METHOD__."() leaving with out=".array2string($out));
	}

	/**
	 * Get actions / context menu for index
	 *
	 * Changes here, require to log out, as $content[self::$nm_index] get stored in session!
	 * @var &$action_links
	 *
	 * @return array see nextmatch_widget::egw_actions()
	 */
	private function get_actions(array &$action_links=array())
	{
		// duplicated from mail_hooks
		static $deleteOptions = array(
			'move_to_trash'		=> 'move to trash',
			'mark_as_deleted'	=> 'mark as deleted',
			'remove_immediately' =>	'remove immediately',
		);
		// todo: real hierarchical folder list
		$folders = array(
			'INBOX' => 'INBOX',
			'Drafts' => 'Drafts',
			'Sent' => 'Sent',
		);
		$lastFolderUsedForMove = null;
		$moveaction = 'move_';
		$lastFolderUsedForMoveCont = egw_cache::getCache(egw_cache::INSTANCE,'email','lastFolderUsedForMove'.trim($GLOBALS['egw_info']['user']['account_id']),null,array(),$expiration=60*60*1);
		if (isset($lastFolderUsedForMoveCont[$this->mail_bo->profileID]))
		{
			$_folder = $this->mail_bo->icServer->getCurrentMailbox();
			//error_log(__METHOD__.__LINE__.' '.$_folder."<->".$lastFolderUsedForMoveCont[$this->mail_bo->profileID].function_backtrace());
			//if ($_folder!=$lastFolderUsedForMoveCont[$this->mail_bo->profileID]) $this->mail_bo->icServer->selectMailbox($lastFolderUsedForMoveCont[$this->mail_bo->profileID]);
			if ($_folder!=$lastFolderUsedForMoveCont[$this->mail_bo->profileID])
			{
				$lastFolderUsedForMove = $this->mail_bo->getFolderStatus($lastFolderUsedForMoveCont[$this->mail_bo->profileID]);
				//error_log(array2string($lastFolderUsedForMove));
				$moveaction .= $lastFolderUsedForMoveCont[$this->mail_bo->profileID];
			}
			//if ($_folder!=$lastFolderUsedForMoveCont[$this->profileID]) $this->mail_bo->icServer->selectMailbox($_folder);

		}
		$actions =  array(
			'open' => array(
				'caption' => lang('Open'),
				'group' => ++$group,
				'onExecute' => 'javaScript:app.mail.mail_open',
				'allowOnMultiple' => false,
				'default' => true,
			),
			'reply' => array(
				'caption' => 'Reply',
				'icon' => 'mail_reply',
				'group' => ++$group,
				'onExecute' => 'javaScript:app.mail.mail_compose',
				'allowOnMultiple' => false,
			),
			'reply_all' => array(
				'caption' => 'Reply All',
				'icon' => 'mail_replyall',
				'group' => $group,
				'onExecute' => 'javaScript:app.mail.mail_compose',
				'allowOnMultiple' => false,
			),
			'forward' => array(
				'caption' => 'Forward',
				'icon' => 'mail_forward',
				'group' => $group,
				'children' => array(
					'forwardinline' => array(
						'caption' => 'forward inline',
						'icon' => 'mail_forward',
						'group' => $group,
						'onExecute' => 'javaScript:app.mail.mail_compose',
						'allowOnMultiple' => false,
					),
					'forwardasattach' => array(
						'caption' => 'forward as attachment',
						'icon' => 'mail_forward',
						'group' => $group,
						'onExecute' => 'javaScript:app.mail.mail_compose',
					),
				),
			),
			'composeasnew' => array(
				'caption' => 'Compose as new',
				'icon' => 'new',
				'group' => $group,
				'onExecute' => 'javaScript:app.mail.mail_compose',
				'allowOnMultiple' => false,
			),
			$moveaction => array(
				'caption' => lang('Move selected to').': '.(isset($lastFolderUsedForMove['shortDisplayName'])?$lastFolderUsedForMove['shortDisplayName']:''),
				'icon' => 'move',
				'group' => ++$group,
				'onExecute' => 'javaScript:app.mail.mail_move2folder',
				'allowOnMultiple' => true,
			),
			'infolog' => array(
				'caption' => 'InfoLog',
				'hint' => 'Save as InfoLog',
				'icon' => 'infolog/navbar',
				'group' => ++$group,
				'onExecute' => 'javaScript:app.mail.mail_infolog',
				'url' => 'menuaction=infolog.infolog_ui.import_mail',
				'popup' => egw_link::get_registry('infolog', 'add_popup'),
				'allowOnMultiple' => false,
			),
			'tracker' => array(
				'caption' => 'Tracker',
				'hint' => 'Save as ticket',
				'group' => $group,
				'icon' => 'tracker/navbar',
				'onExecute' => 'javaScript:app.mail.mail_tracker',
				'url' => 'menuaction=tracker.tracker_ui.import_mail',
				'popup' => egw_link::get_registry('tracker', 'add_popup'),
				'allowOnMultiple' => false,
			),
			'print' => array(
				'caption' => 'Print',
				'group' => ++$group,
				'onExecute' => 'javaScript:app.mail.mail_print',
				'allowOnMultiple' => false,
			),
			'save' => array(
				'caption' => 'Save',
				'group' => $group,
				'icon' => 'fileexport',
				'children' => array(
					'save2disk' => array(
						'caption' => 'Save message to disk',
						'hint' => 'Save message to disk',
						'group' => $group,
						'icon' => 'fileexport',
						'onExecute' => 'javaScript:app.mail.mail_save',
						'allowOnMultiple' => false,
					),
					'save2filemanager' => array(
						'caption' => 'Save to filemanager',
						'hint' => 'Save message to filemanager',
						'group' => $group,
						'icon' => 'filemanager/navbar',
						'onExecute' => 'javaScript:app.mail.mail_save2fm',
						'allowOnMultiple' => false,
					),
				),
			),
			'view' => array(
				'caption' => 'View',
				'group' => $group,
				'icon' => 'kmmsgread',
				'children' => array(
					'header' => array(
						'caption' => 'Header lines',
						'hint' => 'View header lines',
						'group' => $group,
						'icon' => 'kmmsgread',
						'onExecute' => 'javaScript:app.mail.mail_header',
						'allowOnMultiple' => false,
					),
					'mailsource' => array(
						'caption' => 'Mail Source',
						'hint' => 'View full Mail Source',
						'group' => $group,
						'icon' => 'fileexport',
						'onExecute' => 'javaScript:app.mail.mail_mailsource',
						'allowOnMultiple' => false,
					),
					'openastext' => array(
						'caption' => lang('Open in Text mode'),
						'group' => ++$group,
						'icon' => egw_vfs::mime_icon('text/plain'),
						'onExecute' => 'javaScript:app.mail.mail_openAsText',
						'allowOnMultiple' => false,
					),
					'openashtml' => array(
						'caption' => lang('Open in HTML mode'),
						'group' => $group,
						'icon' => egw_vfs::mime_icon('text/html'),
						'onExecute' => 'javaScript:app.mail.mail_openAsHtml',
						'allowOnMultiple' => false,
					),
				),
			),
			'mark' => array(
				'caption' => 'Mark as',
				'icon' => 'read_small',
				'group' => ++$group,
				'children' => array(
					// icons used from http://creativecommons.org/licenses/by-sa/3.0/
					// Artist: Led24
					// Iconset Homepage: http://led24.de/iconset
					// License: CC Attribution 3.0
					'setLabel' => array(
						'caption' => 'Set Label',
						'icon' => 'tag_message',
						'group' => ++$group,
						'children' => array(
							'label1' => array(
								'caption' => "<font color='#ff0000'>".lang('urgent')."</font>",
								'icon' => 'mail_label1',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
							'label2' => array(
								'caption' => "<font color='#ff8000'>".lang('job')."</font>",
								'icon' => 'mail_label2',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
							'label3' => array(
								'caption' => "<font color='#008000'>".lang('personal')."</font>",
								'icon' => 'mail_label3',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
							'label4' => array(
								'caption' => "<font color='#0000ff'>".lang('to do')."</font>",
								'icon' => 'mail_label4',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
							'label5' => array(
								'caption' => "<font color='#8000ff'>".lang('later')."</font>",
								'icon' => 'mail_label5',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
						),
					),
					// modified icons from http://creativecommons.org/licenses/by-sa/3.0/
					'unsetLabel' => array(
						'caption' => 'Remove Label',
						'icon' => 'untag_message',
						'group' => ++$group,
						'children' => array(
							'unlabel1' => array(
								'caption' => "<font color='#ff0000'>".lang('urgent')."</font>",
								'icon' => 'mail_unlabel1',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
							'unlabel2' => array(
								'caption' => "<font color='#ff8000'>".lang('job')."</font>",
								'icon' => 'mail_unlabel2',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
							'unlabel3' => array(
								'caption' => "<font color='#008000'>".lang('personal')."</font>",
								'icon' => 'mail_unlabel3',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
							'unlabel4' => array(
								'caption' => "<font color='#0000ff'>".lang('to do')."</font>",
								'icon' => 'mail_unlabel4',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
							'unlabel5' => array(
								'caption' => "<font color='#8000ff'>".lang('later')."</font>",
								'icon' => 'mail_unlabel5',
								'onExecute' => 'javaScript:app.mail.mail_flag',
							),
						),
					),
					'flagged' => array(
						'group' => ++$group,
						'caption' => 'Flagged',
						'icon' => 'unread_flagged_small',
						'onExecute' => 'javaScript:app.mail.mail_flag',
						//'disableClass' => 'flagged',
						//'enabled' => "javaScript:mail_disabledByClass",
						'shortcut' => egw_keymanager::shortcut(egw_keymanager::F, true, true),
					),
					'unflagged' => array(
						'group' => $group,
						'caption' => 'Unflagged',
						'icon' => 'read_flagged_small',
						'onExecute' => 'javaScript:app.mail.mail_flag',
						//'enableClass' => 'flagged',
						//'enabled' => "javaScript:mail_enabledByClass",
						'shortcut' => egw_keymanager::shortcut(egw_keymanager::U, true, true),
					),
					'read' => array(
						'group' => $group,
						'caption' => 'Read',
						'icon' => 'read_small',
						'onExecute' => 'javaScript:app.mail.mail_flag',
						//'enableClass' => 'unseen',
						//'enabled' => "javaScript:mail_enabledByClass",
					),
					'unread' => array(
						'group' => $group,
						'caption' => 'Unread',
						'icon' => 'unread_small',
						'onExecute' => 'javaScript:app.mail.mail_flag',
						//'disableClass' => 'unseen',
						//'enabled' => "javaScript:mail_disabledByClass",
					),
					'undelete' => array(
						'group' => $group,
						'caption' => 'Undelete',
						'icon' => 'revert',
						'onExecute' => 'javaScript:app.mail.mail_flag',
						'enableClass' => 'deleted',
						'enabled' => "javaScript:mail_enabledByClass",
					),
				),
			),
			'delete' => array(
				'caption' => 'Delete',
				'hint' => $deleteOptions[$this->mail_bo->mailPreferences->preferences['deleteOptions']],
				'group' => ++$group,
				'onExecute' => 'javaScript:app.mail.mail_delete',
			),
			'drag_mail' => array(
				'dragType' => array('mail','file'),
				'type' => 'drag',
				'onExecute' => 'javaScript:app.mail.mail_dragStart',
			)
		);
		// save as tracker, save as infolog, as this are actions that are either available for all, or not, we do that for all and not via css-class disabling
		if (!isset($GLOBALS['egw_info']['user']['apps']['infolog']))
		{
			unset($actions['infolog']);
		}
		if (!isset($GLOBALS['egw_info']['user']['apps']['tracker']))
		{
			unset($actions['tracker']);
		}
		if (empty($lastFolderUsedForMove))
		{
			unset($actions[$moveaction]);
		}
		// note this one is NOT a real CAPABILITY reported by the server, but added by selectMailbox
		if (!$this->mail_bo->icServer->hasCapability('SUPPORTS_KEYWORDS'))
		{
			unset($actions['mark']['children']['setLabel']);
			unset($actions['mark']['children']['unsetLabel']);
		}
		return $actions;
	}

	/**
	 * Callback to fetch the rows for the nextmatch widget
	 *
	 * @param array $query
	 * @param array &$rows
	 * @param array &$readonlys
	 */
	function get_rows($query,&$rows,&$readonlys)
	{
unset($query['actions']);
//_debug_array($query);
//error_log(__METHOD__.__LINE__.array2string($query['order']).'->'.array2string($query['sort']));
//error_log(__METHOD__.__LINE__.' SelectedFolder:'.$query['selectedFolder'].' Start:'.$query['start'].' NumRows:'.$query['num_rows']);
		$starttime = microtime(true);
		//error_log(__METHOD__.__LINE__.array2string($query['search']));
		//$query['search'] is the phrase in the searchbox

		//error_log(__METHOD__.__LINE__.' Folder:'.array2string($_folderName).' FolderType:'.$folderType.' RowsFetched:'.array2string($rowsFetched)." these Uids:".array2string($uidOnly).' Headers passed:'.array2string($headers));
		$this->mail_bo->restoreSessionData();
		$maxMessages = 50; // match the hardcoded setting for data retrieval as inital value
		$previewMessage = $this->mail_bo->sessionData['previewMessage'];
		if (isset($query['selectedFolder'])) $this->mail_bo->sessionData['maibox']=$query['selectedFolder'];
		$this->mail_bo->saveSessionData();

		$sRToFetch = null;
		$_folderName=$query['selectedFolder'];
		list($_profileID,$folderName) = explode(self::$delimiter,$_folderName,2);
		if (is_numeric($_profileID))
		{
			if ($_profileID && $_profileID != $this->mail_bo->profileID)
			{
				//error_log(__METHOD__.__LINE__.' change Profile to ->'.$_profileID);
				$this->changeProfile($_profileID);
			}
			$_folderName = $folderName;
		}
		//save selected Folder to sessionData (mailbox)->currentFolder
		if (isset($query['selectedFolder'])) $this->mail_bo->sessionData['maibox']=$_folderName;
		$this->mail_bo->saveSessionData();

		$rowsFetched['messages'] = null;
		$offset = $query['start']+1; // we always start with 1
		$maxMessages = $query['num_rows'];
		$sort = $query['order'];
		if (!empty($query['search']))
		{
			//([filterName] => Schnellsuche[type] => quick[string] => ebay[status] => any
			$filter = array('filterName' => lang('quicksearch'),'type' => ($query['filter2']?$query['filter2']:'quick'),'string' => $query['search'],'status' => 'any');
		}
		else
		{
			$filter = array();
		}
		if ($query['filter'])
		{
			$filter['status'] = $query['filter'];
		}
		$reverse = ($query['sort']=='ASC'?false:true);
		//error_log(__METHOD__.__LINE__.' maxMessages:'.$maxMessages.' Offset:'.$offset.' Filter:'.array2string($this->sessionData['messageFilter']));
		if ($maxMessages > 75)
		{
			$sR = $this->mail_bo->getSortedList(
				$_folderName,
				$sort,
				$reverse,
				$filter,
				$rByUid=true
			);
			$rowsFetched['messages'] = count($sR);
			// if $sR is false, something failed fundamentally
			if($reverse === true) $sR = ($sR===false?array():array_reverse((array)$sR));
			$sR = array_slice((array)$sR,($offset==0?0:$offset-1),$maxMessages); // we need only $maxMessages of uids
			$sRToFetch = $sR;//array_slice($sR,0,50); // we fetch only the headers of a subset of the fetched uids
			//error_log(__METHOD__.__LINE__.' Rows fetched (UID only):'.count($sR).' Data:'.array2string($sR));
			$maxMessages = 75;
			$sortResultwH['header'] = array();
			if (count($sRToFetch)>0)
			{
				//error_log(__METHOD__.__LINE__.' Headers to fetch with UIDs:'.count($sRToFetch).' Data:'.array2string($sRToFetch));
				$sortResult = array();
				// fetch headers
				$sortResultwH = $this->mail_bo->getHeaders(
					$_folderName,
					$offset,
					$maxMessages,
					$sort,
					$reverse,
					$filter,
					$sRToFetch
				);
			}
		}
		else
		{
			$sortResult = array();
			// fetch headers
			$sortResultwH = $this->mail_bo->getHeaders(
				$_folderName,
				$offset,
				$maxMessages,
				$sort,
				$reverse,
				$filter
			);
			$rowsFetched['messages'] = $sortResultwH['info']['total'];
		}
		if (is_array($sR) && count($sR)>0)
		{
			foreach ((array)$sR as $key => $v)
			{
				if (array_key_exists($key,(array)$sortResultwH['header'])==true)
				{
					$sortResult['header'][] = $sortResultwH['header'][$key];
				}
				else
				{
					if (!empty($v)) $sortResult['header'][] = array('uid'=>$v);
				}
			}
		}
		else
		{
			$sortResult = $sortResultwH;
		}
		$rowsFetched['rowsFetched'] = count($sortResult['header']);
		if (empty($rowsFetched['messages'])) $rowsFetched['messages'] = $rowsFetched['rowsFetched'];

		//error_log(__METHOD__.__LINE__.' Rows fetched:'.$rowsFetched.' Data:'.array2string($sortResult));
		$cols = array('row_id','uid','status','attachments','subject','toaddress','fromaddress','date','size','modified');
		if ($GLOBALS['egw_info']['user']['preferences']['common']['select_mode']=='EGW_SELECTMODE_TOGGLE') unset($cols[0]);
		$rows = $this->header2gridelements($sortResult['header'],$cols, $_folderName, $folderType,$previewMessage);
		//error_log(__METHOD__.__LINE__.array2string($rows));
		$endtime = microtime(true) - $starttime;
		//error_log(__METHOD__.__LINE__.' SelectedFolder:'.$query['selectedFolder'].' Start:'.$query['start'].' NumRows:'.$query['num_rows'].' Took:'.$endtime);
		return $rowsFetched['messages'];
	}

	/**
	 * function createRowID - create a unique rowID for the grid
	 *
	 * @param string $_folderName, used to ensure the uniqueness of the uid over all folders
	 * @param string $message_uid, the message_Uid to be used for creating the rowID
	 * @param boolean $_prependApp, flag to indicate that the app 'mail' is to be used for creating the rowID
	 * @return string - a colon separated string in the form [app:]accountID:profileID:folder:message_uid
	 */
	function createRowID($_folderName, $message_uid, $_prependApp=false)
	{
		return ($_prependApp?'mail'.self::$delimiter:'').trim($GLOBALS['egw_info']['user']['account_id']).self::$delimiter.$this->mail_bo->profileID.self::$delimiter.base64_encode($_folderName).self::$delimiter.$message_uid;
	}

	/**
	 * function splitRowID - split the rowID into its parts
	 *
	 * @param string $_rowID, string - a colon separated string in the form accountID:profileID:folder:message_uid
	 * @return array populated named result array (accountID,profileID,folder,msgUID)
	 */
	static function splitRowID($_rowID)
	{
		$res = explode(self::$delimiter,$_rowID);
		// as a rowID is perceeded by app::, should be mail!
		//error_log(__METHOD__.__LINE__.array2string($res).' [0] isInt:'.is_int($res[0]).' [0] isNumeric:'.is_numeric($res[0]).' [0] isString:'.is_string($res[0]).' Count:'.count($res));
		if (count($res)==4 && is_numeric($res[0]) )
		{
			// we have an own created rowID; prepend app=mail
			array_unshift($res,'mail');
		}
		return array('app'=>$res[0], 'accountID'=>$res[1], 'profileID'=>$res[2], 'folder'=>base64_decode($res[3]), 'msgUID'=>$res[4]);
	}

	/**
	 * function header2gridelements - to populate the grid elements with the collected Data
	 *
	 * @param array $_headers, headerdata to process
	 * @param array $cols, cols to populate
	 * @param array $_folderName, used to ensure the uniqueness of the uid over all folders
	 * @param array $_folderType=0, foldertype, used to determine if we need to populate from/to
	 * @param array $previewMessage=0, the message previewed
	 * @return array populated result array
	 */
	public function header2gridelements($_headers, $cols, $_folderName, $_folderType=0, $previewMessage=0)
	{
		$timestamp7DaysAgo =
			mktime(date("H"), date("i"), date("s"), date("m"), date("d")-7, date("Y"));
		$timestampNow =
			mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
		$dateToday = date("Y-m-d");
		$rv = array();
		$actions = self::get_actions();
		foreach(array('composeasnew','reply','reply_all','forward','flagged','unflagged','delete','print','infolog','tracker','save','header') as $a => $act)
		{
			//error_log(__METHOD__.__LINE__.' '.$act.'->'.array2string($actions[$act]));
			switch ($act)
			{
				case 'forward':
					$actionsenabled[$act]=$actions[$act]['children']['forwardinline'];
					break;
				case 'save':
					$actionsenabled[$act]=$actions[$act]['children']['save2disk'];
					break;
				case 'header':
					$actionsenabled[$act]=$actions['view']['children'][$act];
					break;
				case 'flagged':
				case 'unflagged':
					$actionsenabled[$act]=$actions['mark']['children'][$act];
					break;
				default:
					if (isset($actions[$act])) $actionsenabled[$act]=$actions[$act];
			}
		}

		$i=0;
		$firstuid = null;
		foreach((array)$_headers as $header)
		{
			$i++;
			$data = array();
			//error_log(__METHOD__.array2string($header));
			$result = array(
				"id" => $header['uid'],
				"group" => "mail", // activate the action links for mail objects
			);
			$message_uid = $header['uid'];
			$data['uid'] = $message_uid;
			$data['row_id']=$this->createRowID($_folderName,$message_uid);

			//_debug_array($header);
			#if($i<10) {$i++;continue;}
			#if($i>20) {continue;} $i++;
			// create the listing of subjects
			$maxSubjectLength = 60;
			$maxAddressLength = 20;
			$maxSubjectLengthBold = 50;
			$maxAddressLengthBold = 14;

			$flags = "";
			if(!empty($header['recent'])) $flags .= "R";
			if(!empty($header['flagged'])) $flags .= "F";
			if(!empty($header['answered'])) $flags .= "A";
			if(!empty($header['forwarded'])) $flags .= "W";
			if(!empty($header['deleted'])) $flags .= "D";
			if(!empty($header['seen'])) $flags .= "S";
			if(!empty($header['label1'])) $flags .= "1";
			if(!empty($header['label2'])) $flags .= "2";
			if(!empty($header['label3'])) $flags .= "3";
			if(!empty($header['label4'])) $flags .= "4";
			if(!empty($header['label5'])) $flags .= "5";

			$data["status"] = "<span class=\"status_img\"></span>";
			//error_log(__METHOD__.array2string($header).' Flags:'.$flags);

			// the css for this row
			$is_recent=false;
			$css_styles = array("mail");
			if ($header['deleted']) {
				$css_styles[] = 'deleted';
			}
			if ($header['recent'] && !($header['deleted'] || $header['seen'] || $header['answered'] || $header['forwarded'])) {
				$css_styles[] = 'recent';
				$is_recent=true;
			}
			if ($header['priority'] < 3) {
				$css_styles[] = 'prio_high';
			}
			if ($header['flagged']) {
				$css_styles[] = 'flagged';
/*
				if (!$header['seen'])
				{
					$css_styles[] = 'flagged_unseen';
				}
				else
				{
					$css_styles[] = 'flagged_seen';
				}
*/
			}
			if (!$header['seen']) {
				$css_styles[] = 'unseen'; // different status image for recent // solved via css !important
			}
			if ($header['answered']) {
				$css_styles[] = 'replied';
			}
			if ($header['forwarded']) {
				$css_styles[] = 'forwarded';
			}
			if ($header['label1']) {
				$css_styles[] = 'labelone';
			}
			if ($header['label2']) {
				$css_styles[] = 'labeltwo';
			}
			if ($header['label3']) {
				$css_styles[] = 'labelthree';
			}
			if ($header['label4']) {
				$css_styles[] = 'labelfour';
			}
			if ($header['label5']) {
				$css_styles[] = 'labelfive';
			}

			//error_log(__METHOD__.array2string($css_styles));
			//if (in_array("check", $cols))
			// don't overwrite check with "false" as this forces the grid to
			// deselect the row - sending "0" doesn't do that
			//if (in_array("check", $cols)) $data["check"] = $previewMessage == $header['uid'] ? true : 0;// $row_selected; //TODO:checkbox true or false
			//$data["check"] ='<input  style="width:12px; height:12px; border: none; margin: 1px;" class="{row_css_class}" type="checkbox" id="msgSelectInput" name="msg[]" value="'.$message_uid.'"
			//	onclick="toggleFolderRadio(this, refreshTimeOut)">';

			if (in_array("subject", $cols))
			{
				// filter out undisplayable characters
				$search = array('[\016]','[\017]',
					'[\020]','[\021]','[\022]','[\023]','[\024]','[\025]','[\026]','[\027]',
					'[\030]','[\031]','[\032]','[\033]','[\034]','[\035]','[\036]','[\037]');
				$replace = '';

				$header['subject'] = preg_replace($search,$replace,$header['subject']);
				$headerSubject = $header['subject'];//mail_bo::htmlentities($header['subject'],$this->charset);
				$header['subject'] = $headerSubject;
				// curly brackets get messed up by the template!
				$header['subject'] = str_replace(array('{','}'),array('&#x7B;','&#x7D;'),$header['subject']);

				if (!empty($header['subject'])) {
					// make the subject shorter if it is to long
					$fullSubject = $header['subject'];
					$subject = $header['subject'];
					#$this->t->set_var('attachments', $header['attachment']);
				} else {
					$subject = @htmlspecialchars('('. lang('no subject') .')', ENT_QUOTES, $this->charset);
				}

				$data["subject"] = $subject; // the mailsubject
			}

			//_debug_array($header);
			$imageTag = '';
			$imageHTMLBlock = '';
			if (in_array("attachments", $cols))
			{
				if($header['mimetype'] == 'multipart/mixed' ||
					$header['mimetype'] == 'multipart/signed' ||
					$header['mimetype'] == 'multipart/related' ||
					$header['mimetype'] == 'multipart/report' ||
					$header['mimetype'] == 'text/calendar' ||
					$header['mimetype'] == 'text/html' ||
					substr($header['mimetype'],0,11) == 'application' ||
					substr($header['mimetype'],0,5) == 'audio' ||
					substr($header['mimetype'],0,5) == 'video' ||
					$header['mimetype'] == 'multipart/alternative')
				{
					$image = html::image('mail','attach');
					$imageTag = '';
					$imageHTMLBlock = '';
					$datarowid = $this->createRowID($_folderName,$message_uid,true);
					if (//$header['mimetype'] != 'multipart/mixed' &&
						$header['mimetype'] != 'multipart/signed'
					)
					{
						if ($this->mail_bo->icServer->_connected != 1)
						{
							$this->mail_bo->openConnection($this->profileID); // connect to the current server
							$this->mail_bo->reopen($_folderName);
						}
						$attachments = $this->mail_bo->getMessageAttachments($header['uid'],$_partID='', $_structure='', $fetchEmbeddedImages=true, $fetchTextCalendar=false, $resolveTNEF=false);
						if (count($attachments)<1)
						{
							$image = '&nbsp;';
						}
						if (count($attachments)==1)
						{
							$imageHTMLBlock = self::createAttachmentBlock($attachments, $datarowid, $header['uid'], $_folder);
							$imageTag = json_encode($attachments);
							$image = html::image('mail','attach',$attachments[0]['name'].(!empty($attachments[0]['mimeType'])?' ('.$attachments[0]['mimeType'].')':''));
						}
					}
					if (count($attachments)>1)
					{
						$imageHTMLBlock = self::createAttachmentBlock($attachments, $datarowid, $header['uid'], $_folder);
						$imageTag = json_encode($attachments);
						$image = html::image('mail','attach',lang('%1 attachments',count($attachments)));
					}

					$attachmentFlag = $image;
				} else {
					$attachmentFlag ='&nbsp;';
				}
				// show priority flag
				if ($header['priority'] < 3) {
					 $image = html::image('mail','prio_high');
				} elseif ($header['priority'] > 3) {
					$image = html::image('mail','prio_low');
				} else {
					$image = '';
				}
				// show a flag for flagged messages
				$imageflagged ='';
				if ($header['flagged'])
				{
					$imageflagged = html::image('mail','unread_flagged_small');
				}
				$data["attachments"] = $image.$attachmentFlag.$imageflagged; // icon for attachments available
			}

			// sent or draft or template folder -> to address
			if (in_array("toaddress", $cols))
			{
				if(!empty($header['to_name'])) {
					list($mailbox, $host) = explode('@',$header['to_address']);
					$senderAddress  = imap_rfc822_write_address($mailbox,
							$host,
							$header['to_name']);
				} else {
					$senderAddress  = $header['to_address'];
				}
				$linkData = array
				(
					'menuaction'    => 'mail.mail_compose.compose',
					'send_to'	=> base64_encode($senderAddress)
				);
				$windowName = 'compose_'.$header['uid'];

				// sent or drafts or template folder means foldertype > 0, use to address instead of from
				$header2add = $header['to_address'];//mail_bo::htmlentities($header['to_address'],$this->charset);
				$header['to_address'] = $header2add;
				if (!empty($header['to_name'])) {
					$header2name = $header['to_name'];//mail_bo::htmlentities($header['to_name'],$this->charset);
					$header['to_name'] = $header2name;

					$sender_name	= $header['to_name'];
					$full_address	= $header['to_name'].' <'.$header['to_address'].'>';
				} else {
					$sender_name	= $header['to_address'];
					$full_address	= $header['to_address'];
				}
				//$data["toaddress"] = "<nobr><a href=\"#\" onclick=\"fm_handleComposeClick(false,'".$GLOBALS['egw']->link('/index.php',$linkData)."', '".$windowName."', this); return false;\" title=\"".@htmlspecialchars($full_address, ENT_QUOTES | ENT_IGNORE, $this->charset,false)."\">".@htmlspecialchars($sender_name, ENT_QUOTES | ENT_IGNORE, $this->charset,false)."</a></nobr>";
				$data["toaddress"] = $full_address;
			}

			//fromaddress
			if (in_array("fromaddress", $cols))
			{
				$header2add = $header['sender_address'];//mail_bo::htmlentities($header['sender_address'],$this->charset);
				$header['sender_address'] = $header2add;
				if (!empty($header['sender_name'])) {
					$header2name = $header['sender_name'];//mail_bo::htmlentities($header['sender_name'],$this->charset);
					$header['sender_name'] = $header2name;

					$sender_name	= $header['sender_name'];
					$full_address	= $header['sender_name'].' <'.$header['sender_address'].'>';
				} else {
					$sender_name	= $header['sender_address'];
					$full_address	= $header['sender_address'];
				}
				if(!empty($header['sender_name'])) {
					list($mailbox, $host) = explode('@',$header['sender_address']);
					$senderAddress  = imap_rfc822_write_address($mailbox,
							$host,
							$header['sender_name']);
				} else {
					$senderAddress  = $header['sender_address'];
				}
				/*
				$linkData = array
				(
					'menuaction'    => 'mail.mail_compose.compose',
					'send_to'	=> base64_encode($senderAddress)
				);
				$windowName = 'compose_'.$header['uid'];

				$data["fromaddress"] = "<nobr><a href=\"#\" onclick=\"fm_handleComposeClick(false,'".$GLOBALS['egw']->link('/index.php',$linkData)."', '".$windowName."', this); return false;\" title=\"".@htmlspecialchars($full_address, ENT_QUOTES | ENT_IGNORE, $this->charset,false)."\">".@htmlspecialchars($sender_name, ENT_QUOTES | ENT_IGNORE, $this->charset,false)."</a></nobr>";
				*/
				$data["fromaddress"] = $full_address;
			}
			if (in_array("date", $cols))
			{
				$data["date"] = $header['date'];//$dateShort;//'<nobr><span style="font-size:10px" title="'.$dateLong.'">'.$dateShort.'</span></nobr>';
			}
			if (in_array("modified", $cols))
			{
				$data["modified"] = $header['internaldate'];
			}

			if (in_array("size", $cols))
				$data["size"] = $header['size']; /// size


			/*
			//TODO: url_add_to_addressbook isn't in any of the templates.
			//If you want to use it, you need to adopt syntax to the new addressbook (popup)
			$this->t->set_var('url_add_to_addressbook',$GLOBALS['egw']->link('/index.php',$linkData));
			*/
			//$this->t->set_var('msg_icon_sm',$msg_icon_sm);

			//$this->t->set_var('phpgw_images',EGW_IMAGES);
			//$result["data"] = $data;
			$data["class"] = implode(' ', $css_styles);
			$data['attachmentsPresent'] = $imageTag;
			$data['attachmentsBlock'] = $imageHTMLBlock;
			$data['toolbaractions'] = json_encode($actionsenabled);
			$rv[] = $data;
			//error_log(__METHOD__.__LINE__.array2string($result));
		}
		return $rv;
	}

	/**
	 * display messages header lines
	 *
	 * all params are passed as GET Parameters
	 */
	function displayHeader()
	{
		if(isset($_GET['id'])) $rowID	= $_GET['id'];
		if(isset($_GET['part'])) $partID = $_GET['part'];

		$hA = self::splitRowID($rowID);
		$uid = $hA['msgUID'];
		$mailbox = $hA['folder'];

		$this->mail_bo->reopen($mailbox);
		$rawheaders	= $this->mail_bo->getMessageRawHeader($uid, $partID);

		$webserverURL	= $GLOBALS['egw_info']['server']['webserver_url'];

		#$nonDisplayAbleCharacters = array('[\016]','[\017]',
		#		'[\020]','[\021]','[\022]','[\023]','[\024]','[\025]','[\026]','[\027]',
		#		'[\030]','[\031]','[\032]','[\033]','[\034]','[\035]','[\036]','[\037]');

		#print "<pre>";print_r($rawheaders);print"</pre>";exit;

		// add line breaks to $rawheaders
		$newRawHeaders = explode("\n",$rawheaders);
		reset($newRawHeaders);

		// reset $rawheaders
		$rawheaders 	= "";
		// create it new, with good line breaks
		reset($newRawHeaders);
		while(list($key,$value) = @each($newRawHeaders)) {
			$rawheaders .= wordwrap($value, 90, "\n     ");
		}

		$this->mail_bo->closeConnection();

		header('Content-type: text/html; charset=iso-8859-1');
		print '<pre>'. htmlspecialchars($rawheaders, ENT_NOQUOTES, 'iso-8859-1') .'</pre>';

	}

	/**
	 * display messages
	 *
	 * all params are passed as GET Parameters, but can be passed via ExecMethod2 as array too
	 */
	function displayMessage($_requesteddata = null)
	{
//error_log(__METHOD__.__LINE__.array2string($_requesteddata));
//error_log(__METHOD__.__LINE__.array2string($_REQUEST));
		if (!is_null($_requesteddata) && isset($_requesteddata['id']))
		{
			$rowID = $_requesteddata['id'];
			//unset($_REQUEST);
		}
		if(isset($_GET['id'])) $rowID	= $_GET['id'];
		if(isset($_GET['part'])) $partID = $_GET['part'];
		$htmlOptions = $this->mail_bo->htmlOptions;
		if (!empty($_GET['tryastext'])) $htmlOptions  = "only_if_no_text";
		if (!empty($_GET['tryashtml'])) $htmlOptions  = "always_display";

		$hA = self::splitRowID($rowID);
		$uid = $hA['msgUID'];
		$mailbox = $hA['folder'];
		//error_log(__METHOD__.__LINE__.array2string($hA));
		$this->mail_bo->reopen($mailbox);
		// retrieve the flags of the message, before touching it.
		$headers	= $this->mail_bo->getMessageHeader($uid, $partID);
		if (PEAR::isError($headers)) {
			$error_msg[] = lang("ERROR: Message could not be displayed.");
			$error_msg[] = lang("In Mailbox: %1, with ID: %2, and PartID: %3",$mailbox,$uid,$partID);
			$error_msg[] = $headers->message;
			$error_msg[] = array2string($headers->backtrace[0]);
		}
		if (!empty($uid)) $flags = $this->mail_bo->getFlags($uid);
		$envelope	= $this->mail_bo->getMessageEnvelope($uid, $partID,true);
//_debug_array($headers);
		$rawheaders	= $this->mail_bo->getMessageRawHeader($uid, $partID);
		$fetchEmbeddedImages = false;
		if ($htmlOptions !='always_display') $fetchEmbeddedImages = true;
		$attachments	= $this->mail_bo->getMessageAttachments($uid, $partID, '',$fetchEmbeddedImages);
		//_debug_array($headers);
		$attachmentHTMLBlock = self::createAttachmentBlock($attachments, $rowID, $uid, $mailbox);
		$webserverURL	= $GLOBALS['egw_info']['server']['webserver_url'];

		$nonDisplayAbleCharacters = array('[\016]','[\017]',
				'[\020]','[\021]','[\022]','[\023]','[\024]','[\025]','[\026]','[\027]',
				'[\030]','[\031]','[\032]','[\033]','[\034]','[\035]','[\036]','[\037]');

		#print "<pre>";print_r($rawheaders);print"</pre>";exit;
		$mailBody = $this->get_load_email_data($uid, $partID, $mailbox, $htmlOptions,false);
		//error_log(__METHOD__.__LINE__.$mailBody);
		$this->mail_bo->closeConnection();
		$etpl = new etemplate_new('mail.display');
		// Set cell attributes directly
/*
		$etpl->set_cell_attribute('nm[foldertree]','actions', array(
			'drop_move_mail' => array(
				'type' => 'drop',
				'acceptedTypes' => 'mail',
				'icon' => 'move',
				'caption' => 'Move to',
				'onExecute' => 'javaScript:app.mail.mail_move'
			),
		));
*/
		$subject = /*mail_bo::htmlspecialchars(*/$this->mail_bo->decode_subject(preg_replace($nonDisplayAbleCharacters,'',$envelope['SUBJECT']),false)/*,
            mail_bo::$displayCharset)*/;
		if($envelope['FROM'][0] != $envelope['SENDER'][0]) {
			$content['mail_displayfromaddress'] = self::emailAddressToHTML($envelope['SENDER'],'',false,true,false);
			$content['mail_displayonbehalfofaddress'] = self::emailAddressToHTML($envelope['FROM'], $organization,false,true,false);
		} else {
			$content['mail_displayfromaddress'] = self::emailAddressToHTML($envelope['FROM'], $organization,false,true,false);
		}

		// parse the to header
		$content['mail_displaytoaddress'] = self::emailAddressToHTML($envelope['TO'],'',false,true,false);

		// parse the cc header
		if(count($envelope['CC'])) {
			$content['mail_displayccaddress'] = self::emailAddressToHTML($envelope['CC'],'',false,true,false);
		}

		// parse the bcc header
		if(count($envelope['BCC'])) {
			$content['mail_displaybccaddress'] = self::emailAddressToHTML($envelope['BCC'],'',false,true,false);
		}

		// Set up data for taglist widget(s)
		if ($envelope['FROM']==$envelope['SENDER']) unset($envelope['SENDER']);
		foreach(array('SENDER','FROM','TO','CC','BCC') as $field)
		{
			if (!isset($envelope[$field])) continue;
			foreach($envelope[$field] as $field_data)
			{
				//error_log(__METHOD__.__LINE__.array2string($field_data));
				$content[$field][] = $field_data['EMAIL'];
				$sel_options[$field][] = array(
					// taglist requires these
					'id' => $field_data['EMAIL'],
					'label' => ($field_data['PERSONAL_NAME'] && $field_data['PERSONAL_NAME']!='NIL') ? $field_data['PERSONAL_NAME']:$field_data['EMAIL'],
					// Optional
					'title' => str_replace('"',"'",$field_data['RFC822_EMAIL']),
				)
				// Add all other data, will be preserved & passed to js onclick
				// Also available in widget.options.select_options
				+ $field_data;
			}
		}
		$actionsenabled = self::get_actions();
		unset($actionsenabled['mark']['children']['setLabel']);
		unset($actionsenabled['mark']['children']['unsetLabel']);
		unset($actionsenabled['mark']['children']['read']);
		unset($actionsenabled['mark']['children']['unread']);
		unset($actionsenabled['mark']['children']['undelete']);
		$etpl->setElementAttribute('toolbar','actions', $actionsenabled);
		if (empty($subject)) $subject = lang('no subject');
		$content['msg'] = (is_array($error_msg)?implode("<br>",$error_msg):$error_msg);
		// Send mail ID so we can use it for actions
		$content['mail_id'] = $rowID;
		$content['mail_displaydate'] = mail_bo::_strtotime($headers['DATE'],'ts',true);
		$content['mail_displaysubject'] = $subject;
		$content['mail_displaybody'] = $mailBody;
		//_debug_array($attachments);
		$content['mail_displayattachments'] = $attachmentHTMLBlock;
		$content['mail_id']=$rowID;
//_debug_array($content);
		$readonlys = $preserv = $content;
		$etpl->exec('mail.mail_ui.displayMessage',$content,$sel_options,$readonlys,$preserv,2);
	}

	/**
	 * createAttachmentBlock
	 * helper function to create the attachment block/table
	 *
	 * @param array $attachments, array with the attachments information
	 * @param string $rowID, rowid of the message
	 * @param int $uid, uid of the message
	 * @param string $mailbox, the mailbox identifier
	 * @return string html or empty string
	 */
	static function createAttachmentBlock($attachments, $rowID, $uid, $mailbox)
	{
		$attachmentHTMLBlock='';
		if (is_array($attachments) && count($attachments) > 0) {
			$url_img_vfs = html::image('filemanager','navbar', lang('Filemanager'), ' height="16"');
			$url_img_vfs_save_all = html::image('felamimail','save_all', lang('Save all'));

			$detectedCharSet=$charset2use=mail_bo::$displayCharset;
			foreach ($attachments as $key => $value)
			{
				//_debug_array($value);
				#$detectedCharSet = mb_detect_encoding($value['name'].'a',strtoupper($this->displayCharset).",UTF-8, ISO-8559-1");
				if (function_exists('mb_convert_variables')) mb_convert_variables("UTF-8","ISO-8559-1",$value['name']); # iso 2 UTF8
				//if (mb_convert_variables("ISO-8859-1","UTF-8",$value['name'])){echo "Juhu utf8 2 ISO\n";};
				//echo $value['name']."\n";
				$filename=htmlentities($value['name'], ENT_QUOTES, $detectedCharSet);

				$attachmentHTML[$key]['filename']= ($value['name'] ? ( $filename ? $filename : $value['name'] ) : lang('(no subject)'));
				$attachmentHTML[$key]['mimetype']=mime_magic::mime2label($value['mimeType']);
				$attachmentHTML[$key]['size']=egw_vfs::hsize($value['size']);
				$attachmentHTML[$key]['attachment_number']=$key;

				switch(strtoupper($value['mimeType']))
				{
					case 'MESSAGE/RFC822':
						$linkData = array
						(
							'menuaction'	=> 'mail.mail_ui.displayMessage',
							'id'		=> $rowID,
							'part'		=> $value['partID'],
							'mailbox'	=> base64_encode($mailbox),
							'is_winmail'    => $value['is_winmail']
						);
						$windowName = 'displayMessage_'. $rowID.'_'.$value['partID'];
						$linkView = "egw_openWindowCentered('".$GLOBALS['egw']->link('/index.php',$linkData)."','$windowName',700,egw_getWindowOuterHeight());";
						break;
					case 'IMAGE/JPEG':
					case 'IMAGE/PNG':
					case 'IMAGE/GIF':
					case 'IMAGE/BMP':
					case 'APPLICATION/PDF':
					case 'TEXT/PLAIN':
					case 'TEXT/HTML':
					case 'TEXT/DIRECTORY':
						$sfxMimeType = $value['mimeType'];
						$buff = explode('.',$value['name']);
						$suffix = '';
						if (is_array($buff)) $suffix = array_pop($buff); // take the last extension to check with ext2mime
						if (!empty($suffix)) $sfxMimeType = mime_magic::ext2mime($suffix);
						if (strtoupper($sfxMimeType) == 'TEXT/VCARD' || strtoupper($sfxMimeType) == 'TEXT/X-VCARD')
						{
							$attachments[$key]['mimeType'] = $sfxMimeType;
							$value['mimeType'] = strtoupper($sfxMimeType);
						}
					case 'TEXT/X-VCARD':
					case 'TEXT/VCARD':
					case 'TEXT/CALENDAR':
					case 'TEXT/X-VCALENDAR':
						$linkData = array
						(
							'menuaction'	=> 'mail.mail_ui.getAttachment',
							'id'		=> $rowID,
							'part'		=> $value['partID'],
							'is_winmail'    => $value['is_winmail'],
							'mailbox'   => base64_encode($mailbox),
						);
						$windowName = 'displayAttachment_'. $uid;
						$reg = '800x600';
						// handle calendar/vcard
						if (strtoupper($value['mimeType'])=='TEXT/CALENDAR')
						{
							$windowName = 'displayEvent_'. $rowID;
							$reg2 = egw_link::get_registry('calendar','view_popup');
						}
						if (strtoupper($value['mimeType'])=='TEXT/X-VCARD' || strtoupper($value['mimeType'])=='TEXT/VCARD')
						{
							$windowName = 'displayContact_'. $rowID;
							$reg2 = egw_link::get_registry('addressbook','add_popup');
						}
						// apply to action
						list($width,$height) = explode('x',(!empty($reg2) ? $reg2 : $reg));
						$linkView = "egw_openWindowCentered('".$GLOBALS['egw']->link('/index.php',$linkData)."','$windowName',$width,$height);";
						break;
					default:
						$linkData = array
						(
							'menuaction'	=> 'mail.mail_ui.getAttachment',
							'id'		=> $rowID,
							'part'		=> $value['partID'],
							'is_winmail'    => $value['is_winmail'],
							'mailbox'   => base64_encode($mailbox),
						);
						$linkView = "window.location.href = '".$GLOBALS['egw']->link('/index.php',$linkData)."';";
						break;
				}
				//error_log(__METHOD__.__LINE__.$linkView);
				$attachmentHTML[$key]['link_view'] = '<a href="#" ." title="'.$attachmentHTML[$key]['filename'].'" onclick="'.$linkView.' return false;"><b>'.
					($value['name'] ? ( $filename ? $filename : $value['name'] ) : lang('(no subject)')).
					'</b></a>';

				$linkData = array
				(
					'menuaction'	=> 'mail.mail_ui.getAttachment',
					'mode'		=> 'save',
					'id'		=> $rowID,
					'part'		=> $value['partID'],
					'is_winmail'    => $value['is_winmail'],
					'mailbox'   => base64_encode($mailbox),
				);
				$attachmentHTML[$key]['link_save'] ="<a href='".$GLOBALS['egw']->link('/index.php',$linkData)."' title='".$attachmentHTML[$key]['filename']."'>".html::image('felamimail','fileexport')."</a>";

				if ($GLOBALS['egw_info']['user']['apps']['filemanager'])
				{
					$link_vfs_save = egw::link('/index.php',array(
						'menuaction' => 'filemanager.filemanager_select.select',
						'mode' => 'saveas',
						'name' => $value['name'],
						'mime' => strtolower($value['mimeType']),
						'method' => 'mail.mail_ui.vfsSaveAttachment',
						'id' => $rowID.'::'.$value['partID'].'::'.$value['is_winmail'],
						'label' => lang('Save'),
					));
					$vfs_save = "<a href='#' onclick=\"egw_openWindowCentered('$link_vfs_save','vfs_save_attachment','640','570',window.outerWidth/2,window.outerHeight/2); return false;\">$url_img_vfs</a>";
					// add save-all icon for first attachment
					if (!$key && count($attachments) > 1)
					{
						foreach ($attachments as $ikey => $value)
						{
							//$rowID
							$ids["id[$ikey]"] = $rowID.'::'.$value['partID'].'::'.$value['is_winmail'].'::'.$value['name'];
						}
						$link_vfs_save = egw::link('/index.php',array(
							'menuaction' => 'filemanager.filemanager_select.select',
							'mode' => 'select-dir',
							'method' => 'mail.mail_ui.vfsSaveAttachment',
							'label' => lang('Save all'),
						)+$ids);
						$vfs_save .= "<a href='#' onclick=\"egw_openWindowCentered('$link_vfs_save','vfs_save_attachment','640','530',window.outerWidth/2,window.outerHeight/2); return false;\">$url_img_vfs_save_all</a>";
					}
					$attachmentHTML[$key]['link_save'] .= $vfs_save;
					//error_log(__METHOD__.__LINE__.$attachmentHTML[$key]['link_save']);
				}
			}
			$attachmentHTMLBlock="<table width='100%'>";
			foreach ((array)$attachmentHTML as $row)
			{
				$attachmentHTMLBlock .= "<tr><td><div class='useEllipsis'>".$row['link_view'].'</div></td>';
				$attachmentHTMLBlock .= "<td>".$row['mimetype'].'</td>';
				$attachmentHTMLBlock .= "<td>".$row['size'].'</td>';
				$attachmentHTMLBlock .= "<td>".$row['link_save'].'</td></tr>';
			}
			$attachmentHTMLBlock .= "</table>";
		}
		return $attachmentHTMLBlock;
	}

	/**
	 * emailAddressToHTML
	 *
	 *
	 */
	static function emailAddressToHTML($_emailAddress, $_organisation='', $allwaysShowMailAddress=false, $showAddToAdrdessbookLink=true, $decode=true) {
		//_debug_array($_emailAddress);
		// create some nice formated HTML for senderaddress

		if(is_array($_emailAddress)) {
			$senderAddress = '';
			foreach($_emailAddress as $addressData) {
				#_debug_array($addressData);
				if($addressData['MAILBOX_NAME'] == 'NIL') {
					continue;
				}

				if(!empty($senderAddress)) $senderAddress .= ', ';

				if(strtolower($addressData['MAILBOX_NAME']) == 'undisclosed-recipients') {
					$senderAddress .= 'undisclosed-recipients';
					continue;
				}
				if($addressData['PERSONAL_NAME'] != 'NIL') {
					$newSenderAddressORG = $newSenderAddress = $addressData['RFC822_EMAIL'] != 'NIL' ? $addressData['RFC822_EMAIL'] : $addressData['EMAIL'];
					$decodedPersonalNameORG = $decodedPersonalName = $addressData['PERSONAL_NAME'];
					if ($decode)
					{
						$newSenderAddress = mail_bo::decode_header($newSenderAddressORG);
						$decodedPersonalName = mail_bo::decode_header($decodedPersonalName);
						$addressData['EMAIL'] = mail_bo::decode_header($addressData['EMAIL'],true);
					}
					$realName =  $decodedPersonalName;
					// add mailaddress
					if ($allwaysShowMailAddress) {
						$realName .= ' <'.$addressData['EMAIL'].'>';
						$decodedPersonalNameORG .= ' <'.$addressData['EMAIL'].'>';
					}
					// add organization
					if(!empty($_organisation)) {
						$realName .= ' ('. $_organisation . ')';
						$decodedPersonalNameORG .= ' ('. $_organisation . ')';
					}
					$addAction = egw_link::get_registry('mail','add');
					$linkData = array (
						'menuaction'	=> $addAction,
						'send_to'	=> base64_encode($newSenderAddress)
					);
					$link = $GLOBALS['egw']->link('/index.php',$linkData);

					$newSenderAddress = mail_bo::htmlentities($newSenderAddress);
					$realName = mail_bo::htmlentities($realName);

					$senderAddress .= sprintf('<a href="%s" title="%s">%s</a>',
								$link,
								$newSenderAddress,
								$realName);

					$linkData = array (
						'menuaction'		=> 'addressbook.addressbook_ui.edit',
						'presets[email]'	=> $addressData['EMAIL'],
						'presets[org_name]'	=> $_organisation,
						'referer'		=> $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']
					);

					$decodedPersonalName = $realName;
					if (!empty($decodedPersonalName)) {
						if($spacePos = strrpos($decodedPersonalName, ' ')) {
							$linkData['presets[n_family]']	= substr($decodedPersonalName, $spacePos+1);
							$linkData['presets[n_given]'] 	= substr($decodedPersonalName, 0, $spacePos);
						} else {
							$linkData['presets[n_family]']	= $decodedPersonalName;
						}
						$linkData['presets[n_fn]']	= $decodedPersonalName;
					}

					if ($showAddToAdrdessbookLink && $GLOBALS['egw_info']['user']['apps']['addressbook']) {
						$urlAddToAddressbook = $GLOBALS['egw']->link('/index.php',$linkData);
						$onClick = "window.open(this,this.target,'dependent=yes,width=850,height=440,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes'); return false;";
						$image = $GLOBALS['egw']->common->image('felamimail','sm_envelope');
						$senderAddress .= sprintf('<a href="%s" onClick="%s">
							<img src="%s" width="10" height="8" border="0"
							align="absmiddle" alt="%s"
							title="%s"></a>',
							$urlAddToAddressbook,
							$onClick,
							$image,
							lang('add to addressbook'),
							lang('add to addressbook'));
					}
				} else {
					$addrEMailORG = $addrEMail = $addressData['EMAIL'];
					$addAction = egw_link::get_registry('mail','add');
					if ($decode) $addrEMail = mail_bo::decode_header($addrEMail,true);
					$linkData = array (
						'menuaction'	=> $addAction,
						'send_to'	=> base64_encode($addressData['EMAIL'])
					);
					$link = $GLOBALS['egw']->link('/index.php',$linkData);
					$senderEMail = mail_bo::htmlentities($addrEMail);
					$senderAddress .= sprintf('<a href="%s">%s</a>',
								$link,$senderEMail);
					//TODO: This uses old addressbook code, which should be removed in Version 1.4
					//Please use addressbook.addressbook_ui.edit with proper paramenters
					$linkData = array
					(
						'menuaction'		=> 'addressbook.addressbook_ui.edit',
						'presets[email]'	=> $senderEMail, //$addressData['EMAIL'],
						'presets[org_name]'	=> $_organisation,
						'referer'		=> $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']
					);

					if ($showAddToAdrdessbookLink && $GLOBALS['egw_info']['user']['apps']['addressbook']) {
						$urlAddToAddressbook = $GLOBALS['egw']->link('/index.php',$linkData);
						$onClick = "window.open(this,this.target, 'dependent=yes, width=850, height=440, location=no, menubar=no, toolbar=no, scrollbars=yes, status=yes'); return false;";
						$image = $GLOBALS['egw']->common->image('felamimail','sm_envelope');
						$senderAddress .= sprintf('<a href="%s" onClick="%s">
							<img src="%s" width="10" height="8" border="0"
							align="absmiddle" alt="%s"
							title="%s"></a>',
							$urlAddToAddressbook,
							$onClick,
							$image,
							lang('add to addressbook'),
							lang('add to addressbook'));
					}
				}
			}
			return $senderAddress;
		}

		// if something goes wrong, just return the original address
		return $_emailAddress;
	}

	/**
	 * display image
	 *
	 *
	 */
	function quotaDisplay($_usage, $_limit)
	{

		if($_limit == 0) {
			$quotaPercent=100;
		} else {
			$quotaPercent=round(($_usage*100)/$_limit);
		}

		$quotaLimit=mail_bo::show_readable_size($_limit*1024);
		$quotaUsage=mail_bo::show_readable_size($_usage*1024);


		if($quotaPercent > 90 && $_limit>0) {
			$quotaBG='mail-index_QuotaRed';
		} elseif($quotaPercent > 80 && $_limit>0) {
			$quotaBG='mail-index_QuotaYellow';
		} else {
			$quotaBG='mail-index_QuotaGreen';
		}

		if($_limit > 0) {
			$quotaText = $quotaUsage .'/'.$quotaLimit;
		} else {
			$quotaText = $quotaUsage;
		}

		if($quotaPercent > 50) {
		} else {
		}
		$quota['class'] = $quotaBG;
		$quota['text'] = $quotaText;
		$quota['percent'] = (string)round(($_usage*100)/$_limit);//($_usage/$_limit*100);
		return $quota;
	}

	/**
	 * display image
	 *
	 * all params are passed as GET Parameters
	 */
	function displayImage()
	{
		$uid	= $_GET['uid'];
		$cid	= base64_decode($_GET['cid']);
		$partID = urldecode($_GET['partID']);
		if (!empty($_GET['mailbox'])) $mailbox  = base64_decode($_GET['mailbox']);
		//error_log(__METHOD__.__LINE__.":$uid, $cid, $partID");
		$this->mail_bo->reopen($mailbox);

		$attachment 	= $this->mail_bo->getAttachmentByCID($uid, $cid, $partID);

		$this->mail_bo->closeConnection();

		$GLOBALS['egw']->session->commit_session();

		if(is_array($attachment)) {
			//error_log("Content-Type: ".$attachment['type']."; name=\"". $attachment['filename'] ."\"");
			header ("Content-Type: ". strtolower($attachment['type']) ."; name=\"". $attachment['filename'] ."\"");
			header ('Content-Disposition: inline; filename="'. $attachment['filename'] .'"');
			header("Expires: 0");
			// the next headers are for IE and SSL
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: public");

			echo trim($attachment['attachment']);
			exit;
		}

		$GLOBALS['egw']->common->egw_exit();

		exit;
	}

	function getAttachment()
	{

		if(isset($_GET['id'])) $rowID	= $_GET['id'];
		if(isset($_GET['part'])) $partID = $_GET['part'];

		$hA = self::splitRowID($rowID);
		$uid = $hA['msgUID'];
		$mailbox = $hA['folder'];
		$part		= $_GET['part'];
		$is_winmail = $_GET['is_winmail'] ? $_GET['is_winmail'] : 0;

		$this->mail_bo->reopen($mailbox);
		$attachment = $this->mail_bo->getAttachment($uid,$part,$is_winmail);
		$this->mail_bo->closeConnection();

		$GLOBALS['egw']->session->commit_session();
		if ($_GET['mode'] != "save")
		{
			if (strtoupper($attachment['type']) == 'TEXT/DIRECTORY')
			{
				$sfxMimeType = $attachment['type'];
				$buff = explode('.',$attachment['filename']);
				$suffix = '';
				if (is_array($buff)) $suffix = array_pop($buff); // take the last extension to check with ext2mime
				if (!empty($suffix)) $sfxMimeType = mime_magic::ext2mime($suffix);
				$attachment['type'] = $sfxMimeType;
				if (strtoupper($sfxMimeType) == 'TEXT/VCARD' || strtoupper($sfxMimeType) == 'TEXT/X-VCARD') $attachment['type'] = strtoupper($sfxMimeType);
			}
			//error_log(__METHOD__.print_r($attachment,true));
			if (strtoupper($attachment['type']) == 'TEXT/CALENDAR' || strtoupper($attachment['type']) == 'TEXT/X-VCALENDAR')
			{
				//error_log(__METHOD__."about to call calendar_ical");
				$calendar_ical = new calendar_ical();
				$eventid = $calendar_ical->search($attachment['attachment'],-1);
				//error_log(__METHOD__.array2string($eventid));
				if (!$eventid) $eventid = -1;
				$event = $calendar_ical->importVCal($attachment['attachment'],(is_array($eventid)?$eventid[0]:$eventid),null,true);
				//error_log(__METHOD__.$event);
				if ((int)$event > 0)
				{
					$vars = array(
						'menuaction'      => 'calendar.calendar_uiforms.edit',
						'cal_id'      => $event,
					);
					$GLOBALS['egw']->redirect_link('../index.php',$vars);
				}
				//Import failed, download content anyway
			}
			if (strtoupper($attachment['type']) == 'TEXT/X-VCARD' || strtoupper($attachment['type']) == 'TEXT/VCARD')
			{
				$addressbook_vcal = new addressbook_vcal();
				// double \r\r\n seems to end a vcard prematurely, so we set them to \r\n
				//error_log(__METHOD__.__LINE__.$attachment['attachment']);
				$attachment['attachment'] = str_replace("\r\r\n", "\r\n", $attachment['attachment']);
				$vcard = $addressbook_vcal->vcardtoegw($attachment['attachment']);
				if ($vcard['uid'])
				{
					$vcard['uid'] = trim($vcard['uid']);
					//error_log(__METHOD__.__LINE__.print_r($vcard,true));
					$contact = $addressbook_vcal->find_contact($vcard,false);
				}
				if (!$contact) $contact = null;
				// if there are not enough fields in the vcard (or the parser was unable to correctly parse the vcard (as of VERSION:3.0 created by MSO))
				if ($contact || count($vcard)>2)
				{
					$contact = $addressbook_vcal->addVCard($attachment['attachment'],(is_array($contact)?array_shift($contact):$contact),true);
				}
				if ((int)$contact > 0)
				{
					$vars = array(
						'menuaction'	=> 'addressbook.addressbook_ui.edit',
						'contact_id'	=> $contact,
					);
					$GLOBALS['egw']->redirect_link('../index.php',$vars);
				}
				//Import failed, download content anyway
			}
		}
		header ("Content-Type: ".$attachment['type']."; name=\"". $attachment['filename'] ."\"");
		if($_GET['mode'] == "save") {
			// ask for download
			header ("Content-Disposition: attachment; filename=\"". $attachment['filename'] ."\"");
		} else {
			// display it
			header ("Content-Disposition: inline; filename=\"". $attachment['filename'] ."\"");
		}
		header("Expires: 0");
		// the next headers are for IE and SSL
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: public");

		echo $attachment['attachment'];

		$GLOBALS['egw']->common->egw_exit();
		exit;
	}


	/**
	 * save messages on disk or filemanager, or display it in popup
	 *
	 * all params are passed as GET Parameters
	 */
	function saveMessage()
	{
		$display = false;
		if(isset($_GET['id'])) $rowID	= $_GET['id'];
		if(isset($_GET['part'])) $partID		= $_GET['part'];
		if (isset($_GET['location'])&& ($_GET['location']=='display'||$_GET['location']=='filemanager')) $display	= $_GET['location'];

		$hA = self::splitRowID($rowID);
		$uid = $hA['msgUID'];
		$mailbox = $hA['folder'];

		$this->mail_bo->reopen($mailbox);

		$message = $this->mail_bo->getMessageRawBody($uid, $partID);
		$headers = $this->mail_bo->getMessageHeader($uid, $partID);

		$this->mail_bo->closeConnection();

		$GLOBALS['egw']->session->commit_session();
		if ($display==false)
		{
			$subject = str_replace('$$','__',mail_bo::decode_header($headers['SUBJECT']));
			header ("Content-Type: message/rfc822; name=\"". $subject .".eml\"");
			header ("Content-Disposition: attachment; filename=\"". $subject .".eml\"");
			header("Expires: 0");
			// the next headers are for IE and SSL
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: public");

			echo $message;

			$GLOBALS['egw']->common->egw_exit();
			exit;
		}
		//elseif ($display=='filemanager') // done in vfsSaveMessage
		//{
		//}
		else
		{
			header('Content-type: text/html; charset=iso-8859-1');
			print '<pre>'. htmlspecialchars($message, ENT_NOQUOTES, 'iso-8859-1') .'</pre>';
		}
	}

	/**
	 * Save an Message in the vfs
	 *
	 * @param string|array $ids use splitRowID, to separate values
	 * @param string $path path in vfs (no egw_vfs::PREFIX!), only directory for multiple id's ($ids is an array)
	 * @return string javascript eg. to close the selector window
	 */
	function vfsSaveMessage($ids,$path)
	{
		error_log(__METHOD__.' IDs:'.array2string($ids).' SaveToPath:'.$path);

		if (is_array($ids) && !egw_vfs::is_writable($path) || !is_array($ids) && !egw_vfs::is_writable(dirname($path)))
		{
			return 'alert("'.addslashes(lang('%1 is NOT writable by you!',$path)).'"); window.close();';
		}
		foreach((array)$ids as $id)
		{
			$hA = self::splitRowID($id);
			$uid = $hA['msgUID'];
			$mailbox = $hA['folder'];
			if ($mb != $this->mail_bo->mailbox) $this->mail_bo->reopen($mb = $mailbox);
			$message = $this->mail_bo->getMessageRawBody($uid, $partID='');
			if (!($fp = egw_vfs::fopen($file=$path.($name ? '/'.$name : ''),'wb')) ||
				!fwrite($fp,$message))
			{
				$err .= 'alert("'.addslashes(lang('Error saving %1!',$file)).'");';
				$succeeded = false;
			}
			else
			{
				$succeeded = true;
			}
			if ($fp) fclose($fp);
			if ($succeeded)
			{
				translation::add_app('mail');
				$headers = $this->bmail_bo->getMessageHeader($uid,$partID,true);
				unset($headers['SUBJECT']);//already in filename
				$infoSection = mail_bo::createHeaderInfoSection($headers, 'SUPPRESS', false);
				$props = array(array('name' => 'comment','val' => $infoSection));
				egw_vfs::proppatch($file,$props);
			}
		}
		//$this->mail_bo->closeConnection();

		return $err.'window.close();';
	}

	/**
	 * Save an attachment in the vfs
	 *
	 * @param string|array $ids '::' delimited mailbox::uid::part-id::is_winmail::name (::name for multiple id's)
	 * @param string $path path in vfs (no egw_vfs::PREFIX!), only directory for multiple id's ($ids is an array)
	 * @return string javascript eg. to close the selector window
	 */
	function vfsSaveAttachment($ids,$path)
	{
		error_log(__METHOD__.__LINE__.'("'.array2string($ids).'","'.$path."\")');");

		if (is_array($ids) && !egw_vfs::is_writable($path) || !is_array($ids) && !egw_vfs::is_writable(dirname($path)))
		{
			return 'alert("'.addslashes(lang('%1 is NOT writable by you!',$path)).'"); window.close();';
		}
		foreach((array)$ids as $id)
		{
			list($app,$user,$serverID,$mailbox,$uid,$part,$is_winmail,$name) = explode('::',$id,8);
			$lId = implode('::',array($app,$user,$serverID,$mailbox,$uid));
			$hA = self::splitRowID($lId);
			$uid = $hA['msgUID'];
			$mailbox = $hA['folder'];
			//error_log(__METHOD__.__LINE__.array2string($hA));
			$this->mail_bo->reopen($mailbox);
			$attachment = $this->mail_bo->getAttachment($uid,$part,$is_winmail);

			if (!($fp = egw_vfs::fopen($file=$path.($name ? '/'.$name : ''),'wb')) ||
				!fwrite($fp,$attachment['attachment']))
			{
				$err .= 'alert("'.addslashes(lang('Error saving %1!',$file)).'");';
			}
			if ($fp) fclose($fp);
		}
		$this->mail_bo->closeConnection();

		return $err.'window.close();';
	}


	function get_load_email_data($uid, $partID, $mailbox,$htmlOptions=null,$fullHeader=true)
	{
		// seems to be needed, as if we open a mail from notification popup that is
		// located in a different folder, we experience: could not parse message
		$this->mail_bo->reopen($mailbox);
$this->mailbox = $mailbox;
$this->uid = $uid;
$this->partID = $partID;
		$bufferHtmlOptions = $this->mail_bo->htmlOptions;
		if (empty($htmlOptions)) $htmlOptions = $this->mail_bo->htmlOptions;
		$bodyParts	= $this->mail_bo->getMessageBody($uid, ($htmlOptions?$htmlOptions:''), $partID, '', false, $mailbox);
		//error_log(__METHOD__.__LINE__.array2string($bodyParts));
		$meetingRequest = false;
		$fetchEmbeddedImages = false;
		if ($htmlOptions !='always_display') $fetchEmbeddedImages = true;
		$attachments    = $this->mail_bo->getMessageAttachments($uid, $partID, '',$fetchEmbeddedImages,true);
		foreach ((array)$attachments as $key => $attach)
		{
			if (strtolower($attach['mimeType']) == 'text/calendar' &&
				(strtolower($attach['method']) == 'request' || strtolower($attach['method']) == 'reply') &&
				isset($GLOBALS['egw_info']['user']['apps']['calendar']) &&
				($attachment = $this->mail_bo->getAttachment($uid, $attach['partID'])))
			{
				//error_log(__METHOD__.__LINE__.array2string($attachment));
				egw_cache::setSession('calendar', 'ical', array(
					'charset' => $attach['charset'] ? $attach['charset'] : 'utf-8',
					'attachment' => $attachment['attachment'],
					'method' => $attach['method'],
					'sender' => $sender,
				));
				$this->mail_bo->htmlOptions = $bufferHtmlOptions;
				return ExecMethod( 'calendar.calendar_uiforms.meeting',
					array('event'=>null,'msg'=>'','useSession'=>true)
				);
			}
		}

		// Compose the content of the frame
		$frameHtml =
			$this->get_email_header($this->mail_bo->getStyles($bodyParts),$fullHeader).
			$this->showBody($this->getdisplayableBody($bodyParts), false,$fullHeader);
		//IE10 eats away linebreaks preceeded by a whitespace in PRE sections
		$frameHtml = str_replace(" \r\n","\r\n",$frameHtml);
		$this->mail_bo->htmlOptions = $bufferHtmlOptions;

		return $frameHtml;
	}

	static function get_email_header($additionalStyle='',$fullHeader=true)
	{
		//error_log(__METHOD__.__LINE__.$additionalStyle);
		$header = ($fullHeader?'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />':'').'
		<style>
			body, td, textarea {
				font-family: Verdana, Arial, Helvetica,sans-serif;
				font-size: 11px;
			}
		</style>'.$additionalStyle.'
		<script type="text/javascript">
			function GoToAnchor(aname)
			{
				window.location.hash=aname;
			}
		</script>'.($fullHeader?'
	</head>
	<body>
':'');
		return $header;
	}

	function showBody(&$body, $print=true,$fullPageTags=true)
	{
		$BeginBody = '<style type="text/css">
body,html {
    height:100%;
    width:100%;
    padding:0px;
    margin:0px;
}
.td_display {
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 120%;
    color: black;
    background-color: #FFFFFF;
}
pre {
	white-space: pre-wrap; /* Mozilla, since 1999 */
	white-space: -pre-wrap; /* Opera 4-6 */
	white-space: -o-pre-wrap; /* Opera 7 */
	width: 99%;
}
blockquote[type=cite] {
	margin: 0;
	border-left: 2px solid blue;
	padding-left: 10px;
	margin-left: 0;
	color: blue;
}
</style>
<div style="height:100%;width:100%; background-color:white; padding:0px; margin:0px;">
 <table width="100%" style="table-layout:fixed"><tr><td class="td_display">';

		$EndBody = '</td></tr></table></div>';
		if ($fullPageTags) $EndBody .= "</body></html>";
		if ($print)	{
			print $BeginBody. $body .$EndBody;
		} else {
			return $BeginBody. $body .$EndBody;
		}
	}

	function &getdisplayableBody($_bodyParts,$modifyURI=true)
	{
		$bodyParts	= $_bodyParts;

		$webserverURL	= $GLOBALS['egw_info']['server']['webserver_url'];

		$nonDisplayAbleCharacters = array('[\016]','[\017]',
				'[\020]','[\021]','[\022]','[\023]','[\024]','[\025]','[\026]','[\027]',
				'[\030]','[\031]','[\032]','[\033]','[\034]','[\035]','[\036]','[\037]');

		$body = '';

		//error_log(__METHOD__.array2string($bodyParts)); //exit;
		if (empty($bodyParts)) return "";
		foreach((array)$bodyParts as $singleBodyPart) {
			if (!isset($singleBodyPart['body'])) {
				$singleBodyPart['body'] = $this->getdisplayableBody($singleBodyPart,$modifyURI);
				$body .= $singleBodyPart['body'];
				continue;
			}
			$bodyPartIsSet = strlen(trim($singleBodyPart['body']));
			if (!$bodyPartIsSet)
			{
				$body .= '';
				continue;
			}
			if(!empty($body)) {
				$body .= '<hr style="border:dotted 1px silver;">';
			}
			//_debug_array($singleBodyPart['charSet']);
			//_debug_array($singleBodyPart['mimeType']);
			//error_log($singleBodyPart['body']);
			//error_log(__METHOD__.__LINE__.' CharSet:'.$singleBodyPart['charSet'].' mimeType:'.$singleBodyPart['mimeType']);
			// some characterreplacements, as they fail to translate
			$sar = array(
				'@(\x84|\x93|\x94)@',
				'@(\x96|\x97|\x1a)@',
				'@(\x82|\x91|\x92)@',
				'@(\x85)@',
				'@(\x86)@',
				'@(\x99)@',
				'@(\xae)@',
			);
			$rar = array(
				'"',
				'-',
				'\'',
				'...',
				'&',
				'(TM)',
				'(R)',
			);

			if(($singleBodyPart['mimeType'] == 'text/html' || $singleBodyPart['mimeType'] == 'text/plain') &&
				strtoupper($singleBodyPart['charSet']) != 'UTF-8')
			{
				$singleBodyPart['body'] = preg_replace($sar,$rar,$singleBodyPart['body']);
			}
			if ($singleBodyPart['charSet']===false) $singleBodyPart['charSet'] = translation::detect_encoding($singleBodyPart['body']);
			$singleBodyPart['body'] = $GLOBALS['egw']->translation->convert(
				$singleBodyPart['body'],
				strtolower($singleBodyPart['charSet'])
			);
			// in a way, this tests if we are having real utf-8 (the displayCharset) by now; we should if charsets reported (or detected) are correct
			if (strtoupper(mail_bo::$displayCharset) == 'UTF-8')
			{
				$test = @json_encode($singleBodyPart['body']);
				//error_log(__METHOD__.__LINE__.' ->'.strlen($singleBodyPart['body']).' Error:'.json_last_error().'<- BodyPart:#'.$test.'#');
				//if (json_last_error() != JSON_ERROR_NONE && strlen($singleBodyPart['body'])>0)
				if (($test=="null" || $test === false || is_null($test)) && strlen($singleBodyPart['body'])>0)
				{
					// try to fix broken utf8
					$x = (function_exists('mb_convert_encoding')?mb_convert_encoding($singleBodyPart['body'],'UTF-8','UTF-8'):(function_exists('iconv')?@iconv("UTF-8","UTF-8//IGNORE",$singleBodyPart['body']):$singleBodyPart['body']));
					$test = @json_encode($x);
					if (($test=="null" || $test === false || is_null($test)) && strlen($singleBodyPart['body'])>0)
					{
						// this should not be needed, unless something fails with charset detection/ wrong charset passed
						error_log(__METHOD__.__LINE__.' Charset Reported:'.$singleBodyPart['charSet'].' Charset Detected:'.translation::detect_encoding($singleBodyPart['body']));
						$singleBodyPart['body'] = utf8_encode($singleBodyPart['body']);
					}
					else
					{
						$singleBodyPart['body'] = $x;
					}
				}
			}
			//error_log(__METHOD__.__LINE__.array2string($singleBodyPart));
			#$CharSetUsed = mb_detect_encoding($singleBodyPart['body'] . 'a' , strtoupper($singleBodyPart['charSet']).','.strtoupper(mail_bo::$displayCharset).',UTF-8, ISO-8859-1');

			if($singleBodyPart['mimeType'] == 'text/plain')
			{
				//$newBody	= $singleBodyPart['body'];

				$newBody	= @htmlentities($singleBodyPart['body'],ENT_QUOTES, strtoupper(mail_bo::$displayCharset));
				// if empty and charset is utf8 try sanitizing the string in question
				if (empty($newBody) && strtolower($singleBodyPart['charSet'])=='utf-8') $newBody = @htmlentities(iconv('utf-8', 'utf-8', $singleBodyPart['body']),ENT_QUOTES, strtoupper(mail_bo::$displayCharset));
				// if the conversion to htmlentities fails somehow, try without specifying the charset, which defaults to iso-
				if (empty($newBody)) $newBody    = htmlentities($singleBodyPart['body'],ENT_QUOTES);

				// search http[s] links and make them as links available again
				// to understand what's going on here, have a look at
				// http://www.php.net/manual/en/function.preg-replace.php

				// create links for websites
				if ($modifyURI) $newBody = html::activate_links($newBody);
				// redirect links for websites if you use no cookies
				#if (!($GLOBALS['egw_info']['server']['usecookies']))
				#	$newBody = preg_replace("/href=(\"|\')((http(s?):\/\/)|(www\.))([\w,\-,\/,\?,\=,\.,&amp;,!\n,\%,@,\(,\),\*,#,:,~,\+]+)(\"|\')/ie",
				#		"'href=\"$webserverURL/redirect.php?go='.@htmlentities(urlencode('http$4://$5$6'),ENT_QUOTES,\"mail_bo::$displayCharset\").'\"'", $newBody);

				// create links for email addresses
				//TODO:if ($modifyURI) $this->parseEmail($newBody);
				// create links for inline images
				if ($modifyURI)
				{
					$newBody = preg_replace_callback("/\[cid:(.*)\]/iU",array($this,'image_callback_plain'),$newBody);
				}

				//TODO:$newBody	= $this->highlightQuotes($newBody);
				// to display a mailpart of mimetype plain/text, may be better taged as preformatted
				#$newBody	= nl2br($newBody);
				// since we do not display the message as HTML anymore we may want to insert good linebreaking (for visibility).
				//error_log($newBody);
				// dont break lines that start with > (&gt; as the text was processed with htmlentities before)
				$newBody	= "<pre>".mail_bo::wordwrap($newBody,90,"\n",'&gt;')."</pre>";
				//$newBody   = "<pre>".$newBody."</pre>";
			}
			else
			{
				$newBody	= $singleBodyPart['body'];
				//TODO:$newBody	= $this->highlightQuotes($newBody);
				#error_log(print_r($newBody,true));

				// do the cleanup, set for the use of purifier
				$usepurifier = true;
				$newBodyBuff = $newBody;
				mail_bo::getCleanHTML($newBody,$usepurifier);
				// in a way, this tests if we are having real utf-8 (the displayCharset) by now; we should if charsets reported (or detected) are correct
				if (strtoupper(mail_bo::$displayCharset) == 'UTF-8')
				{
					$test = @json_encode($newBody);
					//error_log(__METHOD__.__LINE__.' ->'.strlen($singleBodyPart['body']).' Error:'.json_last_error().'<- BodyPart:#'.$test.'#');
					//if (json_last_error() != JSON_ERROR_NONE && strlen($singleBodyPart['body'])>0)
					if (($test=="null" || $test === false || is_null($test)) && strlen($newBody)>0)
					{
						$newBody = $newBodyBuff;
						$tv = mail_bo::$htmLawed_config['tidy'];
						mail_bo::$htmLawed_config['tidy'] = 0;
						mail_bo::getCleanHTML($newBody,$usepurifier);
						mail_bo::$htmLawed_config['tidy'] = $tv;
					}
				}

				// removes stuff between http and ?http
				$Protocol = '(http:\/\/|(ftp:\/\/|https:\/\/))';    // only http:// gets removed, other protocolls are shown
				$newBody = preg_replace('~'.$Protocol.'[^>]*\?'.$Protocol.'~sim','$1',$newBody); // removes stuff between http:// and ?http://
				// TRANSFORM MAILTO LINKS TO EMAILADDRESS ONLY, WILL BE SUBSTITUTED BY parseEmail TO CLICKABLE LINK
				$newBody = preg_replace('/(?<!"|href=|href\s=\s|href=\s|href\s=)'.'mailto:([a-z0-9._-]+)@([a-z0-9_-]+)\.([a-z0-9._-]+)/i',
					"\\1@\\2.\\3",
					$newBody);

				// redirect links for websites if you use no cookies
				#if (!($GLOBALS['egw_info']['server']['usecookies'])) { //do it all the time, since it does mask the mailadresses in urls
					//TODO:if ($modifyURI) $this->parseHREF($newBody);
				#}
				// create links for inline images
				if ($modifyURI)
				{
					$newBody = preg_replace_callback("/src=(\"|\')cid:(.*)(\"|\')/iU",array($this,'image_callback'),$newBody);
					$newBody = preg_replace_callback("/url\(cid:(.*)\);/iU",array($this,'image_callback_url'),$newBody);
					$newBody = preg_replace_callback("/background=(\"|\')cid:(.*)(\"|\')/iU",array($this,'image_callback_background'),$newBody);
				}
				$addAction = egw_link::get_registry('mail','add');
				// create links for email addresses
				if ($modifyURI)
				{
					$link = $GLOBALS['egw']->link('/index.php',array('menuaction'    => $addAction));
					$newBody = preg_replace("/href=(\"|\')mailto:([\w,\-,\/,\?,\=,\.,&amp;,!\n,\%,@,\*,#,:,~,\+]+)(\"|\')/ie",
						"'href=\"$link&send_to='.base64_encode('$2').'\"'.' target=\"compose\" onclick=\"window.open(this,this.target,\'dependent=yes,width=700,height=egw_getWindowOuterHeight(),location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false;\"'", $newBody);
					//print "<pre>".htmlentities($newBody)."</pre><hr>";
				}
				// replace emails within the text with clickable links.
				//TODO:$this->parseEmail($newBody);
			}

			$body .= $newBody;
			#print "<hr><pre>$body</pre><hr>";
		}
		// create links for windows shares
		// \\\\\\\\ == '\\' in real life!! :)
		$body = preg_replace("/(\\\\\\\\)([\w,\\\\,-]+)/i",
			"<a href=\"file:$1$2\" target=\"_blank\"><font color=\"blue\">$1$2</font></a>", $body);

		$body = preg_replace($nonDisplayAbleCharacters,'',$body);

		return $body;
	}

	/**
	 * preg_replace callback to replace image cid url's
	 *
	 * @param array $matches matches from preg_replace("/src=(\"|\')cid:(.*)(\"|\')/iU",...)
	 * @return string src attribute to replace
	 */
	function image_callback($matches)
	{
		static $cache = array();	// some caching, if mails containing the same image multiple times

		$linkData = array (
			'menuaction'    => 'mail.mail_ui.displayImage',
			'uid'		=> $this->uid,
			'mailbox'	=> base64_encode($this->mailbox),
			'cid'		=> base64_encode($matches[2]),
			'partID'	=> $this->partID,
		);
		$imageURL = $GLOBALS['egw']->link('/index.php', $linkData);

		// to test without data uris, comment the if close incl. it's body
		if (html::$user_agent != 'msie' || html::$ua_version >= 8)
		{
			if (!isset($cache[$imageURL]))
			{
				$attachment = $this->mail_bo->getAttachmentByCID($this->uid, $matches[2], $this->partID);

				// only use data uri for "smaller" images, as otherwise the first display of the mail takes to long
				if (bytes($attachment['attachment']) < 8192)	// msie=8 allows max 32k data uris
				{
					$cache[$imageURL] = 'data:'.$attachment['type'].';base64,'.base64_encode($attachment['attachment']);
				}
				else
				{
					$cache[$imageURL] = $imageURL;
				}
			}
			$imageURL = $cache[$imageURL];
		}
		return 'src="'.$imageURL.'"';
	}

	/**
	 * preg_replace callback to replace image cid url's
	 *
	 * @param array $matches matches from preg_replace("/src=(\"|\')cid:(.*)(\"|\')/iU",...)
	 * @return string src attribute to replace
	 */
	function image_callback_plain($matches)
	{
		static $cache = array();	// some caching, if mails containing the same image multiple times
		//error_log(__METHOD__.__LINE__.array2string($matches));
		$linkData = array (
			'menuaction'    => 'mail.mail_ui.displayImage',
			'uid'		=> $this->uid,
			'mailbox'	=> base64_encode($this->mailbox),
			'cid'		=> base64_encode($matches[1]),
			'partID'	=> $this->partID,
		);
		$imageURL = $GLOBALS['egw']->link('/index.php', $linkData);

		// to test without data uris, comment the if close incl. it's body
		if (html::$user_agent != 'msie' || html::$ua_version >= 8)
		{
			if (!isset($cache[$imageURL]))
			{
				$attachment = $this->mail_bo->getAttachmentByCID($this->uid, $matches[1], $this->partID);

				// only use data uri for "smaller" images, as otherwise the first display of the mail takes to long
				if (bytes($attachment['attachment']) < 8192)	// msie=8 allows max 32k data uris
				{
					$cache[$imageURL] = 'data:'.$attachment['type'].';base64,'.base64_encode($attachment['attachment']);
				}
				else
				{
					$cache[$imageURL] = $imageURL;
				}
			}
			$imageURL = $cache[$imageURL];
		}
		return '<img src="'.$imageURL.'" />';
	}

	/**
	 * preg_replace callback to replace image cid url's
	 *
	 * @param array $matches matches from preg_replace("/src=(\"|\')cid:(.*)(\"|\')/iU",...)
	 * @return string src attribute to replace
	 */
	function image_callback_url($matches)
	{
		static $cache = array();	// some caching, if mails containing the same image multiple times
		//error_log(__METHOD__.__LINE__.array2string($matches));
		$linkData = array (
			'menuaction'    => 'mail.mail_ui.displayImage',
			'uid'		=> $this->uid,
			'mailbox'	=> base64_encode($this->mailbox),
			'cid'		=> base64_encode($matches[1]),
			'partID'	=> $this->partID,
		);
		$imageURL = $GLOBALS['egw']->link('/index.php', $linkData);

		// to test without data uris, comment the if close incl. it's body
		if (html::$user_agent != 'msie' || html::$ua_version >= 8)
		{
			if (!isset($cache[$imageURL]))
			{
				$attachment = $this->mail_bo->getAttachmentByCID($this->uid, $matches[1], $this->partID);

				// only use data uri for "smaller" images, as otherwise the first display of the mail takes to long
				if (bytes($attachment['attachment']) < 8192)	// msie=8 allows max 32k data uris
				{
					$cache[$imageURL] = 'data:'.$attachment['type'].';base64,'.base64_encode($attachment['attachment']);
				}
				else
				{
					$cache[$imageURL] = $imageURL;
				}
			}
			$imageURL = $cache[$imageURL];
		}
		return 'url('.$imageURL.');';
	}

	/**
	 * preg_replace callback to replace image cid url's
	 *
	 * @param array $matches matches from preg_replace("/src=(\"|\')cid:(.*)(\"|\')/iU",...)
	 * @return string src attribute to replace
	 */
	function image_callback_background($matches)
	{
		static $cache = array();	// some caching, if mails containing the same image multiple times
		$linkData = array (
			'menuaction'    => 'mail.mail_ui.displayImage',
			'uid'		=> $this->uid,
			'mailbox'	=> base64_encode($this->mailbox),
			'cid'		=> base64_encode($matches[2]),
			'partID'	=> $this->partID,
		);
		$imageURL = $GLOBALS['egw']->link('/index.php', $linkData);

		// to test without data uris, comment the if close incl. it's body
		if (html::$user_agent != 'msie' || html::$ua_version >= 8)
		{
			if (!isset($cache[$imageURL]))
			{
				$cache[$imageURL] = $imageURL;
			}
			$imageURL = $cache[$imageURL];
		}
		return 'background="'.$imageURL.'"';
	}

	/**
	 * importMessage
	 */
	function importMessage($content=null)
	{
		//error_log(__METHOD__.__LINE__.$this->mail_bo->getDraftFolder());

		if (!empty($content))
		{
			error_log(__METHOD__.__LINE__.array2string($content));
			$content['divImportArea']['vfsfile'] = array(
				'name' => egw_vfs::basename($content['divImportArea']['vfsfile']),
				'type' => egw_vfs::mime_content_type($content['divImportArea']['vfsfile']),
				'file' => egw_vfs::PREFIX.$content['divImportArea']['vfsfile'],
				'size' => filesize(egw_vfs::PREFIX.$content['divImportArea']['vfsfile']),
			);
			$destination = $content['divImportArea']['FOLDER'][0];
			$importID = mail_bo::getRandomString();
			$importFailed = false;
			try
			{
				$messageUid = $this->importMessageToFolder($content['divImportArea']['vfsfile'],$destination,$importID);
			    $linkData = array
			    (
					'id'		=> $this->createRowID($destination, $messageUid, true),
			    );
			}
			catch (egw_exception_wrong_userinput $e)
			{
					$importFailed=true;
					$content['msg']		= $e->getMessage();
			}
			if (!$importFailed)
			{
				ExecMethod2('mail.mail_ui.displayMessage',$linkData);
				exit;
			}
		}
		if (!is_array($content)) $content = array();
		if (empty($content['divImportArea']['FOLDER'])) $content['divImportArea']['FOLDER']=(array)$this->mail_bo->getDraftFolder();
		if (!empty($content['divImportArea']['FOLDER'])) $sel_options['FOLDER']=mail_compose::ajax_searchFolder(0,true);

		$etpl = new etemplate_new('mail.importMessage');
		$etpl->setElementAttribute('uploadForImport','onFinish','app.mail.uploadForImport');
		$etpl->exec('mail.mail_ui.importMessage',$content,$sel_options,$readonlys,$preserv,2);
	}

	/**
	 * importMessageToFolder
	 *
	 * @param array $_formData Array with information of name, type, file and size
	 * @param string $_folder (passed by reference) will set the folder used. must be set with a folder, but will hold modifications if
	 *					folder is modified
	 * @param string $importID ID for the imported message, used by attachments to identify them unambiguously
	 * @return mixed $messageUID or exception
	 */
	function importMessageToFolder($_formData,&$_folder,$importID='')
	{
		$importfailed = false;
		//error_log(__METHOD__.__LINE__.array2string($_formData));
		if (empty($_formData['file'])) $_formData['file'] = $_formData['tmp_name'];
		// check if formdata meets basic restrictions (in tmp dir, or vfs, mimetype, etc.)
		try
		{
			$tmpFileName = mail_bo::checkFileBasics($_formData,$importID);
		}
		catch (egw_exception_wrong_userinput $e)
		{
			$importfailed = true;
			$alert_msg .= $e->getMessage();
		}
		// -----------------------------------------------------------------------
		if ($importfailed === false)
		{
			$mailObject = new egw_mailer();
			try
			{
				$this->mail_bo->parseFileIntoMailObject($mailObject,$tmpFileName,$Header,$Body);
			}
			catch (egw_exception_assertion_failed $e)
			{
				$importfailed = true;
				$alert_msg .= $e->getMessage();
			}
			//_debug_array($Body);
			$this->mail_bo->openConnection();
			if (empty($_folder))
			{
				$importfailed = true;
				$alert_msg .= lang("Import of message %1 failed. Destination Folder not set.",$_formData['name']);
			}
			$delimiter = $this->mail_bo->getHierarchyDelimiter();
			if($_folder=='INBOX'.$delimiter) $_folder='INBOX';
			if ($importfailed === false)
			{
				if ($this->mail_bo->folderExists($_folder,true)) {
					try
					{
						$messageUid = $this->mail_bo->appendMessage($_folder,
							$Header.$mailObject->LE.$mailObject->LE,
							$Body,
							$flags);
					}
					catch (egw_exception_wrong_userinput $e)
					{
						$importfailed = true;
						$alert_msg .= lang("Import of message %1 failed. Could not save message to folder %2 due to: %3",$_formData['name'],$_folder,$e->getMessage());
					}
				}
				else
				{
					$importfailed = true;
					$alert_msg .= lang("Import of message %1 failed. Destination Folder %2 does not exist.",$_formData['name'],$_folder);
				}
			}
		}
		// set the url to open when refreshing
		if ($importfailed == true)
		{
			throw new egw_exception_wrong_userinput($alert_msg);
		}
		else
		{
			return $messageUid;
		}
	}

	/**
	 * importMessageFromVFS2DraftAndEdit
	 *
	 * @param array $formData Array with information of name, type, file and size; file is required,
	 *                               name, type and size may be set here to meet the requirements
	 *						Example: $formData['name']	= 'a_email.eml';
	 *								 $formData['type']	= 'message/rfc822';
	 *								 $formData['file']	= 'vfs://default/home/leithoff/a_email.eml';
	 *								 $formData['size']	= 2136;
	 * @return void
	 */
	function importMessageFromVFS2DraftAndEdit($formData='')
	{
		$this->importMessageFromVFS2DraftAndDisplay($formData,'edit');
	}

	/**
	 * importMessageFromVFS2DraftAndDisplay
	 *
	 * @param array $formData Array with information of name, type, file and size; file is required,
	 *                               name, type and size may be set here to meet the requirements
	 *						Example: $formData['name']	= 'a_email.eml';
	 *								 $formData['type']	= 'message/rfc822';
	 *								 $formData['file']	= 'vfs://default/home/leithoff/a_email.eml';
	 *								 $formData['size']	= 2136;
	 * @param string $mode mode to open ImportedMessage display and edit are supported
	 * @return void
	 */
	function importMessageFromVFS2DraftAndDisplay($formData='',$mode='display')
	{
		if (empty($formData)) if (isset($_REQUEST['formData'])) $formData = $_REQUEST['formData'];
		//error_log(array2string($formData));
		$draftFolder = $this->mail_bo->getDraftFolder(false);
		$importID = mail_bo::getRandomString();
		// name should be set to meet the requirements of checkFileBasics
		if (parse_url($formData['file'],PHP_URL_SCHEME) == 'vfs' && (!isset($formData['name']) || empty($formData['name'])))
		{
			$buff = explode('/',$formData['file']);
			$suffix = '';
			if (is_array($buff)) $formData['name'] = array_pop($buff); // take the last part as name
		}
		// type should be set to meet the requirements of checkFileBasics
		if (parse_url($formData['file'],PHP_URL_SCHEME) == 'vfs' && (!isset($formData['type']) || empty($formData['type'])))
		{
			$buff = explode('.',$formData['file']);
			$suffix = '';
			if (is_array($buff)) $suffix = array_pop($buff); // take the last extension to check with ext2mime
			if (!empty($suffix)) $formData['type'] = mime_magic::ext2mime($suffix);
		}
		// size should be set to meet the requirements of checkFileBasics
		if (parse_url($formData['file'],PHP_URL_SCHEME) == 'vfs' && !isset($formData['size']))
		{
			$formData['size'] = strlen($formData['file']); // set some size, to meet requirements of checkFileBasics
		}
		try
		{
			$messageUid = $this->importMessageToFolder($formData,$draftFolder,$importID);
			$linkData = array
			(
		        'menuaction'    => ($mode=='display'?'mail.mail_ui.displayMessage':'mail.mail_compose.composeFromDraft'),
				'id'		=> $this->createRowID($draftFolder,$messageUid,true),
				'deleteDraftOnClose' => 1,
			);
			if ($mode!='display')
			{
				unset($linkData['deleteDraftOnClose']);
				$linkData['method']	='importMessageToMergeAndSend';
			}
		}
		catch (egw_exception_wrong_userinput $e)
		{
		    $linkData = array
		    (
		        'menuaction'    => 'mail.mail_ui.importMessage',
				'msg'		=> htmlspecialchars($e->getMessage()),
		    );
		}
		egw::redirect_link('/index.php',$linkData);
		exit;

	}

	/**
	 * loadEmailBody
	 *
	 * @param string _messageID UID
	 *
	 * @return xajax response
	 */
	function loadEmailBody($_messageID=null)
	{
		if (!$_messageID) $_messageID = $_GET['_messageID'];
		if(mail_bo::$debug) error_log(__METHOD__."->".$_flag.':'.print_r($_messageID,true));
		if (empty($_messageID)) return "";
		$uidA = self::splitRowID($_messageID);
		$folder = $uidA['folder']; // all messages in one set are supposed to be within the same folder
		$messageID = $uidA['msgUID'];
		$bodyResponse = $this->get_load_email_data($messageID,'',$folder);
		//error_log(array2string($bodyResponse));
		echo $bodyResponse;

	}

	/**
	 * ajax_setFolderStatus - its called via json, so the function must start with ajax (or the class-name must contain ajax)
	 * gets the counters and sets the text of a treenode if needed (unread Messages found)
	 * @param array $_folder folders to refresh its unseen message counters
	 * @return nothing
	 */
	function ajax_setFolderStatus($_folder)
	{
		//error_log(__METHOD__.__LINE__.array2string($_folder));
		if ($_folder)
		{
			$del = $this->mail_bo->getHierarchyDelimiter(false);
			$oA = array();
			foreach ($_folder as $_folderName)
			{
				list($profileID,$folderName) = explode(self::$delimiter,$_folderName,2);
				if (is_numeric($profileID))
				{
					if ($profileID != $this->mail_bo->profileID) continue; // only current connection
					if ($folderName)
					{
						$fS = $this->mail_bo->getFolderStatus($folderName,false);
						//error_log(__METHOD__.__LINE__.array2string($fS));
						if ($fS['unseen'])
						{
							$oA[$_folderName] = '<b>'.$fS['shortDisplayName'].' ('.$fS['unseen'].')</b>';

						}
					}
				}
			}
			//error_log(__METHOD__.__LINE__.array2string($oA));
			if ($oA)
			{
				$response = egw_json_response::get();
				$response->call('app.mail.mail_setFolderStatus',$oA,'mail');
			}
		}
	}

	/**
	 * ajax_addFolder - its called via json, so the function must start with ajax (or the class-name must contain ajax)
	 * @param string $_parentFolderName folder to add a folder to
	 * @param string $_newName new foldername
	 * @return nothing
	 */
	function ajax_addFolder($_parentFolderName, $_newName)
	{
		//error_log(__METHOD__.__LINE__.' ParentFolderName:'.array2string($_parentFolderName).' NewName/Folder:'.array2string($_newName));
		if ($_parentFolderName)
		{
			$created = false;
			$decodedFolderName = $this->mail_bo->decodeEntityFolderName($_parentFolderName);
			$_newName = translation::convert($this->mail_bo->decodeEntityFolderName($_newName), $this->charset, 'UTF7-IMAP');
			$del = $this->mail_bo->getHierarchyDelimiter(false);
			list($profileID,$parentFolderName) = explode(self::$delimiter,$decodedFolderName,2);
			if (is_numeric($profileID))
			{
				if ($profileID != $this->mail_bo->profileID) return; // only current connection
				$nA = explode($del,$_newName);
				//if (strtoupper($parentFolderName)!= 'INBOX')
				{
					//error_log(__METHOD__.__LINE__."$folderName, $parentFolder, $_newName");
					$oldFolderInfo = $this->mail_bo->getFolderStatus($parentFolderName,false);
					//error_log(__METHOD__.__LINE__.array2string($oldFolderInfo));

					$this->mail_bo->reopen('INBOX');
					$parentName = $parentFolderName;
					// if newName has delimiter ($del) in it, we need to create the subtree
					if (!empty($nA))
					{
						$c=0;
						foreach($nA as $sTName)
						{
							if($parentFolderName = $this->mail_bo->createFolder($parentFolderName, $sTName, true))
							{
								$c++;
							}
						}
						if ($c==count($nA)) $created=true;
					}
					$this->mail_bo->reopen($parentName);
				}
			}
			//error_log(__METHOD__.__LINE__.array2string($oA));
			if ($created===true)
			{
				$this->mail_bo->resetFolderObjectCache($profileID);
				$response = egw_json_response::get();
				$response->call('app.mail.mail_reloadNode',array($_parentFolderName=>$oldFolderInfo['shortDisplayName']),'mail');
			}
		}
	}

	/**
	 * ajax_renameFolder - its called via json, so the function must start with ajax (or the class-name must contain ajax)
	 * @param string $_folderName folder to rename and refresh
	 * @param string $_newName new foldername
	 * @return nothing
	 */
	function ajax_renameFolder($_folderName, $_newName)
	{
		//error_log(__METHOD__.__LINE__.' OldFolderName:'.array2string($_folderName).' NewName:'.array2string($_newName));
		if ($_folderName)
		{
			$decodedFolderName = $this->mail_bo->decodeEntityFolderName($_folderName);
			$_newName = translation::convert($this->mail_bo->decodeEntityFolderName($_newName), $this->charset, 'UTF7-IMAP');
			$del = $this->mail_bo->getHierarchyDelimiter(false);
			$oA = array();
			list($profileID,$folderName) = explode(self::$delimiter,$decodedFolderName,2);
			$hasChildren = false;
			if (is_numeric($profileID))
			{
				if ($profileID != $this->mail_bo->profileID) return; // only current connection
				$pA = explode($del,$folderName);
				array_pop($pA);
				$parentFolder = implode($del,$pA);
				if (strtoupper($folderName)!= 'INBOX')
				{
					//error_log(__METHOD__.__LINE__."$folderName, $parentFolder, $_newName");
					$oldFolderInfo = $this->mail_bo->getFolderStatus($folderName,false);
					//error_log(__METHOD__.__LINE__.array2string($oldFolderInfo));
					if (!empty($oldFolderInfo['attributes']) && stripos(array2string($oldFolderInfo['attributes']),'\hasnochildren')=== false)
					{
						$hasChildren=true; // translates to: hasChildren -> dynamicLoading
						$delimiter = $this->mail_bo->getHierarchyDelimiter();
						$nameSpace = $this->mail_bo->_getNameSpaces();
						$prefix = $this->mail_bo->getFolderPrefixFromNamespace($nameSpace, $folderName);
						//error_log(__METHOD__.__LINE__.'->'."$_folderName, $delimiter, $prefix");
						$fragments = array();
						$subFolders = $this->mail_bo->getMailBoxesRecursive($folderName, $delimiter, $prefix);
						foreach ($subFolders as $k => $folder)
						{
							// we do not monitor failure or success on subfolders
							if ($folder == $folderName)
							{
								unset($subFolders[$k]);
							}
							else
							{
								$rv = $this->mail_bo->subscribe($folder, false);
								$fragments[$profileID.self::$delimiter.$folder] = substr($folder,strlen($folderName));
							}
						}
						//error_log(__METHOD__.__LINE__.' Fetched Subfolders->'.array2string($fragments));
					}

					$this->mail_bo->reopen('INBOX');
					if($newFolderName = $this->mail_bo->renameFolder($folderName, $parentFolder, $_newName)) {
						$this->mail_bo->resetFolderObjectCache($profileID);
						//enforce the subscription to the newly named server, as it seems to fail for names with umlauts
						$rv = $this->mail_bo->subscribe($newFolderName, true);
						$rv = $this->mail_bo->subscribe($folderName, false);
					}
					$this->mail_bo->reopen($newFolderName);
					$fS = $this->mail_bo->getFolderStatus($newFolderName,false);
					//error_log(__METHOD__.__LINE__.array2string($fS));
					if ($hasChildren)
					{
						$subFolders = $this->mail_bo->getMailBoxesRecursive($newFolderName, $delimiter, $prefix);
						foreach ($subFolders as $k => $folder)
						{
							// we do not monitor failure or success on subfolders
							if ($folder == $folderName)
							{
								unset($subFolders[$k]);
							}
							else
							{
								$rv = $this->mail_bo->subscribe($folder, true);
							}
						}
						//error_log(__METHOD__.__LINE__.' Fetched Subfolders->'.array2string($subFolders));
					}

					$oA[$_folderName]['id'] = $profileID.self::$delimiter.$newFolderName;
					$oA[$_folderName]['olddesc'] = $oldFolderInfo['shortDisplayName'];
					if ($fS['unseen'])
					{
						$oA[$_folderName]['desc'] = '<b>'.$fS['shortDisplayName'].' ('.$fS['unseen'].')</b>';

					}
					else
					{
						$oA[$_folderName]['desc'] = $fS['shortDisplayName'];
					}
					foreach($fragments as $oldFolderName => $fragment)
					{
						//error_log(__METHOD__.__LINE__.':'.$oldFolderName.'->'.$profileID.self::$delimiter.$newFolderName.$fragment);
						$oA[$oldFolderName]['id'] = $profileID.self::$delimiter.$newFolderName.$fragment;
						$oA[$oldFolderName]['olddesc'] = '#skip-user-interaction-message#';
						$fS = $this->mail_bo->getFolderStatus($newFolderName.$fragment,false);
						if ($fS['unseen'])
						{
							$oA[$oldFolderName]['desc'] = '<b>'.$fS['shortDisplayName'].' ('.$fS['unseen'].')</b>';

						}
						else
						{
							$oA[$oldFolderName]['desc'] = $fS['shortDisplayName'];
						}
					}
				}
			}
			//error_log(__METHOD__.__LINE__.array2string($oA));
			if ($oA)
			{
				$response = egw_json_response::get();
				$response->call('app.mail.mail_setLeaf',$oA,'mail');
			}
		}
	}

	/**
	 * ajax_deleteFolder - its called via json, so the function must start with ajax (or the class-name must contain ajax)
	 * @param string $_folderName folder to delete
	 * @return nothing
	 */
	function ajax_deleteFolder($_folderName)
	{
		//error_log(__METHOD__.__LINE__.' OldFolderName:'.array2string($_folderName));
		$success = false;
		if ($_folderName)
		{
			$decodedFolderName = $this->mail_bo->decodeEntityFolderName($_folderName);
			$del = $this->mail_bo->getHierarchyDelimiter(false);
			$oA = array();
			list($profileID,$folderName) = explode(self::$delimiter,$decodedFolderName,2);
			$hasChildren = false;
			if (is_numeric($profileID))
			{
				if ($profileID != $this->mail_bo->profileID) return; // only current connection
				$pA = explode($del,$folderName);
				array_pop($pA);
				$parentFolder = implode($del,$pA);
				if (strtoupper($folderName)!= 'INBOX')
				{
					//error_log(__METHOD__.__LINE__."$folderName, $parentFolder, $_newName");
					$oA = array();
					$oldFolderInfo = $this->mail_bo->getFolderStatus($folderName,false);
					//error_log(__METHOD__.__LINE__.array2string($oldFolderInfo));
					if (!empty($oldFolderInfo['attributes']) && stripos(array2string($oldFolderInfo['attributes']),'\hasnochildren')=== false)
					{
						$hasChildren=true; // translates to: hasChildren -> dynamicLoading
						$msg = lang("refused to delete folder with subfolders");
					}
					else
					{
						$success = $this->mail_bo->deleteFolder($folderName);
						if (PEAR::isError($success))
						{
							$msg = $success->message;
							$success = false;
						}
						else
						{
							$oA[$_folderName] = $oldFolderInfo['shortDisplayName'];
						}
					}
				}
				else
				{
					$msg = lang("refused to delete folder INBOX");
				}
			}
			$response = egw_json_response::get();
			if ($success)
			{
				//error_log(__METHOD__.__LINE__.array2string($oA));
				$response->call('app.mail.mail_removeLeaf',$oA,'mail');
			}
			else
			{
				$response->call('egw_refresh',lang('failed to delete %1 ! Reason: %2',$oldFolderInfo['shortDisplayName'],$msg),'mail');
			}
		}
	}

	/**
	 * empty changeProfile - its called via json, so the function must start with ajax (or the class-name must contain ajax)
	 *
	 * @return nothing
	 */
	function ajax_changeProfile($icServerID)
	{
		if ($icServerID && $icServerID != $this->mail_bo->profileID)
		//error_log(__METHOD__.__LINE__.' change Profile to ->'.$icServerID);
		$this->changeProfile($icServerID);
		$response = egw_json_response::get();
		$response->call('egw_refresh',lang('changed profile'),'mail');
	}

	/**
	 * empty trash folder - its called via json, so the function must start with ajax (or the class-name must contain ajax)
	 *
	 * @return nothing
	 */
	function ajax_emptyTrash()
	{
		$trashFolder = $this->mail_bo->getTrashFolder();
		if(!empty($trashFolder)) {
			$this->mail_bo->compressFolder($trashFolder);
		}
		$response = egw_json_response::get();
		$response->call('egw_refresh',lang('empty trash'),'mail');
	}

	/**
	 * compress folder - its called via json, so the function must start with ajax (or the class-name must contain ajax)
	 * fetches the current folder from session and compresses it
	 * @return nothing
	 */
	function ajax_compressFolder()
	{
		$this->mail_bo->restoreSessionData();
		$folder = $this->mail_bo->sessionData['maibox'];
		if ($this->mail_bo->folderExists($folder))
		{
			if(!empty($folder)) {
				$this->mail_bo->compressFolder($folder);
			}
			$response = egw_json_response::get();
			$response->call('egw_refresh',lang('compress folder').': '.$folder,'mail');
		}
	}

	/**
	 * flag messages as read, unread, flagged, ...
	 *
	 * @param string _flag name of the flag
	 * @param array _messageList list of UID's
	 *
	 * @return xajax response
	 */
	function ajax_flagMessages($_flag, $_messageList)
	{
		if(mail_bo::$debug) error_log(__METHOD__."->".$_flag.':'.array2string($_messageList));
		if ($_messageList=='all' || !empty($_messageList['msg']))
		{
			if ($_messageList=='all')
			{
				// we have no folder information
				$folder=null;
			}
			else
			{
				$uidA = self::splitRowID($_messageList['msg'][0]);
				$folder = $uidA['folder']; // all messages in one set are supposed to be within the same folder
			}
			foreach($_messageList['msg'] as $rowID)
			{
				$hA = self::splitRowID($rowID);
				$messageList[] = $hA['msgUID'];
			}
			$this->mail_bo->flagMessages($_flag, ($_messageList=='all' ? 'all':$messageList),$folder);
		}
		else
		{
			if(mail_bo::$debug) error_log(__METHOD__."-> No messages selected.");
		}

		// unset preview, as refresh would mark message again read
/*
		if ($_flag == 'unread' && in_array($this->sessionData['previewMessage'], $_messageList['msg']))
		{
			unset($this->sessionData['previewMessage']);
			$this->saveSessionData();
		}
*/
		$response = egw_json_response::get();
		$response->call('egw_refresh',lang('flagged %1 messages as %2 in %3',count($_messageList['msg']),$_flag,$folder),'mail');
	}

	/**
	 * delete messages
	 *
	 * @param array _messageList list of UID's
	 * @param string _forceDeleteMethod - method of deletion to be enforced
	 * @return xajax response
	 */
	function ajax_deleteMessages($_messageList,$_forceDeleteMethod=null)
	{
		if(mail_bo::$debug) error_log(__METHOD__."->".print_r($_messageList,true).' Method:'.$_forceDeleteMethod);
		$error = null;
		if ($_messageList=='all' || !empty($_messageList['msg']))
		{
			if ($_messageList=='all')
			{
				// we have no folder information
				$folder=null;
			}
			else
			{
				$uidA = self::splitRowID($_messageList['msg'][0]);
				$folder = $uidA['folder']; // all messages in one set are supposed to be within the same folder
			}
			foreach($_messageList['msg'] as $rowID)
			{
				$hA = self::splitRowID($rowID);
				$messageList[] = $hA['msgUID'];
			}
			try
			{
				$this->mail_bo->deleteMessages(($_messageList=='all' ? 'all':$messageList),$folder,(empty($_forceDeleteMethod)?'no':$_forceDeleteMethod));
			}
			catch (egw_exception $e)
			{
				$error = str_replace('"',"'",$e->getMessage());
			}
			$response = egw_json_response::get();
			if (empty($error))
			{
				$response->call('egw_refresh',lang('deleted %1 messages in %2',count($_messageList['msg']),$folder),'mail');
			}
			else
			{
				$error = str_replace('\n',"\n",lang('mailserver reported:\n%1 \ndo you want to proceed by deleting the selected messages immediately (click ok)?\nif not, please try to empty your trashfolder before continuing. (click cancel)',$error));
				$response->call('app.mail.mail_retryForcedDelete',array('response'=>$error,'messageList'=>$_messageList),'mail');
			}
		}
		else
		{
			if(mail_bo::$debug) error_log(__METHOD__."-> No messages selected.");
		}
	}

	/**
	 * move messages
	 *
	 * @param array _folderName target folder
	 * @param array _messageList list of UID's
	 *
	 * @return xajax response
	 */
	function ajax_moveMessages($_folderName, $_messageList)
	{
		if(mail_bo::$debug) error_log(__METHOD__."->".$_folderName.':'.print_r($_messageList,true));
		$_folderName = $this->mail_bo->decodeEntityFolderName($_folderName);
		list($profileID,$targetFolder) = explode(self::$delimiter,$_folderName,2);

		if ($_messageList=='all' || !empty($_messageList['msg']))
		{
			if ($_messageList=='all')
			{
				// we have no folder information
				$folder=null;
			}
			else
			{
				$uidA = self::splitRowID($_messageList['msg'][0]);
				$folder = $uidA['folder']; // all messages in one set are supposed to be within the same folder
			}
			foreach($_messageList['msg'] as $rowID)
			{
				$hA = self::splitRowID($rowID);
				$messageList[] = $hA['msgUID'];
			}

			$this->mail_bo->moveMessages($targetFolder,$messageList,true,$folder);
			$response = egw_json_response::get();
			$response->call('egw_refresh',lang('moved %1 message(s) from %2 to %3',count($messageList),$folder,$targetFolder),'mail');
		}
		else
		{
			if(mail_bo::$debug) error_log(__METHOD__."-> No messages selected.");
		}
	}

	/**
	 * copy messages
	 *
	 * @param array _folderName target folder
	 * @param array _messageList list of UID's
	 *
	 * @return xajax response
	 */
	function ajax_copyMessages($_folderName, $_messageList)
	{
		if(mail_bo::$debug); error_log(__METHOD__."->".$_folderName.':'.print_r($_messageList,true));
		$_folderName = $this->mail_bo->decodeEntityFolderName($_folderName);
		list($profileID,$targetFolder) = explode(self::$delimiter,$_folderName,2);

		if ($_messageList=='all' || !empty($_messageList['msg']))
		{
			if ($_messageList=='all')
			{
				// we have no folder information
				$folder=null;
			}
			else
			{
				$uidA = self::splitRowID($_messageList['msg'][0]);
				$folder = $uidA['folder']; // all messages in one set are supposed to be within the same folder
			}
			foreach($_messageList['msg'] as $rowID)
			{
				$hA = self::splitRowID($rowID);
				$messageList[] = $hA['msgUID'];
			}

			$this->mail_bo->moveMessages($targetFolder,$messageList,false,$folder);
			$response = egw_json_response::get();
			$response->call('egw_refresh',lang('copied %1 message(s) from %2 to %3',count($messageList),$folder,$targetFolder),'mail');
		}
		else
		{
			if(mail_bo::$debug) error_log(__METHOD__."-> No messages selected.");
		}
	}

}
