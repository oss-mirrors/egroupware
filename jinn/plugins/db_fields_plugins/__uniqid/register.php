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
	$this->registry->plugins['uniqid']['name'] 			= 'uniqid';
	$this->registry->plugins['uniqid']['title']			= 'Uniqid generator';
	$this->registry->plugins['uniqid']['author']			= 'Rob van Kraanen';
	$this->registry->plugins['uniqid']['version']			= '1.1';
	$this->registry->plugins['uniqid']['enable']			= 1;
	$this->registry->plugins['uniqid']['default']			= 1;
	$this->registry->plugins['uniqid']['description'] 	= 'Generates an uniqid, can be used for primary key';
	$this->registry->plugins['uniqid']['db_field_hooks']	= array
	(
	   'string','blob'
	);

?>
