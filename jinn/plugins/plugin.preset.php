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

	/* $Id$

	/**
	* PRESET VARCHAR PLUGIN 
	*/
	$this->plugins['pre_string']['name'] 			= 'pre_string';
	$this->plugins['pre_string']['title']			= 'preset value varchar';
	$this->plugins['pre_string']['author']			= 'Pim Snel';
	$this->plugins['pre_string']['description']		= 'Field plugin for assigning a preset value to string/varchar fields';
	$this->plugins['pre_string']['version']			= '0.1';
	$this->plugins['pre_string']['enable']			= 1;
	$this->plugins['pre_string']['db_field_hooks']	= array
	(
	   'string',
	   'blob'
	);

	$this->plugins['pre_string']['config']		= array
	 (
		'Preset_Value' => array('','area',''), 
	 );
	 
	 $this->plugins['pre_string']['config_help']		= array
	 (
		'Preset_Value' => 'Fill in the desired preset (default) value', 
	 );

	function plg_fi_pre_string($field_name, $value, $config,$attr_arr)
	{
	   $input='<input type="hidden" name="'.$field_name.'" value="'.strip_tags($config['Preset_Value']).'">';
		return $input;
	}	

	function plg_ro_pre_string($field_name,$value)
	{
	   return '__hide__';
	}

	function plg_bv_pre_string($field_name,$value, $config,$attr_arr)
	{
	   return '__hide__';
	}
?>
