<?php
/*
	JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
	Author:	Lex Vogelaar for Lingewoud
	Copyright (C)2005 Pim Snel <pim@lingewoud.nl>

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
*/
		//this class was created to avoid all plugin classes loading in memory only to retrieve their names and configurations
	class db_fields_configurations
	{
		var $plugins;
		
		function db_fields_configurations()
		{
			$this->plugins['boolean']['name'] 			= 'boolean';
			$this->plugins['boolean']['title']			= 'Boolean';
			$this->plugins['boolean']['author']			= 'Pim Snel, Lex Vogelaar';
			$this->plugins['boolean']['version']		= '1.2';
			$this->plugins['boolean']['enable']			= 1;
			$this->plugins['boolean']['description']	= 'Input for on/off, yes/no, true/false etc....';
			$this->plugins['boolean']['db_field_hooks']	= array
			(
				'string',	
				'int',
			);
			$this->plugins['boolean']['config']		= array
			(
				'ON_input_display_value'=>array('yes','text','maxlength=20'),
				'OFF_input_display_value'=>array('no','text','maxlength=20'), 
				'ON_output_value_If_not_the_same_as_input_value'=>array('','text','maxlength=20'),
				'OFF_output_value_If_not_the_same_as_input_value'=>array('','text','maxlength=20'),
				'Default_value'=>array(array('ON','OFF','NOTHING'),'select',''),
			);
		}
	}
?>