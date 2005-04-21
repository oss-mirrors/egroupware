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

	$this->registry->plugins['default_']['name'] 			= 'default_';
	$this->registry->plugins['default_']['title']			= 'default';
	$this->registry->plugins['default_']['version']			= '1.0';
	$this->registry->plugins['default_']['author']			= 'Pim Snel, Lex Vogelaar';
	$this->registry->plugins['default_']['enable']			= 1;
	$this->registry->plugins['default_']['default']			= 1;
	$this->registry->plugins['default_']['description']		= 'Last Resort Fallback field plugin for handling all fieldtypes';
	//$this->registry->plugins['default_']['db_field_hooks']	= ();

	 $this->registry->plugins['default_']['config']		= array
	 (
		'New_height_in_pixels' => array('100','text','maxlength=3 size=3'), 
	 );
	 
	 $this->registry->plugins['default_']['config_help']		= array
	 (
		'New_height_in_pixels' => 'Default height is 100 pixels', 
	 );

?>