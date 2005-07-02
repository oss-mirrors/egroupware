<?php
	/**************************************************************************\
	* eGroupWare - Interserver XML-RPC/SOAP Test app                           *
	* http://www.eGroupWare.org                                                *
	* This file written by Miles Lott <milos@groupwhere.org                    *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['egw_info'] = array();
	$GLOBALS['egw_info']['flags'] = array(
		'currentapp' => 'xmlrpc'
	);

	include('../header.inc.php');

	$server_id  = $_POST['server_id'];
	$xsessionid = $_POST['xsessionid'];
	$xkp3       = $_POST['xkp3'];

	$is = CreateObject('phpgwapi.interserver',intval($server_id));

	function applist()
	{
		$select  = "\n" .'<select name="xappname" >' . "\n";
		if($default)
		{
			$select .= '<option value="">' . lang('Please Select') . '</option>'."\n";
		}

		$GLOBALS['egw']->db->query("SELECT * FROM phpgw_applications WHERE app_enabled<3",__LINE__,__FILE__);
		if($GLOBALS['egw']->db->num_rows())
		{
			while ($GLOBALS['egw']->db->next_record())
			{
				$select .= '<option value="' . $GLOBALS['egw']->db->f('app_name') . '"';
				if($GLOBALS['egw']->db->f('app_name') == $_POST['xappname'])
				{
					$select .= ' selected';
				}
				$select .= '>' . $GLOBALS['egw']->db->f('app_name') . '</option>'."\n";
			}
		}
		$select .= '</select>'."\n";
		$select .= '<noscript><input type="submit" name="' . $name . '_select" value="True"></noscript>' . "\n";

		return $select;
	}

	if(!$xsessionid && !$xusername)
	{
		$xserver_name = $_SERVER['HTTP_HOST'];
	}
	else
	{
		$xserver_name = $_POST['xserver_name'];
	}

	/* _debug_array($is->server); */
	if($_POST['login'])
	{
		if($_POST['xserver'])
		{
			$is->send(
				'system.login', array(
					'server_name' => $_POST['xserver_name'],
					'username'    => $_POST['xusername'],
					'password'    => $_POST['xpassword']
				),
				$is->server['server_url'],
				True
			);
		}
		else
		{
			$is->send(
				'system.login', array(
					'domain'      => $_POST['xserver_name'],
					'username'    => $_POST['xusername'],
					'password'    => $_POST['xpassword']
				),
				$is->server['server_url'],
				True
			);
		}
		//_debug_array($is->result);
		$xserver_name = $is->result['domain'];
		$xsessionid = $is->result['sessionid'];
		$xkp3       = $is->result['kp3'];
	}
	elseif($_POST['logout'])
	{
		$is->send(
			'system.logout', array(
				'sessionid' => $xsessionid,
				'kp3'       => $xkp3
			),
			$is->server['server_url'],
			True
		);
		$xsessionid = '';
		$xkp3       = '';
	}
	elseif($_POST['methods'])
	{
		if(!$server_id)
		{
			echo '<br>Please select a server...';
		}

		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;

		if($xsessionid & $_POST['xappname'])
		{
			$method_str = $_POST['xappname'] . '.bo' . $_POST['xappname'] . '.list_methods';
			$server_id ? $is->send($method_str,'xmlrpc',$is->server['server_url']) : '';
		}
		else
		{
			$server_id ? $is->send('system.listMethods','',$is->server['server_url']) : '';
		}
	}
	elseif($_POST['apps'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;

		$is->send('system.listApps','',$is->server['server_url']);
	}
	elseif($_POST['users'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;

		$is->send('system.listUsers','',$is->server['server_url']);
	}
	elseif($_POST['bogus'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;

		$is->send('system.bogus','',$is->server['server_url']);
	}
	elseif($_POST['addressbook'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;
		/* TODO - Adjust the values below as desired */
		$is->send(
			'addressbook.boaddressbook.search',array(
				'start' => 1,
				'limit' => 5,
				//'fields' => array('n_given','n_family','cat_id','bday','last_mod','custom1'),
				'query'  => '',
				'filter' => '',
				'sort'   => '',
				'order'  => ''
			),
			$is->server['server_url'],
			True
		);
	}
	elseif($_POST['infolog'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;
		/* TODO - Adjust the values below as desired */
		$is->send(
			'infolog.boinfolog.search',array(
				'start' => 1,
				'limit' => 5,
				'query'  => '',
				'filter' => '',
				'sort'   => '',
				'order'  => ''
			),
			$is->server['server_url'],
			True
		);
	}
	elseif($_POST['calendar'])
	{
		$is->sessionid = $xsessionid;
		$is->kp3 = $xkp3;
		/* TODO - Adjust the values below as desired */
		$is->send(
			'calendar.bocalendar.store_to_cache', array(
				'start' => date('Y-m-d').'T00:00:00',
				'end'   => date('Y-m-d').'T00:00:00',
			),
			$is->server['server_url'],
			True
		);
	}

	$GLOBALS['egw']->template->set_file('interserv','interserv.tpl');

	$GLOBALS['egw']->template->set_var('action_url',$GLOBALS['egw']->link('/xmlrpc/interserv.php'));
	$GLOBALS['egw']->template->set_var('lang_title',lang('eGroupWare XML-RPC/SOAP Client<->Server and Server<->Server Test (SOAP pending...)'));
	$GLOBALS['egw']->template->set_var('lang_select_target',lang('Select target server'));
	$GLOBALS['egw']->template->set_var('lang_st_note',lang('Configure using admin - Peer servers'));
	$GLOBALS['egw']->template->set_var('lang_this_servername',lang('Servername/Domain'));
	$GLOBALS['egw']->template->set_var('lang_sd_note',lang('(optional: set domain for user/client login, required: set this servername for server login)'));
	$GLOBALS['egw']->template->set_var('lang_addressbook',lang('Addressbook test'));
	$GLOBALS['egw']->template->set_var('lang_calendar',lang('Calendar test'));
	$GLOBALS['egw']->template->set_var('lang_infolog',lang('Infolog test'));
	$GLOBALS['egw']->template->set_var('lang_login',lang('Login'));
	$GLOBALS['egw']->template->set_var('lang_logout',lang('Logout'));
	$GLOBALS['egw']->template->set_var('lang_list',lang('List'));
	$GLOBALS['egw']->template->set_var('lang_apps',lang('Apps'));
	$GLOBALS['egw']->template->set_var('lang_bogus',lang('Bogus Request'));
	$GLOBALS['egw']->template->set_var('lang_users',lang('Users'));
	$GLOBALS['egw']->template->set_var('lang_methods',lang('Methods'));
	$GLOBALS['egw']->template->set_var('lang_username',lang('Username'));
	$GLOBALS['egw']->template->set_var('lang_password',lang('Password'));
	$GLOBALS['egw']->template->set_var('lang_session',lang('Assigned sessionid'));
	$GLOBALS['egw']->template->set_var('lang_kp3',lang('Assigned kp3'));
	$GLOBALS['egw']->template->set_var('login_type',lang('Server<->Server'));
	$GLOBALS['egw']->template->set_var('note',lang('NOTE: listapps and listusers are disabled by default in xml_functions.php') . '.');

	$GLOBALS['egw']->template->set_var('xserver',$_POST['xserver'] ? ' checked' : '');
	$GLOBALS['egw']->template->set_var('xsessionid',$xsessionid ? $xsessionid : lang('none'));
	$GLOBALS['egw']->template->set_var('xkp3',$xkp3 ? $xkp3 : lang('none'));
	$GLOBALS['egw']->template->set_var('xusername',$xusername);
	$GLOBALS['egw']->template->set_var('xpassword',$xpassword);
	$GLOBALS['egw']->template->set_var('xserver_name',$xserver_name);
	$GLOBALS['egw']->template->set_var('server_list',$is->formatted_list($server_id));
	$GLOBALS['egw']->template->set_var('method_type',(($xsessionid == lang('none')) || !$xsessionid) ? lang('System') . ' ' : lang('App') . ' ');
	$GLOBALS['egw']->template->set_var('applist',(($xsessionid == lang('none')) || !$xsessionid) ? '' : 'for&nbsp;' . applist());

	$GLOBALS['egw']->template->pfp('out','interserv');

	$GLOBALS['egw']->common->phpgw_footer();
?>
