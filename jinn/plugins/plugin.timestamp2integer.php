<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
	Copyright (C)2002, 2004 Pim Snel <pim@lingewoud.nl>

	eGroupWare - http://www.egroupware.org

	This file is part of JiNN

	JiNN is free software; you can redistribute it and/or modify it under
	the terms of the GNU General Public License as published by the Free
	Software Foundation; either version 2 of the License, or (at your 
	option) any later version.

	JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
	WARRANTY; without even the implied warranty of MERCHANTABILITY or 
	FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License 
	along with JiNN; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
	*/

	/* 
	plugin.timestamp2integer.php contains the standard image-upload plugin for 
	JiNN number off standardly available 
	plugins for JiNN. 
	*/

	$this->plugins['timestamp2integer']['name']				= 'timestamp2integer';
	$this->plugins['timestamp2integer']['title']			= 'Timestamp2integer plugin';
	$this->plugins['timestamp2integer']['version']			= '0.1.2';
	$this->plugins['timestamp2integer']['description']		= 'create timestamp2integer input box and timestamp2integer storage method.';
	$this->plugins['timestamp2integer']['author']			= 'Pim Snel';
	$this->plugins['timestamp2integer']['enable']			= 1;
	$this->plugins['timestamp2integer']['db_field_hooks']	= array('int');
	
	function plg_fi_timestamp2integer($field_name,$value,$config,$attr_arr)
	{	
		global $local_bo;
//		$stripped_name=substr($field_name,6);	
		
		if($value)
		{
		   $input.='<strong>'.$value.'</strong><br/>';
		   $input.=lang('Give new timestamp').' <input type="checkbox" value="1" name="ST2IN2'.$field_name.'">';
		   $input.='<input type="hidden" value="1" name="'.$field_name.'">';
		   $input.='<input type="hidden" value="'.$value.'" name="ST2IN1'.$field_name.'">';
		}
		else
		{
		   $input.='<input type="hidden" value="1" name="'.$field_name.'">';
		   $input.=lang('automatic');
		}
		
		return $input;
	}

	function plg_sf_timestamp2integer($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	{
		global $local_bo;

		$field_1=$_POST['ST2IN1'.$field_name];//real value
		$field_2=$_POST['ST2IN2'.$field_name];// boolian for new timestamp
		
		if( !$field_1 || $field_2)
		{
		   $time=time();
		   return $time;
		}
		else
		{
			return $field_1;
		}
	}


?>
