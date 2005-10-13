<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$GLOBALS['egw_info'] = array(
		'flags' => array(
			'nocachecontrol' => True,
			'noheader' => True,
			'nonavbar' => True,
			'currentapp' => 'home',
			'noapi' => True
	));
	include('./inc/functions.inc.php');

	// some constanst for pre php4.3
	if (!defined('PHP_SHLIB_SUFFIX'))
	{
		define('PHP_SHLIB_SUFFIX',strtoupper(substr(PHP_OS, 0,3)) == 'WIN' ? 'dll' : 'so');
	}
	if (!defined('PHP_SHLIB_PREFIX'))
	{
		define('PHP_SHLIB_PREFIX',PHP_SHLIB_SUFFIX == 'dll' ? 'php_' : '');
	}
	/**
	 * checks if a named extension is loaded or loadable
	 */
	function check_load_extension($extension)
	{
		return extension_loaded($extension) ||
			function_exists('dl') && @dl(PHP_SHLIB_PREFIX.$extension.'.'.PHP_SHLIB_SUFFIX);
	}

	$GLOBALS['egw_info']['server']['versions']['current_header'] = $setup_info['phpgwapi']['versions']['current_header'];
	$GLOBALS['egw_info']['server']['versions']['phpgwapi'] = $setup_info['phpgwapi']['version'];
	unset($setup_info);

	/* Fetch the current real path.
	 * If this is in the server document root, then it is probably ok.
	 * Otherwise, don't guess, just show the usual instructive default.
	 */
	$realpath = realpath('..');
	if(!ereg('^' . $_SERVER['DOCUMENT_ROOT'],$realpath))
	{
		if(strtoupper(substr(PHP_OS, 0,3)) == 'WIN')
		{
			$realpath = 'Drive:/Path/to/egroupware';
		}
		else
		{
			$realpath = '/path/to/egroupware';
		}
	}

	$adddomain = get_var('adddomain',Array('POST'));

	$db_fullnames = array(
		'pgsql'  => 'PostgreSQL',
		'mysql'  => 'MySQL',
		'mysqli' => 'MySQLi (php5)',
		'mssql'  => 'MS SQL Server',
		'odbc_mssql'  => 'MS SQL Server via ODBC',
		'oracle' => 'Oracle',
		'odbc_oracle' => 'Oracle via ODBC',
		'sapdb'  => 'SAP/Max DB via ODBC',
	);

	$default_db_ports = array(
		'pgsql'  => 5432,
		'mysql'  => 3306,
		'mysqli' => 3306,
		'mssql'  => 1433,
		'odbc_mssql'  => '',
		'oracle' => 1521,
		'odbc_oracle' => '',
		'sapdb'  => '',
	);

	function check_form_values()
	{
		// PHP will automatically replace any dots in incoming
		// variable names with underscores.

		$errors = '';
		$domains = get_var('domains',Array('POST'));
		@reset($domains);
		while(list($k,$v) = @each($domains))
		{
			$variableName = str_replace('.','_',$k);
			$deletedomain = get_var('deletedomain',Array('POST'));
			if(isset($deletedomain[$variableName]))
			{
				continue;
			}
			$dom = get_var('setting_'.$variableName,Array('POST'));
			if(!$dom['config_pass'] && !$dom['config_password'])
			{
				$errors .= '<br />' . lang("You didn't enter a config password for domain %1",$v);
			}
			if(!$dom['config_user'])
			{
				$errors .= '<br />' . lang("You didn't enter a config username for domain %1",$v);
			}
		}

		$setting = get_var('setting',Array('POST'));
		if(!$setting['HEADER_ADMIN_PASSWORD'] && !$setting['HEADER_ADMIN_PASS'])
		{
			$errors .= '<br />' . lang("You didn't enter a header admin password");
		}
		if(!$setting['HEADER_ADMIN_USER'])
		{
			$errors .= '<br />' . lang("You didn't enter a header admin username");
		}

		if($errors)
		{
			$GLOBALS['egw_setup']->html->show_header('Error',True);
			echo $errors;
			echo '<p><input type="submit" value="'.lang('Back to the previous screen').'" onclick="history.back()" /></p>';
			$GLOBALS['egw_setup']->html->show_footer();
			exit;
		}
	}

	/* authentication phase */
	$GLOBALS['egw_info']['setup']['stage']['header'] = $GLOBALS['egw_setup']->detection->check_header();

	// added these to let the app work, need to templatize still
	$tpl_root = $GLOBALS['egw_setup']->html->setup_tpl_dir('setup');
	$setup_tpl = CreateObject('setup.Template',$tpl_root);
	$setup_tpl->set_file(array(
		'T_head' => 'head.tpl',
		'T_footer' => 'footer.tpl',
		'T_alert_msg' => 'msg_alert_msg.tpl',
		'T_login_main' => 'login_main.tpl',
		'T_login_stage_header' => 'login_stage_header.tpl',
		'T_setup_manage' => 'manageheader.tpl'
	));
	$setup_tpl->set_block('T_login_stage_header','B_multi_domain','V_multi_domain');
	$setup_tpl->set_block('T_login_stage_header','B_single_domain','V_single_domain');
	$setup_tpl->set_block('T_setup_manage','manageheader','manageheader');
	$setup_tpl->set_block('T_setup_manage','domain','domain');

	/* Detect current mode */
	switch($GLOBALS['egw_info']['setup']['stage']['header'])
	{
		case '1':
			$GLOBALS['egw_info']['setup']['HeaderFormMSG'] = lang('Create your header.inc.php');
			$GLOBALS['egw_info']['setup']['PageMSG'] = lang('You have not created your header.inc.php yet!<br /> You can create it now.');
			break;
		case '2':
			$GLOBALS['egw_info']['setup']['HeaderFormMSG'] = lang('Your header admin password is NOT set. Please set it now!');
			$GLOBALS['egw_info']['setup']['PageMSG'] = lang('Your header admin password is NOT set. Please set it now!');
			break;
		case '3':
			$GLOBALS['egw_info']['setup']['HeaderFormMSG'] = lang('You need to add some domains to your header.inc.php.');
			$GLOBALS['egw_info']['setup']['PageMSG'] = lang('You need to add some domains to your header.inc.php.');
			$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = lang('You need to add some domains to your header.inc.php.');
			if(!$GLOBALS['egw_setup']->auth('Header'))
			{
				$GLOBALS['egw_setup']->html->show_header('Please login',True);
				$GLOBALS['egw_setup']->html->login_form();
				$GLOBALS['egw_setup']->html->show_footer();
				exit;
			}
			break;
		case '4':
			$GLOBALS['egw_info']['setup']['HeaderFormMSG'] = lang('Your header.inc.php needs upgrading.');
			$GLOBALS['egw_info']['setup']['PageMSG'] = lang('Your header.inc.php needs upgrading.<br /><blink><b class="msg">WARNING!</b></blink><br /><b>MAKE BACKUPS!</b>');
			$GLOBALS['egw_info']['setup']['HeaderLoginMSG'] = lang('Your header.inc.php needs upgrading.');
			if(!$GLOBALS['egw_setup']->auth('Header'))
			{
				$GLOBALS['egw_setup']->html->show_header('Please login',True);
				$GLOBALS['egw_setup']->html->login_form();
				$GLOBALS['egw_setup']->html->show_footer();
				exit;
			}
			break;
		case '10':
			if(!$GLOBALS['egw_setup']->auth('Header'))
			{
				$GLOBALS['egw_setup']->html->show_header('Please login',True);
				$GLOBALS['egw_setup']->html->login_form();
				$GLOBALS['egw_setup']->html->show_footer();
				exit;
			}
			$GLOBALS['egw_info']['setup']['HeaderFormMSG'] = lang('Edit your header.inc.php');
			$GLOBALS['egw_info']['setup']['PageMSG'] = lang('Edit your existing header.inc.php');
			break;
	}

	$action = @get_var('action',Array('POST'));
	list($action) = @each($action);
	switch($action)
	{
		case 'download':
			check_form_values();
			$header_template = CreateObject('setup.Template','../');
			$b = CreateObject('phpgwapi.browser');
			$b->content_header('header.inc.php','application/octet-stream');
			/*
			header('Content-disposition: attachment; filename="header.inc.php"');
			header('Content-type: application/octet-stream');
			header('Pragma: no-cache');
			header('Expires: 0');
			*/
			$newheader = $GLOBALS['egw_setup']->html->generate_header();
			echo $newheader;
			break;
		case 'view':
			check_form_values();
			$header_template = CreateObject('setup.Template','../');
			$GLOBALS['egw_setup']->html->show_header('Generated header.inc.php', False, 'header');
			echo '<table width="90%"><tr><td>';
			echo '<br />' . lang('Save this text as contents of your header.inc.php') . '<br /><hr />';
			$newheader = $GLOBALS['egw_setup']->html->generate_header();
			echo '<pre>';
			echo htmlentities($newheader);
			echo '</pre><hr />';
			echo '<form action="index.php" method="post">';
			echo '<br />' . lang('After retrieving the file, put it into place as the header.inc.php.  Then, click "continue".') . '<br />';
			echo '<input type="hidden" name="FormLogout" value="header" />';
			echo '<input type="submit" name="junk" value="'.lang('Continue').'" />';
			echo '</form>';
			echo '</td></tr></table>';
			$GLOBALS['egw_setup']->html->show_footer();
			break;
		case 'write':
			check_form_values();
			$header_template = CreateObject('setup.Template','../');
			if(is_writeable('../header.inc.php') || (!file_exists('../header.inc.php') && is_writeable('../')))
			{
				$newheader = $GLOBALS['egw_setup']->html->generate_header();
				$fsetup = fopen('../header.inc.php','wb');
				fwrite($fsetup,$newheader);
				fclose($fsetup);
				$GLOBALS['egw_setup']->html->show_header('Saved header.inc.php', False, 'header');
				echo '<form action="index.php" method="post">';
 				echo '<br />' . lang('Created header.inc.php!');
				echo '<input type="hidden" name="FormLogout" value="header" />';
				echo '<input type="submit" name="junk" value="'.lang('Continue').'" />';
				echo '</form>';
				$GLOBALS['egw_setup']->html->show_footer();
				break;
			}
			else
			{
				$GLOBALS['egw_setup']->html->show_header('Error generating header.inc.php', False, 'header');
				echo lang('Could not open header.inc.php for writing!') . '<br />' . "\n";
				echo lang('Please check read/write permissions on directories, or back up and use another option.') . '<br />';
				$GLOBALS['egw_setup']->html->show_footer();
			}
			break;
		default:
			$GLOBALS['egw_setup']->html->show_header($GLOBALS['egw_info']['setup']['HeaderFormMSG'], False, 'header');

			if(!get_var('ConfigLang',array('POST','COOKIE')))
			{
				$setup_tpl->set_var('lang_select','<tr><td colspan="2"><form action="manageheader.php" method="post">Please Select your language '.lang_select(True,'en')."</form></td></tr>");
			}

			$detected = '';
			$detected .= '<tr><td colspan="2"><p>' . $GLOBALS['egw_info']['setup']['PageMSG'] . '<br />&nbsp;</p></td></tr>';
			
			$detected .= '<tr class="th"><td colspan="2">' . lang('Analysis') . '</td></tr>'."\n".'<tr><td colspan="2">'. "\n";

			$supported_db = array();
			foreach(array(
				// short => array(extension,func_to_check,supported_db(s))
				'mysql'  => array('mysql','mysql_connect','mysql'),
				'mysqli' => array('mysql','mysql_iconnect','mysqli'),
				'pgsql'  => array('pgsql','pg_connect','pgsql'),
				'mssql'  => array('mssql','mssql_connect','mssql'),
				'odbc'   => array('odbc',false,'sapdb','odbc_mssql','odbc_oracle'),
				'oracle' => array('oci8',false,'oracle'),
			) as $db => $data)
			{
				$ext = array_shift($data);
				$func_to_check = array_shift($data);
				$name = isset($db_fullnames[$db]) ? $db_fullnames[$db] : strtoupper($db);
				if (check_load_extension($ext) || $func_to_check && function_exists($func_to_check))
				{
					$detected .= lang('You appear to have %1 support.',$name) . "<br />\n";
					$supported_db = array_merge($supported_db,$data);
				}
				else
				{
					$detected .= lang('No %1 support found. Disabling',$name) . "<br />\n";
				}
			}
			if(!count($supported_db))
			{
				$detected .= '<b><p align="center" class="msg">'
					. lang('Did not find any valid DB support!')
					. "<br />\n"
					. lang('Try to configure your php to support one of the above mentioned DBMS, or install eGroupWare by hand.')
					. '</p></b>';
				echo $detected;
				$GLOBALS['egw_setup']->html->show_footer();
				exit;
			}

			if(!function_exists('version_compare'))
			{
				$detected .= '<b><p align="center" class="msg">'
					. lang('You appear to be using PHP earlier than 4.1.0. eGroupWare now requires 4.1.0 or later'). "\n"
					. '</p></b>';
				echo $detected;
				$GLOBALS['egw_setup']->html->show_footer();
				exit;
			}
			if (check_load_extension('session'))
			{
				$detected .= lang('You appear to have PHP session support. Enabling PHP sessions.') . '<br />' . "\n";
				$supported_sessions_type['php4'] = 'PHP';			// makeing php sessions the default
				$supported_sessions_type['php4-restore'] = lang('PHP plus restore');	// php-sessions with restore of egw_info array and egw object from the session
			}
			$supported_sessions_type['db'] = lang('Datebase');

			@reset($default_db_ports);
			$js_default_db_ports = 'var default_db_ports = new Array();'."\n";
			while(list($k,$v) = @each($default_db_ports))
			{
				$js_default_db_ports .= '  default_db_ports["'.$k.'"]="'.$v.'";'."\n";
			}
			$setup_tpl->set_var('js_default_db_ports',$js_default_db_ports);

			/*
			if(check_load_extension('xml') || function_exists('xml_parser_create'))
			{
				$detected .= lang('You appear to have XML support enabled') . '<br />' . "\n";
				$xml_enabled = 'True';
			}
			else
			{
				$detected .= lang('No XML support found. Disabling') . '<br />' . "\n";
			}
			*/

			$no_guess = False;
			if(file_exists('../header.inc.php') && is_file('../header.inc.php') && is_readable('../header.inc.php'))
			{
				$detected .= lang('Found existing configuration file. Loading settings from the file...') . '<br />' . "\n";
				$GLOBALS['egw_info']['flags']['noapi'] = True;
				$no_guess = true;
				/* This code makes sure the newer multi-domain supporting header.inc.php is being used */
				if(!isset($GLOBALS['egw_domain']))
				{
					$detected .= lang('You need to add some domains to your header.inc.php.') . '<br />' . "\n";
					$GLOBALS['egw_domain']['default'] = array();
					$setup_tpl->set_var('lang_domain',lang('Domain'));
					$setup_tpl->set_var('lang_delete',lang('Delete'));
					$setup_tpl->set_var('db_domain','default');
					$setup_tpl->set_var('db_host','localhost');
					$setup_tpl->set_var('db_name','egroupware');
					$setup_tpl->set_var('db_user','egroupware');
					$setup_tpl->set_var('db_pass','');
					$setup_tpl->set_var('config_user','changeme');
					$setup_tpl->set_var('config_pass','');
					while(list($k,$v) = @each($supported_db))
					{
						$dbtype_options .= '<option value="' . $v . '">' . $db_fullnames[$v] . "</option>\n";
						if (!isset($default_port))
							$default_port = $default_db_ports[$v];
					}
					$setup_tpl->set_var('dbtype_options',$dbtype_options);
					$setup_tpl->set_var('db_port',$default_port);
					$setup_tpl->parse('domains','domain',True);
				}
				else
				{
					if(@$GLOBALS['egw_info']['server']['header_version'] != @$GLOBALS['egw_info']['server']['current_header_version'])
					{
						$detected .= lang("You're using an old header.inc.php version...") . '<br />' . "\n";
						$detected .= lang('Importing old settings into the new format....') . '<br />' . "\n";
					}
					reset($GLOBALS['egw_domain']);
					$default_domain = each($GLOBALS['egw_domain']);
					$GLOBALS['egw_info']['server']['default_domain'] = $default_domain[0];
					unset($default_domain); // we kill this for security reasons
					$GLOBALS['egw_info']['server']['config_passwd'] = $GLOBALS['egw_domain'][$GLOBALS['egw_info']['server']['default_domain']]['config_passwd'];
					$GLOBALS['egw_info']['server']['config_user'] = $GLOBALS['egw_domain'][$GLOBALS['egw_info']['server']['default_domain']]['config_user'];

					if(@$adddomain)
					{
						$GLOBALS['egw_domain'][lang('new')] = array();
					}

					reset($GLOBALS['egw_domain']);
					while(list($key,$val) = each($GLOBALS['egw_domain']))
					{
						$setup_tpl->set_var('lang_domain',lang('Domain'));
						$setup_tpl->set_var('lang_delete',lang('Delete'));
						$setup_tpl->set_var('db_domain',$key);
						$setup_tpl->set_var('db_host',$GLOBALS['egw_domain'][$key]['db_host']);
						/* Set default here if the admin didn't set a port yet */
						$setup_tpl->set_var('db_port',$GLOBALS['egw_domain'][$key]['db_port']
							? $GLOBALS['egw_domain'][$key]['db_port']
							: @$default_db_ports[$GLOBALS['egw_domain'][$key]['db_type']]
						);
						$setup_tpl->set_var('db_name',$GLOBALS['egw_domain'][$key]['db_name']);
						$setup_tpl->set_var('db_user',$GLOBALS['egw_domain'][$key]['db_user']);
						$setup_tpl->set_var('db_pass',$GLOBALS['egw_domain'][$key]['db_pass']);
						$setup_tpl->set_var('db_type',$GLOBALS['egw_domain'][$key]['db_type']);
						if(!@isset($GLOBALS['egw_domain'][$key]['config_user']))
						{
							$setup_tpl->set_var('config_user','admin');
						}
						else
						{
							$setup_tpl->set_var('config_user',$GLOBALS['egw_domain'][$key]['config_user']);
						}
						$setup_tpl->set_var('config_pass','');
						$setup_tpl->set_var('config_password',$GLOBALS['egw_domain'][$key]['config_passwd']);

						$selected = '';
						$dbtype_options = '';
						$found_dbtype = False;
						@reset($supported_db);
						while(list($k,$v) = @each($supported_db))
						{
							if($v == $GLOBALS['egw_domain'][$key]['db_type'])
							{
								$selected = ' selected="selected" ';
								$found_dbtype = true;
							}
							else
							{
								$selected = '';
							}
							$dbtype_options .= '<option ' . $selected . 'value="' . $v . '">' . $db_fullnames[$v] . "</option>\n";
						}
						$setup_tpl->set_var('dbtype_options',$dbtype_options);

						$setup_tpl->parse('domains','domain',True);
					}
					$setup_tpl->set_var('domain','');
				}
				if(defined('PHPGW_SERVER_ROOT'))
				{
					$GLOBALS['egw_info']['server']['server_root']  = (PHPGW_SERVER_ROOT  == '..') ? $realpath : PHPGW_SERVER_ROOT;
					$GLOBALS['egw_info']['server']['include_root'] = (PHPGW_INCLUDE_ROOT == '..') ? $realpath : PHPGW_SERVER_ROOT;
				}
				elseif(!@isset($GLOBALS['egw_info']['server']['include_root']) && @$GLOBALS['egw_info']['server']['header_version'] <= 1.6)
				{
					$GLOBALS['egw_info']['server']['include_root'] = @$GLOBALS['egw_info']['server']['server_root'];
				}
				elseif(!@isset($GLOBALS['egw_info']['server']['header_version']) && @$GLOBALS['egw_info']['server']['header_version'] <= 1.6)
				{
					$GLOBALS['egw_info']['server']['include_root'] = @$GLOBALS['egw_info']['server']['server_root'];
				}
			}
			else
			{
				$detected .= lang('Sample configuration not found. using built in defaults') . '<br />' . "\n";
				$GLOBALS['egw_info']['server']['server_root']  = $realpath;
				$GLOBALS['egw_info']['server']['include_root'] = $realpath;
				/* This is the basic include needed on each page for eGroupWare application compliance */
				$GLOBALS['egw_info']['flags']['htmlcompliant'] = True;

				/* These are the settings for the database system */
				$setup_tpl->set_var('lang_domain',lang('Domain'));
				$setup_tpl->set_var('lang_delete',lang('Delete'));
				$setup_tpl->set_var('db_domain','default');
				$setup_tpl->set_var('db_host','localhost');
				$setup_tpl->set_var('db_name','egroupware');
				$setup_tpl->set_var('db_user','egroupware');
				$setup_tpl->set_var('db_pass','');
				$setup_tpl->set_var('config_user','changeme');
				$setup_tpl->set_var('config_pass','');

				while(list($k,$v) = each($supported_db))
				{
					$dbtype_options .= '<option value="' . $v . '">' . $db_fullnames[$v] . "</option>\n";
					if (!isset($default_port))
						$default_port = $default_db_ports[$v];
				}
				$setup_tpl->set_var('db_port',$default_port);
				$setup_tpl->set_var('dbtype_options',$dbtype_options);

				$setup_tpl->parse('domains','domain',True);
				$setup_tpl->set_var('domain','');

				$setup_tpl->set_var('comment_l','<!-- ');
				$setup_tpl->set_var('comment_r',' -->');

				/* These are a few of the advanced settings */
				$GLOBALS['egw_info']['server']['db_persistent'] = True;
				$GLOBALS['egw_info']['server']['mcrypt_enabled'] = false;	// default off, as there are too many problems with buggy mcrypt implementations (was check_load_extension('mcrypt');)
				$GLOBALS['egw_info']['server']['versions']['mcrypt'] = '';

				srand((double)microtime()*1000000);
				$random_char = array(
					'0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f',
					'g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v',
					'w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L',
					'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
				);

				for($i=0; $i<30; $i++)
				{
					$GLOBALS['egw_info']['server']['mcrypt_iv'] .= $random_char[rand(1,count($random_char))];
				}
			}

			// now guessing better settings then the default ones 
			if(!$no_guess)
			{
				$detected .= lang('Now guessing better values for defaults...') . '<br />' . "\n";
				$this_dir = dirname($_SERVER['SCRIPT_FILENAME']);
				$updir    = str_replace('/setup','',$this_dir);
				$GLOBALS['egw_info']['server']['server_root'] = $updir; 
				$GLOBALS['egw_info']['server']['include_root'] = $updir; 
			}

			$detected .= "</td></tr>\n";
			$setup_tpl->set_var('detected',$detected);
			/* End of detected settings, now display the form with the detected or prior values */

			$setup_tpl->set_var('server_root',@$GLOBALS['egw_info']['server']['server_root']);
			$setup_tpl->set_var('include_root',@$GLOBALS['egw_info']['server']['include_root']);
			if(!@isset($GLOBALS['egw_info']['server']['header_admin_user']))
			{
				$setup_tpl->set_var('header_admin_user','admin');
			}
			else
			{
				$setup_tpl->set_var('header_admin_user',@$GLOBALS['egw_info']['server']['header_admin_user']);
			}
			$setup_tpl->set_var('header_admin_pass',@$GLOBALS['egw_info']['server']['header_admin_password']);
			$setup_tpl->set_var('header_admin_password','');


			if(@$GLOBALS['egw_info']['server']['db_persistent'])
			{
				$setup_tpl->set_var('db_persistent_yes',' selected="selected"');
			}
			else
			{
				$setup_tpl->set_var('db_persistent_no',' selected="selected"');
			}

			$selected = '';
			$session_options = '';
			foreach($supported_sessions_type as $k => $v)
			{
				if($k == @$GLOBALS['egw_info']['server']['sessions_type'])
				{
					$selected = ' selected="selected" ';
				}
				else
				{
					$selected = '';
				}
				$session_options .= '<option ' . $selected . 'value="' . $k . '">' . $v . "</option>\n";
			}
			$setup_tpl->set_var('session_options',$session_options);

			if(@$GLOBALS['egw_info']['server']['mcrypt_enabled'])
			{
				$setup_tpl->set_var('mcrypt_enabled_yes',' selected="selected"');
			}
			else
			{
				$setup_tpl->set_var('mcrypt_enabled_no',' selected="selected"');
			}

			$setup_tpl->set_var('mcrypt',$GLOBALS['egw_info']['server']['versions']['mcrypt']);
			$setup_tpl->set_var('mcrypt_iv',$GLOBALS['egw_info']['server']['mcrypt_iv']);

			$setup_tpl->set_var('lang_setup_acl',lang('Limit access to setup to the following addresses, networks or hostnames (e.g. 127.0.0.1,10.1.1,myhost.dnydns.org)'));
			$setup_tpl->set_var('setup_acl',$GLOBALS['egw_info']['server']['setup_acl']);

			if(@$GLOBALS['egw_info']['server']['show_domain_selectbox'])
			{
				$setup_tpl->set_var('domain_selectbox_yes',' selected="selected"');
			}
			else
			{
				$setup_tpl->set_var('domain_selectbox_no',' selected="selected"');
			}

			$errors = '';
			if(!$found_dbtype)
			{
				/*
				$errors .= '<br /><font color="red">' . lang('Warning!') . '<br />'
					. lang('The db_type in defaults (%1) is not supported on this server. using first supported type.',$GLOBALS['egw_info']['server']['db_type'])
					. '</font>';
				*/
			}

			if(is_writeable('../header.inc.php') ||
				(!file_exists('../header.inc.php') && is_writeable('../')))
			{
				$errors .= '<br /><input type="submit" name="action[write]" value="'.lang('Write config').'" />&nbsp;'
					. lang('or') . '&nbsp;<input type="submit" name="action[download]" value="'.lang('Download').'" />&nbsp;'
					. lang('or') . '&nbsp;<input type="submit" name="action[view]" value="'.lang('View').'" /> '.lang('the file').'.';
			}
			else
			{
				$errors .= '<br />'
					. lang('Cannot create the header.inc.php due to file permission restrictions.<br /> Instead you can %1 the file.',
					'<input type="submit" name="action[download]" value="'.lang('Download').'" />' . lang('or') . '&nbsp;<input type="submit" name="action[view]" value="'.lang('View').'" />');
			}
			// set domain and password for the continue button
			@reset($GLOBALS['egw_domain']);
			list($firstDomain) = @each($GLOBALS['egw_domain']);
			$setup_tpl->set_var(array(
				'FormDomain' => $firstDomain,
				'FormUser'   => $GLOBALS['egw_domain'][$firstDomain]['config_user'],
				'FormPW'     => $GLOBALS['egw_domain'][$firstDomain]['config_passwd']
			));
			$setup_tpl->set_var('errors',$errors);

			$setup_tpl->set_var('lang_settings',lang('Settings'));
			$setup_tpl->set_var('lang_adddomain',lang('Add a domain'));
			$setup_tpl->set_var('lang_serverroot',lang('Server Root'));
			$setup_tpl->set_var('lang_includeroot',lang('Include Root (this should be the same as Server Root unless you know what you are doing)'));
			$setup_tpl->set_var('lang_adminuser',lang('Admin user for header manager'));
			$setup_tpl->set_var('lang_adminpass',lang('Admin password to header manager'));
			$setup_tpl->set_var('lang_dbhost',lang('DB Host'));
			$setup_tpl->set_var('lang_dbhostdescr',lang('Hostname/IP of database server'));
			$setup_tpl->set_var('lang_dbport',lang('DB Port'));
			$setup_tpl->set_var('lang_dbportdescr',lang('TCP port number of database server'));
			$setup_tpl->set_var('lang_dbname',lang('DB Name'));
			$setup_tpl->set_var('lang_dbnamedescr',lang('Name of database'));
			$setup_tpl->set_var('lang_dbuser',lang('DB User'));
			$setup_tpl->set_var('lang_dbuserdescr',lang('Name of db user eGroupWare uses to connect'));
			$setup_tpl->set_var('lang_dbpass',lang('DB Password'));
			$setup_tpl->set_var('lang_dbpassdescr',lang('Password of db user'));
			$setup_tpl->set_var('lang_dbtype',lang('DB Type'));
			$setup_tpl->set_var('lang_whichdb',lang('Which database type do you want to use with eGroupWare?'));
			$setup_tpl->set_var('lang_configuser',lang('Configuration User'));
			$setup_tpl->set_var('lang_configpass',lang('Configuration Password'));
			$setup_tpl->set_var('lang_passforconfig',lang('Password needed for configuration'));
			$setup_tpl->set_var('lang_persist',lang('Persistent connections'));
			$setup_tpl->set_var('lang_persistdescr',lang('Do you want persistent connections (higher performance, but consumes more resources)'));
			$setup_tpl->set_var('lang_sesstype',lang('Sessions Type'));
			$setup_tpl->set_var('lang_sesstypedescr',lang('What type of sessions management do you want to use (PHP session management may perform better)?').
				' '.lang('PHP plus restore gives by far the best performance, as it stores the eGW enviroment completly in the session.').
				' '.lang('Unfortunally some PHP/Apache packages have problems with it (Apache dies and you cant login anymore).'));
			$setup_tpl->set_var('lang_enablemcrypt',lang('Enable MCrypt'));
			$setup_tpl->set_var('lang_mcrypt_warning',lang('Not all mcrypt algorithms and modes work with eGroupWare. If you experience problems try switching it off.'));
			$setup_tpl->set_var('lang_mcryptversion',lang('MCrypt version'));
			$setup_tpl->set_var('lang_mcryptversiondescr',lang('Set this to "old" for versions &lt; 2.4, otherwise the exact mcrypt version you use.'));
			$setup_tpl->set_var('lang_mcryptiv',lang('MCrypt initialization vector'));
			$setup_tpl->set_var('lang_mcryptivdescr',lang('This should be around 30 bytes in length.<br />Note: The default has been randomly generated.'));
			$setup_tpl->set_var('lang_domselect',lang('Domain select box on login'));
			$setup_tpl->set_var('lang_finaldescr',lang('After retrieving the file, put it into place as the header.inc.php.  Then, click "continue".'));
			$setup_tpl->set_var('lang_continue',lang('Continue'));
			$setup_tpl->set_var('lang_Yes',lang('Yes'));
			$setup_tpl->set_var('lang_No',lang('No'));


			$setup_tpl->pfp('out','manageheader');

			$GLOBALS['egw_setup']->html->show_footer();

			break; // ending the switch default
	}
?>
