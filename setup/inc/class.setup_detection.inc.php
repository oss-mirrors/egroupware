<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  * This file written by Dan Kuykendall<seek3r@phpgroupware.org>             *
  *  and Miles Lott<milos@groupwhere.org>                                    *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	class setup_detection
	{
		function get_versions()
		{
			$d = dir(EGW_SERVER_ROOT);
			while($entry=$d->read())
			{
				if($entry != ".." && !ereg('setup',$entry) && is_dir(EGW_SERVER_ROOT . '/' . $entry))
				{
					$f = EGW_SERVER_ROOT . '/' . $entry . '/setup/setup.inc.php';
					if (@file_exists ($f))
					{
						include($f);
						$setup_info[$entry]['filename'] = $f;
					}
				}
			}
			$d->close();

			// _debug_array($setup_info);
			@ksort($setup_info);
			return $setup_info;
		}

		function get_db_versions($setup_info='')
		{
			$tname = Array();
			$GLOBALS['egw_setup']->db->Halt_On_Error = 'no';
			
			$GLOBALS['egw_setup']->set_table_names();

			if($GLOBALS['egw_setup']->table_exist(array($GLOBALS['egw_setup']->applications_table)))
			{
				/* one of these tables exists. checking for post/pre beta version */
				if($GLOBALS['egw_setup']->applications_table != 'applications')
				{
					$GLOBALS['egw_setup']->db->select($GLOBALS['egw_setup']->applications_table,'*',false,__LINE__,__FILE__);
					while(@$GLOBALS['egw_setup']->db->next_record())
					{
						$setup_info[$GLOBALS['egw_setup']->db->f('app_name')]['currentver'] = $GLOBALS['egw_setup']->db->f('app_version');
						$setup_info[$GLOBALS['egw_setup']->db->f('app_name')]['enabled'] = $GLOBALS['egw_setup']->db->f('app_enabled');
					}
					/* This is to catch old setup installs that did not have phpgwapi listed as an app */
					$tmp = @$setup_info['phpgwapi']['version']; /* save the file version */
					if(!@$setup_info['phpgwapi']['currentver'])
					{
						$setup_info['phpgwapi']['currentver'] = $setup_info['admin']['currentver'];
						$setup_info['phpgwapi']['version'] = $setup_info['admin']['currentver'];
						$setup_info['phpgwapi']['enabled'] = $setup_info['admin']['enabled'];
						// _debug_array($setup_info['phpgwapi']);exit;
						// There seems to be a problem here.  If ['phpgwapi']['currentver'] is set,
						// The GLOBALS never gets set.
						$GLOBALS['setup_info'] = $setup_info;
						$GLOBALS['egw_setup']->register_app('phpgwapi');
					}
					else
					{
						$GLOBALS['setup_info'] = $setup_info;
					}
					$setup_info['phpgwapi']['version'] = $tmp; /* restore the file version */
				}
				else
				{
					$GLOBALS['egw_setup']->db->query('select * from applications');
					while(@$GLOBALS['egw_setup']->db->next_record())
					{
						if($GLOBALS['egw_setup']->db->f('app_name') == 'admin')
						{
							$setup_info['phpgwapi']['currentver'] = $GLOBALS['egw_setup']->db->f('app_version');
						}
						$setup_info[$GLOBALS['egw_setup']->db->f('app_name')]['currentver'] = $GLOBALS['egw_setup']->db->f('app_version');
					}
				}
			}
			// _debug_array($setup_info);
			return $setup_info;
		}

		/* app status values:
		U	Upgrade required/available
		R	upgrade in pRogress
		C	upgrade Completed successfully
		D	Dependency failure
		P	Post-install dependency failure
		F	upgrade Failed
		V	Version mismatch at end of upgrade (Not used, proposed only)
		M	Missing files at start of upgrade (Not used, proposed only)
		*/
		function compare_versions($setup_info)
		{
			foreach($setup_info as $key => $value)
			{
				//echo '<br>'.$value['name'].'STATUS: '.$value['status'];
				/* Only set this if it has not already failed to upgrade - Milosch */
				if(!( (@$value['status'] == 'F') || (@$value['status'] == 'C') ))
				{
					//if ($setup_info[$key]['currentver'] > $setup_info[$key]['version'])
					if($GLOBALS['egw_setup']->amorethanb($value['currentver'],@$value['version']))
					{
						$setup_info[$key]['status'] = 'V';
					}
					elseif(@$value['currentver'] == @$value['version'])
					{
						$setup_info[$key]['status'] = 'C';
					}
					elseif($GLOBALS['egw_setup']->alessthanb(@$value['currentver'],@$value['version']))
					{
						$setup_info[$key]['status'] = 'U';
					}
					else
					{
						$setup_info[$key]['status'] = 'U';
					}
				}
			}
			// _debug_array($setup_info);
			return $setup_info;
		}

		function check_depends($setup_info)
		{
			/* Run the list of apps */
			foreach($setup_info as $key => $value)
			{
				/* Does this app have any depends */
				if(isset($value['depends']))
				{
					/* If so find out which apps it depends on */
					foreach($value['depends'] as $depkey => $depvalue)
					{
						/* I set this to False until we find a compatible version of this app */
						$setup_info['depends'][$depkey]['status'] = False;
						/* Now we loop thru the versions looking for a compatible version */

						foreach($depvalue['versions'] as $depskey => $depsvalue)
						{
							$currentver = $setup_info[$depvalue['appname']]['currentver'];
							if ($depvalue['appname'] == 'phpgwapi' && substr($currentver,0,6) == '0.9.99')
							{
								$currentver = '0.9.14.508';
							}
							$major = $GLOBALS['egw_setup']->get_major($currentver);
							if ($major == $depsvalue)
							{
								$setup_info['depends'][$depkey]['status'] = True;
							}
							else	// check if majors are equal and minors greater or equal
							{
								$major_depsvalue = $GLOBALS['egw_setup']->get_major($depsvalue);
								list(,,,$minor_depsvalue) = explode('.',$depsvalue);
								list(,,,$minor) = explode('.',$currentver);
								if ($major == $major_depsvalue && $minor <= $minor_depsvalue)
								{
									$setup_info['depends'][$depkey]['status'] = True;
								}
							}
						}
					}
					/*
					 Finally, we loop through the dependencies again to look for apps that still have a failure status
					 If we find one, we set the apps overall status as a dependency failure.
					*/
					foreach($value['depends'] as $depkey => $depvalue)
					{
						if ($setup_info['depends'][$depkey]['status'] == False)
						{
							/* Only set this if it has not already failed to upgrade - Milosch */
							if($setup_info[$key]['status'] != 'F')//&& $setup_info[$key]['status'] != 'C')
							{
								/* Added check for status U - uninstalled apps carry this flag (upgrade from nothing == install).
								 * This should fix apps showing post-install dep failure when they are not yet installed.
								 */
								if($setup_info[$key]['status'] == 'C' || $setup_info[$key]['status'] == 'U')
								{
									$setup_info[$key]['status'] = 'D';
								}
								else
								{
									$setup_info[$key]['status'] = 'P';
								}
							}
						}
					}
				}
			}
			return $setup_info;
		}

		/*
		 Called during the mass upgrade routine (Stage 1) to check for apps
		 that wish to be excluded from this process.
		*/
		function upgrade_exclude($setup_info)
		{
			foreach($setup_info as $key => $value)
			{
				if(isset($value['no_mass_update']))
				{
					unset($setup_info[$key]);
				}
			}
			return $setup_info;
		}

		function check_header()
		{
			if(!file_exists('../header.inc.php'))
			{
				$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage One';
				return '1';
			}
			else
			{
				if(!@isset($GLOBALS['egw_info']['server']['header_admin_password']))
				{
					$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage One (No header admin password set)';
					return '2';
				}
				elseif(!@isset($GLOBALS['egw_domain']))
				{
					$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage One (Add domains to your header.inc.php)';
					return '3';
				}
				elseif(@$GLOBALS['egw_info']['server']['versions']['header'] != @$GLOBALS['egw_info']['server']['versions']['current_header'])
				{
					$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage One (Upgrade your header.inc.php)';
					return '4';
				}
			}
			/* header.inc.php part settled. Moving to authentication */
			$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage One (Completed)';
			return '10';
		}

		function check_db($setup_info='')
		{
			$setup_info = $setup_info ? $setup_info : $GLOBALS['setup_info'];

			$GLOBALS['egw_setup']->db->Halt_On_Error = 'no';
			// _debug_array($setup_info);

			if (!$GLOBALS['egw_setup']->db->Link_ID)
			{
				$old = error_reporting();
				error_reporting($old & ~E_WARNING);	// no warnings
				$GLOBALS['egw_setup']->db->connect();
				error_reporting($old);
			}
			$GLOBALS['egw_setup']->set_table_names();

			if (!$GLOBALS['egw_setup']->db->Link_ID)
			{
				$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 1 (Create Database)';
				return 1;
			}
			if(!isset($setup_info['phpgwapi']['currentver']))
			{
				$setup_info = $this->get_db_versions($setup_info);
			}
			//_debug_array($setup_info);
			if (isset($setup_info['phpgwapi']['currentver']))
			{
				if(@$setup_info['phpgwapi']['currentver'] == @$setup_info['phpgwapi']['version'])
				{
					$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 1 (Tables Complete)';
					return 10;
				}
				else
				{
					$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 1 (Tables need upgrading)';
					return 4;
				}
			}
			else
			{
				/* no tables, so checking if we can create them */
				$GLOBALS['egw_setup']->db->query('CREATE TABLE egw_testrights ( testfield varchar(5) NOT NULL )');
				if(!$GLOBALS['egw_setup']->db->Errno)
				{
					$GLOBALS['egw_setup']->db->query('DROP TABLE egw_testrights');
					$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 3 (Install Applications)';
					return 3;
				}
				else
				{
					$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 1 (Create Database)';
					return 1;
				}
			}
		}

		function check_config()
		{
			$GLOBALS['egw_setup']->db->Halt_On_Error = 'no';
			if(@$GLOBALS['egw_info']['setup']['stage']['db'] != 10)
			{
				return '';
			}

			$GLOBALS['egw_setup']->db->select($GLOBALS['egw_setup']->config_table,'config_value',array('config_name'=>'freshinstall'),__LINE__,__FILE__);
			$configured = $GLOBALS['egw_setup']->db->next_record() ? $GLOBALS['egw_setup']->db->f('config_value') : False;
			if($configed)
			{
				$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 2 (Needs Configuration)';
				return 1;
			}
			else
			{
				$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 2 (Configuration OK)';
				return 10;
			}
		}

		function check_lang($check = True)
		{
			$GLOBALS['egw_setup']->db->Halt_On_Error = 'no';
			if($check && $GLOBALS['egw_info']['setup']['stage']['db'] != 10)
			{
				return '';
			}
			if (!$check)
			{
				$GLOBALS['setup_info'] = $GLOBALS['egw_setup']->detection->get_db_versions($GLOBALS['setup_info']);
			}
			$GLOBALS['egw_setup']->db->query($q = "SELECT DISTINCT lang FROM {$GLOBALS['egw_setup']->lang_table}",__LINE__,__FILE__);
			if($GLOBALS['egw_setup']->db->num_rows() == 0)
			{
				$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 3 (No languages installed)';
				return 1;
			}
			else
			{
				while(@$GLOBALS['egw_setup']->db->next_record())
				{
					$GLOBALS['egw_info']['setup']['installed_langs'][$GLOBALS['egw_setup']->db->f('lang')] = $GLOBALS['egw_setup']->db->f('lang');
				}
				foreach($GLOBALS['egw_info']['setup']['installed_langs'] as $key => $value)
				{
					$sql = "SELECT lang_name FROM {$GLOBALS['egw_setup']->languages_table} WHERE lang_id = '".$value."'";
					$GLOBALS['egw_setup']->db->query($sql);
					if ($GLOBALS['egw_setup']->db->next_record())
					{
						$GLOBALS['egw_info']['setup']['installed_langs'][$value] = $GLOBALS['egw_setup']->db->f('lang_name');
					}
				}
				$GLOBALS['egw_info']['setup']['header_msg'] = 'Stage 3 (Completed)';
				return 10;
			}
		}

		/*
		@function check_app_tables
		@abstract	Verify that all of an app's tables exist in the db
		@param $appname
		@param $any		optional, set to True to see if any of the apps tables are installed
		*/
		function check_app_tables($appname,$any=False)
		{
			$none = 0;
			$setup_info = $GLOBALS['setup_info'];

			if(@$setup_info[$appname]['tables'])
			{
				/* Make a copy, else we send some callers into an infinite loop */
				$copy = $setup_info;
				$GLOBALS['egw_setup']->db->Halt_On_Error = 'no';
				$table_names = $GLOBALS['egw_setup']->db->table_names();
				$tables = Array();
				foreach($table_names as $key => $val)
				{
					$tables[] = $val['table_name'];
				}
				foreach($copy[$appname]['tables'] as $key => $val)
				{
					if($GLOBALS['DEBUG'])
					{
						echo '<br>check_app_tables(): Checking: ' . $appname . ',table: ' . $val;
					}
					if(!in_array($val,$tables))
					{
						if($GLOBALS['DEBUG'])
						{
							echo '<br>check_app_tables(): ' . $val . ' missing!';
						}
						if(!$any)
						{
							return False;
						}
						else
						{
							$none++;
						}
					}
					else
					{
						if($any)
						{
							if($GLOBALS['DEBUG'])
							{
								echo '<br>check_app_tables(): Some tables installed';
							}
							return True;
						}
					}
				}
			}
			if($none && $any)
			{
				if($GLOBALS['DEBUG'])
				{
					echo '<br>check_app_tables(): No tables installed';
				}
				return False;
			}
			else
			{
				if($GLOBALS['DEBUG'])
				{
					echo '<br>check_app_tables(): All tables installed';
				}
				return True;
			}
		}
	}
?>
