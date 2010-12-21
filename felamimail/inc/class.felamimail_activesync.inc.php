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
require_once (EGW_INCLUDE_ROOT.'/felamimail/inc/class.uidisplay.inc.php');

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
	 * Instance of uidisplay
	 * needed to use various bodyprocessing functions
	 *
	 * @var uidisplay
	 */
	//private $ui; // may not be needed after all

	/**
	 * Integer id of trash folder
	 *
	 * @var mixed
	 */
	private $_wasteID = false;

	/**
	 * Integer id of sent folder
	 *
	 * @var mixed
	 */
	private $_sentID = false;

	/**
	 * Integer id of current mail account / connection
	 *
	 * @var int
	 */
	private $account;

	/**
	 * debugLevel - enables more debug
	 *
	 * @var int
	 */
	private $debugLevel = 1;

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

		$this->_wasteID = false;
		$this->_sentID = false;
		if (!$this->mail)
		{
			$this->account = $account;
			// todo: tell fmail which account to use
			$this->mail = new bofelamimail ("UTF-8",false);
			//$this->ui	= new uidisplay();

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
		debugLog(__METHOD__.__LINE__);
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
				if ($this->debugLevel>0) debugLog(__METHOD__."() returning ".array2string($f));
			}
		}
		//debugLog(__METHOD__."() returning ".array2string($folderlist));

		return $folderlist;
	}

	public function GetMessage($folderid, $id, $truncsize, $bodypreference=false, $mimesupport = 0)
	{
		debugLog (__METHOD__.__LINE__);
		$stat = $this->StatMessage($folderid, $id);
		if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($stat));
		// StatMessage should reopen the folder in question, so we dont need folderids in the following statements.
		if ($stat)
		{
			debugLog(__METHOD__.__LINE__." Message $id with stat ");
			// initialize the object
			$output = new SyncMail();
			$headers = $this->mail->getMessageHeader($id,'',true);
			$rawHeaders = $this->mail->getMessageRawHeader($id);	
			// simple style
			// start AS12 Stuff (bodypreference === false) case = old behaviour
			debugLog(__METHOD__.__LINE__.' Bodypreference: '.array2string($bodypreference));
			if ($bodypreference === false) {
				$bodyStruct = $this->mail->getMessageBody($id, 'only_if_no_text');
				$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);	
				$body = html_entity_decode($body,ENT_QUOTES,$this->mail->detect_encoding($body));
				$body = preg_replace("/<style.*?<\/style>/is", "", $body); // in case there is only a html part
				// remove all other html
				$body = strip_tags($body);
				if(strlen($body) > $truncsize) {
					$body = utf8_truncate($body, $truncsize);
					$output->bodytruncated = 1;
				}
				else
				{
					$output->bodytruncated = 0;
				}			
				$output->bodysize = strlen($body);
				$output->body = $body;
			}
			else // style with bodypreferences
			{
				if (isset($bodypreference[1]) && !isset($bodypreference[1]["TruncationSize"])) 
					$bodypreference[1]["TruncationSize"] = 1024*1024;
				if (isset($bodypreference[2]) && !isset($bodypreference[2]["TruncationSize"])) 
					$bodypreference[2]["TruncationSize"] = 1024*1024;
				if (isset($bodypreference[3]) && !isset($bodypreference[3]["TruncationSize"]))
					$bodypreference[3]["TruncationSize"] = 1024*1024;
				if (isset($bodypreference[4]) && !isset($bodypreference[4]["TruncationSize"]))
					$bodypreference[4]["TruncationSize"] = 1024*1024;
				$output->airsyncbasebody = new SyncAirSyncBaseBody();
				if ($this->debugLevel>0) debugLog("airsyncbasebody!");
				
				$bodyStruct = $this->mail->getMessageBody($id, 'always_display');
				$body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
			    if ($body != "") {
					// may be html
				    $output->airsyncbasenativebodytype=2;
				} else {
					// plain text Message
				    $output->airsyncbasenativebodytype=1;
			        $bodyStruct = $this->mail->getMessageBody($id,'never_display');
				    $body = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
					$body = html_entity_decode($body,ENT_QUOTES,$this->mail->detect_encoding($body));
					$body = strip_tags($body);
				}
				//$body = str_replace("\n","\r\n", str_replace("\r","",$body)); // do we need that?
				if (isset($bodypreference[4])) 
				{
					$output->airsyncbasebody->type = 4;
					$body = "";
					foreach($rawHeaders as $key=>$value) 
					{
						debugLog(__METHOD__.__LINE__.' Key:'.$key.' Value:'.array2string($value));
						if ($key != "content-type" && $key != "mime-version" && $key != "content-transfer-encoding" &&
						    !is_array($value)) {
							$body .= $key.":";
							$tokens = split(" ",trim($value));
							$line = "";
							foreach($tokens as $valu) {
			    				if ((strlen($line)+strlen($valu)+2) > 60) {
									$line .= "\n";
									$body .= $line;
									$line = " ".$valu;
							    } else {
									$line .= " ".$valu;
							    }
							}
							$body .= $line."\n";
						}
					}

					if ($this->debugLevel>0) debugLog("MIME Body");
					if ($output->airsyncbasenativebodytype==1) { //plain
					}
					if ($output->airsyncbasenativebodytype==2) { //html
					}
				}
				else if (isset($bodypreference[2]))
				{
					if ($this->debugLevel>0) debugLog("HTML Body");
					// Send HTML if requested and native type was html
				} 
				else 
				{
					// Send Plaintext as Fallback or if original body is plainttext
					if ($this->debugLevel>0) debugLog("Plaintext Body");
					$bodyStruct = $this->mail->getMessageBody($id,'never_display');
					$plain = $this->mail->getdisplayableBody($this->mail,$bodyStruct);//$this->ui->getdisplayableBody($bodyStruct,false);
					$plain = html_entity_decode($plain,ENT_QUOTES,$this->mail->detect_encoding($plain));
					$plain = strip_tags($plain);
					//$plain = str_replace("\n","\r\n",str_replace("\r","",$plain));
					$output->airsyncbasebody->type = 1;
					if(isset($bodypreference[1]["TruncationSize"]) &&
			    		strlen($plain) > $bodypreference[1]["TruncationSize"]) 
					{
						$plain = utf8_truncate($plain, $bodypreference[1]["TruncationSize"]);
						$output->airsyncbasebody->truncated = 1;
					}
					$output->airsyncbasebody->data = $plain;
				}
				// In case we have nothing for the body, send at least a blank... 
				// dw2412 but only in case the body is not rtf!
				if ($output->airsyncbasebody->type != 3 && (!isset($output->airsyncbasebody->data) || strlen($output->airsyncbasebody->data) == 0))
				{
					$output->airsyncbasebody->data = " ";
				}
				$output->airsyncbasebody->estimateddatasize = strlen($output->airsyncbasebody->data);
			}
			// end AS12 Stuff
			debugLog(__METHOD__.__LINE__.' gather Header info:'.$headers['SUBJECT'].' from:'.$headers['DATE']);
			$output->read = $stat["flags"];
			$output->subject = $this->messages[$id]['subject'];
			$output->importance = ($this->messages[$id]['priority'] ?  $this->messages[$id]['priority']:1) ;
			$output->datereceived = $this->mail->_strtotime($headers['DATE'],'ts',true);
			$output->displayto = ($headers['TO'] ? $headers['TO']:null); //$stat['FETCHED_HEADER']['to_name']
			$output->to = $this->messages[$id]['to_address']; //$stat['FETCHED_HEADER']['to_name']
			$output->from = $this->messages[$id]['sender_address']; //$stat['FETCHED_HEADER']['sender_name']
			$output->cc = ($headers['CC'] ? $headers['CC']:null);
			$output->reply_to = ($headers['REPLY_TO']?$headers['REPLY_TO']:null);
			$output->messageclass = "IPM.Note";
			if (stripos($this->messages[$id]['mimetype'],'multipart')!== false && 
				stripos($this->messages[$id]['mimetype'],'signed')!== false) 
			{
				$output->messageclass = "IPM.Note.SMIME.MultipartSigned";
			}
			// start AS12 Stuff
			$output->poommailflag = new SyncPoommailFlag();
			$output->poommailflag->flagstatus = 0;
			$output->internetcpid = 65001;
			$output->contentclass="urn:content-classes:message";
			// end AS12 Stuff
			
			// start handle Attachments
			$attachments = $this->mail->getMessageAttachments($id);

			// end handle Attachments
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($output));
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
	 * This function is called when a message has been changed on the PDA. You should parse the new
	 * message here and save the changes to disk. The return value must be whatever would be returned
	 * from StatMessage() after the message has been saved. This means that both the 'flags' and the 'mod'
	 * properties of the StatMessage() item may change via ChangeMessage().
	 * Note that this function will never be called on E-mail items as you can't change e-mail items, you
	 * can only set them as 'read'.
	 */
	function ChangeMessage($folderid, $id, $message) {
		return false;
	}

	/**
	 *  This function is analogous to GetMessageList.
	 *
	 *  @ToDo loop over available email accounts
	 */
	public function GetMessageList($folderid, $cutoffdate=NULL)
	{
		static $cutdate;
		if (!empty($cutoffdate) && $cutoffdate >0 && (empty($cutdate) || $cutoffdate != $cutdate))  $cutdate = $cutoffdate;
		debugLog (__METHOD__.' for Folder:'.$folderid.' SINCE:'.$cutdate);

		return $this->fetchMessages($folderid, $cutdate);
	}

	private function fetchMessages($folderid, $cutoffdate=NULL, $_id=NULL)
	{
		debugLog(__METHOD__.__LINE__);
		$this->_connect($this->account);
		$messagelist = array();
		if (!empty($cutoffdate)) $_filter = array('type'=>"SINCE",'string'=> date("d-M-Y", $cutoffdate));
		$rv = $this->splitID($folderid,$account,$_folderName,$id);
		if ($this->debugLevel>0) debugLog (__METHOD__.' for Folder:'.$_folderName.' Filter:'.array2string($_filter).' Ids:'.array2string($_id));
		$rv_messages = $this->mail->getHeaders($_folderName, $_startMessage=1, $_numberOfMessages=9999999, $_sort=0, $_reverse=false, $_filter, $_id);
		//debugLog(__METHOD__.__LINE__.array2string($rv_messages));
		foreach ((array)$rv_messages['header'] as $k => $vars)
		{
			if ($this->debugLevel>0) debugLog(__METHOD__.__LINE__.' ID:'.$vars['uid'].' Subject:'.$vars['subject']);
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
			if ($this->debugLevel>3) debugLog(__METHOD__.__LINE__.array2string($mess));
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
		if ($this->debugLevel>1) debugLog(__METHOD__."('$folder') --> parent=$parent --> $id");
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
			$this->_wasteID = $folder;
		}
		elseif($this->mail->isSentFolder($folder))
		{
			$folderObj->type = SYNC_FOLDER_TYPE_SENTMAIL;
			$this->_sentID = $folder;
		}
		else
		{
			$folderObj->type = SYNC_FOLDER_TYPE_USER_MAIL;
		}
		if ($this->debugLevel>1) debugLog(__METHOD__."($id) --> $folder --> type=$folderObj->type, parentID=$folderObj->parentid, displayname=$folderObj->displayname");
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
	 * Return a changes array
	 *
	 * if changes occurr default diff engine computes the actual changes
	 *
	 * @param string $folderid
	 * @param string &$syncstate on call old syncstate, on return new syncstate
	 * @return array|boolean false if $folderid not found, array() if no changes or array(array("type" => "fakeChange"))
	 */
	function AlterPingChanges($folderid, &$syncstate)
	{
		debugLog(__METHOD__.' called with '.$folderid);
		$this->splitID($folderid, $account, $folder);
		if (is_numeric($account)) $type = 'felamimail';
		if ($type != 'felamimail') return false;

		if (!isset($this->mail)) $this->mail = new bofelamimail ("UTF-8",false);

		$changes = array();
        debugLog("AlterPingChanges on $folderid ($folder) stat: ". $syncstate);
        $this->mail->reopen($folder);

        $status = $this->mail->getFolderStatus($folder);
        if (!$status) {
            debugLog("AlterPingChanges: could not stat folder $folder ");
            return false;
        } else {
            $newstate = "M:". $status['messages'] ."-R:". $status['recent'] ."-U:". $status['unseen']."-NUID:".$status['uidnext']."-UIDV:".$status['uidvalidity'];

            // message number is different - change occured
            if ($syncstate != $newstate) {
                $syncstate = $newstate;
                debugLog("AlterPingChanges: Change FOUND!");
                // build a dummy change
                $changes = array(array("type" => "fakeChange"));
            }
        }
		//error_log(__METHOD__."('$folderid','$syncstate_was') syncstate='$syncstate' returning ".array2string($changes));
		return $changes;
	}

	/**
	 * Should return a wastebasket folder if there is one. This is used when deleting
	 * items; if this function returns a valid folder ID, then all deletes are handled
	 * as moves and are sent to your backend as a move. If it returns FALSE, then deletes
	 * are always handled as real deletes and will be sent to your importer as a DELETE
	 */
	function GetWasteBasket() {
		return $this->_wasteID;
	}

	/**
	 * This function is called when the user has requested to delete (really delete) a message. Usually
	 * this means just unlinking the file its in or somesuch. After this call has succeeded, a call to
	 * GetMessageList() should no longer list the message. If it does, the message will be re-sent to the PDA
	 * as it will be seen as a 'new' item. This means that if you don't implement this function, you will
	 * be able to delete messages on the PDA, but as soon as you sync, you'll get the item back
	 */
	function DeleteMessage($folderid, $id) 
	{
		debugLog("IMAP-DeleteMessage: (fid: '$folderid'  id: '$id' )");
		/*
		$this->imap_reopenFolder($folderid);
		$s1 = @imap_delete ($this->_mbox, $id, FT_UID);
		$s11 = @imap_setflag_full($this->_mbox, $id, "\\Deleted", FT_UID);
		$s2 = @imap_expunge($this->_mbox);
		*/
		$_messageUID = (array)$id;

		$rv = $this->mail->deleteMessages($_messageUID, $folderid);
		// this may be a bit rude, it may be sufficient that GetMessageList does not list messages flagged as deleted
		if ($this->mail->mailPreferences->preferences['deleteOptions'] == 'mark_as_deleted')
		{
			// ignore mark as deleted -> Expunge!
			//$this->mail->icServer->expunge(); // do not expunge as GetMessageList does not List messages flagged as deleted
		}
		debugLog("IMAP-DeleteMessage: $rv");

		return $rv;
	}

	/**
	 * This should change the 'read' flag of a message on disk. The $flags
	 * parameter can only be '1' (read) or '0' (unread). After a call to
	 * SetReadFlag(), GetMessageList() should return the message with the
	 * new 'flags' but should not modify the 'mod' parameter. If you do
	 * change 'mod', simply setting the message to 'read' on the PDA will trigger
	 * a full resync of the item from the server
	 */
	function SetReadFlag($folderid, $id, $flags) 
	{
		debugLog("IMAP-SetReadFlag: (fid: '$folderid'  id: '$id'  flags: '$flags' )");
		$_messageUID = (array)$id;
		$rv = $this->mail->flagMessages((($flags) ? "read" : "unread"), $_messageUID,$_folderid);
		debugLog("IMAP-SetReadFlag -> set as " . (($flags) ? "read" : "unread") . "-->". $rv);

		return $rv;
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

		if ($this->debugLevel>1) debugLog(__METHOD__."($account,'$f',$id) type=$account, folder=$folder --> '$str'");

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

		if ($this->debugLevel>1) debugLog(__METHOD__."('$str','$account','$folder',$id)");
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
