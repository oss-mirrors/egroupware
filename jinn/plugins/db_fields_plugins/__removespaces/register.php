<?php
/*
	JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
	Author:	Pim Snel for Lingewoud
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
   $this->registry->plugins['removespaces']['name']			= 'removespaces';
   $this->registry->plugins['removespaces']['title']			= 'Remove Spaces';
   $this->registry->plugins['removespaces']['author']			= 'Pim Snel';
   $this->registry->plugins['removespaces']['version']			= '1.0.0rc';
   $this->registry->plugins['removespaces']['description']		= 'Make the user choose for a new stamp of saving the exiting stamp';
   $this->registry->plugins['removespaces']['enable']			= 1;
   $this->registry->plugins['removespaces']['db_field_hooks']	= array('string');

   $this->registry->plugins['removespaces']['config']		= array
   (
	  'Default_action'=> array( array('Leave value untouched','New Time Stamp')  ,'select',''),
	  'Display_format'=> array( array('Y-M-d H:i:s','y-m-d H:i:s','d-M-Y H:i:s','d-m-y H:i:s')  ,'select',''),
	  'Allow_users_to_choose_action'=> array( array('False','True')  ,'select',''),
   );

   $this->registry->plugins['removespaces']['config_help'] = array
   (
	  'Default_action'=>'Leave untouched keeps the save removespaces when the record is updated, else always a new stamp is given to the record.',
	  'Allow_users_to_choose_action' =>'Let users choose the to leave is untouched or not.'
   );

?>
