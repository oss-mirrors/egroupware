<?php
	/**************************************************************************\
	* eGroupWare API - Preferences                                             *
	* This file written by Joseph Engo <jengo@phpgroupware.org>                *
	* and Mark Peters <skeeter@phpgroupware.org>                               *
	* Manages user preferences                                                 *
	* Copyright (C) 2000, 2001 Joseph Engo                                     *
	* -------------------------------------------------------------------------*
	* This library is part of the eGroupWare API                               *
	* http://www.egroupware.org/api                                            *
	* ------------------------------------------------------------------------ *
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

	/* $Id$ */

	/**
	 * preferences class used for setting application preferences
	 *
	 * the prefs are read into 4 arrays: 
	 * 	 $data the effective prefs used everywhere in phpgw, they are merged from the other 3 arrays
	 * 	 $user the stored user prefs, only used for manipulating and storeing the user prefs
	 * 	 $default the default preferences, always used when the user has no own preference set
	 * 	 $forced forced preferences set by the admin, they take precedence over user or default prefs
	 *
	 * @package api
	 * @license LGPL
	 * @author Joseph Engo <jengo@phpgroupware.org>
	 * @author Mark Peters <skeeter@phpgroupware.org>
	 * @author Ralf Becker <RalfBecker@outdoor-training.de> merging prefs on runtime, session prefs and reworked the class
	 */
	class preferences
	{
		/**
		  * @var int $account_id account the class is instanciated for
		  */
		var $account_id;
		/**
		  * @var string $ccount_type u or g
		  */
		var $account_type;
		/**
		  * @var array $data effectiv user prefs, used by all apps 
		  */
		var $data = array();
		/**
		  * @var array $user set user prefs for saveing (no defaults/forced prefs merged) 
		  */
		var $user = array();
		/**
		  * @var array $default default prefs 
		  */
		var $default = array();
		/**
		  * @var array $forced forced prefs 
		  */
		var $forced = array();
		/**
		  * @var array $session session / tempory prefs 
		  */
		var $session = array();
		/**
		  * @var db 
		  */
		var $db;
		/**
		 * @var string $table table-name
		 */
		var $table = 'egw_preferences';

		var $values,$vars;	// standard notify substitues, will be set by standard_substitues()

		/**************************************************************************\
		* Standard constructor for setting $this->account_id                       *
		\**************************************************************************/
		/**
		 * Standard constructor for setting $this->account_id
		 */
		function preferences($account_id = '')
		{
			if (is_object($GLOBALS['egw']->db))
			{
				$this->db = clone($GLOBALS['egw']->db);
			}
			else
			{
				$this->db = clone($GLOBALS['egw_setup']->db);
				$this->table = $GLOBALS['egw_setup']->prefs_table;
			}
 			$this->account_id = get_account_id($account_id);
		}

		/**************************************************************************\
		* These are the standard $this->account_id specific functions              *
		\**************************************************************************/

		/**
		 * parses a notify and replaces the substitutes
		 *
		 * @param string $msg message to parse / substitute
		 * @param array $values=array() extra vars to replace in addition to $this->values, vars are in an array with \
		 * 	$key => $value pairs, $key does not include the $'s and is the *untranslated* name
		 * @param boolean $use_standard_values=true should the standard values are used
		 * @return string with parsed notify-msg
		 */
		function parse_notify($msg,$values=array(),$use_standard_values=True)
		{
			$vals = $values ? $values : array();

			if ($use_standard_values && is_array($this->values))
			{
				$vals += $this->values;
			}
			foreach($vals as $key => $val)
			{
				$replace[] = '$$'.$key.'$$';
				$with[]    = $val;
			}
			return str_replace($replace,$with,$msg);
		}
		
		/**
		 * replaces the english key's with translated ones, or if $un_lang the opposite
		 *
		 * @param string $msg message to translate
		 * @param array $values=array() extra vars to replace in addition to $this->values, vars are in an array with \
		 * 	$key => $value pairs, $key does not include the $'s and is the *untranslated* name
		 * @param boolean $un_lang=false if true translate back
		 * @return string
		 */
		function lang_notify($msg,$vals=array(),$un_lang=False)
		{
			foreach($vals as $key => $val)
			{
				$lname = ($lname = lang($key)) == $key.'*' ? $key : $lname;
				if ($un_lang)
				{
					$langs[$lname] = '$$'.$key.'$$';
				}
				else
				{
					$langs[$key] = '$$'.$lname.'$$';
				}
			}
			return $this->parse_notify($msg,$langs,False);
		}

		/**
		 * define some standard substitues-values and use them on the prefs, if needed
		 */
		function standard_substitutes()
		{
			if (!is_array(@$GLOBALS['egw_info']['user']['preferences']))
			{
				$GLOBALS['egw_info']['user']['preferences'] = $this->data;	// else no lang()
			}
			// we cant use phpgw_info/user/fullname, as it's not set when we run
			$GLOBALS['egw']->accounts->get_account_name($this->account_id,$lid,$fname,$lname);

			$this->values = array(	// standard notify replacements
				'fullname'  => $GLOBALS['egw']->common->display_fullname('',$fname,$lname),
				'firstname' => $fname,
				'lastname'  => $lname,
				'domain'    => $GLOBALS['egw_info']['server']['mail_suffix'],
				'email'     => $this->email_address($this->account_id),
				'date'      => $GLOBALS['egw']->common->show_date('',$GLOBALS['egw_info']['user']['preferences']['common']['dateformat']),
			);
			// do this first, as it might be already contain some substitues
			//
			$this->values['email'] = $this->parse_notify($this->values['email']);

			$this->vars = array(	// langs have to be in common !!!
				'fullname'  => lang('name of the user, eg. "%1"',$this->values['fullname']),
				'firstname' => lang('first name of the user, eg. "%1"',$this->values['firstname']),
				'lastname'  => lang('last name of the user, eg. "%1"',$this->values['lastname']),
				'domain'    => lang('domain name for mail-address, eg. "%1"',$this->values['domain']),
				'email'     => lang('email-address of the user, eg. "%1"',$this->values['email']),
				'date'      => lang('todays date, eg. "%1"',$this->values['date']),
			);
			// do the substituetion in the effective prefs (data)
			//
			foreach($this->data as $app => $data)
			{
				foreach($data as $key => $val)
				{
					if (!is_array($val) && strstr($val,'$$') !== False)
					{
						$this->data[$app][$key] = $this->parse_notify($val);
					}
					elseif (is_array($val))
					{
						foreach($val as $k => $v)
						{
							if (!is_array($v) && strstr($v,'$$') !== False)
							{
								$this->data[$app][$key][$k] = $this->parse_notify($v);
							}
						}
					}
				}
			}
		}

		/**
		 * unquote (stripslashes) recursivly the whole array
		 *
		 * @param array &$arr array to unquote (var-param!)
		 */
		function unquote(&$arr)
		{
			if (!is_array($arr))
			{
				$arr = stripslashes($arr);
				return;
			}
			foreach($arr as $key => $value)
			{
				if (is_array($value))
				{
					$this->unquote($arr[$key]);
				}
				else
				{
					$arr[$key] = stripslashes($value);
				}
			}
		}

		/**
		 * read preferences from the repository
		 *
		 * the function ready all 3 prefs user/default/forced and merges them to the effective ones
		 *
		 * @internal private function should only be called from within this class
		 * @return array with effective prefs ($this->data)
		 */
		function read_repository()
		{
			$this->session = $GLOBALS['egw']->session->appsession('preferences','preferences');
			if (!is_array($this->session))
			{
				$this->session = array();
			}
			$this->db->select($this->table,'*','preference_owner IN (-1,-2,'.(int) $this->account_id.')',__LINE__,__FILE__);

			$this->forced = $this->default = $this->user = array();
			while($this->db->next_record())
			{
				// The following replacement is required for PostgreSQL to work
				$app = trim($this->db->f('preference_app'));
				$value = unserialize($this->db->f('preference_value'));
				$this->unquote($value);
				if (!is_array($value))
				{
					continue;
				}
				switch($this->db->f('preference_owner'))
				{
					case -1:	// forced
						$this->forced[$app] = $value;
						break;
					case -2:	// default
						$this->default[$app] = $value;
						break;
					default:	// user
						$this->user[$app] = $value;
						break;
				}
			}
			$this->data = $this->user;

			// let the (temp.) session prefs. override the user prefs.
			//
			foreach($this->session as $app => $values)
			{
				foreach($values as $var => $value)
				{
					$this->data[$app][$var] = $value;
				}
			}

			// now use defaults if needed (user-value unset or empty)
			//
			foreach($this->default as $app => $values)
			{
				foreach($values as $var => $value)
				{
					if (!isset($this->data[$app][$var]) || $this->data[$app][$var] === '')
					{
						$this->data[$app][$var] = $value;
					}
				}
			}
			// now set/force forced values
			//
			foreach($this->forced as $app => $values)
			{
				foreach($values as $var => $value)
				{
					$this->data[$app][$var] = $value;
				}
			}
			// setup the standard substitutes and substitutes the data in $this->data
			//
			$this->standard_substitutes();

			// This is to supress warnings during login
			if (is_array($this->data))
			{
				reset($this->data);
			}
			if (isset($this->debug) && substr($GLOBALS['egw_info']['flags']['currentapp'],0,3) != 'log')
			{
				echo 'user<pre>';     print_r($this->user); echo "</pre>\n";
				echo 'forced<pre>';   print_r($this->forced); echo "</pre>\n";
				echo 'default<pre>';  print_r($this->default); echo "</pre>\n";
				echo 'effectiv<pre>'; print_r($this->data); echo "</pre>\n"; 
			}
			return $this->data;
		}

		/**
		 * read preferences from repository and stores in an array
		 *
		 * @return array containing the effective user preferences
		 */
		function read()
		{
			if (count($this->data) == 0)
			{
				$this->read_repository();
			}
			reset ($this->data);
			return $this->data;
		}

		/**
		 * add preference to $app_name a particular app
		 *
		 * the effective prefs ($this->data) are updated to reflect the change
		 *
		 * @param string $app_name name of the app
		 * @param string $var name of preference to be stored
		 * @param mixed $value='##undef##' value of the preference, if not given $GLOBALS[$var] is used
		 * @param $type='user' of preference to set: forced, default, user
		 * @return array with new effective prefs (even when forced or default prefs are set !)
		 */
		function add($app_name,$var,$value = '##undef##',$type='user')
		{
			//echo "<p>add('$app_name','$var','$value')</p>\n";
			if ($value === '##undef##')
			{
				$value = $GLOBALS[$var];
			}
 
			switch ($type)
			{
				case 'session':
					if (!isset($this->forced[$app_name][$var]) || $this->forced[$app_name][$var] === '')
					{
						$this->session[$app_name][$var] = $this->data[$app_name][$var] = $value;
						$GLOBALS['egw']->session->appsession('preferences','preferences',$this->session);
						if (method_exists($GLOBALS['egw'],'invalidate_session_cache'))	// egw object in setup is limited
						{
							$GLOBALS['egw']->invalidate_session_cache();	// in case with cache the egw_info array in the session
						}
					}
					break;

				case 'forced':
					$this->data[$app_name][$var] = $this->forced[$app_name][$var] = $value;
					break;

				case 'default':
					$this->default[$app_name][$var] = $value;
					if ((!isset($this->forced[$app_name][$var]) || $this->forced[$app_name][$var] === '') &&
						(!isset($this->user[$app_name][$var]) || $this->user[$app_name][$var] === ''))
					{
						$this->data[$app_name][$var] = $value;
					}
					break;

				case user:
				default:
					$this->user[$app_name][$var] = $value;
					if (!isset($this->forced[$app_name][$var]) || $this->forced[$app_name][$var] === '')
					{
						$this->data[$app_name][$var] = $value;
					}
					break;
			}
			reset($this->data);
			return $this->data;
		}

		/**
		 * delete preference from $app_name
		 *
		 * the effektive prefs ($this->data) are updated to reflect the change
		 *
		 * @param string $app_name name of app
		 * @param string $var=false variable to be deleted
		 * @param string $type='user' of preference to set: forced, default, user
		 * @return array with new effective prefs (even when forced or default prefs are deleted!)
		 */
		function delete($app_name, $var = False,$type = 'user')
		{
			//echo "<p>delete('$app_name','$var','$type')</p>\n";
			$set_via = array(
				'forced'  => array('user','default'),
				'default' => array('forced','user'),
				'user'    => array('forced','default')
			);
			if (!isset($set_via[$type]))
			{
				$type = 'user';
			}
			$pref = &$this->$type;

			if (($all = (is_string($var) && $var == '')))
			{
				unset($pref[$app_name]);
				unset($this->data[$app_name]);
			}
			else
			{
				unset($pref[$app_name][$var]);
				unset($this->data[$app_name][$var]);
			}
			// set the effectiv pref again if needed
			//
			foreach ($set_via[$type] as $set_from)
			{
				if ($all)
				{
					if (isset($this->$set_from[$app_name]))
					{
						$this->data[$app_name] = $this->$set_from[$app_name];
						break;
					}
				}
				else
				{
					$arr = $this->$set_from;
					if($var && @isset($arr[$app_name][$var]) && $arr[$app_name][$var] !== '')
					{
						$this->data[$app_name][$var] = $this->$set_from[$app_name][$var];
						break;
					}
					unset($arr);
				}
			}
			reset ($this->data);
			return $this->data;
		}
		
		/**
		 * delete all prefs of a given user
		 *
		 * @param int $accountid
		 */
		function delete_user($accountid)
		{
			$this->delete($this->table,array('preference_owner' => $accountid),__LINE__,__FILE__);
		}

		/**
		 * add complex array data preference to $app_name a particular app
		 *
		 * @deprecated we can now correctly store arrays in the prefs, AFAIK only used in email
		 *
		 * @param string $app_name name of the app
		 * @param string $var array keys separated by '/', eg. 'ex_accounts/1'
		 * @param mixed $value='' value of the preference
		 * @return array with new effective prefs (even when forced or default prefs are deleted!)
		 */
		function add_struct($app_name,$var,$value = '')
		{
			$parts = explode('/',str_replace(array('][','[',']','"',"'"),array('/','','','',''),$var));
			$data = &$this->data[$app_name];
			$user = &$this->user[$app_name];
			foreach($parts as $name)
			{
				$data = &$data[$name];
				$user = &$user[$name];
			}
			$data = $user = $value;
			print_debug('class.preferences: add_struct: $this->data[$app_name] dump:', $this->data[$app_name],'api');
			reset($this->data);
			return $this->data;
		}

		/**
		 * delete complex array data preference from $app_name
		 *
		 * @deprecated we can now correctly store arrays in the prefs, AFAIK only used in email
		 *
		 * @param $app_name name of app
		 * @param $var array keys separated by '/', eg. 'ex_accounts/1'
		 * @return array with new effective prefs (even when forced or default prefs are deleted!)
		 */
		function delete_struct($app_name, $var = '')
		{
			$parts = explode('/',str_replace(array('][','[',']','"',"'"),array('/','','','',''),$var));
			$last = array_pop($parts);
			$data = &$this->data[$app_name];
			$user = &$this->user[$app_name];
			foreach($parts as $name)
			{
				$data = &$data[$name];
				$user = &$user[$name];
			}
			unset($user[$last]);
			print_debug('* $this->data[$app_name] dump:', $this->data[$app_name],'api');
			reset ($this->data);
			return $this->data;
		}

		/**
		 * quote (addslashes) recursivly the whole array
		 *
		 * @param array &$arr array to quote (var-param!)
		 */
		function quote(&$arr)
		{
			if (!is_array($arr))
			{
				$arr = addslashes($arr);
				return;
			}
			foreach($arr as $key => $value)
			{
				if (is_array($value))
				{
					$this->quote($arr[$key]);
				}
				else
				{
					$arr[$key] = addslashes($value);
				}
			}
		}

		/**
		 * save the the preferences to the repository
		 *
		 * User prefs for saveing are in $this->user not in $this->data, which are the effectiv prefs only!
		 *
		 * @param boolean $update_session_info=false old param, seems not to be used
		 * @param string $type='user' which prefs to update: user/default/forced
		 * @param boolean $invalid_cache=true should we invalidate the cache, default true
		 * @return array with new effective prefs (even when forced or default prefs are deleted!)
		 */
		function save_repository($update_session_info = False,$type='user',$invalid_cache=true)
		{
			switch($type)
			{
				case 'forced':
					$account_id = -1;
					$prefs = &$this->forced;
					break;
				case 'default':
					$account_id = -2;
					$prefs = &$this->default;
					break;
				default:
					$account_id = (int)$this->account_id;
					$prefs = &$this->user;	// we use the user-array as data contains default values too
					break;
			}
			//echo "<p>preferences::save_repository(,$type): account_id=$account_id, prefs="; print_r($prefs); echo "</p>\n";

			if (!$GLOBALS['egw']->acl->check('session_only_preferences',1,'preferences'))
			{
				$this->db->transaction_begin();
				$this->db->delete($this->table,array('preference_owner' => $account_id),__LINE__,__FILE__);

				foreach($prefs as $app => $value)
				{
					if (!is_array($value))
					{
						continue;
					}
					$this->quote($value);	// this quote-ing is for serialize, not for the db

					$this->db->insert($this->table,array(
						'preference_value' => serialize($value),
					),array(
						'preference_owner' => $account_id,
						'preference_app'   => $app,
					),__LINE__,__FILE__);
				}
				$this->db->transaction_commit();
			}
			else
			{
				$GLOBALS['egw_info']['user']['preferences'] = $this->data;
				$GLOBALS['egw']->session->save_repositories();
			}
			if ($invalid_cache && method_exists($GLOBALS['egw'],'invalidate_session_cache'))	// egw object in setup is limited
			{
				$GLOBALS['egw']->invalidate_session_cache();	// in case with cache the egw_info array in the session
			}
			return $this->data;
		}

		/**
		 * insert a copy of the default preferences for use by real account_id
		 *
		 * @deprecated not longer needed, as the defaults are merged in on runtime
		 *
		 * @param int $account_id numerical id of account for which to create the prefs
		 */
		function create_defaults($account_id)
		{
		}

		/**
		 * update the preferences array
		 *
		 * @param array $data array of preferences
		 * @return array with new effective prefs (even when forced or default prefs are deleted!)
		 */
		function update_data($data)
		{
			$this->data = $data;
			reset($this->data);
			return $this->data;
		}

		/* legacy support */
		function change($app_name,$var,$value = "")
		{
			return $this->add($app_name,$var,$value);
		}
		function commit($update_session_info = True)
		{
			//return $this->save_repository($update_session_info);
		}

		/**************************************************************************\
		* These are the non-standard $this->account_id specific functions          *
		\**************************************************************************/

		/**
		 * verify basic settings
		 *
		 * @discussion
		 */
		function verify_basic_settings()
		{
			if (!@is_array($GLOBALS['egw_info']['user']['preferences']))
			{
				$GLOBALS['egw_info']['user']['preferences'] = array();
			}
			/* This takes care of new users who don't have proper default prefs setup */
			if (!isset($GLOBALS['egw_info']['flags']['nocommon_preferences']) || 
				!$GLOBALS['egw_info']['flags']['nocommon_preferences'])
			{
				$preferences_update = False;
				if (!isset($GLOBALS['egw_info']['user']['preferences']['common']['maxmatchs']) || 
					!$GLOBALS['egw_info']['user']['preferences']['common']['maxmatchs'])
				{
					$this->add('common','maxmatchs',15);
					$preferences_update = True;
				}
				if (!isset($GLOBALS['egw_info']['user']['preferences']['common']['theme']) || 
					!$GLOBALS['egw_info']['user']['preferences']['common']['theme'])
				{
					$this->add('common','theme','default');
					$preferences_update = True;
				}
				if (!isset($GLOBALS['egw_info']['user']['preferences']['common']['template_set']) || 
					!$GLOBALS['egw_info']['user']['preferences']['common']['template_set'])
				{
					$this->add('common','template_set','default');
					$preferences_update = True;
				}
				if (!isset($GLOBALS['egw_info']['user']['preferences']['common']['dateformat']) || 
					!$GLOBALS['egw_info']['user']['preferences']['common']['dateformat'])
				{
					$this->add('common','dateformat','m/d/Y');
					$preferences_update = True;
				}
				if (!isset($GLOBALS['egw_info']['user']['preferences']['common']['timeformat']) || 
					!$GLOBALS['egw_info']['user']['preferences']['common']['timeformat'])
				{
					$this->add('common','timeformat',12);
					$preferences_update = True;
				}
				if (!isset($GLOBALS['egw_info']['user']['preferences']['common']['lang']) || 
					!$GLOBALS['egw_info']['user']['preferences']['common']['lang'])
				{
					$this->add('common','lang',$GLOBALS['egw']->common->getPreferredLanguage());
					$preferences_update = True;
				}
				if ($preferences_update)
				{
					$this->save_repository();
				}
				unset($preferences_update);
			}
		}

		/****************************************************\
		* Email Preferences and Private Support Functions   *
		\****************************************************/

		/**
		 * Helper function for create_email_preferences, gets mail server port number.
		 *
		 * This will generate the appropriate port number to access a
		 * mail server of type pop3, pop3s, imap, imaps users value from
		 * $phpgw_info['user']['preferences']['email']['mail_port'].
		 * if that value is not set, it generates a default port for the given $server_type.
		 * Someday, this *MAY* be 
		 * (a) a se4rver wide admin setting, or 
		 * (b)user custom preference
		 * Until then, simply set the port number based on the mail_server_type, thereof
		 * ONLY call this function AFTER ['email']['mail_server_type'] has been set.
		 * @param $prefs - user preferences array based on element ['email'][]
		 * @author  Angles
		 * @access Private
		 */
		function sub_get_mailsvr_port($prefs, $acctnum=0)
		{
			// first we try the port number supplied in preferences
			if((isset($prefs['email']['accounts'][$acctnum]['mail_port'])) &&
				($prefs['email']['accounts'][$acctnum]['mail_port'] != ''))
			{
				$port_number = $prefs['email']['accounts'][$acctnum]['mail_port'];
			}
			// preferences does not have a port number, generate a default value
			else
			{
				if (!isset($prefs['email']['accounts'][$acctnum]['mail_server_type']))
				{
					$prefs['email']['accounts'][$acctnum]['mail_server_type'] = $prefs['email']['mail_server_type'];
				}

				switch($prefs['email']['accounts'][$acctnum]['mail_server_type'])
				{
					case 'pop3s':
						// POP3 over SSL
						$port_number = 995;
						break;
					case 'pop3':
						// POP3 normal connection, No SSL
						// ( same string as normal imap above)
						$port_number = 110;
						break;
					case 'nntp':
						// NNTP news server port
						$port_number = 119;
						break;
					case 'imaps':
						// IMAP over SSL
						$port_number = 993;
						break;
					case 'imap':
						// IMAP normal connection, No SSL 
					default:
						// UNKNOWN SERVER in Preferences, return a
						// default value that is likely to work
						// probably should raise some kind of error here
						$port_number = 143;
						break;
				}
			}
			return $port_number;
		}

		/**
		 * Helper function for create_email_preferences, gets default userid for email
		 *
		 * This will generate the appropriate userid for accessing an email server.
		 * In the absence of a custom ['email']['userid'], this function should be used to set it.
		 * @param $accountid - as determined in and/or passed to "create_email_preferences"
		 * @access Private
		 */
		function sub_default_userid($account_id='')
		{
			if ($GLOBALS['egw_info']['server']['mail_login_type'] == 'vmailmgr')
			{
				$prefs_email_userid = $GLOBALS['egw']->accounts->id2name($account_id)
					. '@' . $GLOBALS['egw_info']['server']['mail_suffix'];
			}
			else
			{
				$prefs_email_userid = $GLOBALS['egw']->accounts->id2name($account_id);
			}
			return $prefs_email_userid;
		}

		/**
		 * returns the custom email-address (if set) or generates a default one
		 *
		 * This will generate the appropriate email address used as the "From:" 
		 * email address when the user sends email, the localpert * part. The "personal" 
		 * part is generated elsewhere.
		 * In the absence of a custom ['email']['address'], this function should be used to set it.
		 *
		 * @access public
		 * @param int $accountid - as determined in and/or passed to "create_email_preferences"
		 * @return string with email-address
		 */
		function email_address($account_id='')
		{
			if (isset($this->data['email']['address']))
			{
				return $this->data['email']['address'];
			}
			// if email-address is set in the account, return it
			if (($email = $GLOBALS['egw']->accounts->id2name($account_id,'account_email')))
			{
				return $email;
			}
			$prefs_email_address = $GLOBALS['egw']->accounts->id2name($account_id);
			if (strstr($prefs_email_address,'@') === False)
			{
				$prefs_email_address .= '@' . $GLOBALS['egw_info']['server']['mail_suffix'];
			}
			return $prefs_email_address;
		}

		function sub_default_address($account_id='')
		{
			return $this->email_address($account_id);
		}

		/**
		 * create email preferences
		 *
		 * @param $account_id -optional defaults to : get_account_id()
		 * fills a local copy of ['email'][] prefs array which is then returned to the calling
		 * function, which the calling function generally tacks onto the $GLOBALS['egw_info'] array as such:
		 * 	$GLOBALS['egw_info']['user']['preferences'] = $GLOBALS['egw']->preferences->create_email_preferences();
		 * which fills an array based at:
		 * 	$GLOBALS['egw_info']['user']['preferences']['email'][prefs_are_elements_here]
		 * Reading the raw preference DB data and comparing to the email preference schema defined in 
		 * /email/class.bopreferences.inc.php (see discussion there and below) to create default preference values 
		 * for the  in the ['email'][] pref data array in cases where the user has not supplied 
		 * a preference value for any particular preference item available to the user.
		 * @access Public
		 */
		function create_email_preferences($accountid='', $acctnum=0)
		{
			print_debug('class.preferences: create_email_preferences: ENTERING<br>', 'messageonly','api');
			// we may need function "html_quotes_decode" from the mail_msg class
			$email_base =& CreateObject("email.mail_msg");

			$account_id = get_account_id($accountid);
			// If the current user is not the request user, grab the preferences
			// and reset back to current user.
			if($account_id != $this->account_id)
			{
				// Temporarily store the values to a temp, so when the
				// read_repository() is called, it doesn't destory the
				// current users settings.
				$temp_account_id = $this->account_id;
				$temp_data = $this->data;

				// Grab the new users settings, only if they are not the
				// current users settings.
				$this->account_id = $account_id;
				$prefs = $this->read_repository();

				// Reset the data to what it was prior to this call
				$this->account_id = $temp_account_id;
				$this->data = $temp_data;
			}
			else
			{
				$prefs = $this->data;
			}
			// are we dealing with the default email account or an extra email account?
			if ($acctnum != 0)
			{
				// prefs are actually a sub-element of the main email prefs
				// at location [email][ex_accounts][X][...pref names] => pref values
				// make this look like "prefs[email] so the code below code below will do its job transparently
				
				// obtain the desired sub-array of extra account prefs
				$sub_prefs = array();
				$sub_prefs['email'] = $prefs['email']['ex_accounts'][$acctnum];
				// make the switch, make it seem like top level email prefs
				$prefs = array();
				$prefs['email'] = $sub_prefs['email'];
				// since we return just $prefs, it's up to the calling program to put the sub prefs in the right place
			}
			print_debug('class.preferences: create_email_preferences: $acctnum: ['.$acctnum.'] ; raw $this->data dump', $this->data,'api');

			// = = = =  NOT-SIMPLE  PREFS  = = = =
			// Default Preferences info that is:
			// (a) not controlled by email prefs itself (mostly api and/or server level stuff)
			// (b) too complicated to be described in the email prefs data array instructions
			
			// ---  [server][mail_server_type]  ---
			// Set API Level Server Mail Type if not defined
			// if for some reason the API didnot have a mail server type set during initialization
			if (empty($GLOBALS['egw_info']['server']['mail_server_type']))
			{
				$GLOBALS['egw_info']['server']['mail_server_type'] = 'imap';
			}

			// ---  [server][mail_folder]  ---
			// ====  UWash Mail Folder Location used to be "mail", now it's changeable, but keep the
			// ====  default to "mail" so upgrades happen transparently
			// ---  TEMP MAKE DEFAULT UWASH MAIL FOLDER ~/mail (a.k.a. $HOME/mail)
			$GLOBALS['egw_info']['server']['mail_folder'] = 'mail';
			// ---  DELETE THE ABOVE WHEN THIS OPTION GETS INTO THE SYSTEM SETUP
			// pick up custom "mail_folder" if it exists (used for UWash and UWash Maildir servers)
			// else use the system default (which we temporarily hard coded to "mail" just above here)

			//---  [email][mail_port]  ---
			// These sets the mail_port server variable
			// someday (not currently) this may be a site-wide property set during site setup
			// additionally, someday (not currently) the user may be able to override this with
			// a custom email preference. Currently, we simply use standard port numbers
			// for the service in question.
			$prefs['email']['mail_port'] = $this->sub_get_mailsvr_port($prefs);

			//---  [email][fullname]  ---
			// we pick this up from phpgw api for the default account
			// the user does not directly manipulate this pref for the default email account
			if ((string)$acctnum == '0')
			{
				$prefs['email']['fullname'] = $GLOBALS['egw_info']['user']['fullname'];
			}

			// = = = =  SIMPLER PREFS  = = = =

			// Default Preferences info that is articulated in the email prefs schema array itself
			// such email prefs schema array is described and established in /email/class.bopreferences
			// by function "init_available_prefs", see the discussion there.

			// --- create the objectified /email/class.bopreferences.inc.php ---
			$bo_mail_prefs =& CreateObject('email.bopreferences');

			// --- bo_mail_prefs->init_available_prefs() ---
			// this fills object_email_bopreferences->std_prefs and ->cust_prefs
			// we will initialize the users preferences according to the rules and instructions
			// embodied in those prefs arrays, applying those rules to the unprocessed
			// data read from the preferences DB. By taking the raw data and applying those rules,
			// we will construct valid and known email preference data for this user.
			$bo_mail_prefs->init_available_prefs();

			// --- combine the two array (std and cust) for 1 pass handling ---
			// when this preference DB was submitted and saved, it was hopefully so well structured
			// that we can simply combine the two arrays, std_prefs and cust_prefs, and do a one
			// pass analysis and preparation of this users preferences.
			$avail_pref_array = $bo_mail_prefs->std_prefs;
			$c_cust_prefs = count($bo_mail_prefs->cust_prefs);
			for($i=0;$i<$c_cust_prefs;$i++)
			{
				// add each custom prefs to the std prefs array
				$next_idx = count($avail_pref_array);
				$avail_pref_array[$next_idx] = $bo_mail_prefs->cust_prefs[$i];
			}
			print_debug('class.preferences: create_email_preferences: std AND cust arrays combined:', $avail_pref_array,'api');

			// --- make the schema-based pref data for this user ---
			// user defined values and/or user specified custom email prefs are read from the
			// prefs DB with mininal manipulation of the data. Currently the only change to 
			// users raw data is related to reversing the encoding of "database un-friendly" chars
			// which itself may become unnecessary if and when the database handlers can reliably 
			// take care of this for us. Of course, password data requires special decoding,
			// but the password in the array [email][paswd] should be left in encrypted form 
			// and only decrypted seperately when used to login in to an email server.

			// --- generating a default value if necessary ---
			// in the absence of a user defined custom email preference for a particular item, we can
			// determine the desired default value for that pref as such:
			// $this_avail_pref['init_default']  is a comma seperated seperated string which should
			// be exploded into an array containing 2 elements that are:
			// exploded[0] : an description of how to handle the next string element to get a default value.
			// Possible "instructional tokens" for exploded[0] (called $set_proc[0] below) are:
			//	string
			//	set_or_not
			//	function
			//	init_no_fill
			//	varEVAL
			// tells you how to handle the string in exploded[1] (called $set_proc[1] below) to get a valid
			// default value for a particular preference if one is needed (i.e. if no user custom
			// email preference exists that should override that default value, in which case we
			// do not even need to obtain such a default value as described in ['init_default'] anyway).
			
			// --- loop thru $avail_pref_array and process each pref item ---
			$c_prefs = count($avail_pref_array);
			for($i=0;$i<$c_prefs;$i++)
			{
				$this_avail_pref = $avail_pref_array[$i];
				print_debug('class.preferences: create_email_preferences: value from DB for $prefs[email]['.$this_avail_pref['id'].'] = ['.$prefs['email'][$this_avail_pref['id']].']', 'messageonly','api');
				print_debug('class.preferences: create_email_preferences: std/cust_prefs $this_avail_pref['.$i.'] dump:', $this_avail_pref,'api');

				// --- is there a value in the DB for this preference item ---
				// if the prefs DB has no value for this defined available preference, we must make one.
				// This occurs if (a) this is user's first login, or (b) this is a custom pref which the user 
				// has not overriden, do a default (non-custom) value is needed.
				if (!isset($prefs['email'][$this_avail_pref['id']]))
				{
					// now we are analizing an individual pref that is available to the user
					// AND the user had no existing value in the prefs DB for this.

					// --- get instructions on how to generate a default value ---
					$set_proc = explode(',', $this_avail_pref['init_default']);
					print_debug(' * set_proc=['.serialize($set_proc).']', 'messageonly','api');

					// --- use "instructional token" in $set_proc[0] to take appropriate action ---
					// STRING
					if ($set_proc[0] == 'string')
					{
						// means this pref item's value type is string
						// which defined string default value is in $set_proc[1]
						print_debug('* handle "string" set_proc: ', serialize($set_proc),'api');
						if (trim($set_proc[1]) == '')
						{
							// this happens when $this_avail_pref['init_default'] = "string, "
							$this_string = '';
						}
						else
						{
							$this_string = $set_proc[1];
						}
						$prefs['email'][$this_avail_pref['id']] = $this_string;
					}
					// SET_OR_NOT
					elseif ($set_proc[0] == 'set_or_not')
					{
						// typical with boolean options, True = "set/exists" and False = unset
						print_debug('* handle "set_or_not" set_proc: ', serialize($set_proc),'api');
						if ($set_proc[1] == 'not_set')
						{
							// leave it NOT SET
						}
						else
						{
							// opposite of boolean not_set  = string "True" which simply sets a 
							// value it exists in the users session [email][] preference array
							$prefs['email'][$this_avail_pref['id']] = 'True';
						}
					}
					// FUNCTION
					elseif ($set_proc[0] == 'function')
					{
						// string in $set_proc[1] should be "eval"uated as code, calling a function
						// which will give us a default value to put in users session [email][] prefs array
						print_debug(' * handle "function" set_proc: ', serialize($set_proc),'api');
						$evaled = '';
						//eval('$evaled = $this->'.$set_proc[1].'('.$account_id.');');

						$code = '$evaled = $this->'.$set_proc[1].'('.$account_id.');';
						print_debug(' * $code: ', $code,'api');
						eval($code);

						print_debug('* $evaled:', $evaled,'api');
						$prefs['email'][$this_avail_pref['id']] = $evaled;
					}
					// INIT_NO_FILL
					elseif ($set_proc[0] == 'init_no_fill')
					{
						// we have an available preference item that we may NOT fill with a default 
						// value. Only the user may supply a value for this pref item.
						print_debug('* handle "init_no_fill" set_proc:', serialize($set_proc),'api');
						// we are FORBADE from filling this at this time!
					}
					// varEVAL
					elseif ($set_proc[0] == 'varEVAL')
					{
						// similar to "function" but used for array references, the string in $set_proc[1] 
						// represents code which typically is an array referencing a system/api property
						print_debug('* handle "GLOBALS" set_proc:', serialize($set_proc),'api');
						$evaled = '';
						$code = '$evaled = '.$set_proc[1];
						print_debug(' * $code:', $code,'api');
						eval($code);
						print_debug('* $evaled:', $evaled,'api');
						$prefs['email'][$this_avail_pref['id']] = $evaled;
					}
					else
					{
						// error, no instructions on how to handle this element's default value creation
						echo 'class.preferences: create_email_preferences: set_proc ERROR: '.serialize($set_proc).'<br>';
					}
				}
				else
				{
					// we have a value in the database, do we need to prepare it in any way?
					// (the following discussion is unconfirmed:)
					// DO NOT ALTER the data in the prefs array!!!! or the next time we call
					// save_repository withOUT undoing what we might do here, the
					// prefs will permenantly LOOSE the very thing(s) we are un-doing
					/// here until the next OFFICIAL submit email prefs function, where it
					// will again get this preparation before being written to the database.

					// NOTE: if database de-fanging is eventually handled deeper in the 
					// preferences class, then the following code would become depreciated 
					// and should be removed in that case.
					if (($this_avail_pref['type'] == 'user_string') &&
						(stristr($this_avail_pref['write_props'], 'no_db_defang') == False))
					{
						// this value was "de-fanged" before putting it in the database
						// undo that defanging now
						$db_unfriendly = $email_base->html_quotes_decode($prefs['email'][$this_avail_pref['id']]);
						$prefs['email'][$this_avail_pref['id']] = $db_unfriendly;
					}
				}
			}
			// users preferences are now established to known structured values...

			// SANITY CHECK 
			// ---  [email][use_trash_folder]  ---
			// ---  [email][use_sent_folder]  ---
			// is it possible to use Trash and Sent folders - i.e. using IMAP server
			// if not - force settings to false
			if (stristr($prefs['email']['mail_server_type'], 'imap') == False)
			{
				if (isset($prefs['email']['use_trash_folder']))
				{
					unset($prefs['email']['use_trash_folder']);
				}

				if (isset($prefs['email']['use_sent_folder']))
				{
					unset($prefs['email']['use_sent_folder']);
				}
			}

			// DEBUG : force some settings to test stuff
			//$prefs['email']['p_persistent'] = 'True';
			
			print_debug('class.preferences: $acctnum: ['.$acctnum.'] ; create_email_preferences: $prefs[email]', $prefs['email'],'api');
			print_debug('class.preferences: create_email_preferences: LEAVING', 'messageonly','api');
			return $prefs;
		}

			/*
			// ==== DEPRECATED - ARCHIVAL CODE ====
			// used to be part of function this->create_email_preferences()
			// = = = =  SIMPLER PREFS  = = = =
			// Default Preferences info that is:
			// described in the email prefs array itself

			$default_trash_folder = 'Trash';
			$default_sent_folder = 'Sent';

			// ---  userid  ---
			if (!isset($prefs['email']['userid']))
			{
				$prefs['email']['userid'] = $this->sub_default_userid($accountid);
			}
			// ---  address  --- 
			if (!isset($prefs['email']['address']))
			{
				$prefs['email']['address'] = $this->email_address($accountid);
			}
			// ---  mail_server  ---
			if (!isset($prefs['email']['mail_server']))
			{
				$prefs['email']['mail_server'] = $GLOBALS['egw_info']['server']['mail_server'];
			}
			// ---  mail_server_type  ---
			if (!isset($prefs['email']['mail_server_type']))
			{
				$prefs['email']['mail_server_type'] = $GLOBALS['egw_info']['server']['mail_server_type'];
			}
			// ---  imap_server_type  ---
			if (!isset($prefs['email']['imap_server_type']))
			{
				$prefs['email']['imap_server_type'] = $GLOBALS['egw_info']['server']['imap_server_type'];
			}
			// ---  mail_folder  ---
			// because of the way this option works, an empty string IS ACTUALLY a valid value
			// which represents the $HOME/* as the UWash mail files location
			// THERFOR we must check the "Use_custom_setting" option to help us figure out what to do
			if (!isset($prefs['email']['use_custom_settings']))
			{
				// we are NOT using custom settings so this MUST be the server default
				$prefs['email']['mail_folder'] = $GLOBALS['egw_info']['server']['mail_folder'];
			}
			else
			{
				// we ARE using custom settings AND a BLANK STRING is a valid option, so...
				if ((isset($prefs['email']['mail_folder']))
				&& ($prefs['email']['mail_folder'] != ''))
				{
					// using custom AND a string exists, so "mail_folder" is that string stored in the custom prefs by the user
					// DO NOTING - VALID OPTION VALUE for $prefs['email']['mail_folder']
				}
				else
				{
					// using Custom Prefs BUT this text box was left empty by the user on submit, so no value stored
					// BUT since we are using custom prefs, "mail_folder" MUST BE AN EMPTY STRING
					// which is an acceptable, valid preference, overriding any value which
					// may have been set in ["server"]["mail_folder"]
					// This is one of the few instances in the preference class where an empty, unspecified value
					// actually does NOT get deleted from the repository.
					$prefs['email']['mail_folder'] = '';
				}
			}

			// ---  use_trash_folder  ---
			// ---  trash_folder_name  ---
			// if the option to use the Trash folder is ON, make sure a proper name is specified
			if (isset($prefs['email']['use_trash_folder']))
			{
				if ((!isset($prefs['email']['trash_folder_name']))
				|| ($prefs['email']['trash_folder_name'] == ''))
				{
					$prefs['email']['trash_folder_name'] = $default_trash_folder;
				}
			}

			// ---  use_sent_folder  ---
			// ---  sent_folder_name  ---
			// if the option to use the sent folder is ON, make sure a proper name is specified
			if (isset($prefs['email']['use_sent_folder']))
			{
				if ((!isset($prefs['email']['sent_folder_name']))
				|| ($prefs['email']['sent_folder_name'] == ''))
				{
					$prefs['email']['sent_folder_name'] = $default_sent_folder;
				}
			}

			// ---  layout  ---
			// Layout Template Preference
			// layout 1 = default ; others are prefs
			if (!isset($prefs['email']['layout']))
			{
				$prefs['email']['layout'] = 1;
			}

			//// ---  font_size_offset  ---
			//// Email Index Page Font Size Preference
			//// layout 1 = default ; others are prefs
			//if (!isset($prefs['email']['font_size_offset']))
			//{
			//	$prefs['email']['font_size_offset'] = 'normal';
			//}

			// SANITY CHECK 
			// ---  use_trash_folder  ---
			// ---  use_sent_folder  ---
			// is it possible to use Trash and Sent folders - i.e. using IMAP server
			// if not - force settings to false
			if (stristr($prefs['email']['mail_server_type'], 'imap') == False)
			{
				if (isset($prefs['email']['use_trash_folder']))
				{
					unset($prefs['email']['use_trash_folder']);
				}
				
				if (isset($prefs['email']['use_sent_folder']))
				{
					unset($prefs['email']['use_sent_folder']);
				}
			}

			// DEBUG : force some settings to test stuff
			//$prefs['email']['layout'] = 1;
			//$prefs['email']['layout'] = 2;
			//$prefs['email']['font_size_offset'] = (-1);

			// DEBUG
			//echo "<br>prefs['email']: <br>"
			//	.'<pre>'.serialize($prefs['email']) .'</pre><br>';
			return $prefs;
			*/
	} /* end of preferences class */
?>
