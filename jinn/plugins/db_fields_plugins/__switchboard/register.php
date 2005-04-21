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
   $this->registry->plugins['switchboard']['name'] 				= 'switchboard';
   $this->registry->plugins['switchboard']['title']				= 'Switchboard';
   $this->registry->plugins['switchboard']['author']			= 'Pim Snel';
   $this->registry->plugins['switchboard']['version']			= '0.2';
   $this->registry->plugins['switchboard']['enable']			= 1;
   $this->registry->plugins['switchboard']['screenshot']		= 'switchboard.png'; 
   $this->registry->plugins['switchboard']['description']		= 'Switchboard with radio buttons.';
   $this->registry->plugins['switchboard']['help']				= '
   <p>Create as many switches with as many options as you like in the following notation:<br/>
   NameSwitchOne:option 1/option 2/option 3;</br>
   NameSwitchTwo:yes/no;
   NameSwitchThree:0/1/2/3/4/5;</p>
   <p>The above example produces the switchboard:</p>
   ';
   $this->registry->plugins['switchboard']['db_field_hooks']	= array
   (
	  'string',
	  'blob'
   );
   $this->registry->plugins['switchboard']['config']		= array
   (
	  'Switchboard_data'=>array("NameSwitchOne:option 1/option 2/option 3;\nNameSwitchTwo:yes/no;\nNameSwitchThree:0/1/2/3/4/5;",'area',''),
	  'Store_as'=>array(array('serialized array','string with seperation characters'),'select',''),
	  'Store_as'=>array(array('serialized array'),'select',''),
	 // 	  'Seperation_character_when_storing_as_string'=>array(array(';',',','|','/','[space]'),'select',''),
   );

?>