<?php
	/**************************************************************************\
	* phpGroupWare                                                             *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	function parse_navbar($force = False)
	{
		$tpl = createobject('phpgwapi.Template',PHPGW_TEMPLATE_DIR);

		$tpl->set_file(
			array(
				'navbar' => 'navbar.tpl'
			)
		);

		$var['img_root'] = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/verdilak/images';
		$var['table_bg_color'] = $GLOBALS['phpgw_info']['theme']['navbar_bg'];
		$applications = '';
		while ($app = each($GLOBALS['phpgw_info']['navbar']))
		{
			if ($app[1]['title'] != 'Home' && $app[1]['title'] != 'preferences' && ! ereg('About',$app[1]['title']) && $app[1]['title'] != 'Logout')
			{
				if ($GLOBALS['phpgw_info']['user']['preferences']['common']['navbar_format'] != 'text')
				{
					$title = '<img src="' . $app[1]['icon'] . '" alt="' . lang($app[1]['title']) . '" title="'
						. lang($app[1]['title']) . '" border="0">';
				}

				if ($GLOBALS['phpgw_info']['user']['preferences']['common']['navbar_format'] != 'icons')
				{
					$title .= '<br>' . lang($app[1]['title']);
				}
				$applications .= '<br><a href="' . $app[1]['url'] . '"';
				if (isset($GLOBALS['phpgw_info']['flags']['navbar_target']) &&
				    $GLOBALS['phpgw_info']['flags']['navbar_target'])
				{
					$applications .= ' target="' . $GLOBALS['phpgw_info']['flags']['navbar_target'] . '"';
				}
				$applications .= '>' . $title . '</a>';
				unset($title);
			}
		}
		$var['applications'] = $applications;

		if (isset($GLOBALS['phpgw_info']['theme']['special_logo']))
		{
			$var['logo'] = $GLOBALS['phpgw_info']['theme']['special_logo'];
		}
		else
		{
			$var['logo'] = 'logo.gif';
		}

		$var['home_link'] = $GLOBALS['phpgw_info']['navbar']['home']['url'];
		$var['preferences_link'] = $GLOBALS['phpgw_info']['navbar']['preferences']['url'];
		$var['logout_link'] = $GLOBALS['phpgw_info']['navbar']['logout']['url'];
		$var['help_link'] = $GLOBALS['phpgw_info']['navbar']['about']['url'];

		$ir = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/verdilak/images';
		if ($GLOBALS['phpgw_info']['flags']['currentapp'] == 'home')
		{
			$var['welcome_img'] = $ir . '/welcome-red.gif';
		}
		else
		{
			$var['welcome_img'] = $ir . '/welcome-grey.gif';
		}

		if ($phpgw_info['flags']['currentapp'] == 'preferences')
		{
			$var['preferences_img'] = $ir . '/preferences-red.gif';
		}
		else
		{
			$var['preferences_img'] = $ir . '/preferences-grey.gif';
		}
		$var['logout_img'] = $ir . '/logout-grey.gif';

		$var['powered_by'] = lang('Powered by phpGroupWare version x',$GLOBALS['phpgw_info']['server']['versions']['phpgwapi']);

		if (isset($GLOBALS['phpgw_info']['navbar']['admin']) && isset($GLOBALS['phpgw_info']['user']['preferences']['common']['show_currentusers']))
		{
			$db  = $GLOBALS['phpgw']->db;
			$db->query("select count(session_id) from phpgw_sessions where session_flags != 'A'");
			$db->next_record();
			$var['current_users'] = '<a style="font-family: Geneva,Arial,Helvetica,sans-serif; font-size: 12pt;" href="'
				. $GLOBALS['phpgw']->link('/index.php','menuaction=admin.uicurrentsessions.list_sessions') . '">&nbsp;'
				. lang('Current users') . ': ' . $db->f(0) . '</a>';
		}
		$var['user_info'] = $GLOBALS['phpgw']->common->display_fullname() . ' - '
                             . lang($GLOBALS['phpgw']->common->show_date(time(),'l')) . ' '
                             . lang($GLOBALS['phpgw']->common->show_date(time(),'F')) . ' '
                             . $GLOBALS['phpgw']->common->show_date(time(),'d, Y');

		// Maybe we should create a common function in the phpgw_accounts_shared.inc.php file
		// to get rid of duplicate code.
		if ($GLOBALS['phpgw_info']['user']['lastpasswd_change'] == 0)
		{
			$api_messages = lang('You are required to change your password during your first login')
                      . '<br> Click this image on the navbar: <img src="'
                      . $GLOBALS['phpgw']->common->image('preferences','navbar.gif').'">';
		}
		else if ($GLOBALS['phpgw_info']['user']['lastpasswd_change'] < time() - (86400*30))
		{
			$api_messages = lang('it has been more then x days since you changed your password',30);
		}
 
		// This is gonna change
		if (isset($cd))
		{
			$var['messages'] = $api_messages . '<br>' . checkcode($cd);
		}
		$tpl->set_var($var);
		$tpl->pfp('out','navbar');
		// If the application has a header include, we now include it
		if (!@$GLOBALS['phpgw_info']['flags']['noappheader'] && $GLOBALS['HTTP_GET_VARS']['menuaction'])
		{
			if (is_array($GLOBALS['obj']->public_functions) && $GLOBALS['obj']->public_functions['header'])
			{
				eval("\$GLOBALS['obj']->header();");
			}
		}
		$GLOBALS['phpgw']->common->hook('after_navbar');
		return;
	}

	function parse_navbar_end()
	{
		$tpl = createobject('phpgwapi.Template',PHPGW_TEMPLATE_DIR);
  
		$tpl->set_file(
			array(
				'footer' => 'footer.tpl'
			)
		);
		$var = Array(
			'img_root'	=> $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/verdilak/images',
			'table_bg_color'	=> $GLOBALS['phpgw_info']['theme']['navbar_bg'],
			'version'	=> $GLOBALS['phpgw_info']['server']['versions']['phpgwapi']
		);
		$tpl->set_var($var);
		$GLOBALS['phpgw']->common->hook('navbar_end');
		echo $tpl->pfp('out','footer');
	}
