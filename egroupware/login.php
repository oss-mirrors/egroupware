<?php
	/**************************************************************************\
	* phpGroupWare                                                             *
	* http://www.phpgroupware.org                                              *
	* Written by Dan Kuykendall <seek3r@phpgroupware.org>                      *
	*            Joseph Engo    <jengo@phpgroupware.org>                       *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'disable_template_class' => True,
		'login'                  => True,
		'currentapp'             => 'login',
		'noheader'               => True
	);
	include('./header.inc.php');

	$phpgw_info['server']['template_dir'] = PHPGW_SERVER_ROOT . '/phpgwapi/templates/' . $phpgw_info['login_template_set'];
	$tmpl = CreateObject('phpgwapi.Template', $phpgw_info['server']['template_dir']);

	// This is used for system downtime, to prevent new logins.
	if ($phpgw_info['server']['deny_all_logins'])
	{
		$tmpl->set_file(array(
			'login_form'  => 'login_denylogin.tpl'
		));
		$tmpl->set_var('template_set','default');		
		$tmpl->pfp('loginout','login_form');
		exit;
	}

	// !! NOTE !!
	// Do NOT and I repeat, do NOT touch ANYTHING to do with lang in this file.
	// If there is a problem, tell me and I will fix it. (jengo)

/*
	if ($code != 10 && $phpgw_info['server']['usecookies'] == False)
	{
		Setcookie('sessionid');
		Setcookie('kp3');
		Setcookie('domain');
	}
*/

/* This is not working yet because I need to figure out a way to clear the $cd =1
	if (isset($PHP_AUTH_USER) && $cd == '1')
	{
		Header('HTTP/1.0 401 Unauthorized');
		Header('WWW-Authenticate: Basic realm="phpGroupWare"'); 
		echo 'You have to re-authentificate yourself'; 
		exit;
	}
*/

	if (! $deny_login && ! $phpgw_info['server']['show_domain_selectbox'])
	{
		$tmpl->set_file(array('login_form'  => 'login.tpl'));
		$tmpl->set_var('charset',lang('charset'));
	}
	else if ($phpgw_info['server']['show_domain_selectbox'])
	{
		$tmpl->set_file(array('login_form'  => 'login_selectdomain.tpl'));
		$tmpl->set_var('charset',lang('charset'));
	}

	function show_cookie()
	{
		global $phpgw_info, $code, $last_loginid, $login;
		/* This needs to be this way, because if someone doesnt want to use cookies, we shouldnt sneak one in */
		if ($code != 5 && (isset($phpgw_info['server']['usecookies']) && $phpgw_info['server']['usecookies']))
		{
			return $last_loginid;
		}
	}

	function check_logoutcode($code)
	{
		global $phpgw_info;
		switch($code)
		{
			case 1:
				return lang('You have been successfully logged out');
				break;
			case 2:
				return lang('Sorry, your login has expired');
				break;
			case 5:
				return '<font color="FF0000">' . lang('Bad login or password') . '</font>';
				break;
			case 10:
				Setcookie('sessionid');
				Setcookie('kp3');
				Setcookie('domain');
				return '<font color=FF0000>' . lang('Your session could not be verified.') . '</font>';
				break;
			default:
				return '&nbsp;';
		}
	}

	/* Program starts here */
  
	if ($phpgw_info['server']['auth_type'] == 'http' && isset($PHP_AUTH_USER))
	{
		$submit = True;
		$login  = $PHP_AUTH_USER;
		$passwd = $PHP_AUTH_PW;
	}

	# Apache + mod_ssl style SSL certificate authentication
	# Certificate (chain) verification occurs inside mod_ssl
	if ($phpgw_info['server']['auth_type'] == 'sqlssl' && isset($HTTP_SERVER_VARS["SSL_CLIENT_S_DN"]) && !isset($cd))
	{
		# an X.509 subject looks like:
		# /CN=john.doe/OU=Department/O=Company/C=xx/Email=john@comapy.tld/L=City/
		# the username is deliberately lowercase, to ease LDAP integration
		$sslattribs = explode("/",$HTTP_SERVER_VARS["SSL_CLIENT_S_DN"]);
		# skip the part in front of the first "/" (nothing)
		while ($sslattrib = next($sslattribs))
		{
			list($key,$val) = explode("=",$sslattrib);
			$sslattributes[$key] = $val;
		}

		if (isset($sslattributes["Email"]))
		{
			$submit = True;

			# login will be set here if the user logged out and uses a different username with
			# the same SSL-certificate.
			if (!isset($login)&&isset($sslattributes["Email"])) {
				$login = $sslattributes["Email"];
				# not checked against the database, but delivered to authentication module
				$passwd = $HTTP_SERVER_VARS["SSL_CLIENT_S_DN"];
			}
		}
		unset ($key,$val,$sslattributes);
	}

	if (isset($submit) && $submit)
	{
		if (getenv(REQUEST_METHOD) != 'POST' && !isset($PHP_AUTH_USER) && !isset($HTTP_SERVER_VARS["SSL_CLIENT_S_DN"]))
		{
			$phpgw->redirect($phpgw->link('/login.php','code=5'));
		}
		$sessionid = $phpgw->session->create($login,$passwd);
		if (! isset($sessionid) || ! $sessionid)
		{
			$phpgw->redirect($phpgw_info['server']['webserver_url'] . '/login.php?cd=5');
		}
		else
		{
			if ($phpgw_forward)
			{
				while (list($name,$value) = each($HTTP_GET_VARS))
				{
					if (ereg('phpgw_',$name))
					{
						$extra_vars .= '&' . $name . '=' . urlencode($value);
					}
				}
			}
			$phpgw->redirect($phpgw->link('/index.php','cd=yes' . $extra_vars));
		}
	}
	else
	{
		// !!! DONT CHANGE THESE LINES !!!
		// If there is something wrong with this code TELL ME!
		// Commenting out the code will not fix it. (jengo)
		if (isset($last_loginid))
		{
			$accounts = CreateObject('phpgwapi.accounts');
			$prefs = CreateObject('phpgwapi.preferences', $accounts->name2id($last_loginid));

			if (! $prefs->account_id)
			{
				$phpgw_info['user']['preferences']['common']['lang'] = 'en';
			}
			else
			{
				$phpgw_info['user']['preferences'] = $prefs->read_repository();
			}
			#print 'LANG:' . $phpgw_info['user']['preferences']['common']['lang'] . '<br>';
			$phpgw->translation->add_app('login');
			$phpgw->translation->add_app('loginscreen');
			if (lang('loginscreen_message') != 'loginscreen_message*')
			{
				$tmpl->set_var('lang_message',stripslashes(lang('loginscreen_message')));
			}
		}
		else
		{
			// If the lastloginid cookies isn't set, we will default to english.
			// Change this if you need.
			$phpgw_info['user']['preferences']['common']['lang'] = 'en';
			$phpgw->translation->add_app('login');
			$phpgw->translation->add_app('loginscreen');
			if (lang('loginscreen_message') != 'loginscreen_message*')
			{
				$tmpl->set_var('lang_message',stripslashes(lang('loginscreen_message')));
			}
		}
	}

	if (!isset($cd) || !$cd)
	{
		$cd = '';
	}

	if ($phpgw_info['server']['show_domain_selectbox'])
	{
		reset($phpgw_domain);
		unset($domain_select);      // For security ... just in case
		while ($domain = each($phpgw_domain))
		{
			$domain_select .= '<option value="' . $domain[0] . '"';
			if ($domain[0] == $last_domain)
			{
				$domain_select .= ' selected';
			}
			$domain_select .= '>' . $domain[0] . '</option>';
		}
		$tmpl->set_var('select_domain',$domain_select);
	}

	while (list($name,$value) = each($HTTP_GET_VARS))
	{
		if (ereg('phpgw_',$name))
		{
			$extra_vars .= '&' . $name . '=' . urlencode($value);
		}
	}

	if ($extra_vars)
	{
		$extra_vars = '?' . substr($extra_vars,1,strlen($extra_vars));
	}

	$tmpl->set_var('login_url', $phpgw_info['server']['webserver_url'] . '/login.php' . $extra_vars);
	$tmpl->set_var('website_title', $phpgw_info['server']['site_title']);
	$tmpl->set_var('cd',check_logoutcode($cd));
	$tmpl->set_var('cookie',show_cookie());
	$tmpl->set_var('lang_username',lang('username'));
	$tmpl->set_var('lang_phpgw_login',lang('phpGroupWare login'));
	$tmpl->set_var('version',$phpgw_info['server']['versions']['phpgwapi']);
	$tmpl->set_var('lang_password',lang('password'));
	$tmpl->set_var('lang_login',lang('login'));
	$tmpl->set_var('template_set',$phpgw_info['login_template_set']);

	$tmpl->pfp('loginout','login_form');
?>
