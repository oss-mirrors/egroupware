<?php
/*
	JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
	Author:	Gabriël Ramaker, Lex Vogelaar for Lingewoud
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

   $this->registry->plugins['colorlab']['name']				= 'colorlab';
   $this->registry->plugins['colorlab']['title']		    = 'Colorlab';
   $this->registry->plugins['colorlab']['version']			= '0.02';
   $this->registry->plugins['colorlab']['author']			= 'Gabriël Ramaker';
   $this->registry->plugins['colorlab']['description']		= 'Colorlab plugin for JiNN with Flash 6 front-end. This version is not (fully) functional/stable, use at own risk.<br/>   The Flash(.Fla) source file is located in the \'colorlab\' folder in the JiNN \'plugins\' folder.<br/>';
   $this->registry->plugins['colorlab']['enable']			= 1;
   $this->registry->plugins['colorlab']['db_field_hooks']	= array('string', 'blob');
   $this->registry->plugins['colorlab']['config']		    = array(
	  'Available_colors' => array(array('Unlimited','User-defined'),'select',''),
	  'User_defined_colors' => array('#FFFFFF,#000000,#CCCCCC','area','')
   );
?>