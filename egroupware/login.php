<?php
	/**************************************************************************\
	* eGroupWare login                                                         *
	* http://www.egroupware.org                                                *
	* Originaly written by Dan Kuykendall <seek3r@phpgroupware.org>            *
	*                      Joseph Engo    <jengo@phpgroupware.org>             *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$submit = False;			// set to some initial value

	$GLOBALS['egw_info'] = array('flags' => array(
		'disable_Template_class'  => True,
		'login'                   => True,
		'currentapp'              => 'login',
		'noheader'                => True
	));

	if(file_exists('./header.inc.php'))
	{
		include('./header.inc.php');
		if(!function_exists('CreateObject'))
		{
			Header('Location: setup/index.php');
			exit;
		}
	}
	else
	{
		Header('Location: setup/index.php');
		exit;
	}

	$GLOBALS['egw_info']['server']['template_dir'] = EGW_SERVER_ROOT . '/phpgwapi/templates/' . $GLOBALS['egw_info']['login_template_set'];
	$tmpl = CreateObject('phpgwapi.Template', $GLOBALS['egw_info']['server']['template_dir']);

	// read the images from the login-template-set, not the (maybe not even set) users template-set
	$GLOBALS['egw_info']['user']['preferences']['common']['template_set'] = $GLOBALS['egw_info']['login_template_set'];

	// This is used for system downtime, to prevent new logins.
	if($GLOBALS['egw_info']['server']['deny_all_logins'])
	{
		$deny_msg=lang('Oops! You caught us in the middle of system maintainance.<br/>
		Please, check back with us shortly.');

		$tmpl->set_file(array
		(
			'login_form' => 'login_denylogin.tpl'
		));

		$tmpl->set_var('template_set','default');
		$tmpl->set_var('deny_msg',$deny_msg);
		$tmpl->pfp('loginout','login_form');
		exit;
	}
	$tmpl->set_file(array('login_form' => 'login.tpl'));

	// !! NOTE !!
	// Do NOT and I repeat, do NOT touch ANYTHING to do with lang in this file.
	// If there is a problem, tell me and I will fix it. (jengo)

	// whoooo scaring
/*
	if($GLOBALS['egw_info']['server']['usecookies'] == True)
	{
		$GLOBALS['egw']->session->egw_setcookie('eGroupWareLoginTime', time());
	}
*/
/*
	if($_GET['cd'] != 10 && $GLOBALS['egw_info']['server']['usecookies'] == False)
	{
		$GLOBALS['egw']->session->egw_setcookie('sessionid');
		$GLOBALS['egw']->session->egw_setcookie('kp3');
		$GLOBALS['egw']->session->egw_setcookie('domain');
	}
*/

/* This is not working yet because I need to figure out a way to clear the $cd =1
	if(isset($_SERVER['PHP_AUTH_USER']) && $_GET['cd'] == '1')
	{
		Header('HTTP/1.0 401 Unauthorized');
		Header('WWW-Authenticate: Basic realm="phpGroupWare"');
		echo 'You have to re-authentificate yourself';
		exit;
	}
*/

	function check_logoutcode($code)
	{
		switch($code)
		{
			case 1:
				return lang('You have been successfully logged out');
			case 2:
				return lang('Sorry, your login has expired');
			case 4:
				return lang('Cookies are required to login to this site.');
			case 5:
				return '<font color="red">' . lang('Bad login or password') . '</font>';
			case 98:
				return '<font color="red">' . lang('Account is expired') . '</font>';
			case 99:
				return '<font color="red">' . lang('Blocked, too many attempts') . '</font>';
			case 10:
				$GLOBALS['egw']->session->egw_setcookie('sessionid');
				$GLOBALS['egw']->session->egw_setcookie('kp3');
				$GLOBALS['egw']->session->egw_setcookie('domain');
				return '<font color="red">' . lang('Your session could not be verified.') . '</font>';
			default:
				return '&nbsp;';
		}
	}

	/* Program starts here */

	if($GLOBALS['egw_info']['server']['auth_type'] == 'http' && isset($_SERVER['PHP_AUTH_USER']))
	{
		$submit = True;
		$login  = $_SERVER['PHP_AUTH_USER'];
		$passwd = $_SERVER['PHP_AUTH_PW'];
		$passwd_type = 'text';
	}
	else
	{
		$passwd = $_POST['passwd'];
		$passwd_type = $_POST['passwd_type'];

		if($GLOBALS['egw_info']['server']['allow_cookie_auth'])
		{
			$eGW_remember = unserialize(stripslashes($_COOKIE['eGW_remember']));

			if($eGW_remember['login'] && $eGW_remember['passwd'] && $eGW_remember['passwd_type'])
			{
				$_SERVER['PHP_AUTH_USER'] = $login = $eGW_remember['login'];
				$_SERVER['PHP_AUTH_PW'] = $passwd = $eGW_remember['passwd'];
				$passwd_type = $eGW_remember['passwd_type'];
				$submit = True;
			}
		}
		if(!$passwd && ($GLOBALS['egw_info']['server']['auto_anon_login']) && !$_GET['cd'])
		{
			$_SERVER['PHP_AUTH_USER'] = $login = 'anonymous';
			$_SERVER['PHP_AUTH_PW'] =  $passwd = 'anonymous';
			$passwd_type = 'text';
			$submit = True;
		}
	}

	# Apache + mod_ssl style SSL certificate authentication
	# Certificate (chain) verification occurs inside mod_ssl
	if($GLOBALS['egw_info']['server']['auth_type'] == 'sqlssl' && isset($_SERVER['SSL_CLIENT_S_DN']) && !isset($_GET['cd']))
	{
		# an X.509 subject looks like:
		# /CN=john.doe/OU=Department/O=Company/C=xx/Email=john@comapy.tld/L=City/
		# the username is deliberately lowercase, to ease LDAP integration
		$sslattribs = explode('/',$_SERVER['SSL_CLIENT_S_DN']);
		# skip the part in front of the first '/' (nothing)
		while(($sslattrib = next($sslattribs)))
		{
			list($key,$val) = explode('=',$sslattrib);
			$sslattributes[$key] = $val;
		}

		if(isset($sslattributes['Email']))
		{
			$submit = True;

			# login will be set here if the user logged out and uses a different username with
			# the same SSL-certificate.
			if(!isset($_POST['login'])&&isset($sslattributes['Email']))
			{
				$login = $sslattributes['Email'];
				# not checked against the database, but delivered to authentication module
				$passwd = $_SERVER['SSL_CLIENT_S_DN'];
			}
		}
		unset($key);
		unset($val);
		unset($sslattributes);
	}

	if(isset($passwd_type) || $_POST['submitit_x'] || $_POST['submitit_y'] || $submit)
//		isset($_POST['passwd']) && $_POST['passwd']) // enable konqueror to login via Return
	{
		if(getenv('REQUEST_METHOD') != 'POST' && $_SERVER['REQUEST_METHOD'] != 'POST' &&
			!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['SSL_CLIENT_S_DN']))
		{
			$GLOBALS['egw']->session->egw_setcookie('eGW_remember');
			$GLOBALS['egw']->redirect($GLOBALS['egw']->link('/login.php','cd=5'));
		}
		#if(!isset($_COOKIE['eGroupWareLoginTime']))
		#{
		#	$GLOBALS['egw']->redirect($GLOBALS['egw']->link('/login.php','cd=4'));
		#}

		// don't get login data again when $submit is true
		if($submit == false)
		{
			$login = $_POST['login'];
		}

		//conference - for strings like vinicius@thyamad.com@default , allows
		//that user have a login that is his e-mail. (viniciuscb)
		$login_parts = explode('@',$login);
		$got_login = false;
		if (count($login_parts) > 1)
		{
			//Last part of login string, when separated by @, is a domain name
			if (array_key_exists(array_pop($login_parts),$GLOBALS['egw_domain']))
			{
				$got_login = true;
			}
		}

		if (!$got_login)
		{
			if(isset($_POST['logindomain']))
			{
				$login .= '@' . $_POST['logindomain'];
			}
			elseif(!isset($GLOBALS['egw_domain'][$GLOBALS['egw_info']['user']['domain']]))
			{
				$login .= '@'.$GLOBALS['egw_info']['server']['default_domain'];
			}
		}
		$GLOBALS['sessionid'] = $GLOBALS['egw']->session->create($login,$passwd,$passwd_type,'u');

		if(!isset($GLOBALS['sessionid']) || ! $GLOBALS['sessionid'])
		{
			$GLOBALS['egw']->session->egw_setcookie('eGW_remember');
			$GLOBALS['egw']->redirect($GLOBALS['egw_info']['server']['webserver_url'] . '/login.php?cd=' . $GLOBALS['egw']->session->cd_reason);
		}
		else
		{
			/* set auth_cookie  */
			if($GLOBALS['egw_info']['server']['allow_cookie_auth'] && $_POST['remember_me'] && $_POST['passwd'])
			{
				switch ($_POST['remember_me'])
				{
					case '1hour' :
						$remember_time = time()+60*60;
						break;
					case '1day' :
						$remember_time = time()+60*60*24;
						break;
					case '1week' :
						$remember_time = time()+60*60*24*7;
						break;
					case '1month' :
						$remember_time = time()+60*60*24*30;
						break;
					case 'forever' :
					default:
						$remember_time = 2147483647;
						break;
				}
				$GLOBALS['egw']->session->egw_setcookie('eGW_remember',serialize(array(
					'login' => $login,
					'passwd' => $passwd,
					'passwd_type' => $passwd_type)),
					$remember_time);
			}

			if ($_POST['lang'] && preg_match('/^[a-z]{2}(-[a-z]{2}){0,1}$/',$_POST['lang']) &&
				$_POST['lang'] != $GLOBALS['egw_info']['user']['preferences']['common']['lang'])
			{
				$GLOBALS['egw']->preferences->add('common','lang',$_POST['lang'],'session');
			}

			if(!$GLOBALS['egw_info']['server']['disable_autoload_langfiles'])
			{
				$GLOBALS['egw']->translation->autoload_changed_langfiles();
			}
			$forward = isset($_GET['phpgw_forward']) ? urldecode($_GET['phpgw_forward']) : @$_POST['phpgw_forward'];
			if (!$forward)
			{
				$extra_vars['cd'] = 'yes';
				if($GLOBALS['egw']->hooks->single('hasUpdates', 'home'))
				{
					$extra_vars['hasupdates'] = 'yes';
				}
				$forward = '/index.php';
			}
			else
			{
				list($forward,$extra_vars) = explode('?',$forward,2);
			}
			
			if(strpos($_SERVER['HTTP_REFERER'], $_SERVER['REQUEST_URI']) === false) {
				// login requuest does not come from login.php
				// redirect to referer on logout
				$GLOBALS['egw']->session->appsession('referer', 'login', $_SERVER['HTTP_REFERER']);
			}
			
			// Check for save passwd
			if($GLOBALS['egw_info']['server']['check_save_passwd'] && $GLOBALS['egw']->acl->check('changepassword', 1, 'preferences') && $unsave_msg = $GLOBALS['egw']->auth->crackcheck($passwd))
			{
				$GLOBALS['egw']->log->write(array('text'=>'D-message, User '. $login. ' authenticated with an unsave password','file' => __FILE__,'line'=>__LINE__));
				$message = '<font color="red">'. lang('eGroupWare checked your password for saftyness. You have to change your password for the following reason:').'<br>';
				$GLOBALS['egw']->redirect_link('/index.php', array('menuaction' => 'preferences.uipassword.change','message' => $message. $unsave_msg. '</font>'));
			}
			else 
			{
				$GLOBALS['egw']->redirect_link($forward,$extra_vars);
			}
		}
	}
	else
	{
		// !!! DONT CHANGE THESE LINES !!!
		// If there is something wrong with this code TELL ME!
		// Commenting out the code will not fix it. (jengo)
		if(isset($_COOKIE['last_loginid']))
		{
			$accounts =& CreateObject('phpgwapi.accounts');
			$prefs =& CreateObject('phpgwapi.preferences', $accounts->name2id($_COOKIE['last_loginid']));

			if($prefs->account_id)
			{
				$GLOBALS['egw_info']['user']['preferences'] = $prefs->read_repository();
			}
		}
		if ($_GET['lang'])
		{
			$GLOBALS['egw_info']['user']['preferences']['common']['lang'] = $_GET['lang'];
		}
		elseif(!isset($_COOKIE['last_loginid']) || !$prefs->account_id)
		{
			// If the lastloginid cookies isn't set, we will default to the first language,
			// the users browser accepts.
			list($lang) = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if(strlen($lang) > 2)
			{
				$lang = substr($lang,0,2);
			}
			$GLOBALS['egw_info']['user']['preferences']['common']['lang'] = $lang;
		}
		#print 'LANG:' . $GLOBALS['egw_info']['user']['preferences']['common']['lang'] . '<br>';

		$GLOBALS['egw']->translation->init();	// this will set the language according to the (new) set prefs
		$GLOBALS['egw']->translation->add_app('login');
		$GLOBALS['egw']->translation->add_app('loginscreen');
		if(lang('loginscreen_message') == 'loginscreen_message*')
		{
			$GLOBALS['egw']->translation->add_app('loginscreen','en');	// trying the en one
		}
		if(lang('loginscreen_message') != 'loginscreen_message*')
		{
			$tmpl->set_var('lang_message',stripslashes(lang('loginscreen_message')));
		}
	}

	$tmpl->set_block('login_form','domain_selection');
	$domain_select = '&nbsp;';
	$lang_domain_select = '&nbsp;';
	$last_loginid = $_COOKIE['last_loginid'];
	if($GLOBALS['egw_info']['server']['show_domain_selectbox'])
	{
		$domain_select = "<select name=\"logindomain\">\n";
		foreach($GLOBALS['egw_domain'] as $domain_name => $domain_vars)
		{
			$domain_select .= '<option value="' . $domain_name . '"';

			if($domain_name == $_COOKIE['last_domain'])
			{
				$domain_select .= ' selected';
			}
			$domain_select .= '>' . $domain_name . "</option>\n";
		}
		$domain_select .= "</select>\n";
		$lang_domain_select = lang('Domain');
	}
	elseif($last_loginid !== '')
	{
		reset($GLOBALS['egw_domain']);
		list($default_domain) = each($GLOBALS['egw_domain']);

		if($_COOKIE['last_domain'] != $default_domain && !empty($_COOKIE['last_domain']))
		{
			$last_loginid .= '@' . $_COOKIE['last_domain'];
		}
	}
	$tmpl->set_var('lang_select_domain',$lang_domain_select);
	$tmpl->set_var('select_domain',$domain_select);

	if(!$GLOBALS['egw_info']['server']['show_domain_selectbox'])
	{
		/* trick to make domain section disapear */
		$tmpl->set_var('domain_selection',$GLOBALS['egw_info']['user']['domain'] ? 
			'<input type="hidden" name="logindomain" value="'.htmlspecialchars($GLOBALS['egw_info']['user']['domain']).'" />' : '');
	}

	foreach($_GET as $name => $value)
	{
		if(ereg('phpgw_',$name))
		{
			$extra_vars .= '&' . $name . '=' . urlencode($value);
		}
	}

	if($extra_vars)
	{
		$extra_vars = '?' . substr($extra_vars,1);
	}

	/********************************************************\
	* Check is the registration app is installed, activated  *
	* And if the register link must be placed                *
	\********************************************************/

	$cnf_reg =& CreateObject('phpgwapi.config','registration');
	$cnf_reg->read_repository();
	$config_reg = $cnf_reg->config_data;

	if($config_reg[enable_registration]=='True')
	{
		if ($config_reg[register_link]=='True')
		{
			$reg_link='&nbsp;<a href="'. $GLOBALS['egw']->link('/registration/index.php'). '">'.lang('Not a user yet? Register now').'</a><br/>';
		}
		if ($config_reg[lostpassword_link]=='True')
		{
			$lostpw_link='&nbsp;<a href="'. $GLOBALS['egw']->link('/registration/index.php','menuaction=registration.boreg.lostpw1'). '">'.lang('Lost password').'</a><br/>';
		}
		if ($config_reg[lostid_link]=='True')
		{
			$lostid_link='&nbsp;<a href="'. $GLOBALS['egw']->link('/registration/index.php','menuaction=registration.boreg.lostid1'). '">'.lang('Lost Login Id').'</a><br/>';
		}

		/* if at least one option of "registration" is activated display the registration section */
		if($config_reg[register_link]=='True' || $config_reg[lostpassword_link]=='True' || $config_reg[lostid_link]=='True')
		{
			$tmpl->set_var('register_link',$reg_link);
			$tmpl->set_var('lostpassword_link',$lostpw_link);
			$tmpl->set_var('lostid_link',$lostid_link) ;

			//$tmpl->set_var('registration_url',$GLOBALS['egw_info']['server']['webserver_url'] . '/registration/');
		}
		else
		{
			/* trick to make registration section disapear */
			$tmpl->set_block('login_form','registration');
			$tmpl->set_var('registration','');
		}
	}

	// add a content-type header to overwrite an existing default charset in apache (AddDefaultCharset directiv)
	header('Content-type: text/html; charset='.$GLOBALS['egw']->translation->charset());

	$GLOBALS['egw_info']['server']['template_set'] = $GLOBALS['egw_info']['login_template_set'];

	$tmpl->set_var('charset',$GLOBALS['egw']->translation->charset());
	$tmpl->set_var('login_url', $GLOBALS['egw_info']['server']['webserver_url'] . '/login.php' . $extra_vars);
	$tmpl->set_var('version',$GLOBALS['egw_info']['server']['versions']['phpgwapi']);
	$tmpl->set_var('cd',check_logoutcode($_GET['cd']));
	$tmpl->set_var('cookie',$last_loginid);

	$tmpl->set_var('lang_username',lang('username'));
	$tmpl->set_var('lang_password',lang('password'));
	$tmpl->set_var('lang_login',lang('login'));

	$tmpl->set_var('website_title', $GLOBALS['egw_info']['server']['site_title']);
	$tmpl->set_var('template_set',$GLOBALS['egw_info']['login_template_set']);
	$tmpl->set_var('bg_color',($GLOBALS['egw_info']['server']['login_bg_color']?$GLOBALS['egw_info']['server']['login_bg_color']:'FFFFFF'));
	$tmpl->set_var('bg_color_title',($GLOBALS['egw_info']['server']['login_bg_color_title']?$GLOBALS['egw_info']['server']['login_bg_color_title']:'486591'));

	if (substr($GLOBALS['egw_info']['server']['login_logo_file'],0,4) == 'http')
	{
		$var['logo_file'] = $GLOBALS['egw_info']['server']['login_logo_file'];
	}
	else
	{
		$var['logo_file'] = $GLOBALS['egw']->common->image('phpgwapi',$GLOBALS['egw_info']['server']['login_logo_file']?$GLOBALS['egw_info']['server']['login_logo_file']:'logo');
	}
	$var['logo_url'] = $GLOBALS['egw_info']['server']['login_logo_url']?$GLOBALS['egw_info']['server']['login_logo_url']:'http://www.eGroupWare.org';
	if (substr($var['logo_url'],0,4) != 'http')
	{
		$var['logo_url'] = 'http://'.$var['logo_url'];
	}
	$var['logo_title'] = $GLOBALS['egw_info']['server']['login_logo_title']?$GLOBALS['egw_info']['server']['login_logo_title']:'www.eGroupWare.org';
	$tmpl->set_var($var);

	/* language section if activated in site config */
	if (@$GLOBALS['egw_info']['server']['login_show_language_selection'])
	{
		$select_lang = '<select name="lang" onchange="'."if (this.form.login.value && this.form.passwd.value) this.form.submit(); else location.href=location.href+(location.search?'&':'?')+'lang='+this.value".'">';
		foreach ($GLOBALS['egw']->translation->get_installed_langs() as $key => $name)	// if we have a translation use it
		{
			$select_lang .= "\n\t".'<option value="'.$key.'"'.($key == $GLOBALS['egw_info']['user']['preferences']['common']['lang'] ? ' selected="selected"' : '').'>'.$name.'</option>';
		}
		$select_lang .= "\n</select>\n";
		$tmpl->set_var(array(
			'lang_language' => lang('Language'),
			'select_language' => $select_lang,
		));
	}
	else
	{
		$tmpl->set_block('login_form','language_select');
		$tmpl->set_var('language_select','');
	}

	/********************************************************\
	* Check if authentification via cookies is allowed       *
	* and place a time selectbox, how long cookie is valid   *
	\********************************************************/

	if($GLOBALS['egw_info']['server']['allow_cookie_auth'])
	{
		$html =& CreateObject('phpgwapi.html'); /* Why the hell was nobody useing this here before??? */
		$tmpl->set_block('login_form','remember_me_selection');
		$tmpl->set_var('lang_remember_me',lang('Remember me'));
		$tmpl->set_var('select_remember_me',$html->select('remember_me', 'forever', array(
			false => lang('not'),
			'1hour' => lang('1 Hour'),
			'1day' => lang('1 Day'),
			'1week'=> lang('1 Week'),
			'1month' => lang('1 Month'),
			'forever' => lang('Forever')),true
		));
	}
	else
	{
		/* trick to make remember_me section disapear */
		$tmpl->set_block('login_form','remember_me_selection');
		$tmpl->set_var('remember_me_selection','');
	}

	$tmpl->set_var('autocomplete', ($GLOBALS['egw_info']['server']['autocomplete_login'] ? 'autocomplete="off"' : ''));

	$tmpl->pfp('loginout','login_form');
?>
