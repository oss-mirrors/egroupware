<?php
/**
 * EGroupware: ActiveSync access: Addressbook plugin
 *
 * @link http://www.egroupware.org
 * @package addressbook
 * @subpackage activesync
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
 * @author Philip Herbert <philip@knauber.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Addressbook activesync plugin
 */
class addressbook_activesync implements activesync_plugin_write, activesync_plugin_search_gal
{
	/**
	 * @var BackendEGW
	 */
	private $backend;

	/**
	 * Instance of addressbook_bo
	 *
	 * @var addressbook_bo
	 */
	private $addressbook;

	/**
	 * Mapping of ActiveSync SyncContact attributes to EGroupware contact array-keys
	 *
	 * @var array
	 */
	static public $mapping = array(
	    //'anniversary'	=> '',
    	'assistantname'	=> 'assistent',
    	'assistnamephonenumber'	=> 'tel_assistent',
    	'birthday'	=>	'bday',
    	'body'	=> 'note',
    	//'bodysize'	=> '',
    	//'bodytruncated'	=> '',
    	//'business2phonenumber'	=> '',
    	'businesscity'	=>	'adr_one_locality',
    	'businesscountry'	=> 'adr_one_countryname',
    	'businesspostalcode'	=> 'adr_one_postalcode',
    	'businessstate'	=> 'adr_one_region',
    	'businessstreet'	=> 'adr_one_street',
    	'businessfaxnumber'	=> 'tel_fax',
    	'businessphonenumber'	=> 'tel_work',
    	'carphonenumber'	=> 'tel_car',
    	'categories'	=> 'cat_id',
    	//'children'	=> '',
    	'companyname'	=> 'org_name',
    	'department'	=>	'org_unit',
    	'email1address'	=> 'email',
    	'email2address'	=> 'email_home',
    	//'email3address'	=> '',
    	'fileas'	=>	'n_fileas',
    	'firstname'	=>	'n_given',
    	//'home2phonenumber'	=> '',
    	'homecity'	=> 'adr_two_locality',
    	'homecountry'	=> 'adr_two_countryname',
    	'homepostalcode'	=> 'adr_two_postalcode',
    	'homestate'	=> 'adr_two_region',
    	'homestreet'	=>	'adr_two_street',
    	'homefaxnumber'	=> 'tel_fax_home',
    	'homephonenumber'	=>	'tel_home',
    	'jobtitle'	=>	'role',
    	'lastname'	=> 'n_family',
    	'middlename'	=> 'n_middle',
    	'mobilephonenumber'	=> 'tel_cell',
    	'officelocation'	=> 'room',
    	//'othercity'	=> '',
    	//'othercountry'	=> '',
    	//'otherpostalcode'	=> '',
    	//'otherstate'	=> '',
    	//'otherstreet'	=> '',
    	'pagernumber'	=> 'tel_pager',
    	//'radiophonenumber'	=> '',
    	//'spouse'	=> '',
    	'suffix'	=>	'n_suffix',
    	'title'	=> 'title',	// @TODO: check if n_prefix
    	'webpage'	=> 'url',
    	//'yomicompanyname'	=> '',
    	//'yomifirstname'	=>	'',
    	//'yomilastname'	=>	'',
    	//'rtf'	=> '',
    	'picture'	=> 'jpegphoto',
    	//'nickname'	=>	'',
    	//'airsyncbasebody'	=>	'',
	);

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
	 * Get addressbooks (no extra private one and do some caching)
	 *
	 * @param int $account=null account_id of addressbook or null to get array of all addressbooks
	 * @return string|array addressbook name of array with int account_id => label pairs
	 */
	private function get_addressbooks($account=null)
	{
		static $abs;

		if (!isset($abs))
		{
			translation::add_app('addressbook');	// we need the addressbook translations

			if ($GLOBALS['egw_info']['user']['preferences']['addressbook']['private_addressbook'])
			{
				unset($GLOBALS['egw_info']['user']['preferences']['addressbook']['private_addressbook']);
				if (isset($this->addressbook)) $this->addressbook->private_addressbook = false;
			}
			if (!isset($this->addressbook)) $this->addressbook = new addressbook_bo();

			$abs = $this->addressbook->get_addressbooks(EGW_ACL_READ);
		}
		return is_null($account) ? $abs : $abs[$account];
	}

	/**
	 *  This function is analogous to GetMessageList.
	 *
	 *  @ToDo implement preference, include own private calendar
	 */
	public function GetFolderList()
	{
		// error_log(print_r($this->addressbook->get_addressbooks(EGW_ACL_READ),true));

		foreach ($this->get_addressbooks() as $account => $label)
		{
			$folderlist[] = array(
				'id'	=>	$this->backend->createID('addressbook',$account),
				'mod'	=>	$label,
				'parent'=>	'0',
			);
		};
		debugLog(__METHOD__."() returning ".array2string($folderlist));
		//error_log(__METHOD__."() returning ".array2string($folderlist));
		return $folderlist;
	}

	/**
	 * Get Information about a folder
	 *
	 * @param string $id
	 * @return SyncFolder|boolean false on error
	 */
	public function GetFolder($id)
	{
		$this->backend->splitID($id, $type, $owner);

		$folderObj = new SyncFolder();
		$folderObj->serverid = $id;
		$folderObj->parentid = '0';
		$folderObj->displayname = $this->get_addressbooks($owner);

		if ($owner == $GLOBALS['egw_info']['user']['account_id'])
		{
			$folderObj->type = SYNC_FOLDER_TYPE_CONTACT;
		}
		else
		{
			$folderObj->type = SYNC_FOLDER_TYPE_USER_CONTACT;
		}
		//error_log(__METHOD__."('$id') returning ".array2string($folderObj));
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
		$this->backend->splitID($id, $type, $owner);

		$stat = array(
			'id'     => $id,
			'mod'    => $this->get_addressbooks($owner),
			'parent' => '0',
		);
		//error_log(__METHOD__."('$id') returning ".array2string($stat));
		debugLog(__METHOD__."('$id') returning ".array2string($stat));
		return $stat;
	}

	/**
	 * Should return a list (array) of messages, each entry being an associative array
     * with the same entries as StatMessage(). This function should return stable information; ie
     * if nothing has changed, the items in the array must be exactly the same. The order of
     * the items within the array is not important though.
     *
     * The cutoffdate is a date in the past, representing the date since which items should be shown.
     * This cutoffdate is determined by the user's setting of getting 'Last 3 days' of e-mail, etc. If
     * you ignore the cutoffdate, the user will not be able to select their own cutoffdate, but all
     * will work OK apart from that.
     *
     * @param string $id folder id
     * @param int $cutoffdate=null
     * @return array
  	 */
	function GetMessageList($id, $cutoffdate=NULL)
	{
		if (!isset($this->addressbook)) $this->addressbook = new addressbook_bo();

		$this->backend->splitID($id,$type,$user);
		$filter = array('owner' => $user);

		$messagelist = array();
		if (($contacts =& $this->addressbook->search($criteria,'contact_id,contact_etag',$order_by='',$extra_cols='',$wildcard='',
			$empty=false,$op='AND',$start=false,$filter)))
		{
			foreach($contacts as $contact)
			{
				$messagelist[] = $this->StatMessage($id, $contact);
			}
		}
		//error_log(__METHOD__."('$id') returning ".count($messagelist).' entries');
		return $messagelist;
	}

	/**
	 * Get specified item from specified folder.
	 *
	 * @param string $folderid
	 * @param string $id
	 * @param int $truncsize
	 * @param int $bodypreference
	 * @param bool $mimesupport
	 * @return $messageobject|boolean false on error
	*/
	public function GetMessage($folderid, $id, $truncsize, $bodypreference=false, $mimesupport = 0)
	{
		if (!isset($this->addressbook)) $this->addressbook = new addressbook_bo();

		debugLog (__METHOD__."('$folderid', $id, truncsize=$truncsize, bodyprefence=$bodypreference, mimesupport=$mimesupport)");
		$this->backend->splitID($folderid, $type, $account);
		if ($type != 'addressbook' || !($contact = $this->addressbook->read($id)))
		{
			error_log(__METHOD__."('$folderid',$id,...) Folder wrong (type=$type, account=$account) or contact not existing (read($id)=".array2string($contact).")! returning false");
			return false;
		}
		$message = new SyncContact();
		foreach(self::$mapping as $key => $attr)
		{
			switch ($attr)
			{
				case 'note':
					if (empty($contact[$attr])) break;
					if ($bodypreference == false)
					{
    			    	$message->body = $contact[$attr];
			    		$message->bodysize = strlen($message->body);
        		    	$message->bodytruncated = 0;
					}
					else
					{
						$message->airsyncbasebody = new SyncAirSyncBaseBody();
			    		debugLog("airsyncbasebody!");
			    		$message->airsyncbasenativebodytype=1;
			    		$message->airsyncbasebody = new SyncAirSyncBaseBody();
						if (isset($bodypreference[2]))
						{
							//debugLog("HTML Body");
							$message->airsyncbasebody->type = 2;
							$html = '<html>'.
									'<head>'.
									'<meta name="Generator" content="Z-Push">'.
									'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
									'</head>'.
									'<body>'.
									str_replace(array("\n","\r","\r\n"),"<br />",$contact[$attr]).
									'</body>'.
									'</html>';
									if (isset($bodypreference[2]["TruncationSize"]) && strlen($html) > $bodypreference[2]["TruncationSize"])
									{
        	        	    			$html = utf8_truncate($html,$bodypreference[2]["TruncationSize"]);
										$message->airsyncbasebody->truncated = 1;
									}
									$message->airsyncbasebody->data = $html;
									$message->airsyncbasebody->estimateddatasize = strlen($html);
						}
						else
						{
							// debugLog("Plaintext Body");
							$note = str_replace("\n","\r\n",str_replace("\r","",$contact[$attr]));
							$message->airsyncbasebody->type = 1;
							if(isset($bodypreference[1]["TruncationSize"]) && strlen($note) > $bodypreference[1]["TruncationSize"])
							{
								$note = utf8_truncate($note, $bodypreference[1]["TruncationSize"]);
								$message->airsyncbasebody->truncated = 1;
    	    		        }
							$message->airsyncbasebody->estimateddatasize = strlen($note);
							$message->airsyncbasebody->data = $note;
						}
						if ($message->airsyncbasebody->type != 3 && (!isset($message->airsyncbasebody->data) || strlen($message->airsyncbasebody->data) == 0))
						{
        					$message->airsyncbasebody->data = " ";
						}
					}
					break;

				case 'jpegphoto':
					if (!empty($contact[$attr])) $message->$key = base64_encode($contact[$attr]);
					break;

				case 'bday':
					if (!empty($contact[$attr])) $message->$key = egw_time::to($contact[$attr],'ts');
					break;

				case 'cat_id':
					/*$message->$key = array();
					foreach($contact[$attr] ? explode(',',$contact[$attr]) : array() as $cat_id)
					{
						$message->categories[] = categories::id2name($cat_id);
					}*/
					break;

				default:
					if (!empty($contact[$attr])) $message->$key = $contact[$attr];
			}
		}
		//error_log(__METHOD__."(folder='$folderid',$id,...) returning ".array2string($message));
		return $message;
	}

	/**
	 * StatMessage should return message stats, analogous to the folder stats (StatFolder). Entries are:
     * 'id'     => Server unique identifier for the message. Again, try to keep this short (under 20 chars)
     * 'flags'     => simply '0' for unread, '1' for read
     * 'mod'    => modification signature. As soon as this signature changes, the item is assumed to be completely
     *             changed, and will be sent to the PDA as a whole. Normally you can use something like the modification
     *             time for this field, which will change as soon as the contents have changed.
     *
     * @param string $folderid
     * @param int|array $contact contact id or array
     * @return array
     */
	public function StatMessage($folderid, $contact)
	{
		if (!isset($this->addressbook)) $this->addressbook = new addressbook_bo();

		if (!is_array($contact)) $contact = $this->addressbook->read($contact);

		if (!$contact)
		{
			$stat = false;
		}
		else
		{
			$stat = array(
				'mod' => $contact['etag'],
				'id' => $contact['id'],
				'flags' => 1,
			);
		}
		//debugLog (__METHOD__."('$folderid',".array2string($id).") returning ".array2string($stat));
		//error_log(__METHOD__."('$folderid',$contact) returning ".array2string($stat));
		return $stat;
	}

	/**
	 *  Creates or modifies a folder
     *
     * @param $id of the parent folder
     * @param $oldid => if empty -> new folder created, else folder is to be renamed
     * @param $displayname => new folder name (to be created, or to be renamed to)
     * @param type => folder type, ignored in IMAP
     *
     * @return stat | boolean false on error
     *
     */
    public function ChangeFolder($id, $oldid, $displayname, $type)
    {
    	debugLog(__METHOD__." not implemented");
    }

    /**
     * Deletes (really delete) a Folder
     *
     * @param $parentid of the folder to delete
     * @param $id of the folder to delete
     *
     * @return
     * @TODO check what is to be returned
     *
     */
    public function DeleteFolder($parentid, $id)
    {
    	debugLog(__METHOD__." not implemented");
    }


    /**
     * Changes or adds a message on the server
     *
     * @param $folderid
     * @param $id for change | empty for create new
     * @param $message object to SyncObject to create
     *
     * @return $stat whatever would be returned from StatMessage
     *
     * This function is called when a message has been changed on the PDA. You should parse the new
     * message here and save the changes to disk. The return value must be whatever would be returned
     * from StatMessage() after the message has been saved. This means that both the 'flags' and the 'mod'
     * properties of the StatMessage() item may change via ChangeMessage().
     * Note that this function will never be called on E-mail items as you can't change e-mail items, you
     * can only set them as 'read'.
     */
    public function ChangeMessage($folderid, $id, $message)
    {
    	if (!isset($this->addressbook)) $this->addressbook = new addressbook_bo();

    	$this->backend->splitID($folderid, $type, $account);
    	// error_log(__METHOD__. " Id " .$id. " Account ". $account . " FolderID " . $folderid);
    	if ($type != 'addressbook') // || !($contact = $this->addressbook->read($id)))
		{
			debugLog(__METHOD__." Folder wrong or contact not existing");
			return false;
		}
		if (empty($id))
		{
			$contact = array();
			debugLog (__METHOD__." creating new contact");
			foreach (self::$mapping as $key => $attr)
			{
				switch ($attr)
				{
					case 'note':
						error_log ("Note !");
						break;

					case 'jpegphoto':
						error_log("jpegphoto");
						if (!empty($message->$key) && (!empty(self::$mapping[$key])) )  $contact[$attr] = base64_decode($message->$key);
						break;

					default:
						if (!empty($message->$key) && (!empty(self::$mapping[$key])) )  $contact[$attr] = $message->$key;
						break;
				}
			}

			$contact['owner'] = $account;
			$this->addressbook->fixup_contact($contact);
			error_log (print_r($contact,true));
			//$this->addressbook->save($contact);
		}
    }

    /**
     * Moves a message from one folder to another
     *
     * @param $folderid of the current folder
     * @param $id of the message
     * @param $newfolderid
     *
     * @return $newid as a string | boolean false on error
     *
     * After this call, StatMessage() and GetMessageList() should show the items
     * to have a new parent. This means that it will disappear from GetMessageList() will not return the item
     * at all on the source folder, and the destination folder will show the new message
     *
     */
    public function MoveMessage($folderid, $id, $newfolderid)
    {
    	error_log(__METHOD__);
    }


    /**
     * Delete (really delete) a message in a folder
     *
     * @param $folderid
     * @param $id
     *
     * @TODO check what is to be returned
     *
     * @DESC After this call has succeeded, a call to
     * GetMessageList() should no longer list the message. If it does, the message will be re-sent to the PDA
     * as it will be seen as a 'new' item. This means that if you don't implement this function, you will
     * be able to delete messages on the PDA, but as soon as you sync, you'll get the item back
     */
    public function DeleteMessage($folderid, $id)
    {
    	error_log (__METHOD__);
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
		$this->backend->splitID($folderid, $type, $owner);

		if ($type != 'addressbook') return false;

    	if (!isset($this->addressbook)) $this->addressbook = new addressbook_bo();
		$ctag = $this->addressbook->get_ctag($owner);

		$changes = array();	// no change
		$syncstate_was = $syncstate;

		if ($ctag !== $syncstate)
		{
			$syncstate = $ctag;
			$changes = array(array('type' => 'fakeChange'));
		}
		//error_log(__METHOD__."('$folderid','$syncstate_was') syncstate='$syncstate' returning ".array2string($changes));
		return $changes;
	}

	/**
	 * Search global address list for a given pattern
	 *
	 * @param string $searchquery
	 * @return array with just rows (no values for keys rows, status or global_search_status!)
	 * @todo search range not verified, limits might be a good idea
	 */
	function getSearchResultsGAL($searchquery)
	{
    	if (!isset($this->addressbook)) $this->addressbook = new addressbook_bo();

		$items = array();
		if (($contacts =& $this->addressbook->search($searchquery, false, false, '', '%', false, 'OR')))
		{
			foreach($contacts as $contact)
			{
			  	$item['username'] = $contact['n_family'];
				$item['fullname'] = $contact['n_fn'];
				if (!trim($item['fullname'])) $item['fullname'] = $item['username'];
				$item['emailaddress'] = $contact['email'] ? $contact['email'] : (string)$contact['email_private'] ;
				$item['nameid'] = $searchquery;
				$item['phone'] = (string)$contact['tel_work'];
				$item['homephone'] = (string)$contact['tel_home'];
				$item['mobilephone'] = (string)$contact['tel_cell'];
				$item['company'] = (string)$contact['org_name'];
				$item['office'] = $contact['room'];
				$item['title'] = $contact['title'];

			  	//do not return users without email
				if (!trim($item['emailaddress'])) continue;

				$items[] = $item;
			}
		}
		return $items;
	}
}
