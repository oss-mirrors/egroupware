<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
	Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

	phpGroupWare - http://www.phpgroupware.org

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


	/* DEFAULT/FALLBACK BLOB/TEXT/TEXTAREA PLUGIN */
	$this->plugins['def_blob']['name'] 			= 'def_blob';
	$this->plugins['def_blob']['title']			= 'default area';
	$this->plugins['def_blob']['version']		= '1.0';
	$this->plugins['def_blob']['enable']		= 1;
	$this->plugins['def_blob']['db_field_hooks']	= array
	(
		'text',
		'blob',
	);

	function plg_fi_def_blob($field_name,$value, $config)
	{
		$input='<textarea name="'.$field_name.'" style="width:100%; height:200">'.$value.'</textarea>';

		return $input;
	}


	/* DEFAULT/FALLBACK VARCHAR PLUGIN */
	$this->plugins['def_string']['name'] 			= 'def_string';
	$this->plugins['def_string']['title']			= 'default varchar';
	$this->plugins['def_string']['version']		= '1.0';
	$this->plugins['def_string']['enable']			= 1;
	$this->plugins['def_string']['db_field_hooks']	= array
	(
		'string',
	);

	function plg_fi_def_string($field_name, $value, $config)
	{
		$input='<input type="text" name="'.$field_name.'" input_max_length" value="'.$value.'">';

		return $input;

	}	

	/* DEFAULT/FALLBACK INTEGER PLUGIN */
	$this->plugins['def_int']['name'] 			= 'def_int';
	$this->plugins['def_int']['title']			= 'default int plugin';
	$this->plugins['def_int']['version']		= '1.0';
	$this->plugins['def_int']['enable']			= 1;
	$this->plugins['def_int']['db_field_hooks']	= array
	(
		'int',	
	);

	function plg_fi_def_int($field_name,$value, $config)
	{
		$input='<input type="text" name="'.$field_name.'" size="10" value="'.$value.'">';

		return $input;
	}

	/* DEFAULT/FALLBACK TIMESPAMP/DATE PLUGIN */
	$this->plugins['def_timestamp']['name'] 			= 'def_timestamp';
	$this->plugins['def_timestamp']['title']			= 'default timestamp plugin';
	$this->plugins['def_timestamp']['version']		= '1.0';
	$this->plugins['def_timestamp']['enable']			= 1;
	$this->plugins['def_timestamp']['db_field_hooks']	= array
	(
		'timestamp',	
	);

	function plg_fi_def_timestamp($field_name,$value, $config)
	{

		global $local_bo;
		$input=$local_bo->format_date($value);

		return $input;
	}


?>
