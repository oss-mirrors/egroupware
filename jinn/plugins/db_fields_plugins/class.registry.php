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
	class db_fields_registry
	{
		var $plugins = array();
		var $aliases = array();
		
		function db_fields_registry()
		{
			$this->aliases['boolian'] 					= 'boolean';			//REPLACEMENT Alias
			$this->aliases['def_auto'] 					= 'default_auto';		//REPLACEMENT Alias
			$this->aliases['def_binary'] 				= 'default_binary';		//REPLACEMENT Alias
			$this->aliases['def_blob'] 					= 'default_blob';		//REPLACEMENT Alias
			$this->aliases['def_date'] 					= 'default_date';		//REPLACEMENT Alias
			$this->aliases['def_float'] 				= 'default_float';		//REPLACEMENT Alias
			$this->aliases['def_int'] 					= 'default_int';		//REPLACEMENT Alias
			$this->aliases['def_string'] 				= 'default_string';		//REPLACEMENT Alias
			$this->aliases['def_timestamp'] 			= 'default_timestamp';	//REPLACEMENT Alias
			$this->aliases['hidefield'] 				= 'disable';			//REPLACEMENT Alias
			$this->aliases['disable_field'] 			= 'disable';			//REPLACEMENT Alias
			$this->aliases['pre_string'] 				= 'preset_string';		//REPLACEMENT Alias
			$this->aliases['imagepath'] 				= 'filemanager';		//REPLACEMENT Alias
			
		}
	}
?>