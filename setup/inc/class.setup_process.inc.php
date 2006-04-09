<?php
	/**************************************************************************\
	* eGroupWare - Setup                                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	* This file written by Miles Lott <milos@groupwhere.org>                   *
	*  Originally written for phpGroupWare.                                    *
	* (C) 2001-2004 Miles Lott                                                 *
	* Upgrade process rewritten by <RalfBecker@outdoor-training.de> to no      *
	* longer require tables_baseline files and delta-upgrades                  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	/* app status values:
	U	Upgrade required/available
	R	upgrade in pRogress
	C	upgrade Completed successfully
	D	Dependency failure
	F	upgrade Failed
	V	Version mismatch at end of upgrade (Not used, proposed only)
	M	Missing files at start of upgrade (Not used, proposed only)
	*/

	class setup_process
	{
		var $oProc;
		var $tables;
		var $updateincluded = array();
		var $translation;

		function setup_process()
		{
			$this->translation =& CreateObject('setup.setup_translation');
		}

		/**
		 * create schema_proc object
		 *
		 * @param none
		 */
		function init_process()
		{
			$GLOBALS['egw_setup']->oProc =& CreateObject('phpgwapi.schema_proc');
		}

		/**
		 * the mother of all multipass upgrade parental loop functions
		 *
		 * @param array $setup_info	array of application info from setup.inc.php files
		 * @param string $type='new' defaults to new(install), could also be 'upgrade'
		 * @param boolean $DEBUG=false print debugging info
		 * @param boolean $force_en=false install english language files
		 * @param string $system_charset=null charset to use	
		 */
		function pass($setup_info,$method='new',$DEBUG=False,$force_en=False)
		{
			if(!$method)
			{
				return False;
			}
			// Place api first
			$pass['phpgwapi'] = $setup_info['phpgwapi'];
			$pass['admin']    = $setup_info['admin'];
			$pass['preferences'] = $setup_info['preferences'];
			@reset($setup_info);
			$setup_info = $GLOBALS['egw_setup']->detection->get_versions($setup_info);
			@reset($setup_info);

			$i = 1;
			$passed = array();
			$passing = array();
			$pass_string = implode (':', $pass);
			$passing_string = implode (':', $passing);
			$do_langs = false;
			while($pass_string != $passing_string)
			{
				$passing = array();
				if($DEBUG) { echo '<br>process->pass(): #' . $i . ' for ' . $method . ' processing' . "\n"; }
				/* Check current versions and dependencies */
				$setup_info = $GLOBALS['egw_setup']->detection->get_db_versions($setup_info);
				$setup_info = $GLOBALS['egw_setup']->detection->compare_versions($setup_info);
				//_debug_array($setup_info);exit;
				$setup_info = $GLOBALS['egw_setup']->detection->check_depends($setup_info);
				//if($i==2) { _debug_array($passed);exit; }

				/* stuff the rest of the apps, but only those with available upgrades */
				while(list($key,$value) = @each($setup_info))
				{
					if (isset($value['only_db']) && (
						is_array($value['only_db']) && !in_array($GLOBALS['egw_setup']->db->Type,$value['only_db']) ||
						!is_array($value['only_db']) && $GLOBALS['egw_setup']->db->Type != $value['only_db']))
					{
						continue;	// app does not support this db-type, dont try installing it
					}
					if(($value['name'] != 'phpgwapi') && ($value['status'] == 'U'))
					{
						if(($passed[$value['name']]['status'] != 'F') && ($passed[$value['name']]['status'] != 'C'))
						{
							$pass[$value['name']] = $setup_info[$value['name']];
						}
					}
					/*
					Now if we are on the 2nd or more passes, add api in
					if (!$pass['phpgwapi'])
					{
						$pass['phpgwapi'] = $setup_info['phpgwapi'];
					}
					*/
				}

				switch($method)
				{
					case 'new':
						/* Create tables and insert new records for each app in this list */
						$passing = $this->current($pass,$DEBUG);
						$this->save_minimal_config();
						$passing = $this->default_records($passing,$DEBUG);
						$do_langs = true;	// just do it once at the end of all passes
						break;
					case 'upgrade':
						/* Run upgrade scripts on each app in the list */
						$passing = $this->upgrade($pass,$DEBUG);
						$do_langs = true;	// just do it once at the end of all passes
						//_debug_array($pass);exit;
						break;
					default:
						/* What the heck are you doing? */
						return False;
						break;
				}

				$pass = array();
				@reset($passing);
				while(list($key,$value) = @each($passing))
				{
					if($value['status'] == 'C')
					{
						$passed[$value['name']] = $passing[$value['name']];
						if($DEBUG) { echo '<br>process->pass(): '.$passed[$value['name']]['name'] . ' install completed'."\n"; }
					}
					elseif($value['status'] == 'F')
					{
						$setup_info[$value['name']] = $passing[$value['name']];
						if($DEBUG) { echo '<br>process->pass(): '.$setup_info[$value['name']]['name'] . ' install failed'."\n"; }
					}
					elseif($value['status'] == 'D')
					{
						$pass[$value['name']] = $setup_info[$value['name']];
						if($DEBUG) { echo '<br>process->pass(): '.$pass[$value['name']]['name'] . ' fails dependency check on this pass'."\n"; }
					}
					else
					{
						$tmp = $passing[$value['name']]['name'];
						if($DEBUG) { echo '<br>process->pass(): '.$tmp . ' skipped on this pass'."\n"; }
					}
				}

				$i++;
				if($i == 20) /* Then oops it broke */
				{
					echo '<br>Setup failure: excess looping in process->pass():'."\n";
					echo '<br>Pass:<br>'."\n";
					_debug_array($pass);
					echo '<br>Passed:<br>'."\n";
					_debug_array($passed);
					exit;
				}
				$pass_string = implode (':', $pass);
				$passing_string = implode (':', $passing);
			}
			if ($do_langs)	// just do it once at the end of all passes
			{
				$langs = false;
				if ($method == 'new')
				{
					$langs[] = ($own_lang = get_var('ConfigLang',Array('POST','COOKIE')));
					if ($own_lang != 'en') $langs[] = 'en';
				}
				$this->translation->drop_add_all_langs($langs);
			}
			/* now return the list */
			return $setup_info = array_merge($setup_info,$passed);
		}

		/**
		 * saves a minimal default config, so you get a running install without entering and saveing Step #2 config
		 *
		 */
		function save_minimal_config()
		{
			$is_windows = strtoupper(substr(PHP_OS,0,3)) == 'WIN';
		
			$GLOBALS['current_config']['site_title'] = 'eGroupWare';
			$GLOBALS['current_config']['hostname']  = $_SERVER['HTTP_HOST'];

			// guessing the phpGW url
			$parts = explode('/',$_SERVER['PHP_SELF']);
			array_pop($parts);	// remove config.php
			array_pop($parts);	// remove setup
			$GLOBALS['current_config']['webserver_url'] = implode('/',$parts);
			$egroupwareDirName = end($parts);

			if(!$is_windows) {
				if(@is_dir('/tmp')) {
					$GLOBALS['current_config']['temp_dir'] = '/tmp';
				} else {
					$GLOBALS['current_config']['temp_dir'] = '/path/to/temp/dir';
				}
				$GLOBALS['current_config']['files_dir'] = '/var/lib/'.$egroupwareDirName.'/'.$GLOBALS['egw_setup']->ConfigDomain.'/files';
				$GLOBALS['current_config']['backup_dir'] = '/var/lib/'.$egroupwareDirName.'/'.$GLOBALS['egw_setup']->ConfigDomain.'/backup';
			} else {
				if(@is_dir('c:\\windows\\temp')) {
					$GLOBALS['current_config']['temp_dir'] = 'c:\\windows\\temp';
				} else {
					$GLOBALS['current_config']['temp_dir'] = 'c:\\path\\to\\temp\\dir';
				}
				$GLOBALS['current_config']['files_dir'] = 'c:\\Program files\\'.$egroupwareDirName.'\\'.$GLOBALS['egw_setup']->ConfigDomain.'\\files';
				$GLOBALS['current_config']['backup_dir'] = 'c:\\Program files\\'.$egroupwareDirName.'\\'.$GLOBALS['egw_setup']->ConfigDomain.'\\backup';
			} 
			$datetime =& CreateObject('phpgwapi.datetime');
			$GLOBALS['current_config']['tz_offset'] = $datetime->getbestguess();
			unset($datetime);

			// RalfBecker: php.net recommend this for security reasons, it should be our default too
			$GLOBALS['current_config']['usecookies'] = 'True';
			
			if ($GLOBALS['egw_setup']->system_charset)
			{
				$GLOBALS['current_config']['system_charset'] = $GLOBALS['egw_setup']->system_charset;
			}

			foreach($GLOBALS['current_config'] as $name => $value)
			{
				$GLOBALS['egw_setup']->db->insert($GLOBALS['egw_setup']->config_table,array(
					'config_value' => $value,
				),array(
					'config_app' => 'phpgwapi',
					'config_name' => $name,
				),__FILE__,__LINE__);
			}
		}

		/**
		 * drop tables per application, check that they are in the db first
		 *
		 * @param $setup_info	array of application info from setup.inc.php files, etc.
		 */
		function droptables($setup_info,$DEBUG=False)
		{
			if(!@$GLOBALS['egw_setup']->oProc)
			{
				$this->init_process();
			}
			/* The following is built so below we won't try to drop a table that isn't there. */
			$tablenames = $GLOBALS['egw_setup']->db->table_names();
			if (!is_array($setup_info) || !is_array($tablenames))
			{
				return $setup_info;	// nothing to do
			}
			$tables = array();
			foreach($tablenames as $data)
			{
				$tables[] = $data['table_name'];
			}

			if (!is_array($setup_info))
			{
				return $setup_info;
			}
			foreach($setup_info as $app_name => $data)
			{
				if(is_array($data['tables']))
				{
					foreach($data['tables'] as $table)
					{
						//echo $table;
						if(in_array($table,$tables))
						{
							if($DEBUG){ echo '<br>process->droptables(): Dropping :'. $app_name . ' table: ' . $table; }
							$GLOBALS['egw_setup']->oProc->DropTable($table);
							// Update the array values for return below
							$setup_info[$app_name]['status'] = 'U';
						}
					}
				}
			}

			/* Done, return current status */
			return $setup_info;
		}

		/**
		 * process current table setup in each application/setup dir
		 *
		 * @param array $setup_info	array of application info from setup.inc.php files, etc.
		 * @param boolean $DEBUG=false
		 */
		function current($setup_info,$DEBUG=False)
		{
			if(!@$GLOBALS['egw_setup']->oProc)
			{
				$this->init_process();
			}
			@reset($setup_info);
			while(list($key,$null) = @each($setup_info))
			{
				$enabled = False;
				$appname  = $setup_info[$key]['name'];
				$apptitle = $setup_info[$key]['title'];

				if($DEBUG) { echo '<br>process->current(): Incoming status: ' . $appname . ',status: '. $setup_info[$key]['status']; }

				$appdir  = EGW_SERVER_ROOT . SEP . $appname . SEP . 'setup' . SEP;

				if($setup_info[$key]['tables'] && file_exists($appdir.'tables_current.inc.php'))
				{
					if($DEBUG) { echo '<br>process->current(): Including: ' . $appdir.'tables_current.inc.php'; }
					include ($appdir.'tables_current.inc.php');
					$ret = $this->post_process($phpgw_baseline,$DEBUG);
					if($ret)
					{
						if($GLOBALS['egw_setup']->app_registered($appname))
						{
							$GLOBALS['egw_setup']->update_app($appname);
							$GLOBALS['egw_setup']->update_hooks($appname);
						}
						else
						{
							$GLOBALS['egw_setup']->register_app($appname);
							$GLOBALS['egw_setup']->register_hooks($appname);
						}
						// Update the array values for return below
						$setup_info[$key]['status'] = 'C';
					}
					else
					{
						/* script processing failed */
						if($DEBUG) { echo '<br>process->current(): Failed for ' . $appname . ',status: '. $setup_info[$key]['status']; }
						$setup_info[$key]['status'] = 'F';
					}
				}
				else
				{
					if($DEBUG) { echo '<br>process->current(): No current tables for ' . $apptitle . "\n"; }
					/*
					 Add the app, but disable it if it has tables defined.
					 A manual sql script install is needed, but we do add the hooks
					*/
					$enabled = 99;
					if($setup_info[$key]['tables'][0] != '')
					{
						$enabled = False;
					}
					if($GLOBALS['egw_setup']->app_registered($appname))
					{
						$GLOBALS['egw_setup']->update_app($appname);
						$GLOBALS['egw_setup']->update_hooks($appname);
					}
					else
					{
						$GLOBALS['egw_setup']->register_app($appname,$enabled);
						$GLOBALS['egw_setup']->register_hooks($appname);
					}
					$setup_info[$key]['status'] = 'C';
				}
				if($DEBUG) { echo '<br>process->current(): Outgoing status: ' . $appname . ',status: '. $setup_info[$key]['status']; }
			}

			/* Done, return current status */
			return ($setup_info);
		}

		/**
		 * process default_records.inc.php in each application/setup dir
		 *
		 * @param $setup_info	array of application info from setup.inc.php files, etc.
		 */
		function default_records($setup_info,$DEBUG=False)
		{
			if(!@$GLOBALS['egw_setup']->oProc)
			{
				$this->init_process();
			}
			@reset($setup_info);
			while(list($key,$null) = @each($setup_info))
			{
				$appname = $setup_info[$key]['name'];
				$appdir  = EGW_SERVER_ROOT . SEP . $appname . SEP . 'setup' . SEP;

				if($setup_info[$key]['tables'] && file_exists($appdir.'default_records.inc.php'))
				{
					if($DEBUG)
					{
						echo '<br>process->default_records(): Including default records for ' . $appname . "\n";
					}
					$GLOBALS['egw_setup']->oProc->m_odb->transaction_begin();
					$oProc = &$GLOBALS['egw_setup']->oProc;	// to be compatible with old apps
					include ($appdir.'default_records.inc.php');
					$GLOBALS['egw_setup']->oProc->m_odb->transaction_commit();
				}
				/* $setup_info[$key]['status'] = 'C'; */
			}

			/* Done, return current status */
			return ($setup_info);
		}

		/**
		 * process test_data.inc.php in each application/setup dir for developer tests
		 *
		 * This data should work with the baseline tables
		 * @param $setup_info	array of application info from setup.inc.php files, etc.
		 */
		function test_data($setup_info,$DEBUG=False)
		{
			if(!@$GLOBALS['egw_setup']->oProc)
			{
				$this->init_process();
			}
			@reset($setup_info);
			while(list($key,$null) = @each($setup_info))
			{
				$appname = $setup_info[$key]['name'];
				$appdir  = EGW_SERVER_ROOT . SEP . $appname . SEP . 'setup' . SEP;

				if(file_exists($appdir.'test_data.inc.php'))
				{
					if($DEBUG)
					{
						echo '<br>process->test_data(): Including baseline test data for ' . $appname . "\n";
					}
					$GLOBALS['egw_setup']->oProc->m_odb->transaction_begin();
					include ($appdir.'test_data.inc.php');
					$GLOBALS['egw_setup']->oProc->m_odb->transaction_commit();
				}
			}

			/* Done, return current status */
			return ($setup_info);
		}

		/**
		 * process baseline table setup in each application/setup dir
		 *
		 * @param $appinfo	array of application info from setup.inc.php files, etc.
		 */
		function baseline($setup_info,$DEBUG=False)
		{
			if(!@$GLOBALS['egw_setup']->oProc)
			{
				$this->init_process();
			}

			@reset($setup_info);
			while(list($key,$null) = @each($setup_info))
			{
				$appname = $setup_info[$key]['name'];
				$appdir  = EGW_SERVER_ROOT . SEP . $appname . SEP . 'setup' . SEP;

				if(file_exists($appdir.'tables_baseline.inc.php'))
				{
					if($DEBUG)
					{
						echo '<br>process->baseline(): Including baseline tables for ' . $appname . "\n";
					}
					include ($appdir.'tables_baseline.inc.php');
					$GLOBALS['egw_setup']->oProc->GenerateScripts($phpgw_baseline, $DEBUG);
					$this->post_process($phpgw_baseline,$DEBUG);

					/* Update the array values for return below */
					/* $setup_info[$key]['status'] = 'R'; */
				}
				else
				{
					if($DEBUG)
					{
						echo '<br>process->baseline(): No baseline tables for ' . $appname . "\n";
					}
					//$setup_info[$key]['status'] = 'C';
				}
			}

			/* Done, return current status */
			return ($setup_info);
		}

		/**
		 * process available upgrades in each application/setup dir
		 *
		 * @param $appinfo	array of application info from setup.inc.php files, etc.
		 */
		function upgrade($setup_info,$DEBUG=False)
		{
			if(!@$GLOBALS['egw_setup']->oProc)
			{
				$this->init_process();
			}
			$GLOBALS['egw_setup']->oProc->m_odb->HaltOnError = 'yes';

			foreach($setup_info as $key => $appdata)
			{
				$appname = $appdata['name'];
				/* Don't try to upgrade an app that is not installed */
				if(!$GLOBALS['egw_setup']->app_registered($appname))
				{
					if($DEBUG)
					{
						echo "<p>process->upgrade(): Application not installed: $appname</p>\n";
					}
					unset($setup_info[$appname]);
					continue;
				}

				/* if upgrade required, or if we are running again after an upgrade or dependency failure */
				if($DEBUG) 
				{ 
					echo '<div style="text-align: left; border: thin dashed black; margin-top: 5px;">'."process->upgrade(): Incoming : appname: $appname, version: $appdata[currentver], status: $appdata[status]\n"; 
				}
				if($appdata['status'] == 'U' || $appdata['status'] == 'D' ||$appdata['status'] == 'V' || $appdata['status'] == '') // TODO this is not getting set for api upgrade, sometimes ???
				{
					$currentver = $appdata['currentver'];
					$targetver  = $appdata['version'];	// The version we need to match when done
					$appdir     = EGW_SERVER_ROOT . SEP . $appname . SEP . 'setup' . SEP;

					if(file_exists($appdir . 'tables_update.inc.php') && !@$this->updateincluded[$appname])
					{
						include ($appdir . 'tables_update.inc.php');
						$this->updateincluded[$appname] = True;

						while ($currentver && $currentver != $targetver && 
							function_exists($function = $appname . '_upgrade' . str_replace('.','_',$currentver)))
						{
							if($DEBUG)
							{
								echo "<br>process->upgrade(): $appname($currentver --> $targetver): running $function()\n";
							}
							if (!($currentver = $function()))
							{
								if($DEBUG)
								{
									echo "<b>failed!!!</b>\n";
								}
								$appstatus  = 'F';
							}
							else
							{
								if($DEBUG)
								{
									echo "--> $currentver\n";
								}
							}
						}
						if ($currentver == $targetver)	// upgrades succesful
						{	
							if($DEBUG)
							{
								echo "<br>process->upgrade(): Upgrade of $appname to $targetver is completed.\n";
							}
							$appstatus = 'C';
						}
						elseif ($currentver)
						{
							if($DEBUG)
							{
								echo "<br><b>process->upgrade(): No table upgrade available for appname: $appname, version: $currentver</b>\n";
							}
							$setup_info[$key]['currentver'] = $targetver;
							$appstatus  = 'F';
						}
					}
					else
					{
						if($DEBUG)
						{
							echo "<br>process->upgrade(): No table upgrade required/availible for $appname\n";
						}
						$appstatus  = 'C';
					}
					if ($appstatus == 'C')	// update successful completed
					{
						$setup_info[$key]['currentver'] = $targetver;

						if($GLOBALS['egw_setup']->app_registered($appname))
						{
							$GLOBALS['egw_setup']->update_app($appname);
							$GLOBALS['egw_setup']->update_hooks($appname);
						}
						else
						{
							$GLOBALS['egw_setup']->register_app($appname);
							$GLOBALS['egw_setup']->register_hooks($appname);
						}
					}
					
				}
				else
				{
					if($DEBUG)
					{
						echo "<br>process->upgrade(): No upgrade required for $appname\n";
					}
					$appstatus  = 'C';
				}
				/* Done with this app, update status */
				if($DEBUG)
				{
					echo "<br>process->upgrade(): Outgoing : appname: $appname, status: $appstatus</div>\n";
				}
				$setup_info[$key]['status'] = $appstatus;
			}

			/* Done, return current status */
			return ($setup_info);
		}

		/**
		 * commit above processing to the db
		 *
		 */
		function post_process($tables,$DEBUG=False)
		{
			if(!$tables)
			{
				return False;
			}

			return $GLOBALS['egw_setup']->oProc->ExecuteScripts($tables,$DEBUG);
		}

		/**
		 * send this a table name, returns printable column spec and keys for the table from schema_proc
		 *
		 * @param	$tablename	table whose array you want to see
		 */
		function sql_to_array($tablename='')
		{
			if(!$tablename)
			{
				return False;
			}

			if(!$GLOBALS['egw_setup']->oProc)
			{
				$this->init_process();
			}

			$GLOBALS['egw_setup']->oProc->m_oTranslator->_GetColumns($GLOBALS['egw_setup']->oProc, $tablename, $sColumns, $sColumnName);

			while(list($key,$tbldata) = each($GLOBALS['egw_setup']->oProc->m_oTranslator->sCol))
			{
				$arr .= $tbldata;
			}
			$pk = $GLOBALS['egw_setup']->oProc->m_oTranslator->pk;
			$fk = $GLOBALS['egw_setup']->oProc->m_oTranslator->fk;
			$ix = $GLOBALS['egw_setup']->oProc->m_oTranslator->ix;
			$uc = $GLOBALS['egw_setup']->oProc->m_oTranslator->uc;

			return array($arr,$pk,$fk,$ix,$uc);
		}
	}
?>
