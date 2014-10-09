<?php
	/***************************************************************************\
	* eGroupWare - SambaAdmin                                                   *
	* http://www.egroupware.org                                                 *
	* http://www.linux-at-work.de                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class uiuserdata
	{
		var $public_functions = array
		(
			'editUserData'	=> True,
			'saveUserData'	=> True
		);

		function __construct()
		{
			if (!$GLOBALS['egw_info']['user']['apps']['admin'])
			{
				throw new egw_exception_no_permission_admin();
			}
			$this->t		= new Template(common::get_tpl_dir('sambaadmin'));
			$this->bosambaadmin	= CreateObject('sambaadmin.bosambaadmin');
		}

		function display_app_header()
		{
			common::egw_header();
			echo parse_navbar();
		}

		function editUserData($_useCache='0')
		{
			$accountID = $_GET['account_id'];

			$this->display_app_header();

			$this->translate();

			$this->t->set_file(array("editUserData" => "edituserdata.tpl"));
			$this->t->set_block('editUserData','form','form');

			$linkData = array
			(
				'menuaction'	=> 'sambaadmin.uiuserdata.saveUserData',
				'account_id'	=> $accountID
			);
			$this->t->set_var("form_action", $GLOBALS['egw']->link('/index.php',$linkData));

			// only when we show a existing user
			if(($userData = $this->bosambaadmin->getUserData($accountID, $_useCache)))
			{
				$charset = translation::charset();
				$this->t->set_var('displayname',htmlspecialchars($userData["displayname"],ENT_QUOTES,$charset));
				$this->t->set_var('sambahomepath',htmlspecialchars($userData["sambahomepath"],ENT_QUOTES,$charset));
				$this->t->set_var('sambahomedrive',htmlspecialchars($userData['sambahomedrive'],ENT_QUOTES,$charset));
				$this->t->set_var('sambalogonscript',htmlspecialchars($userData['sambalogonscript'],ENT_QUOTES,$charset));
				$this->t->set_var('sambaprofilepath',htmlspecialchars($userData['sambaprofilepath'],ENT_QUOTES,$charset));

				$this->t->set_var("uid",rawurlencode($userData["dn"]));
			}

			$this->t->pfp("out","form");
		}

		function saveUserData()
		{
			if ($_POST['save'] || $_POST['apply'])
			{
				$formData = array
				(
					'displayname'		=> get_var('displayname',array('POST')),
					'sambahomepath'		=> get_var('sambahomepath',array('POST')),
					'sambahomedrive'	=> get_var('sambahomedrive',array('POST')),
					'sambalogonscript'	=> get_var('sambalogonscript',array('POST')),
					'sambaprofilepath'	=> get_var('sambaprofilepath',array('POST'))
				);

				$this->bosambaadmin->saveUserData(get_var('account_id',array('GET')), $formData);
			}
			if ($_POST['apply'])
			{
				// read data fresh from ldap storage
				return $this->editUserData();
			}
			egw_framework::window_close();
		}

		function translate()
		{
			$this->t->set_var('lang_displayname',lang('displayname'));
			$this->t->set_var('lang_homepath',lang('homepath'));
			$this->t->set_var('lang_homedrive',lang('homedrive'));
			$this->t->set_var('lang_logonscript',lang('logonscript'));
			$this->t->set_var('lang_profilepath',lang('profilepath'));
			$this->t->set_var('lang_samba_config',lang('samba config'));
			$this->t->set_var("lang_save",lang("Save"));
			$this->t->set_var("lang_apply",lang("Apply"));
			$this->t->set_var("lang_cancel",lang("Cancel"));
		}
	}
