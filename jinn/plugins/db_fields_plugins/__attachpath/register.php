<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Author:	Pim Snel
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

   $this->registry->plugins['attachpath']['name']			= 'attachpath';
   $this->registry->plugins['attachpath']['title']			= 'AttachmentPath plugin';
   $this->registry->plugins['attachpath']['version']		= '0.9.0';
   $this->registry->plugins['attachpath']['author']			= 'Pim Snel';
   $this->registry->plugins['attachpath']['enable']			= 1;
   $this->registry->plugins['attachpath']['description']	= 'Plugin with can upload files of any type and store the paths in the database seperated by semicolons.';
   $this->registry->plugins['attachpath']['db_field_hooks']	= array
   (
	  'string',
	  'text'
   );

   /* ATTENTION: spaces and special character are not allowed in config array 
   use underscores for spaces */
   $this->registry->plugins['attachpath']['config']		= array
   (
	  'Max_files' => array('3','text','maxlength=2 size=2'), 
	  'Max_attachment_size_in_megabytes_Leave_empty_to_have_no_limit' => array('','text','maxlength=3 size=3'),
	  'Activate_manual_path_input' => array( array('False','True'),'select',''),
	  'Store_full_path' => array( array('True','False'),'select','')
   );

   $this->registry->plugins['attachpath']['config_help']		= array
   (
	  'Max_files' => 'Defaults to three files', 
	  'Activate_manual_path_input' => 'With manual path the user can point to an existing file',
	  'Store_full_path' => 'If you select True, the complete path is stored in the database, else a path relative to the upload path is stored ' 
   );

?>
