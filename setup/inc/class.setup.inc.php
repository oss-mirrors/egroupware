<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  * This file written by Joseph Engo<jengo@phpgroupware.org>                 *
  *  and Dan Kuykendall<seek3r@phpgroupware.org>                             *
  *  and Mark Peters<skeeter@phpgroupware.org>                               *
  *  and Miles Lott<milosch@groupwhere.org>                                  *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class egw_dummy {
		var $db;
		var $common;
		var $accounts;
		
		function invalidate_session_cache() { }
	}

	class setup
	{
		var $db;
		var $config_table       = 'egw_config';
		var $applications_table = 'egw_applications';
		var $acl_table          = 'egw_acl';
		var $accounts_table     = 'egw_accounts';
		var $prefs_table        = 'egw_preferences';
		var $lang_table         = 'egw_lang';
		var $languages_table    = 'egw_languages';
		var $hooks_table        = 'egw_hooks';
		var $cats_table         = 'egw_categories';
		var $oProc;

		var $detection = '';
		var $process = '';
		var $lang = '';
		var $html = '';

		/* table name vars */
		var $tbl_apps;
		var $tbl_config;
		var $tbl_hooks;

		function setup($html=False, $translation=False)
		{
			$this->detection =& CreateObject('setup.setup_detection');
			$this->process   =& CreateObject('setup.setup_process');

			/* The setup application needs these */
			$this->html = $html ? CreateObject('setup.setup_html') : '';
			$this->translation = $translation ? CreateObject('setup.setup_translation') : '';
		}

		/**
		 * include api db class for the ConfigDomain and connect to the db
		 */
		function loaddb($connect_and_setcharset=true)
		{
			if(!isset($this->ConfigDomain) || empty($this->ConfigDomain))
			{
				$this->ConfigDomain = get_var('ConfigDomain',array('COOKIE','POST'),$_POST['FormDomain']);
			}

			$GLOBALS['egw_info']['server']['db_type'] = $GLOBALS['egw_domain'][$this->ConfigDomain]['db_type'];

			if ($GLOBALS['egw_info']['server']['db_type'] == 'pgsql')
			{
				$GLOBALS['egw_info']['server']['db_persistent'] = False;
			}
			$this->db           =& CreateObject('phpgwapi.egw_db');
			$this->db->Host     = $GLOBALS['egw_domain'][$this->ConfigDomain]['db_host'];
			$this->db->Port     = $GLOBALS['egw_domain'][$this->ConfigDomain]['db_port'];
			$this->db->Type     = $GLOBALS['egw_domain'][$this->ConfigDomain]['db_type'];
			$this->db->Database = $GLOBALS['egw_domain'][$this->ConfigDomain]['db_name'];
			$this->db->User     = $GLOBALS['egw_domain'][$this->ConfigDomain]['db_user'];
			$this->db->Password = $GLOBALS['egw_domain'][$this->ConfigDomain]['db_pass'];
			
			$this->db->set_app('phpgwapi');
			
			if ($connect_and_setcharset)
			{
				$this->db->Halt_On_Error = 'no';	// table might not be created at that stage
				
				$this->set_table_names();		// sets/checks config- and applications-table-name

				// Set the DB's client charset if a system-charset is set
				$this->db->select($this->config_table,'config_value',array(
					'config_app'  => 'phpgwapi',
					'config_name' => 'system_charset',
				),__LINE__,__FILE__);
				if ($this->db->next_record() && $this->db->f(0))
				{
					$this->system_charset = $this->db->f(0);
					$this->db_charset_was = $this->db->Link_ID->GetCharSet();	// needed for the update

					// we can NOT set the DB charset for mysql, if the api version < 1.0.1.019, as it would mess up the DB content!!!
					if (substr($this->db->Type,0,5) == 'mysql')	// we need to check the api version
					{
						$this->db->select($this->applications_table,'app_version',array(
							'app_name'  => 'phpgwapi',
						),__LINE__,__FILE__);
						$api_version = $this->db->next_record() ? $this->db->f(0) : false;
					}
					if (!$api_version || !$this->alessthanb($api_version,'1.0.1.019'))
					{
						$this->db->Link_ID->SetCharSet($this->system_charset);
					}
				}	
				$this->db->Halt_On_Error = 'yes';	// setting the default again
			}
		}

		/**
		* Set the domain used for cookies
		*
		* @return string domain
		*/
		function set_cookiedomain()
		{
			$this->cookie_domain = $_SERVER['HTTP_HOST'];

			// remove port from HTTP_HOST
			if (preg_match("/^(.*):(.*)$/",$this->cookie_domain,$arr))
			{
				$this->cookie_domain = $arr[1];
			}
			if (count(explode('.',$this->cookie_domain)) <= 1)
			{
				// setcookie dont likes domains without dots, leaving it empty, gets setcookie to fill the domain in
				$this->cookie_domain = '';
			}
		}

		/**
		* Set a cookie
		*
		* @param string $cookiename name of cookie to be set
		* @param string $cookievalue value to be used, if unset cookie is cleared (optional)
		* @param int $cookietime when cookie should expire, 0 for session only (optional)
		*/
		function set_cookie($cookiename,$cookievalue='',$cookietime=0)
		{
			if(!isset($this->cookie_domain) || !$this->cookie_domain)
			{
				$this->set_cookiedomain();
			}
			setcookie($cookiename,$cookievalue,$cookietime,'/',$this->cookie_domain);
		}

		/**
		 * authenticate the setup user
		 *
		 * @param	$auth_type	???
		 */
		function auth($auth_type='Config')
		{
			#phpinfo();
			$FormLogout = get_var('FormLogout',  array('GET','POST'));
			if(!$FormLogout)
			{
				$ConfigLogin  = get_var('ConfigLogin', array('POST'));
				$HeaderLogin  = get_var('HeaderLogin', array('POST'));
				$FormDomain   = get_var('FormDomain',  array('POST'));
				$FormUser     = get_var('FormUser',    array('POST'));
				$FormPW       = get_var('FormPW',      array('POST'));

				$this->ConfigDomain = get_var('ConfigDomain',array('POST','COOKIE'));
				$ConfigUser   = get_var('ConfigUser',  array('POST','COOKIE'));
				$ConfigPW     = get_var('ConfigPW',    array('POST','COOKIE'));
				$HeaderUser   = get_var('HeaderUser',  array('POST','COOKIE'));
				$HeaderPW     = get_var('HeaderPW',    array('POST','COOKIE'));
				$ConfigLang   = get_var('ConfigLang',  array('POST','COOKIE'));

				/* Setup defaults to aid in header upgrade to version 1.26.
				 * This was the first version to include the following values.
				 */
				if(!@isset($GLOBALS['egw_domain'][$FormDomain]['config_user']) && isset($GLOBALS['egw_domain'][$FormDomain]))
				{
					@$GLOBALS['egw_domain'][$FormDomain]['config_user'] = 'admin';
				}
				if(!@isset($GLOBALS['egw_info']['server']['header_admin_user']))
				{
					@$GLOBALS['egw_info']['server']['header_admin_user'] = 'admin';
				}
			}

			$remoteip   = $_SERVER['REMOTE_ADDR'];
			if(!empty($remoteip) && !$this->checkip($remoteip)) { return False; }

			/* If FormLogout is set, simply invalidate the cookies (LOGOUT) */
			switch(strtolower($FormLogout))
			{
				case 'config':
					/* config logout */
					$expire = time() - 86400;
					$this->set_cookie('ConfigUser','',$expire,'/');
					$this->set_cookie('ConfigPW','',$expire,'/');
					$this->set_cookie('ConfigDomain','',$expire,'/');
					$this->set_cookie('ConfigLang','',$expire,'/');
					$GLOBALS['egw_info']['setup']['LastDomain'] = $_COOKIE['ConfigDomain'];
					$GLOBALS['egw_info']['setup']['ConfigLoginMSG'] = lang('You have successfully logged out');
					$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = '';
					return False;
				case 'header':
					/* header admin logout */
					$expire = time() - 86400;
					$this->set_cookie('HeaderUser','',$expire,'/');
					$this->set_cookie('HeaderPW','',$expire,'/');
					$this->set_cookie('ConfigLang','',$expire,'/');
					$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = lang('You have successfully logged out');
					$GLOBALS['egw_info']['setup']['ConfigLoginMSG'] = '';
					return False;
			}

			/* We get here if FormLogout is not set (LOGIN or subsequent pages) */
			/* Expire login if idle for 20 minutes.  The cookies are updated on every page load. */
			$expire = (int)(time() + (1200*9));

			switch(strtolower($auth_type))
			{
				case 'header':
					if(!empty($HeaderLogin))
					{
						/* header admin login */
						/* New test is md5, cleartext version is for header < 1.26 */
						if ($this->check_auth($FormUser,$FormPW,$GLOBALS['egw_info']['server']['header_admin_user'],
							$GLOBALS['egw_info']['server']['header_admin_password']))
						{
							$this->set_cookie('HeaderUser',$FormUser,$expire,'/');
							$this->set_cookie('HeaderPW',md5($FormPW),$expire,'/');
							$this->set_cookie('ConfigLang',$ConfigLang,$expire,'/');
							return True;
						}
						else
						{
							$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = lang('Invalid password');
							$GLOBALS['egw_info']['setup']['ConfigLoginMSG'] = '';
							return False;
						}
					}
					elseif(!empty($HeaderPW) && $auth_type == 'Header')
					{
						// Returning after login to header admin
						/* New test is md5, cleartext version is for header < 1.26 */
						if ($this->check_auth($HeaderUser,$HeaderPW,$GLOBALS['egw_info']['server']['header_admin_user'],
							$GLOBALS['egw_info']['server']['header_admin_password']))
						{
							$this->set_cookie('HeaderUser',$HeaderUser,$expire,'/');
							$this->set_cookie('HeaderPW',$HeaderPW,$expire,'/');
							$this->set_cookie('ConfigLang',$ConfigLang,$expire,'/');
							return True;
						}
						else
						{
							$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = lang('Invalid password');
							$GLOBALS['egw_info']['setup']['ConfigLoginMSG'] = '';
							return False;
						}
					}
					break;
				case 'config':
					if(!empty($ConfigLogin))
					{
						/* config login */
						/* New test is md5, cleartext version is for header < 1.26 */
						if (isset($GLOBALS['egw_domain'][$FormDomain]) &&
							$this->check_auth($FormUser,$FormPW,@$GLOBALS['egw_domain'][$FormDomain]['config_user'],
							@$GLOBALS['egw_domain'][$FormDomain]['config_passwd']))
						{
							$this->set_cookie('ConfigUser',$FormUser,$expire,'/');
							$this->set_cookie('ConfigPW',md5($FormPW),$expire,'/');
							$this->set_cookie('ConfigDomain',$FormDomain,$expire,'/');
							/* Set this now since the cookie will not be available until the next page load */
							$this->ConfigDomain = $FormDomain;
							$this->set_cookie('ConfigLang',$ConfigLang,$expire,'/');
							return True;
						}
						else
						{
							$GLOBALS['egw_info']['setup']['ConfigLoginMSG'] = lang('Invalid password');
							$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = '';
							return False;
						}
					}
					elseif(!empty($ConfigPW))
					{
						// Returning after login to config
						/* New test is md5, cleartext version is for header < 1.26 */
						if ($this->check_auth($ConfigUser,$ConfigPW,@$GLOBALS['egw_domain'][$this->ConfigDomain]['config_user'],
							@$GLOBALS['egw_domain'][$this->ConfigDomain]['config_passwd']))
						{
							$this->set_cookie('ConfigUser',$ConfigUser,$expire,'/');
							$this->set_cookie('ConfigPW',$ConfigPW,$expire,'/');
							$this->set_cookie('ConfigDomain',$this->ConfigDomain,$expire,'/');
							$this->set_cookie('ConfigLang',$ConfigLang,$expire,'/');
							return True;
						}
						else
						{
							$GLOBALS['egw_info']['setup']['ConfigLoginMSG'] = lang('Invalid password');
							$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = '';
							return False;
						}
					}
					break;
			}

			return False;
		}

        /**
        * check if username and password is valid
        *
        * this function compares the supplied and stored username and password
		* as any of the passwords can be clear text or md5 we convert them to md5 
		* internal and compare always the md5 hashs
        *
        * @param string $user the user supplied username
        * @param string $pw the user supplied password
        * @param string $conf_user the configured username
        * @param string $conf_pw the configured password
        * @returns bool
        */
		function check_auth($user,$pw,$conf_user,$conf_pw)
		{
			#echo "<p>setup::check_auth('$user','$pw','$conf_user','$conf_pw')</p>\n";exit;
			if ($user != $conf_user)
			{
				return False; // wrong username
			}

			// Verify that $pw is not already encoded as md5
			if(!preg_match('/^[0-9a-f]{32}$/',$conf_pw))
			{
				$conf_pw = md5($conf_pw);
			}
			

			// Verify that $pw is not already encoded as md5
			if(!preg_match('/^[0-9a-f]{32}$/',$pw))
			{
				$pw = md5($pw);
			}
			
			return $pw == $conf_pw;
			 
		}

		function checkip($remoteip='')
		{
			//echo "<p>setup::checkip($remoteip) against setup_acl='".$GLOBALS['egw_info']['server']['setup_acl']."'</p>\n";
			$allowed_ips = explode(',',@$GLOBALS['egw_info']['server']['setup_acl']);
			if(empty($GLOBALS['egw_info']['server']['setup_acl']) || !is_array($allowed_ips))
			{
				return True;	// no test
			}
			$remotes = explode('.',$remoteip);
			foreach($allowed_ips as $value)
			{
				if (!preg_match('/^[0-9.]+$/',$value))
				{
					$value = gethostbyname($was=$value);		// resolve domain-name, eg. a dyndns account
					//echo "resolving '$was' to '$value'<br>\n";
				}
				$values = explode('.',$value);
				for($i = 0; $i < count($values); ++$i)
				{
					if ((int) $values[$i] != (int) $remotes[$i])
					{
						break;
					}
				}
				if ($i == count($values))
				{
					return True;	// match
				}
			}
			$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = '';
			$GLOBALS['egw_info']['setup']['ConfigLoginMSG'] = lang('Invalid IP address');

			return False;
		}

		/**
		 * Return X.X.X major version from X.X.X.X versionstring
		 *
		 * @param	$
		 */
		function get_major($versionstring)
		{
			if(!$versionstring)
			{
				return False;
			}

			$version = str_replace('pre','.',$versionstring);
			$varray  = explode('.',$version);
			$major   = implode('.',array($varray[0],$varray[1],$varray[2]));

			return $major;
		}

		/**
		 * Clear system/user level cache so as to have it rebuilt with the next access
		 *
		 * @deprecated AFAIK this code is not used anymore -- RalfBecker 2005/11/04
		 */
		function clear_session_cache()
		{
		}

		/**
		 * Add an application to the phpgw_applications table
		 *
		 * @param	$appname	Application 'name' with a matching $setup_info[$appname] array slice
		 * @param	$enable		 * optional, set to True/False to override setup.inc.php setting
		 */
		function register_app($appname,$enable=99)
		{
			$setup_info = $GLOBALS['setup_info'];

			if(!$appname)
			{
				return False;
			}

			if($enable==99)
			{
				$enable = $setup_info[$appname]['enable'];
			}
			$enable = (int)$enable;

			if($GLOBALS['DEBUG'])
			{
				echo '<br>register_app(): ' . $appname . ', version: ' . $setup_info[$appname]['version'] . ', table: ' . $appstbl . '<br>';
				// _debug_array($setup_info[$appname]);
			}

			if($setup_info[$appname]['version'])
			{
				if($setup_info[$appname]['tables'])
				{
					$tables = implode(',',$setup_info[$appname]['tables']);
				}
				if ($setup_info[$appname]['tables_use_prefix'] == True)
				{
					if($GLOBALS['DEBUG'])
					{
						echo "<br>$appname uses tables_use_prefix, storing ". $setup_info[$appname]['tables_prefix']." as prefix for tables\n";
					}
					$this->db->insert($this->config_table,array(
							'config_app'	=> $appname,
							'config_name'	=> $appname.'_tables_prefix',
							'config_value'	=> $setup_info[$appname]['tables_prefix'],
						),False,__LINE__,__FILE__);
				}
				$this->db->insert($this->applications_table,array(
						'app_name'		=> $appname,
						'app_enabled'	=> $enable,
						'app_order'		=> $setup_info[$appname]['app_order'],
						'app_tables'	=> $tables,
						'app_version'	=> $setup_info[$appname]['version'],
					),False,__LINE__,__FILE__);

				$this->clear_session_cache();
			}
		}

		/**
		 * Check if an application has info in the db
		 *
		 * @param	$appname	Application 'name' with a matching $setup_info[$appname] array slice
		 * @param	$enabled	optional, set to False to not enable this app
		 */
		function app_registered($appname)
		{
			$setup_info = $GLOBALS['setup_info'];

			if(!$appname)
			{
				return False;
			}

			if(@$GLOBALS['DEBUG'])
			{
				echo '<br>app_registered(): checking ' . $appname . ', table: ' . $this->applications_table;
				// _debug_array($setup_info[$appname]);
			}

			$this->db->select($this->applications_table,'COUNT(*)',array('app_name' => $appname),__LINE__,__FILE__);
			if($this->db->next_record() && $this->db->f(0))
			{
				if(@$GLOBALS['DEBUG'])
				{
					echo '... app previously registered.';
				}
				return True;
			}
			if(@$GLOBALS['DEBUG'])
			{
				echo '... app not registered';
			}
			return False;
		}

		/**
		 * Update application info in the db
		 *
		 * @param	$appname	Application 'name' with a matching $setup_info[$appname] array slice
		 * @param	$enabled	optional, set to False to not enable this app
		 */
		function update_app($appname)
		{
			$setup_info = $GLOBALS['setup_info'];

			if(!$appname)
			{
				return False;
			}

			if($GLOBALS['DEBUG'])
			{
				echo '<br>update_app(): ' . $appname . ', version: ' . $setup_info[$appname]['currentver'] . ', table: ' . $this->applications_table . '<br>';
				// _debug_array($setup_info[$appname]);
			}

			if(!$this->app_registered($appname))
			{
				return False;
			}

			if($setup_info[$appname]['version'])
			{
				//echo '<br>' . $setup_info[$appname]['version'];
				if($setup_info[$appname]['tables'])
				{
					$tables = implode(',',$setup_info[$appname]['tables']);
				}
				$this->db->update($this->applications_table,array(
						'app_enabled'	=> $setup_info[$appname]['enable'],
						'app_order'		=> $setup_info[$appname]['app_order'],
						'app_tables'	=> $tables,
						'app_version'	=> $setup_info[$appname]['version'],
					),array('app_name'=>$appname),__LINE__,__FILE__);
			}
		}

		/**
		 * Update application version in applications table, post upgrade
		 *
		 * @param	$setup_info		 * Array of application information (multiple apps or single)
		 * @param	$appname		 * Application 'name' with a matching $setup_info[$appname] array slice
		 * @param	$tableschanged	???
		 */
		function update_app_version($setup_info, $appname, $tableschanged = True)
		{
			if(!$appname)
			{
				return False;
			}

			if($tableschanged == True)
			{
				$GLOBALS['egw_info']['setup']['tableschanged'] = True;
			}
			if($setup_info[$appname]['currentver'])
			{
				$this->db->update($this->applications_table,array(
						'app_version'	=> $setup_info[$appname]['currentver'],
					),array('app_name'=>$appname),__LINE__,__FILE__);
			}
			return $setup_info;
		}

		/**
		 * de-Register an application
		 *
		 * @param	$appname	Application 'name' with a matching $setup_info[$appname] array slice
		 */
		function deregister_app($appname)
		{
			if(!$appname)
			{
				return False;
			}
			$setup_info = $GLOBALS['setup_info'];

			//echo 'DELETING application: ' . $appname;
			$this->db->delete($this->applications_table,array('app_name'=>$appname),__LINE__,__FILE__);
			$this->clear_session_cache();
		}

		/**
		 * Register an application's hooks
		 *
		 * @param	$appname	Application 'name' with a matching $setup_info[$appname] array slice
		 */
		function register_hooks($appname)
		{
			$setup_info = $GLOBALS['setup_info'];

			if(!$appname)
			{
				return False;
			}

			if(!$this->hooks_table)	// No hooks table yet
			{
				return False;
			}

			if (!is_object($this->hooks))
			{
				$this->hooks =& CreateObject('phpgwapi.hooks',$this->db,$this->hooks_table);
			}
			$this->hooks->register_hooks($appname,$setup_info[$appname]['hooks']);
		}

		/**
		 * Update an application's hooks
		 *
		 * @param	$appname	Application 'name' with a matching $setup_info[$appname] array slice
		 */
		function update_hooks($appname)
		{
			$this->register_hooks($appname);
		}

		/**
		 * de-Register an application's hooks
		 *
		 * @param	$appname	Application 'name' with a matching $setup_info[$appname] array slice
		 */
		function deregister_hooks($appname)
		{
			if(!$this->hooks_table)	// No hooks table yet
			{
				return False;
			}

			if(!$appname)
			{
				return False;
			}
			
			//echo "DELETING hooks for: " . $setup_info[$appname]['name'];
			if (!is_object($this->hooks))
			{
				$this->hooks =& CreateObject('phpgwapi.hooks',$this->db,$this->hooks_table);
			}
			$this->hooks->register_hooks($appname);
		}

		/**
		  * call the hooks for a single application
		  *
		  * @param $location hook location - required
		  * @param $appname application name - optional
		 */
		function hook($location, $appname='')
		{
			if (!is_object($this->hooks))
			{
				$this->hooks =& CreateObject('phpgwapi.hooks',$this->db,$this->hooks_table);
			}
			return $this->hooks->single($location,$appname,True,True);
		}

		/**
		 * egw version checking, is param 1 < param 2 in phpgw versionspeak?
		 * @param	$a	phpgw version number to check if less than $b
		 * @param	$b	phpgw version number to check $a against
		 * @return	True if $a < $b
		 */
		function alessthanb($a,$b,$DEBUG=False)
		{
			$num = array('1st','2nd','3rd','4th');

			if($DEBUG)
			{
				echo'<br>Input values: '
					. 'A="'.$a.'", B="'.$b.'"';
			}
			$newa = str_replace('pre','.',$a);
			$newb = str_replace('pre','.',$b);
			$testa = explode('.',$newa);
			if(@$testa[1] == '')
			{
				$testa[1] = 0;
			}

			$testb = explode('.',$newb);
			if(@$testb[1] == '')
			{
				$testb[1] = 0;
			}
			if(@$testb[3] == '')
			{
				$testb[3] = 0;
			}
			$less = 0;

			for($i=0;$i<count($testa);$i++)
			{
				if($DEBUG) { echo'<br>Checking if '. (int)$testa[$i] . ' is less than ' . (int)$testb[$i] . ' ...'; }
				if((int)$testa[$i] < (int)$testb[$i])
				{
					if ($DEBUG) { echo ' yes.'; }
					$less++;
					if($i<3)
					{
						/* Ensure that this is definitely smaller */
						if($DEBUG) { echo"  This is the $num[$i] octet, so A is definitely less than B."; }
						$less = 5;
						break;
					}
				}
				elseif((int)$testa[$i] > (int)$testb[$i])
				{
					if($DEBUG) { echo ' no.'; }
					$less--;
					if($i<2)
					{
						/* Ensure that this is definitely greater */
						if($DEBUG) { echo"  This is the $num[$i] octet, so A is definitely greater than B."; }
						$less = -5;
						break;
					}
				}
				else
				{
					if($DEBUG) { echo ' no, they are equal or of different length.'; }
					// makes sure eg. '1.0.0' is counted less the '1.0.0.xxx' !
					$less = count($testa) < count($testb) ? 1 : 0;
				}
			}
			if($DEBUG) { echo '<br>Check value is: "'.$less.'"'; }
			if($less>0)
			{
				if($DEBUG) { echo '<br>A is less than B'; }
				return True;
			}
			elseif($less<0)
			{
				if($DEBUG) { echo '<br>A is greater than B'; }
				return False;
			}
			else
			{
				if($DEBUG) { echo '<br>A is equal to B'; }
				return False;
			}
		}

		/**
		 * egw version checking, is param 1 > param 2 in phpgw versionspeak?
		 *
		 * @param	$a	phpgw version number to check if more than $b
		 * @param	$b	phpgw version number to check $a against
		 * @return	True if $a < $b
		 */
		function amorethanb($a,$b,$DEBUG=False)
		{
			$num = array('1st','2nd','3rd','4th');

			if($DEBUG)
			{
				echo'<br>Input values: '
					. 'A="'.$a.'", B="'.$b.'"';
			}
			$newa = str_replace('pre','.',$a);
			$newb = str_replace('pre','.',$b);
			$testa = explode('.',$newa);
			if($testa[3] == '')
			{
				$testa[3] = 0;
			}
			$testb = explode('.',$newb);
			if($testb[3] == '')
			{
				$testb[3] = 0;
			}
			$less = 0;

			for($i=0;$i<count($testa);$i++)
			{
				if($DEBUG) { echo'<br>Checking if '. (int)$testa[$i] . ' is more than ' . (int)$testb[$i] . ' ...'; }
				if((int)$testa[$i] > (int)$testb[$i])
				{
					if($DEBUG) { echo ' yes.'; }
					$less++;
					if($i<3)
					{
						/* Ensure that this is definitely greater */
						if($DEBUG) { echo"  This is the $num[$i] octet, so A is definitely greater than B."; }
						$less = 5;
						break;
					}
				}
				elseif((int)$testa[$i] < (int)$testb[$i])
				{
					if($DEBUG) { echo ' no.'; }
					$less--;
					if($i<2)
					{
						/* Ensure that this is definitely smaller */
						if($DEBUG) { echo"  This is the $num[$i] octet, so A is definitely less than B."; }
						$less = -5;
						break;
					}
				}
				else
				{
					if($DEBUG) { echo ' no, they are equal.'; }
					$less = 0;
				}
			}
			if($DEBUG) { echo '<br>Check value is: "'.$less.'"'; }
			if($less>0)
			{
				if($DEBUG) { echo '<br>A is greater than B'; }
				return True;
			}
			elseif($less<0)
			{
				if($DEBUG) { echo '<br>A is less than B'; }
				return False;
			}
			else
			{
				if($DEBUG) { echo '<br>A is equal to B'; }
				return False;
			}
		}

		function setup_account_object()
		{
			if (!is_object($GLOBALS['egw']->accounts))
			{
				if (!is_object($this->db))
				{
					$this->loaddb();
				}
				/* Load up some configured values */
				$this->db->query("SELECT config_name,config_value FROM $this->config_table "
					. "WHERE config_name LIKE 'ldap%' OR config_name LIKE 'account_%' OR config_name LIKE '%encryption%'",__LINE__,__FILE__);
				while($this->db->next_record())
				{
					$GLOBALS['egw_info']['server'][$this->db->f('config_name')] = $this->db->f('config_value');
				}
				//if (!is_object($GLOBALS['egw']))
				{
					$GLOBALS['egw'] =& new egw_dummy();
					$GLOBALS['phpgw'] =& $GLOBALS['egw'];
				}
				$GLOBALS['egw']->db          = clone($this->db);
				$GLOBALS['egw']->common      =& CreateObject('phpgwapi.common');
				$GLOBALS['egw']->accounts    =& CreateObject('phpgwapi.accounts');

				if(($GLOBALS['egw_info']['server']['account_repository'] == 'ldap') &&
					!$GLOBALS['egw']->accounts->ds)
				{
					printf("<b>Error: Error connecting to LDAP server %s!</b><br>",$GLOBALS['egw_info']['server']['ldap_host']);
					exit;
				}
			}
		}

		/**
		 * add an user account or a user group
		 *
		 * if the $username already exists, only the id is returned, no new user / group gets created
		 *
		 * @param username string alphanumerical username or groupname (account_lid)
		 * @param first, last string first / last name
		 * @param $passwd string cleartext pw
		 * @param $group string/boolean Groupname for users primary group or False for a group, default 'Default'
		 * @param $changepw boolean user has right to change pw, default False
		 * @return the numerical user-id
		 */
		function add_account($username,$first,$last,$passwd,$group='default',$changepw=False)
		{
			$this->setup_account_object();

			$groupid = $group ? $GLOBALS['egw']->accounts->name2id($group) : False;

			if(!($accountid = $GLOBALS['egw']->accounts->name2id($username)))
			{
				$accountid = $accountid ? $accountid : $GLOBALS['egw']->accounts->create(array(
					'account_type'      => $group ? 'u' : 'g',
					'account_lid'       => $username,
					'account_passwd'    => $passwd,
					'account_firstname' => $first,
					'account_lastname'  => $last,
					'account_status'    => 'A',
					'account_primary_group' => $groupid,
					'account_expires'   => -1
				));
			}
			$accountid = (int)$accountid;
			if($groupid)
			{
				$this->add_acl('phpgw_group',(int)$groupid,$accountid);
			}
			$this->add_acl('preferences','changepassword',$accountid,(int)$changepw);

			return $accountid;
		}
		
		/**
		 * Check if accounts other then the automatically installed anonymous account exist
		 *
		 * We check via the account object, to deal with different account-storages
		 *
		 * @return boolean
		 */
		function accounts_exist()
		{
			$this->setup_account_object();

			$accounts = $GLOBALS['egw']->accounts->search(array(
				'type'   => 'accounts',
				'start'  => 0,
				'offset' => 2,	// we only need to check 2 accounts, if we just check for not anonymous
			));
			
			if (!$accounts || !is_array($accounts) || !count($accounts))
			{
				return false;
			}
			foreach($accounts as $account)
			{
				if ($account['account_lid'] != 'anonymous')
				{
					// we might add further checks, eg. if the account really has admin rights here
					return true;
				}
			}
			return false;
		}

		/**
		 * Add ACL rights
		 *
		 * @param $app string/array with app-names
		 * @param $locations string eg. run
		 * @param $account int/string accountid or account_lid
		 * @param $rights int rights to set, default 1
		 */
		function add_acl($apps,$location,$account,$rights=1)
		{
			if (!is_int($account))
			{
				$this->setup_account_object();
				$account = $GLOBALS['egw']->accounts->name2id($account);
			}
			if(!is_object($this->db))
			{
				$this->loaddb();
			}

			if(!is_array($apps))
			{
				$apps = array($apps);
			}
			foreach($apps as $app)
			{
				$this->db->delete($this->acl_table,array(
					'acl_appname'  => $app,
					'acl_location' => $location,
					'acl_account'  => $account,
				),__LINE__,__FILE__);

				if ((int) $rights)
				{
					$this->db->insert($this->acl_table,array(
						'acl_rights' => $rights
					),array(
						'acl_appname'  => $app,
						'acl_location' => $location,
						'acl_account'  => $account,
					),__LINE__,__FILE__);
				}
			}
		}
		
		/**
		 * checks if one of the given tables exist, returns the first match
		 *
		 * @param array $tables array with possible table-names
		 * @return string/boolean tablename or false
		 */
		function table_exist($tables)
		{
			static $table_names;
			
			if (!$table_names) $table_names = $this->db->table_names();
			
			if (!$table_names) return false;
			
			foreach($table_names as $data)	
			{
				if (($key = array_search($data['table_name'],$tables)) !== false)
				{
					return $tables[$key];
				}
			}
			return false;
		}
		
		/**
		 * Checks and set the names of the tables, which get accessed before an update: eg. config- and applications-table
		 *
		 * Other tables can always use the most up to date name
		 */
		function set_table_names()
		{
			foreach(array(
				'config_table'       => array('egw_config','phpgw_config','config'),
				'applications_table' => array('egw_applications','phpgw_applications','applications'),
				'lang_table'         => array('egw_lang','phpgw_lang','lang'),
				'languages_table'    => array('egw_languages','phpgw_languages','languages'),
			) as $name => $tables)
			{
				$table = $this->table_exist($tables);

				if ($table && $table != $this->$name)	// only overwrite the default name, if we realy got one (important for new installs)
				{
					$this->$name = $table;
				}
				//echo "<p>setup::set_table_names: $name = '{$this->$name}'</p>\n";
			}
		}
	}
