<?php
/**
 * Addressbook - General business object
 *
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package addressbook
 * @copyright (c) 2005/6 by Cornelius Weiss <egw@von-und-zu-weiss.de> and Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

require_once(EGW_INCLUDE_ROOT.'/addressbook/inc/class.socontacts.inc.php');

/**
 * General business object of the adressbook
 *
 * @package addressbook
 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2005/6 by Cornelius Weiss <egw@von-und-zu-weiss.de> and Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */
class bocontacts extends socontacts
{
	/**
	 * @var int $tz_offset_s offset in secconds between user and server-time,
	 *	it need to be add to a server-time to get the user-time or substracted from a user-time to get the server-time
	 */
	var $tz_offset_s;

	/**
	 * @var int $now_su actual user (!) time
	 */
	var $now_su;

	/**
	 * @var array $timestamps timestamps
	 */
	var $timestamps = array('modified','created');

	/**
	 * @var array $fileas_types
	 */
	var $fileas_types = array(
		'org_name: n_family, n_given',
		'org_name: n_family, n_prefix',
		'org_name: n_given n_family',
		'org_name: n_fn',
		'n_family, n_given: org_name',
		'n_family, n_prefix: org_name',
		'n_given n_family: org_name',
		'n_prefix n_family: org_name',
		'n_fn: org_name',
		'org_name',
		'org_name - org_unit',
		'n_given n_family',
		'n_prefix n_family',
		'n_family, n_given',
		'n_family, n_prefix',
		'n_fn',
	);

	/**
	 * @var array $org_fields fields belonging to the (virtual) organisation entry
	 */
	var $org_fields = array(
		'org_name',
		'org_unit',
		'adr_one_street',
		'adr_one_street2',
		'adr_one_locality',
		'adr_one_region',
		'adr_one_postalcode',
		'adr_one_countryname',
		'label',
		'tel_work',
		'tel_fax',
		'tel_assistent',
		'assistent',
		'email',
		'url',
		'tz',
	);

	/**
	 * Which fields is a (non-admin) user allowed to edit in his own account
	 *
	 * @var array
	 */
	var $own_account_acl;

	/**
	 * @var double $org_common_factor minimum percentage of the contacts with identical values to construct the "common" (virtual) org-entry
	 */
	var $org_common_factor = 0.6;

	var $contact_fields = array();
	var $business_contact_fields = array();
	var $home_contact_fields = array();

	/**
	 * Number and message of last error or false if no error, atm. only used for saving
	 *
	 * @var string/boolean
	 */
	var $error;
	/**
	 * Addressbook preferences of the user
	 *
	 * @var array
	 */
	var $prefs;
	/**
	 * Default addressbook for new contacts, if no addressbook is specified (user preference)
	 *
	 * @var int
	 */
	var $default_addressbook;
	/**
	 * Default addressbook is the private one
	 *
	 * @var boolean
	 */
	var $default_private;

	function bocontacts($contact_app='addressbook')
	{
		$this->socontacts($contact_app);

		$this->tz_offset_s = 3600 * $GLOBALS['egw_info']['user']['preferences']['common']['tz_offset'];
		$this->now_su = time() + $this->tz_offset_s;

		$this->prefs =& $GLOBALS['egw_info']['user']['preferences']['addressbook'];
		// get the default addressbook from the users prefs
		$this->default_addressbook = $GLOBALS['egw_info']['user']['preferences']['addressbook']['add_default'] ?
			(int)$GLOBALS['egw_info']['user']['preferences']['addressbook']['add_default'] : $this->user;
		$this->default_private = substr($GLOBALS['egw_info']['user']['preferences']['addressbook']['add_default'],-1) == 'p';
		if ($this->default_addressbook > 0 && $this->default_addressbook != $this->user &&
			($this->default_private ||
			$this->default_addressbook == (int)$GLOBALS['egw']->preferences->forced['addressbook']['add_default'] ||
			$this->default_addressbook == (int)$GLOBALS['egw']->preferences->default['addressbook']['add_default']))
		{
			$this->default_addressbook = $this->user;	// admin set a default or forced pref for personal addressbook
		}

		$this->contact_fields = array(
			'id'                   => lang('Contact ID'),
			'tid'                  => lang('Type'),
			'owner'                => lang('Addressbook'),
			'private'              => lang('private'),
			'cat_id'               => lang('Category'),
			'n_prefix'             => lang('prefix'),
			'n_given'              => lang('first name'),
			'n_middle'             => lang('middle name'),
			'n_family'             => lang('last name'),
			'n_suffix'             => lang('suffix'),
			'n_fn'                 => lang('full name'),
			'n_fileas'             => lang('own sorting'),
			'bday'                 => lang('birthday'),
			'org_name'             => lang('Company'),
			'org_unit'             => lang('Department'),
			'title'                => lang('Title'),
			'role'                 => lang('Role'),
			'assistent'            => lang('Assistent'),
			'room'                 => lang('Room'),
			'adr_one_street'       => lang('street').' ('.lang('business').')',
			'adr_one_street2'      => lang('address line 2').' ('.lang('business').')',
			'adr_one_locality'     => lang('city').' ('.lang('business').')',
			'adr_one_region'       => lang('state').' ('.lang('business').')',
			'adr_one_postalcode'   => lang('zip code').' ('.lang('business').')',
			'adr_one_countryname'  => lang('country').' ('.lang('business').')',
			'label'                => lang('label'),
			'adr_two_street'       => lang('street').' ('.lang('private').')',
			'adr_two_street2'      => lang('address line 2').' ('.lang('private').')',
			'adr_two_locality'     => lang('city').' ('.lang('private').')',
			'adr_two_region'       => lang('state').' ('.lang('private').')',
			'adr_two_postalcode'   => lang('zip code').' ('.lang('private').')',
			'adr_two_countryname'  => lang('country').' ('.lang('private').')',
			'tel_work'             => lang('work phone'),
			'tel_cell'             => lang('mobile phone'),
			'tel_fax'              => lang('fax').' ('.lang('business').')',
			'tel_assistent'        => lang('assistent phone'),
			'tel_car'              => lang('car phone'),
			'tel_pager'            => lang('pager'),
			'tel_home'             => lang('home phone'),
			'tel_fax_home'         => lang('fax').' ('.lang('private').')',
			'tel_cell_private'     => lang('mobile phone').' ('.lang('private').')',
			'tel_other'            => lang('other phone'),
			'tel_prefer'           => lang('preferred phone'),
			'email'                => lang('email').' ('.lang('business').')',
			'email_home'           => lang('email').' ('.lang('private').')',
			'url'                  => lang('url').' ('.lang('business').')',
			'url_home'             => lang('url').' ('.lang('private').')',
			'freebusy_uri'         => lang('Freebusy URI'),
			'calendar_uri'         => lang('Calendar URI'),
			'note'                 => lang('note'),
			'tz'                   => lang('time zone'),
			'geo'                  => lang('geo'),
			'pubkey'               => lang('public key'),
			'created'              => lang('created'),
			'creator'              => lang('created by'),
			'modified'             => lang('last modified'),
			'modifier'             => lang('last modified by'),
			'jpegphoto'            => lang('photo'),
		);
		$this->business_contact_fields = array(
			'org_name'             => lang('Company'),
			'org_unit'             => lang('Department'),
			'title'                => lang('Title'),
			'role'                 => lang('Role'),
			'n_prefix'             => lang('prefix'),
			'n_given'              => lang('first name'),
			'n_middle'             => lang('middle name'),
			'n_family'             => lang('last name'),
			'n_suffix'             => lang('suffix'),
			'adr_one_street'       => lang('street').' ('.lang('business').')',
			'adr_one_street2'      => lang('address line 2').' ('.lang('business').')',
			'adr_one_locality'     => lang('city').' ('.lang('business').')',
			'adr_one_region'       => lang('state').' ('.lang('business').')',
			'adr_one_postalcode'   => lang('zip code').' ('.lang('business').')',
			'adr_one_countryname'  => lang('country').' ('.lang('business').')',
		);
		$this->home_contact_fields = array(
			'org_name'             => lang('Company'),
			'org_unit'             => lang('Department'),
			'title'                => lang('Title'),
			'role'                 => lang('Role'),
			'n_prefix'             => lang('prefix'),
			'n_given'              => lang('first name'),
			'n_middle'             => lang('middle name'),
			'n_family'             => lang('last name'),
			'n_suffix'             => lang('suffix'),
			'adr_two_street'       => lang('street').' ('.lang('business').')',
			'adr_two_street2'      => lang('address line 2').' ('.lang('business').')',
			'adr_two_locality'     => lang('city').' ('.lang('business').')',
			'adr_two_region'       => lang('state').' ('.lang('business').')',
			'adr_two_postalcode'   => lang('zip code').' ('.lang('business').')',
			'adr_two_countryname'  => lang('country').' ('.lang('business').')',
		);
		//_debug_array($this->contact_fields);
		$this->own_account_acl = unserialize($GLOBALS['egw_info']['server']['own_account_acl']);
		// we have only one acl (n_fn) for the whole name, as not all backends store every part in an own field
		if ($this->own_account_acl && in_array('n_fn',$this->own_account_acl))
		{
			$this->own_account_acl = array_merge($this->own_account_acl,array('n_prefix','n_given','n_middle','n_family','n_suffix'));
		}
		if ($GLOBALS['egw_info']['server']['org_fileds_to_update'])
		{
			$this->org_fields =  unserialize($GLOBALS['egw_info']['server']['org_fileds_to_update']);
		}
	}

	/**
	 * calculate the file_as string from the contact and the file_as type
	 *
	 * @param array $contact
	 * @param string $type=null file_as type, default null to read it from the contact, unknown/not set type default to the first one
	 * @return string
	 */
	function fileas($contact,$type=null)
	{
		if (is_null($type)) $type = $contact['fileas_type'];
		if (!$type) $type = $this->fileas_types[0];

		if (strpos($type,'n_fn') !== false) $contact['n_fn'] = $this->fullname($contact);

		$fileas = str_replace(array('n_prefix','n_given','n_middle','n_family','n_suffix','n_fn','org_name','org_unit','adr_one_locality'),
			array($contact['n_prefix'],$contact['n_given'],$contact['n_middle'],$contact['n_family'],$contact['n_suffix'],
				$contact['n_fn'],$contact['org_name'],$contact['org_unit'],$contact['adr_one_locality']),$type);

		// removing empty delimiters, caused by empty contact fields
		$fileas = str_replace(array(', , : ',', : ',': , ',', , ',': : '),array(': ',': ',': ',', ',': '),$fileas);
		while ($fileas{0} == ':' ||  $fileas{0} == ',') $fileas = substr($fileas,2);
		while (substr($fileas,-2) == ': ' || substr($fileas,-2) == ', ') $fileas = substr($fileas,0,-2);

		//echo "<p align=right>bocontacts::fileas(,$type)='$fileas'</p>\n";
		return $fileas;
	}

	/**
	 * determine the file_as type from the file_as string and the contact
	 *
	 * @param array $contact
	 * @param string $type=null file_as type, default null to read it from the contact, unknown/not set type default to the first one
	 * @return string
	 */
	function fileas_type($contact,$file_as=null)
	{
		if (is_null($file_as)) $file_as = $contact['n_fileas'];

		if ($file_as)
		{
			foreach($this->fileas_types as $type)
			{
				if ($this->fileas($contact,$type) == $file_as)
				{
					return $type;
				}
			}
		}
		return $this->fileas_types[0];
	}

	/**
	 * get selectbox options for the fileas types with translated labels, or real content
	 *
	 * @param array $contact=null real content to use, default none
	 * @return array with options: fileas type => label pairs
	 */
	function fileas_options($contact=null)
	{
		$labels = array(
			'n_prefix' => lang('prefix'),
			'n_given'  => lang('first name'),
			'n_middle' => lang('middle name'),
			'n_family' => lang('last name'),
			'n_suffix' => lang('suffix'),
			'n_fn'     => lang('full name'),
			'org_name' => lang('company'),
			'org_unit' => lang('department'),
			'adr_one_locality' => lang('city'),
		);
		foreach($labels as $name => $label)
		{
			if ($contact[$name]) $labels[$name] = $contact[$name];
		}
		foreach($this->fileas_types as $fileas_type)
		{
			$options[$fileas_type] = $this->fileas($labels,$fileas_type);
		}
		return $options;
	}

	/**
	 * get full name from the name-parts
	 *
	 * @param array $contact
	 * @return string full name
	 */
	function fullname($contact)
	{
		$parts = array();
		foreach(array('n_prefix','n_given','n_middle','n_family','n_suffix') as $n)
		{
			if ($contact[$n]) $parts[] = $contact[$n];
		}
		return implode(' ',$parts);
	}

	/**
	 * changes the data from the db-format to your work-format
	 *
	 * it gets called everytime when data is read from the db
	 * This function needs to be reimplemented in the derived class
	 *
	 * @param array $data
	 */
	function db2data($data)
	{
		// convert timestamps from server-time in the db to user-time
		foreach($this->timestamps as $name)
		{
			if(isset($data[$name]))
			{
				$data[$name] += $this->tz_offset_s;
			}
		}
		$data['photo'] = $this->photo_src($data['id'],$data['jpegphoto']);

		// set freebusy_uri for accounts
		if (!$data['freebusy_uri'] && !$data['owner'] && $data['account_id'] && !is_object($GLOBALS['egw_setup']))
		{
			static $fb_url;
			if (!$fb_url && @is_dir(EGW_SERVER_ROOT.'/calendar/inc')) $fb_url = ExecMethod('calendar.bocal.freebusy_url','');
			if ($fb_url) $data['freebusy_uri'] = $fb_url.urlencode($GLOBALS['egw']->accounts->id2name($data['account_id']));
		}
		return $data;
	}

	/**
	 * src for photo: returns array with linkparams if jpeg exists or the $default image-name if not
	 * @param int $id contact_id
	 * @param boolean $jpeg=false jpeg exists or not
	 * @param string $default='' image-name to use if !$jpeg, eg. 'template'
	 * @return string/array
	 */
	function photo_src($id,$jpeg,$default='')
	{
		return $jpeg ? array(
			'menuaction' => 'addressbook.uicontacts.photo',
			'contact_id' => $id,
		) : $default;
	}

	/**
	 * changes the data from your work-format to the db-format
	 *
	 * It gets called everytime when data gets writen into db or on keys for db-searches
	 * this needs to be reimplemented in the derived class
	 *
	 * @param array $data
	 */
	function data2db($data)
	{
		// convert timestamps from user-time to server-time in the db
		foreach($this->timestamps as $name)
		{
			if(isset($data[$name]))
			{
				$data[$name] -= $this->tz_offset_s;
			}
		}
		return $data;
	}

	/**
	* deletes contact in db
	*
	* @param mixed &$contact contact array with key id or (array of) id(s)
	* @param boolean $deny_account_delete=true if true never allow to delete accounts
	* @return boolean true on success or false on failiure
	*/
	function delete($contact,$deny_account_delete=true)
	{
		if (is_array($contact) && isset($contact['id']))
		{
			$contact = array($contact);
		}
		elseif (!is_array($contact))
		{
			$contact = array($contact);
		}
		foreach($contact as $c)
		{
			$id = is_array($c) ? $c['id'] : $c;

			if ($this->check_perms(EGW_ACL_DELETE,$c,$deny_account_delete) && parent::delete($id))
			{
				egw_link::unlink(0,'addressbook',$id);
				$GLOBALS['egw']->contenthistory->updateTimeStamp('contacts', $id, 'delete', time());
			}
			else
			{
				return false;
			}
		}
		return true;
	}

	/**
	* saves contact to db
	*
	* @param array &$contact contact array from etemplate::exec
	* @param boolean $ignore_acl=false should the acl be checked or not
	* @return int/string/boolean id on success, false on failure, the error-message is in $this->error
	*/
	function save(&$contact,$ignore_acl=false)
	{
		// remember if we add or update a entry
		if (($isUpdate = $contact['id']))
		{
			if (!isset($contact['owner']) || !isset($contact['private']))	// owner/private not set on update, eg. SyncML
			{
				if (($old = $this->read($contact['id'])))	// --> try reading the old entry and set it from there
				{
					if(!isset($contact['owner']))
					{
						$contact['owner'] = $old['owner'];
					}
					if(!isset($contact['private']))
					{
						$contact['private'] = $old['private'];
					}
				}
				else	// entry not found --> create a new one
				{
					$isUpdate = $contact['id'] = null;
				}
			}
		}
		else
		{
			// if no owner/addressbook set use the setting of the add_default prefs (if set, otherwise the users personal addressbook)
			if (!isset($contact['owner'])) $contact['owner'] = $this->default_addressbook;
			if (!isset($contact['private'])) $contact['private'] = (int)$this->default_private;
			// allow admins to import contacts with creator / created date set
			if (!$contact['creator'] || !$this->is_admin($contact)) $contact['creator'] = $this->user;
			if (!$contact['created'] || !$this->is_admin($contact)) $contact['created'] = $this->now_su;

			if (!$contact['tid']) $contact['tid'] = 'n';
		}
		if (!$contact['owner'])
		{
			$contact['private'] = 0;	// accounts are never private!
		}
		if(!$ignore_acl && !$this->check_perms($isUpdate ? EGW_ACL_EDIT : EGW_ACL_ADD,$contact))
		{
			$this->error = 'access denied';
			return false;
		}
		// convert categories
		if (is_array($contact['cat_id'])) {
			$contact['cat_id'] = implode(',',$contact['cat_id']);
		}
		// last modified
		$contact['modifier'] = $this->user;
		$contact['modified'] = $this->now_su;
		// set full name and fileas from the content
		if (isset($contact['n_family']) && isset($contact['n_given']))
		{
			$contact['n_fn'] = $this->fullname($contact);
			if (isset($contact['org_name'])) $contact['n_fileas'] = $this->fileas($contact);
		}
		$to_write = $contact;
		// (non-admin) user editing his own account, make sure he does not change fields he is not allowed to (eg. via SyncML or xmlrpc)
		if (!$ignore_acl && !$contact['owner'] && !$this->is_admin($contact))
		{
			foreach($contact as $field => $value)
			{
				if (!in_array($field,$this->own_account_acl) && !in_array($field,array('id','owner','account_id','modified','modifier')))
				{
					unset($to_write[$field]);	// user is now allowed to change that
				}
			}
		}
		// we dont update the content-history, if we run inside setup (admin-account-creation)
		if(!($this->error = parent::save($to_write)) && is_object($GLOBALS['egw']->contenthistory))
		{
			$contact['id'] = $to_write['id'];
			$contact['etag'] = $to_write['etag'];
			$GLOBALS['egw']->contenthistory->updateTimeStamp('contacts', $contact['id'],$isUpdate ? 'modify' : 'add', time());

			if ($contact['account_id'])	// invalidate the cache of the accounts class
			{
				$GLOBALS['egw']->accounts->cache_invalidate($contact['account_id']);
			}
			// notify interested apps about changes in the account-contact data
			if (!$to_write['owner'] && $to_write['account_id'])
			{
				$to_write['location'] = 'editaccountcontact';
				$GLOBALS['egw']->hooks->process($to_write,False,True);	// called for every app now, not only enabled ones));
			}
		}

		return $this->error ? false : $contact['id'];
	}

	/**
	* reads contacts matched by key and puts all cols in the data array
	*
	* @param int/string $contact_id
	* @return array/boolean array with contact data, null if not found or false on no view perms
	*/
	function read($contact_id)
	{
		if (!($data = parent::read($contact_id)))
		{
			return null;	// not found
		}
		if (!$this->check_perms(EGW_ACL_READ,$data))
		{
			return false;	// no view perms
		}
		// determine the file-as type
		$data['fileas_type'] = $this->fileas_type($data);

		return $data;
	}

	/**
	* Checks if the current user has the necessary ACL rights
	*
	* If the access of a contact is set to private, one need a private grant for a personal addressbook
	* or the group membership for a group-addressbook
	*
	* @param int $needed necessary ACL right: EGW_ACL_{READ|EDIT|DELETE}
	* @param mixed $contact contact as array or the contact-id
	* @param boolean $deny_account_delete=false if true never allow to delete accounts
	* @return boolean true permission granted, false for permission denied, null for contact does not exist
	*/
	function check_perms($needed,$contact,$deny_account_delete=false)
	{
		if ((!is_array($contact) || !isset($contact['owner'])) &&
			!($contact = parent::read(is_array($contact) ? $contact['id'] : $contact)))
		{
			return null;
		}
		$owner = $contact['owner'];

		// allow the user to edit his own account
		if (!$owner && $needed == EGW_ACL_EDIT && $contact['account_id'] == $this->user && $this->own_account_acl)
		{
			return true;
		}
		// dont allow to delete own account (as admin handels it too)
		if (!$owner && $needed == EGW_ACL_DELETE && ($deny_account_delete || $contact['account_id'] == $this->user))
		{
			return false;
		}
		return ($this->grants[$owner] & $needed) &&
			(!$contact['private'] || ($this->grants[$owner] & EGW_ACL_PRIVATE) || in_array($owner,$this->memberships));
	}

	/**
	 * Read (virtual) org-entry (values "common" for most contacts in the given org)
	 *
	 * @param string $org_id org_name:oooooo|||org_unit:uuuuuuuuu|||adr_one_locality:lllllll (org_unit and adr_one_locality are optional)
	 * @return array/boolean array with common org fields or false if org not found
	 */
	function read_org($org_id)
	{
		if (!$org_id) return false;

		$org = array();
		foreach(explode('|||',$org_id) as $part)
		{
			list($name,$value) = explode(':',$part);
			$org[$name] = $value;
		}
		$csvs = array('cat_id');	// fields with comma-separated-values

		// split regular fields and custom fields
		$custom_fields = $regular_fields = array();
		foreach($this->org_fields as $name)
		{
			if ($name[0] != '#')
			{
				$regular_fields[] = $name;
			}
			else
			{
				$custom_fields[] = $name = substr($name,1);
				$regular_fields['id'] = 'id';
				if (substr($this->customfields[$name]['type'],0,6)=='select' && $this->customfields[$name]['rows'] ||	// multiselection
					$this->customfields[$name]['type'] == 'radio')
				{
					$csvs[] = '#'.$name;
				}
			}
		}
		// read the regular fields
		$contacts = parent::search('',$regular_fields,'','','',false,'AND',false,$org);
		if (!$contacts) return false;

		// if we have custom fields, read and merge them in
		if ($custom_fields)
		{
			foreach($contacts as $contact)
			{
				$ids[] = $contact['id'];
			}
			if (($cfs = $this->read_customfields($ids,$custom_fields)))
			{
				foreach ($contacts as &$contact)
				{
					$id = $contact['id'];
					if (isset($cfs[$id]))
					{
						foreach($cfs[$id] as $name => $value)
						{
							$contact['#'.$name] = $value;
						}
					}
				}
				unset($contact);
			}
		}

		// create a statistic about the commonness of each fields values
		$fields = array();
		foreach($contacts as $contact)
		{
			foreach($contact as $name => $value)
			{
				if (!in_array($name,$csvs))
				{
					$fields[$name][$value]++;
				}
				else
				{
					// for comma separated fields, we have to use each single value
					foreach(explode(',',$value) as $val)
					{
						$fields[$name][$val]++;
					}
				}
			}
		}
		foreach($fields as $name => $values)
		{
			if (!in_array($name,$this->org_fields)) continue;

			arsort($values,SORT_NUMERIC);
			list($value,$num) = each($values);
			//echo "<p>$name: '$value' $num/".count($contacts)."=".($num / (double) count($contacts))." >= $this->org_common_factor = ".($num / (double) count($contacts) >= $this->org_common_factor ? 'true' : 'false')."</p>\n";
			if ($value && $num / (double) count($contacts) >= $this->org_common_factor)
			{
				if (!in_array($name,$csvs))
				{
					$org[$name] = $value;
				}
				else
				{
					$org[$name] = array();
					foreach ($values as $value => $num)
					{
						if ($value && $num / (double) count($contacts) >= $this->org_common_factor)
						{
							$org[$name][] = $value;
						}
					}
					$org[$name] = implode(',',$org[$name]);
				}
			}
		}
		return $org;
	}

	/**
	 * Return all org-members with same content in one or more of the given fields (only org_fields are counting)
	 *
	 * @param string $org_name
	 * @param array $fields field-name => value pairs
	 * @return array with contacts
	 */
	function org_similar($org_name,$fields)
	{
		$criteria = array();
		foreach($this->org_fields as $name)
		{
			if (isset($fields[$name]))
			{
				$criteria[$name] = $fields[$name];
			}
		}
		return parent::search($criteria,false,'n_family,n_given','','',false,'OR',false,array('org_name'=>$org_name));
	}

	/**
	 * Return the changed fields from two versions of a contact (not modified or modifier)
	 *
	 * @param array $from original/old version of the contact
	 * @param array $to changed/new version of the contact
	 * @param boolean $onld_org_fields=true check and return only org_fields, default true
	 * @return array with field-name => value from $from
	 */
	function changed_fields($from,$to,$only_org_fields=true)
	{
		$changed = array();
		foreach($only_org_fields ? $this->org_fields : array_keys($this->contact_fields) as $name)
		{
			if (!isset($from[$name]) || in_array($name,array('modified','modifier')))	// never count these
			{
				continue;
			}
			if ((string) $from[$name] != (string) $to[$name])
			{
				$changed[$name] = $from[$name];
			}
		}
		return $changed;
	}

	/**
	 * Change given fields in all members of the org with identical content in the field
	 *
	 * @param string $org_name
	 * @param array $from original/old version of the contact
	 * @param array $to changed/new version of the contact
	 * @param array $members=null org-members to change, default null --> function queries them itself
	 * @return array/boolean (changed-members,changed-fields,failed-members) or false if no org_fields changed or no (other) members matching that fields
	 */
	function change_org($org_name,$from,$to,$members=null)
	{
		if (!($changed = $this->changed_fields($from,$to,true))) return false;

		if (is_null($members) || !is_array($members))
		{
			$members = $this->org_similar($org_name,$changed);
		}
		if (!$members) return false;

		$changed_members = $changed_fields = $failed_members = 0;
		foreach($members as $member)
		{
			$fields = 0;
			foreach($changed as $name => $value)
			{
				if ((string)$value == (string)$member[$name])
				{
					$member[$name] = $to[$name];
					//echo "<p>$member[n_family], $member[n_given]: $name='{$to[$name]}'</p>\n";
					++$fields;
				}
			}
			if ($fields)
			{
				if (!$this->check_perms(EGW_ACL_EDIT,$member) || !$this->save($member))
				{
					++$failed_members;
				}
				else
				{
					++$changed_members;
					$changed_fields += $fields;
				}
			}
		}
		return array($changed_members,$changed_fields,$failed_members);
	}

	/**
	 * get title for a contact identified by $contact
	 *
	 * Is called as hook to participate in the linking. The format is determined by the link_title preference.
	 *
	 * @param int/string/array $contact int/string id or array with contact
	 * @return string/boolean string with the title, null if contact does not exitst, false if no perms to view it
	 */
	function link_title($contact)
	{
		if (!is_array($contact) && $contact)
		{
			$contact = $this->read($contact);
		}
		if (!is_array($contact))
		{
			return $contact;
		}
		$type = $this->prefs['link_title'];
		if (!$type || $type === 'n_fileas')
		{
			if ($contact['n_fileas']) return $contact['n_fileas'];
			$type = null;
		}
		return $this->fileas($contact,$type);
	}

	/**
	 * get title for multiple contacts identified by $ids
	 *
	 * Is called as hook to participate in the linking. The format is determined by the link_title preference.
	 *
	 * @param array $ids array with contact-id's
	 * @return array with titles, see link_title
	 */
	function link_titles(array $ids)
	{
		$titles = array();
		if (($contacts =& $this->search(array('contact_id' => $ids),false)))
		{
			foreach($contacts as $contact)
			{
				$titles[$contact['id']] = $this->link_title($contact);
			}
		}
		// we assume all not returned contacts are not readable for the user (as we report all deleted contacts to egw_link)
		foreach($ids as $id)
		{
			if (!isset($titles[$id]))
			{
				$titles[$id] = false;
			}
		}
		return $titles;
	}

	/**
	 * query addressbook for contacts matching $pattern
	 *
	 * Is called as hook to participate in the linking
	 *
	 * @param string $pattern pattern to search
	 * @return array with id - title pairs of the matching entries
	 */
	function link_query($pattern)
	{
		$result = $criteria = array();
		if ($pattern)
		{
			foreach($this->columns_to_search as $col)
			{
				$criteria[$col] = $pattern;
			}
		}
		if (($contacts = parent::search($criteria,false,'org_name,n_family,n_given,cat_id','','%',false,'OR')))
		{
			foreach($contacts as $contact)
			{
				$result[$contact['id']] = $this->link_title($contact);
				// show category color
				if ($contact['cat_id'] && ($color = etemplate::cats2color($contact['cat_id'])))
				{
					$result[$contact['id']] = array(
						'label' => $result[$contact['id']],
						'style.backgroundColor' => $color,
					);
				}
			}
		}
		return $result;
	}

	/**
	 * returns info about contacts for calender
	 *
	 * @param int/array $ids single contact-id or array of id's
	 * @return array
	 */
	function calendar_info($ids)
	{
		if (!$ids) return null;

		$data = array();
		foreach(!is_array($ids) ? array($ids) : $ids as $id)
		{
			if (!($contact = $this->read($id))) continue;

			$data[] = array(
				'res_id' => $id,
				'email' => $contact['email'] ? $contact['email'] : $contact['email_home'],
				'rights' => EGW_ACL_READ_FOR_PARTICIPANTS,
				'name' => $this->link_title($contact),
			);
		}
		//echo "<p>calendar_info(".print_r($ids,true).")="; _debug_array($data);
		return $data;
	}

	/**
	 * Called by delete-account hook, when an account get deleted --> deletes/moves the personal addressbook
	 *
	 * @param array $data
	 */
	function deleteaccount($data)
	{
		// delete/move personal addressbook
		parent::deleteaccount($data);
	}

	/**
	 * Called by edit-account hook, when an account get edited --> not longer used
	 *
	 * This function is still there, to not give a fatal error, if the hook still exists.
	 * Can be removed after the next db-update, which also reloads the hooks. RalfBecker 2006/09/18
	 *
	 * @param array $data
	 */
	function editaccount($data)
	{
		// just force a new registration of the addressbook hooks
		include(EGW_INCLUDE_ROOT.'/addressbook/setup/setup.inc.php');
		$GLOBALS['egw']->hooks->register_hooks('addressbook',$setup_info['addressbook']['hooks']);
	}

	/**
	 * Merges some given addresses into the first one and delete the others
	 *
	 * If one of the other addresses is an account, everything is merged into the account.
	 * If two accounts are in $ids, the function fails (returns false).
	 *
	 * @param array $ids contact-id's to merge
	 * @return int number of successful merged contacts, false on a fatal error (eg. cant merge two accounts)
	 */
	function merge($ids)
	{
		$this->error = false;
		foreach(parent::search(array('id'=>$ids),false) as $contact)	// $this->search calls the extended search from ui!
		{
			if ($contact['account_id'])
			{
				if (!is_null($account))
				{
					echo $this->error = 'Can not merge more then one account!';
					return false;	// we dont deal with two accounts!
				}
				$account = $contact;
				continue;
			}
			$pos = array_search($contact['id'],$ids);
			$contacts[$pos] = $contact;
		}
		if (!is_null($account))	// we found an account, so we merge the contacts into it
		{
			$target = $account;
			unset($account);
		}
		else					// we found no account, so we merge all but the first into the first
		{
			$target = $contacts[0];
			unset($contacts[0]);
		}
		if (!$this->check_perms(EGW_ACL_EDIT,$target))
		{
			echo $this->error = 'No edit permission for the target contact!';
			return 0;
		}
		foreach($contacts as $contact)
		{
			foreach($contact as $name => $value)
			{
				if (!$value) continue;

				switch($name)
				{
					case 'id':
					case 'tid':
					case 'owner':
					case 'private':
						break;	// ignored

					case 'cat_id':	// cats are all merged together
						if (!is_array($target['cat_id'])) $target['cat_id'] = $target['cat_id'] ? explode(',',$target['cat_id']) : array();
						$target['cat_id'] = array_unique(array_merge($target['cat_id'],is_array($value)?$value:explode(',',$value)));
						break;

					default:
						if (!$target[$name]) $target[$name] = $value;
						break;
				}
			}
		}
		if (!$this->save($target)) return 0;

		$success = 1;
		foreach($contacts as $contact)
		{
			if (!$this->check_perms(EGW_ACL_DELETE,$contact))
			{
				continue;
			}
			foreach(egw_link::get_links('addressbook',$contact['id']) as $data)
			{
				egw_link::link('addressbook',$target['id'],$data['app'],$data['id'],$data['remark'],$target['owner']);
			}
			if ($this->delete($contact['id'])) $success++;
		}
		return $success;
	}

	/**
	 * Check if user has required rights for a list or list-owner
	 *
	 * @param int $list
	 * @param int $required
	 * @param int $owner=null
	 * @return boolean
	 */
	function check_list($list,$required,$owner=null)
	{
		if ($list && ($list_data = $this->read_list($list)))
		{
			$owner = $list_data['list_owner'];
		}
		return !!($this->grants[$owner] & $required);
	}

	/**
	 * Adds a distribution list
	 *
	 * @param string $name list-name
	 * @param int $owner user- or group-id
	 * @param array $contacts=array() contacts to add
	 * @return list_id or false on error
	 */
	function add_list($name,$owner,$contacts=array())
	{
		if (!$this->check_list(null,EGW_ACL_ADD,$owner)) return false;

		return parent::add_list($name,$owner,$contacts);
	}

	/**
	 * Adds one contact to a distribution list
	 *
	 * @param int $contact contact_id
	 * @param int $list list-id
	 * @return false on error
	 */
	function add2list($contact,$list)
	{
		if (!$this->check_list($list,EGW_ACL_EDIT)) return false;

		return parent::add2list($contact,$list);
	}

	/**
	 * Removes one contact from distribution list(s)
	 *
	 * @param int $contact contact_id
	 * @param int $list list-id
	 * @return false on error
	 */
	function remove_from_list($contact,$list=null)
	{
		if ($list && !$this->check_list($list,EGW_ACL_EDIT)) return false;

		return parent::remove_from_list($contact,$list);
	}

	/**
	 * Deletes a distribution list (incl. it's members)
	 *
	 * @param int/array $list list_id(s)
	 * @return number of members deleted or false if list does not exist
	 */
	function delete_list($list)
	{
		if (!$this->check_list($list,EGW_ACL_DELETE)) return false;

		return parent::delete_list($list);
	}

	/**
	 * Read data of a distribution list
	 *
	 * @param int $list list_id
	 * @return array of data or false if list does not exist
	 */
	function read_list($list)
	{
		static $cache;

		if (isset($cache[$list])) return $cache[$list];

		return $cache[$list] = parent::read_list($list);
	}

	/**
	 * Get the address-format of a country
	 *
	 * This is a good reference where I got nearly all information, thanks to mikaelarhelger-AT-gmail.com
	 * http://www.bitboost.com/ref/international-address-formats.html
	 *
	 * Mail me (RalfBecker-AT-outdoor-training.de) if you want your nation added or fixed.
	 *
	 * @param string $country
	 * @return string 'city_state_postcode' (eg. US) or 'postcode_city' (eg. DE)
	 */
	function addr_format_by_country($country)
	{
		$code = $GLOBALS['egw']->country->country_code($country);

		switch($code)
		{
			case 'AU':
			case 'CA':
			case 'GB':	// not exactly right, postcode is in separate line
			case 'HK':	// not exactly right, they have no postcode
			case 'IN':
			case 'ID':
			case 'IE':	// not exactly right, they have no postcode
			case 'JP':	// not exactly right
			case 'KR':
			case 'LV':
			case 'NZ':
			case 'TW':
			case 'SA':	// not exactly right, postcode is in separate line
			case 'SG':
			case 'US':
				$adr_format = 'city_state_postcode';
				break;

			case 'AR':
			case 'AT':
			case 'BE':
			case 'CH':
			case 'CZ':
			case 'DK':
			case 'EE':
			case 'ES':
			case 'FI':
			case 'FR':
			case 'DE':
			case 'GL':
			case 'IS':
			case 'IL':
			case 'IT':
			case 'LT':
			case 'LU':
			case 'MY':
			case 'MX':
			case 'NL':
			case 'NO':
			case 'PL':
			case 'PT':
			case 'RO':
			case 'RU':
			case 'SE':
				$adr_format = 'postcode_city';
				break;

			default:
				$adr_format = $this->prefs['addr_format'] ? $this->prefs['addr_format'] : 'postcode_city';
		}
		//echo "<p>bocontacts::addr_format_by_country('$country'='$code') = '$adr_format'</p>\n";
		return $adr_format;
	}

	var $categories;

	function find_or_add_categories($catname_list)
	{
		if (!is_object($this->categories))
		{
			$this->categories =& CreateObject('phpgwapi.categories',$this->owner,'addressbook');
		}

		$cat_id_list = array();
		foreach($catname_list as $cat_name)
		{
			$cat_name = trim($cat_name);
			$cat_id = $this->categories->name2id($cat_name, 'X-');

			if (!$cat_id)
			{
				// some SyncML clients (mostly phones add an X- to the category names
				if (strncmp($cat_name, 'X-', 2) == 0)
				{
					$cat_name = substr($cat_name, 2);
				}
				$cat_id = $this->categories->add(array('name' => $cat_name,'descr' => $cat_name));
			}

			if ($cat_id)
			{
				$cat_id_list[] = $cat_id;
			}
		}

		if (count($cat_id_list) > 1)
		{
			$cat_id_list = array_unique($cat_id_list);
			sort($cat_id_list, SORT_NUMERIC);
		}
		return $cat_id_list;
	}

	function get_categories($cat_id_list)
	{
		if (!is_object($this->categories))
		{
			$this->categories =& CreateObject('phpgwapi.categories',$this->owner,'addressbook');
		}

		if (!is_array($cat_id_list))
		{
			$cat_id_list = explode(',',$cat_id_list);
		}
		$cat_list = array();
		foreach($cat_id_list as $cat_id)
		{
			if ($cat_data = $this->categories->return_single($cat_id))
			{
				$cat_list[] = $cat_data[0]['name'];
			}
		}

		return $cat_list;
	}

	function fixup_contact(&$contact)
	{
		if (!isset($contact['n_fn']) || empty($contact['n_fn']))
		{
			$contact['n_fn'] = $this->fullname($contact);
		}

		if (!isset($contact['n_fileas']) || empty($contact['n_fileas']))
		{
			$contact['n_fileas'] = $this->fileas($contact);
		}
	}

}
