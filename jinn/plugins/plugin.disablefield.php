<?php
	/*
	JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
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

	---------------------------------------------------------------------

    /*-------------------------------------------------------------------
	Disable This Field PLUGIN
	-------------------------------------------------------------------*/
	$this->plugins['disable_field']['name'] 			= 'disable_field';
	$this->plugins['disable_field']['title']			= 'Disable Field';
	$this->plugins['disable_field']['author']			= 'Pim Snel';
	$this->plugins['disable_field']['version']			= '1.0';
	$this->plugins['disable_field']['enable']			= 1;
	$this->plugins['disable_field']['description']		= 'This just hides the input field for users';
	$this->plugins['disable_field']['db_field_hooks']	= array
	(
		'string',
		'int',
		'blob',
		'date',
		'timestamp'
	);


	function plg_fi_disable_field($field_name,$value, $config,$attr_arr)
	{
	   return '__disabled__';
	}
	
	function plg_ro_disable_field($field_name,$value)
	{
	   return '__disabled__';
	}
	
	function plg_bv_disable_field($field_name,$value, $config,$attr_arr)
	{
	   return '__disabled__';
	}

 ?>
