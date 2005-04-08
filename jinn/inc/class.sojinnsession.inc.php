<?php
	/*
	JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
	Author Lex Vogelaar <lex_vogelaar@users.sourceforge.net> for Lingewoud
	Copyright 2005 Pim Snel <pim@lingewoud.nl>

	eGroupWare - http://www.egroupware.org

	This file is part of JiNN

	JiNN is free software; you can redistribute it and/or modify it under
	the terms of the GNU General Public License as published by the Free
	Software Foundation; version 2 of the License.

	JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
	WARRANTY; without even the implied warranty of MERCHANTABILITY or 
	FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License 
	along with JiNN; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
	*/

	
	/*
	This class handles all session logic. It gets its information from the eGroupWare session, GET vars or POST vars.
	Every JiNN class can arbitrarily read/write to/from the session class using get/set functions
	*/
	class sojinnsession
	{
		var $sessionarray;
		
		//constructor
		function sojinnsession()
		{
//_debug_array('SoJiNNsession constructor called');
			$this->read_sessiondata();
		}
		
		function read_sessiondata()
		{
			$this->sessionarray = $GLOBALS['phpgw']->session->appsession('session_data','jinn');

			//override session variables with POST/GET data
			$_form = $_POST['form'] ? $_POST['form']   : $_GET['form'];
			$_site_id = $_POST['site_id'] ? $_POST['site_id']   : $_GET['site_id'];
			$_site_object_id = $_POST['site_object_id'] ? $_POST['site_object_id']    : $_GET['site_object_id'];
			
			if (($_form == 'main_menu') || (!empty($_site_id))) //fixme: moet dit geen && zijn i.p.v. || ?
			{
				if($_site_id != $this->get('site_id'))
				{
					$this->set('site_id', $_site_id);
					$this->set('site_object_id', '');
					unset($_POST[site_object_id]);
				}
			}
			
			if (($_form == 'main_menu') || (!empty($_site_object_id)))
			{
				$this->set('site_object_id', $_site_object_id);
			}
		}
		
		function get($var)
		{
			return $this->sessionarray[$var];
		}
		function set($var, $value)
		{
			$this->sessionarray[$var] = $value;
		}
		function save()
		{
			$GLOBALS['phpgw']->session->appsession('session_data','jinn',$this->sessionarray);
		}
	}
?>