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
   $this->registry->plugins['formatstring']['name']				= 'formatstring';
   $this->registry->plugins['formatstring']['title']			= 'formatstring';
   $this->registry->plugins['formatstring']['version']			= '0.1';
   $this->registry->plugins['formatstring']['enable']			= 1;
   $this->registry->plugins['formatstring']['author']			= 'Pim Snel';
   $this->registry->plugins['formatstring']['description']		= 
   'This plugin echo a formatted string in all modes. You can embed field values using $$fieldname$$';
   $this->registry->plugins['formatstring']['element_type']	= 'lay-out';//no defined element type is automaticly table_field

   $this->registry->plugins['formatstring']['config2'] = array
   (
	  'fstring' => array(
		 'name' => 'fstring',
		 'label' => lang('Format String'),
		 'type' => 'area',
	  ),
   );


   $this->registry->plugins['formatstring']['config_execute']		= false;

?>
