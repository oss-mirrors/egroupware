<?php
	/**************************************************************************\
	* eGroupWare SiteMgr - Web Content Management                              *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

class module_login extends Module
{
	function module_login()
	{		
		$this->arguments = array(
			'security_redirect'=>array(
				'type' => 'textfield',
				'label' => lang('If nonsecure redirect to:') . '<br>'.
					lang('(leave blank to allow insecure logins)')
			),
			'login_dest' => array(
				'type' => 'select', 
				'label' => lang('Which application should be executed after login?'), 
				'options' => array()
			)
		);
		if (file_exists(EGW_SERVER_ROOT . '/registration'))
		{
			$this->arguments['registration'] = array(
				'type' => 'checkbox',
				'label' => lang('Display link to autoregistration below login form?')
			);
		}
		$this->properties = array();
		$this->title = lang('Login');
		$this->description = lang('This module displays a login form');
		$this->html =& CreateObject('phpgwapi.html');
	}
	
	function get_user_interface()
	{
		$installed_apps = array_keys($GLOBALS['egw_info']['apps']);
		sort($installed_apps);
		$installed_apps = array_flip($installed_apps);
		$chooseable_apps = array(
			false => lang('select one'),
// 			'user' => lang('users choice')
		);
		foreach($installed_apps as $app => $lang_app)
		{
			$chooseable_apps[$app] = lang($app);
		}
		// whipe out some weired apps for this content...
		unset($chooseable_apps['emailadmin']);
		unset($chooseable_apps['phpgwapi']);
		unset($chooseable_apps['manual']);
		unset($chooseable_apps['notifywindow']);
		unset($chooseable_apps['phpsysinfo']);
		unset($chooseable_apps['preferences']);
		unset($chooseable_apps['notifywindow']);
		unset($chooseable_apps['registration']);
		unset($chooseable_apps['skel']);
		unset($chooseable_apps['smfbridge']);
		$this->arguments['login_dest']['options'] = $chooseable_apps;
		
		$config =& CreateObject('phpgwapi.config','registration');
		$config = $config->read_repository();
		if (file_exists(EGW_SERVER_ROOT . '/registration') && $config['enable_registration'] != 'True')
		{
			$this->arguments['registration']['label'] .= '<br><font color="red">'.
				lang('<b>Autoregistration is not enabled / configured in the registration app !!!</b><br>You need to do so, to get the autoregistration link working.').
				'</font>';
		}
		return parent::get_user_interface();
	}

	function get_content(&$arguments,$properties)
	{
		if($GLOBALS['phpgw_info']['user']['userid'] == $GLOBALS['sitemgr_info'][anonymous_user])
		{
			if (empty($arguments['security_redirect']) || $_SERVER['HTTPS']){
				$content = '<form name="login" action="'.phpgw_link('/login.php').'" method="post">';
				$content .= '<input type="hidden" name="passwd_type" value="text">';
				$content .= '<input type="hidden" name="logindomain" value="'. $GLOBALS['egw_info']['user']['domain'] .'">';
				$content .= '<center><font class="content">' . lang('Login Name') .'<br>';
				$content .= '<input type="text" name="login" size="8" value=""><br>';
				$content .= lang('Password') . '<br>';
				$content .= '<input name="passwd" size="8" type="password"><br>';
				
				if($GLOBALS['egw_info']['server']['allow_cookie_auth'])
				{
					$content .= '<center><font class="content">' . lang("remember me");
					$content .= $this->html->select('remember_me', 'forever', array(
						false => lang('not'),
						'1hour' => lang('1 Hour'),
						'1day' => lang('1 Day'),
						'1week'=> lang('1 Week'),
						'1month' => lang('1 Month'),
						'forever' => lang('Forever')),true
					);
					$content .= '</font></center><br>';
				}
				
				switch($arguments['login_dest'])
				{
					case false :
						$forward = '/home';
						break;
					case 'sitemgr-link' :
						$forward = '/sitemgr-link/index.php?location='. $this->link();
						break;
					case 'user' :
						break;
					
					default :
						$forward = '/'.$arguments['login_dest']; 
				}
				$content .= '<input type="hidden" name="phpgw_forward" value="'. $forward. '">'; 
				$content .= '<input type="submit" value="' . lang('Login') .'" name="submitit">';
				$content .= '</font></center></form>';
			}
			else {
				$content .= '<center><font class="content">'. lang("Your connection is not safe.") .'<br>  ';
				$content .= '<a href="'.$arguments['security_redirect'].'">';
				$content .= lang('Click here to login through a safe connection.') . '</a></font></center><br><br>';
			}
			if (file_exists(EGW_SERVER_ROOT . '/registration') && $arguments['registration'])
			{
				$content .= '<center><font class="content">' . lang("Don't have an account?") .'  ';
				$content .= '<a href="'.phpgw_link('/registration/index.php').'"><br/>';
				$content .= lang('Register for one now.') . '</a></font></center>';
			}
		}
		else
		{
			$content  = '<form name="login" action="'.phpgw_link('/logout.php').'" method="post">';
			$content .= '<font class="content">'. lang('Loged in as:') .'<br>';
			$content .= ' ['.$GLOBALS['phpgw_info']['user']['userid']. '] '. $GLOBALS['phpgw_info']['user']['fullname'];
			$content .= '</font><br><br><center>';
			$content .= '<input type="submit" value="' . lang('Logout') .'" name="submitit">';
			$content .= '</center></form>';
		}
		return $content;
	}
}
