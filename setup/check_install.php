<?php
  /**************************************************************************\
  * eGroupWare - Setup Check Installation                                    *
  * http://www.eGroupWare.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$run_by_webserver = !!$_SERVER['PHP_SELF'];

	if ($run_by_webserver)
	{
		$phpgw_info = array();
		$GLOBALS['phpgw_info']['flags'] = array(
			'noheader' => True,
			'nonavbar' => True,
			'currentapp' => 'home',
			'noapi' => True
		);
		$safe_er = error_reporting();
		include ('./inc/functions.inc.php');
		error_reporting($safe_er);

		$GLOBALS['phpgw_info']['setup']['stage']['header'] = $GLOBALS['phpgw_setup']->detection->check_header();
		if ($GLOBALS['phpgw_info']['setup']['stage']['header'] == '10')
		{
			// Check header and authentication
			if (!$GLOBALS['phpgw_setup']->auth('Config') && !$GLOBALS['phpgw_setup']->auth('Header'))
			{
				Header('Location: index.php');
				exit;
			}
		}
		$passed_icon = '<img src="templates/default/images/completed.png" title="Passed" align="middle"> ';
		$error_icon = '<img src="templates/default/images/incomplete.png" title="Error" align="middle"> ';
		$warning_icon = '<img src="templates/default/images/dep.png" title="Warning" align="middle"> ';
	}
	else
	{
		$passed_icon = ' Passed';
		$error_icon = '*** Error: ';
		$warning_icon = '!!! Warning: ';
	}
	$checks = array(
		'safe_mode' => array(
			'func' => 'php_ini_check',
			'value' => 0,
			'verbose_value' => 'Off',
			'warning' => 'safe_mode is turned on, which is generaly a good thing as it makes your install more secure.
If safe_mode is turned on, eGW is not able to change certain settings on runtime, nor can we load any not yet loaded module.
*** You have to do the changes manualy in your php.ini (usualy in /etc on linux) in order to get eGW fully working !!!
*** Do NOT update your database via setup, as the update might be interrupted by the max_execution_time,
which leaves your DB in an unrecoverable state (your data is lost) !!!'
		),
/* not longer needed, as it gets set now on runtime (works even with safe_mode)
		'error_reporting' => array(
			'func' => 'php_ini_check',
			'value' => E_NOTICE,
			'verbose_value' => 'E_NOTICE',
			'check' => 'not set',
			'safe_mode' => 'error_reporting = E_ALL & ~E_NOTICE'
		),
*/
		'magic_quotes_runtime' => array(
			'func' => 'php_ini_check',
			'value' => 0,
			'verbose_value' => 'Off',
			'safe_mode' => 'magic_qoutes_runtime = Off'
		),
		'register_globals' => array(
			'func' => 'php_ini_check',
			'value' => 0,
			'verbose_value' => 'Off',
			'warning' => "register_globals is turned On, eGroupWare does NOT require it and it's generaly more secure to have it turned Off"
		),
		'memory_limit' => array(
			'func' => 'php_ini_check',
			'value' => '16M',
			'check' => '>=',
			'error' => 'memory_limit is set to less than 16M: some applications of eGroupWare need more than the recommend 8M,
expect occasional failures',
			'change' => 'memory_limit = 16M'
		),
		'max_execution_time' => array(
			'func' => 'php_ini_check',
			'value' => 30,
			'check' => '>=',
			'error' => 'max_execution_time is set to less than 30 (seconds): eGroupWare sometimes needs a higher execution_time,
expect occasional failures',
			'save_mode' => 'max_execution_time = 30'
		),
		'mysql' => array(
			'func' => 'extension_check',
			'warning' => 'The mysql extension is needed, if you plan to use a MySQL database.'
		),
		'pgsql' => array(
			'func' => 'extension_check',
			'warning' => 'The pgsql extension is needed, if you plan to use a pgSQL database.'
		),
		'mssql' => array(
			'func' => 'extension_check',
			'warning' => 'The mssql extension is needed, if you plan to use a MsSQL database.',
			'win_only' => True
		),
		'mbstring' => array(
			'func' => 'extension_check',
			'warning' => 'The mbstring extension is needed to fully support unicode (utf-8) or other multibyte-charsets.'
		),
		'imap' => array(
			'func' => 'extension_check',
			'warning' => 'The imap extension is needed by the two email apps (even if you use email with pop3 as protocoll).'
		),
		'.' => array(
			'func' => 'permission_check',
			'is_world_writable' => False,
			'recursiv' => True
		),
		'header.inc.php' => array(
			'func' => 'permission_check',
			'is_world_readable' => False,
			'only_if_exists' => $GLOBALS['phpgw_info']['setup']['stage']['header'] != 10
		),
		'phpgwapi/images' => array(
			'func' => 'permission_check',
			'is_writable' => True
		),
		'fudforum' => array(
			'func' => 'permission_check',
			'is_writable' => True,
			'only_if_exists' => True
		),
	);

	// some constanst for pre php4.3
	if (!defined('PHP_SHLIB_SUFFIX'))
	{
		define('PHP_SHLIB_SUFFIX',strtoupper(substr(PHP_OS, 0,3)) == 'WIN' ? 'dll' : 'so');
	}
	if (!defined('PHP_SHLIB_PREFIX'))
	{
		define('PHP_SHLIB_PREFIX',PHP_SHLIB_SUFFIX == 'dll' ? 'php_' : '');
	}

	function extension_check($name,$args)
	{
		global $passed_icon, $error_icon, $warning_icon;

		$is_win = strtoupper(substr(PHP_OS,0,3)) == 'WIN';

		if (isset($args['win_only']) && $args['win_only'] && !$is_win)
		{
			return True;	// check only under windows
		}
		$availible = extension_loaded($name) || @dl(PHP_SHLIB_PREFIX.$name.'.'.PHP_SHLIB_SUFFIX);

		echo "Checking extension $name is loaded or loadable: ".($availible ? 'True' : 'False')."\n";

		if (!$availible)
		{
			echo $warning_icon.$args['warning']."\n\n";
		}
		else
		{
			echo $passed_icon."\n\n";
		}
		return $availible;
	}

	function verbosePerms( $in_Perms )
	{
		if($in_Perms & 0x1000)     // FIFO pipe
		{
			$sP = 'p';
		}
		elseif($in_Perms & 0x2000) // Character special
		{
			$sP = 'c';
		}
		elseif($in_Perms & 0x4000) // Directory
		{
			$sP = 'd';
		}
		elseif($in_Perms & 0x6000) // Block special
		{
			$sP = 'b';
		}
		elseif($in_Perms & 0x8000) // Regular
		{
			$sP = '-';
		}
		elseif($in_Perms & 0xA000) // Symbolic Link
		{
			$sP = 'l';
		}
		elseif($in_Perms & 0xC000) // Socket
		{
			$sP = 's';
		}
		else                         // UNKNOWN
		{
			$sP = 'u';
		}

		// owner
		$sP .= (($in_Perms & 0x0100) ? 'r' : '-') .
		(($in_Perms & 0x0080) ? 'w' : '-') .
		(($in_Perms & 0x0040) ? (($in_Perms & 0x0800) ? 's' : 'x' ) :
		(($in_Perms & 0x0800) ? 'S' : '-'));

		// group
		$sP .= (($in_Perms & 0x0020) ? 'r' : '-') .
		(($in_Perms & 0x0010) ? 'w' : '-') .
		(($in_Perms & 0x0008) ? (($in_Perms & 0x0400) ? 's' : 'x' ) :
		(($in_Perms & 0x0400) ? 'S' : '-'));

		// world
		$sP .= (($in_Perms & 0x0004) ? 'r' : '-') .
		(($in_Perms & 0x0002) ? 'w' : '-') .
		(($in_Perms & 0x0001) ? (($in_Perms & 0x0200) ? 't' : 'x' ) :
		(($in_Perms & 0x0200) ? 'T' : '-'));
		return $sP;
	}

	function permission_check($name,$args,$verbose=True)
	{
		global $passed_icon, $error_icon, $warning_icon;
		//echo "<p>permision_check('$name',".print_r($args,True).",'$verbose')</p>\n";

		if (substr($name,0,3) != '../')
		{
			$name = '../'.$name;
		}
		$rel_name = substr($name,3);

		// dont know how much of this is working on windows
		if (!file_exists($name) && isset($args['only_if_exists']) && $args['only_if_exists'])
		{
			return True;
		}

		if ($verbose)
		{
			$perms = '';
			if (file_exists($name))
			{
				$owner = function_exists('posix_getpwuid') ? posix_getpwuid(@fileowner($name)) : array('name' => 'nn');
				$group = function_exists('posix_getgrgid') ? posix_getgrgid(@filegroup($name)) : array('name' => 'nn');

				$checks = array();
				if (isset($args['is_writable'])) $checks[] = (!$args['is_writable']?'not ':'').'writable by webserver';
				if (isset($args['is_world_readable'])) $checks[] = (!$args['is_world_readable']?'not ':'').'world readable';
				if (isset($args['is_world_writable'])) $checks[] = (!$args['is_world_writable']?'not ':'').'world writable';
				$checks = implode(', ',$checks);

				$perms = "$owner[name]/$group[name] ".verbosePerms(@fileperms($name));
			}
			echo "Checking file-permissions of $rel_name for $checks: $perms\n";
		}
		if (!file_exists($name))
		{
			echo "$error_icon$rel_name does not exist !!!\n";
			return False;
		}
		$warning = False;
		if (!$GLOBALS['run_by_webserver'] && ($args['is_readable'] || $args['is_writable']))
		{
			echo "$warning_icon check can only be performed, if called via a webserver, as the user-id/-name of the webserver is not known.\n";
			unset($args['is_readable']);
			unset($args['is_writable']);
			$warning = True;
		}
		$Ok = True;
		if (isset($args['is_writable']) && is_writable($name) != $args['is_writable'])
		{
			echo "$error_icon$rel_name ".($args['is_writable']?'not ':'')."writable by the webserver !!!\n";
			$Ok = False;
		}
		if (isset($args['is_readable']) && is_readable($name) != $args['is_readable'])
		{
			echo "$error_icon$rel_name ".($args['is_readable']?'not ':'')."readable by the webserver !!!\n";
			$Ok = False;
		}
		if (isset($args['is_world_readable']) && !(fileperms($name) & 04) == $args['is_world_readable'])
		{
			echo "$error_icon$rel_name ".($args['is_world_readable']?'not ':'')."world-readable !!!\n";
			$Ok = False;
		}
		if (isset($args['is_world_writable']) && !(fileperms($name) & 02) == $args['is_world_writable'])
		{
			echo "$error_icon$rel_name ".($args['is_world_writable']?'not ':'')."world-writable !!!\n";
			$Ok = False;
		}
		if ($Ok && !$warning && $verbose)
		{
			echo "$passed_icon\n";
		}
		if ($verbose) echo "\n";

		if ($Ok && @$args['recursiv'] && is_dir($name))
		{
			$handle = @opendir($name);
			while($handle && ($file = readdir($handle)))
			{
				if ($file != '.' && $file != '..')
				{
					$Ok = $Ok && permission_check(($name!='.'?$name.'/':'').$file,$args,False);
				}
			}
			if ($handle) closedir($handle);
		}
		return $Ok;
	}

	function php_ini_check($name,$args)
	{
		global $passed_icon, $error_icon, $warning_icon;

		$safe_mode = ini_get('safe_mode');

		$ini_value = ini_get($name);
		$check = isset($args['check']) ? $args['check'] : '=';
		$verbose_value = isset($args['verbose_value']) ? $args['verbose_value'] : $args['value'];
		$ini_value_verbose = '';
		if ($verbose_value == 'On' || $verbose_value == 'Off')
		{
			$ini_value_verbose = ' = '.($ini_value ? 'On' : 'Off');
		}
		echo "Checking php.ini: $name $check $verbose_value: ini_get('$name')='$ini_value'$ini_value_verbose\n";
		switch ($check)
		{
			case 'not set':
				$result = !($ini_value & $args['value']);
				break;
			case 'set':
				$result = !!($ini_value & $args['value']);
				break;
			case '>=':
				$result = !$ini_value ||	// value not used, eg. no memory limit
				intval($ini_value) >= intval($args['value']) &&
				($args['value'] == intval($args['value']) ||
				substr($args['value'],-1) == substr($ini_value,-1));
				break;
			case '=':
			default:
				$result = $ini_value == $args['value'];
				break;
		}
		if (!$result)
		{
			if (isset($args['warning']))
			{
				echo $warning_icon.$args['warning']."\n";
			}
			if (isset($args['error']))
			{
				echo $error_icon.$args['error']."\n";
			}
			if (isset($args['safe_mode']) && $safe_mode || @$args['change'])
			{
				echo $error_icon."Please make the following change in your php.ini: ".($args['safe_mode']?$args['safe_mode']:$args['change'])."\n";
			}
			echo "\n";
			return False;
		}
		echo "$passed_icon\n\n";

		return True;
	}

	if ($run_by_webserver)
	{
		//echo "<html>\n<header>\n<title>Checking the eGroupWare install</title>\n</header>\n<body>\n";
		$tpl_root = $GLOBALS['phpgw_setup']->html->setup_tpl_dir('setup');
		$setup_tpl = CreateObject('setup.Template',$tpl_root);
		$setup_tpl->set_file(array(
			'T_head' => 'head.tpl',
			'T_footer' => 'footer.tpl',
		));
		$ConfigDomain = get_var('ConfigDomain',Array('POST','COOKIE'));
		$GLOBALS['phpgw_setup']->html->show_header(lang('Checking the eGroupWare Installation'),False,'config',$ConfigDomain . '(' . @$phpgw_domain[$ConfigDomain]['db_type'] . ')');
		echo '<h1>'.lang('Checking the eGroupWare Installation')."</h1>\n";
		echo "<pre style=\"text-align: left;\">\n";;
	}
	else
	{
		echo "Checking the eGroupWare Installation\n";
		echo "====================================\n\n";
	}

	$Ok = True;
	foreach ($checks as $name => $args)
	{
		$check_ok = $args['func']($name,$args);
		$Ok = $Ok && $check_ok;
	}

	if ($run_by_webserver)
	{
		echo "</pre>\n";;

		if ($GLOBALS['phpgw_info']['setup']['stage']['header'] != 10)
		{
			if (!$Ok)
			{
				echo '<h3>'.lang('Please fix the above errors (%1) and warnings(%2) and %3continue to the Header Admin%4',$error_icon,$warning_icon,'<a href="manageheader.php">','</a>')."</h3>\n";
			}
			else
			{
				echo '<h3><a href="manageheader.php">'.lang('Continue to the Header Admin')."</a></h3>\n";
			}
		}
		else
		{
			echo '<h3><a href="'.str_replace('check_install.php','',$_SERVER['HTTP_REFERER']).'">'.lang('Return to Setup')."</a></h3>\n";
		}
		$setup_tpl->pparse('out','T_footer');
		//echo "</body>\n</html>\n";
	}
?>
