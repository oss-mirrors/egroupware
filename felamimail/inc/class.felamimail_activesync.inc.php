<?php
/**
 * EGroupware: ActiveSync access: FMail plugin
 *
 * @link http://www.egroupware.org
 * @package felamimail
 * @subpackage activesync
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
 * @author Philip Herbert <philip@knauber.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once (EGW_INCLUDE_ROOT.'/felamimail/inc/class.bofelamimail.inc.php');

/**
 * FMail activesync plugin
 *
 * Plugin creates a device specific file to map alphanumeric folder names to nummeric id's.
 */
class felamimail_activesync implements activesync_plugin_read
{
	/**
	 * var BackendEGW
	 */
	private $backend;

	/**
	 * Instance of bofelamimail
	 *
	 * @var bofelamimail
	 */
	private $mail;

	/**
	 * Integer id of current mail account / connection
	 *
	 * @var int
	 */
	private $account;

	/**
	 * Constructor
	 *
	 * @param BackendEGW $backend
	 */
	public function __construct(BackendEGW $backend)
	{
		$this->backend = $backend;
	}

	/**
	 * Open IMAP connection
	 *
	 * @param int $account integer id of account to use
	 * @todo support different accounts
	 */
	private function _connect($account=0)
	{
		if ($this->mail && $this->account != $account) $this->_disconnect();

		if (!$this->mail)
		{
			$this->account = $account;
			// todo: tell fmail which account to use
			$this->mail = new bofelamimail ("UTF-8",false);
			if (!$this->mail->openConnection(0,false))
			{
				throw new egw_exception_not_found(__METHOD__."($account) can not open connection!");
			}
		}
	}

	/**
	 * Close IMAP connection
	 */
	private function _disconnect()
	{
		debugLog(__METHOD__);
		if ($this->mail) $this->mail->closeConnection();

		unset($this->mail);
		unset($this->account);
		unset($this->folders);
	}

	private $folders;
	private $messages;

	/**
	 *  This function is analogous to GetMessageList.
	 *
	 *  @ToDo loop over available email accounts
	 */
	public function GetFolderList()
	{
		$folderlist = array();

		/*foreach($available_accounts as $account)*/ $account = 0;
		{
			$this->_connect($account);
			if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false);

			foreach ($this->folders as $folder => $folderObj) {
				$folderlist[] = $f = array(
					'id'     => $this->createID($account,$folder),
					'mod'    => $folderObj->shortDisplayName,
					'parent' => $this->getParentID($account,$folder),
				);
				debugLog(__METHOD__."() returning ".array2string($f));
			}
		}
		//debugLog(__METHOD__."() returning ".array2string($folderlist));

		return $folderlist;
	}
	
	public function GetMessage($folderid, $id, $truncsize, $bodypreference=false, $mimesupport = 0)
	{
		debugLog (__METHOD__);
		$stat = $this->StatMessage($folderid, $id);
		debugLog(__METHOD__.__LINE__.array2string($stat));
		// StatMessage should reopen the folder in question, so we dont need folderids in the following statements.
		if ($stat)
		{
			$header = $this->mail->getMessageRawHeader($id);
			$body = $this->mail->getMessageRawBody($id);
			$output = new SyncMail();
			$output->bodytruncated = 0; // should be handeled by comparing bodysize vs. $truncsize
			// if ....
			$output->bodysize = 1;
			$output->body = 'i';
			$output->read = $stat["flags"];
			$output->subject = $this->messages[$id]['subject'];
			$output->importance = $this->messages[$id]['priority'] ;
			$output->daterecieved = $stat['mod'];
			$output->displayto = $this->messages[$id]['to_address']; //$stat['FETCHED_HEADER']['to_name']
			$output->to = $this->messages[$id]['to_address']; //$stat['FETCHED_HEADER']['to_name']
			$output->from = $this->messages[$id]['sender_address']; //$stat['FETCHED_HEADER']['sender_name']
			$output->cc = '';
			$output->reply_to ='';
			$output->messageclass = "IPM.Note";
			if (stripos($this->messages[$id]['mimetype'],'signed')!== false) $output->messageclass = "IPM.Note.SMIME.MultipartSigned";
			// start AS12 Stuff
			$output->poommailflag = new SyncPoommailFlag();
			$output->poommailflag->flagstatus = 0;
			$output->internetcpid = 65001;
			$output->contentclass="urn:content-classes:message";
			if ($bodypreference == true)
			{
				$output->airsyncbasebody = new SyncAirSyncBaseBody();
				debugLog("airsyncbasebody!");
				if (isset($bodypreference[4])) 
				{
					debugLog("MIME Body");
					$output->airsyncbasebody->type = 4;
					$output->airsyncbasenativebodytype = 4;
				}
				$output->airsyncbasebody->data = $header."\r\n".$body;
				$output->airsyncbasebody->estimateddatasize = strlen($output->airsyncbasebody->data);
			}
			// end AS12 Stuff
			debugLog(__METHOD__.__LINE__.array2string($output));
			return $output;
		}
		return false;
	}
	
	public function StatMessage($folderid, $id) 
	{
        debugLog (__METHOD__.' for Folder:'.$folderid.' ID:'.$id);
        return $this->fetchMessages($folderid, NULL, (array)$id);
	}
	
	
	/**
	 *  This function is analogous to GetMessageList.
	 *
	 *  @ToDo loop over available email accounts
	 */
	public function GetMessageList($folderid, $cutoffdate=NULL)
	{
		debugLog (__METHOD__.' for Folder:'.$folderid.' SINCE:'.$cutoffdate);
		return $this->fetchMessages($folderid, $cutoffdate);
	}
	
	private function fetchMessages($folderid, $cutoffdate=NULL, $_id=NULL)
	{
		
		$this->_connect($this->account);
		$messagelist = array();
		if (!empty($cutoffdate)) $_filter = array('type'=>"SINCE",'string'=> date("d-M-Y", $cutoffdate));
		$rv = $this->splitID($folderid,$account,$_folderName,$id);
		debugLog (__METHOD__.' for Folder:'.$_folderName.' '.array2string($_filter).' Ids:'.array2string($_id));
		$rv_messages = $this->mail->getHeaders($_folderName, $_startMessage=1, $_numberOfMessages=9999999, $_sort=0, $_reverse=false, $_filter, $_id);
		foreach ((array)$rv_messages['header'] as $k => $vars)
		{
			$this->messages[$vars['uid']] = $vars;
			//debugLog(__METHOD__.__LINE__.' MailID:'.$k.'->'.array2string($vars));
			if (!empty($vars['deleted'])) continue; // cut of deleted messages
			if ($cutoffdate && $vars['date'] < $cutoffdate) continue; // message is out of range for cutoffdate, ignore it
			$mess["mod"] = $vars['date'];
			$mess["id"] = $vars['uid'];
			// 'seen' aka 'read' is the only flag we want to know about
			$mess["flags"] = 0;
			// outlook supports additional flags, set them to 0
			$mess["olflags"] = 0;
			if($vars["seen"]) $mess["flags"] = 1;
			debugLog(__METHOD__.__LINE__.array2string($mess));
			$messagelist[$vars['uid']] = $mess;
			unset($mess);
		}
		return $messagelist;
	}
	

	
	/**
	 * Get ID of parent Folder or '0' for folders in root
	 *
	 * @param int $account
	 * @param string $folder
	 * @return string
	 */
	private function getParentID($account,$folder)
	{
		$this->_connect($account);
		if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false);

		$fmailFolder = $this->folders[$folder];
		if (!isset($fmailFolder)) return false;

		$parent = explode($fmailFolder->delimiter,$folder);
		array_pop($parent);
		$parent = implode($fmailFolder->delimiter,$parent);

		$id = $parent ? $this->createID($account, $parent) : '0';
		//debugLog(__METHOD__."('$folder') --> parent=$parent --> $id");
		return $id;
	}

	/**
	 * Get Information about a folder
	 *
	 * @param string $id
	 * @return SyncFolder|boolean false on error
	 */
	public function GetFolder($id)
	{
		static $last_id;
		static $folderObj;
		if (isset($last_id) && $last_id === $id) return $folderObj;

		try {
			$this->splitID($id, $account, $folder);
		}
		catch(Exception $e) {
			return $folderObj=false;
		}
		$this->_connect($account);
		if (!isset($this->folders)) $this->folders = $this->mail->getFolderObjects(true,false);

		$fmailFolder = $this->folders[$folder];
		if (!isset($fmailFolder)) return $folderObj=false;

		$folderObj = new SyncFolder();
		$folderObj->serverid = $id;
		$folderObj->parentid = $this->getParentID($account,$folder);
		$folderObj->displayname = $fmailFolder->shortDisplayName;

		// get folder-type
		foreach($this->folders as $inbox => $fmailFolder) break;
		if ($folder == $inbox)
		{
			$folderObj->type = SYNC_FOLDER_TYPE_INBOX;
		}
		elseif($this->mail->isDraftFolder($folder))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_DRAFTS;
		}
		elseif($this->mail->isTrashFolder($folder))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_WASTEBASKET;
		}
		elseif($this->mail->isSentFolder($folder))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_SENTMAIL;
		}
		else
		{
			$folderObj->type = SYNC_FOLDER_TYPE_USER_MAIL;
		}
		debugLog(__METHOD__."($id) --> $folder --> type=$folderObj->type, parentID=$folderObj->parentid, displayname=$folderObj->displayname");
		return $folderObj;
	}

	/**
	 * Return folder stats. This means you must return an associative array with the
	 * following properties:
	 *
	 * "id" => The server ID that will be used to identify the folder. It must be unique, and not too long
	 *		 How long exactly is not known, but try keeping it under 20 chars or so. It must be a string.
	 * "parent" => The server ID of the parent of the folder. Same restrictions as 'id' apply.
	 * "mod" => This is the modification signature. It is any arbitrary string which is constant as long as
	 *		  the folder has not changed. In practice this means that 'mod' can be equal to the folder name
	 *		  as this is the only thing that ever changes in folders. (the type is normally constant)
	 *
	 * @return array with values for keys 'id', 'mod' and 'parent'
	 */
	public function StatFolder($id)
	{
		$folder = $this->GetFolder($id);

		$stat = array(
			'id'     => $id,
			'mod'    => $folder->displayname,
			'parent' => $folder->parentid,
		);

		return $stat;
	}
	
	
	/**
	 * Create a max. 32 hex letter ID, current 20 chars are used
	 *
	 * @param int $account mail account id
	 * @param string $folder
	 * @param int $id=0
	 * @return string
	 * @throws egw_exception_wrong_parameter
	 */
	private function createID($account,$folder,$id=0)
	{
		if (!is_numeric($folder))
		{
			// convert string $folder in numeric id
			$folder = $this->folder2hash($account,$f=$folder);
		}

		$str = $this->backend->createID($account, $folder, $id);

		//debugLog(__METHOD__."($account,'$f',$id) type=$account, folder=$folder --> '$str'");

		return $str;
	}

	/**
	 * Split an ID string into $app, $folder and $id
	 *
	 * @param string $str
	 * @param int &$account mail account id
	 * @param string &$folder
	 * @param int &$id=null
	 * @throws egw_exception_wrong_parameter
	 */
	private function splitID($str,&$account,&$folder,&$id=null)
	{
		$this->backend->splitID($str, $account, $folder, $id=null);

		// convert numeric folder-id back to folder name
		$folder = $this->hash2folder($account,$f=$folder);

		//debugLog(__METHOD__."('$str','$account','$folder',$id)");
	}

	/**
	 * Methods to convert (hierarchical) folder names to nummerical id's
	 *
	 * This is currently done by storing a serialized array in the device specific
	 * state directory.
	 */

	/**
	 * Convert folder string to nummeric hash
	 *
	 * @param int $account
	 * @param string $folder
	 * @return int
	 */
	private function folder2hash($account,$folder)
	{
		if(!isset($this->folderHashes)) $this->readFolderHashes();

		if (($index = array_search($folder, (array)$this->folderHashes[$account])) === false)
		{
			// new hash
			$this->folderHashes[$account][] = $folder;
			$index = array_search($folder, (array)$this->folderHashes[$account]);

			// maybe later storing in on class destruction only
			$this->storeFolderHashes();
		}
		return $index;
	}

	/**
	 * Convert numeric hash to folder string
	 *
	 * @param int $account
	 * @param int $index
	 * @return string NULL if not used so far
	 */
	private function hash2folder($account,$index)
	{
		if(!isset($this->folderHashes)) $this->readFolderHashes();

		return $this->folderHashes[$account][$index];
	}

	private $folderHashes;

	/**
	 * Read hashfile from state dir
	 */
	private function readFolderHashes()
	{
		if (file_exists($file = $this->hashFile()) &&
			($hashes = file_get_contents($file)))
		{
			$this->folderHashes = unserialize($hashes);
		}
		else
		{
			$this->folderHashes = array();
		}
	}

	/**
	 * Store hashfile in state dir
	 *
	 * return int|boolean false on error
	 */
	private function storeFolderHashes()
	{
		return file_put_contents($this->hashFile(), serialize($this->folderHashes));
	}

	/**
	 * Get name of hashfile in state dir
	 *
	 * @throws egw_exception_assertion_failed
	 */
	private function hashFile()
	{
		if (!isset($this->backend->_devid))
		{
			throw new egw_exception_assertion_failed(__METHOD__."() called without this->_devid set!");
		}
		return STATE_DIR.'/'.strtolower($this->backend->_devid).'/'.$this->backend->_devid.'.hashes';
	}
}
