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
   $this->registry->plugins['mediabrowser']['name']				= 'mediabrowser';
   $this->registry->plugins['mediabrowser']['title']			= 'mediabrowser';
   $this->registry->plugins['mediabrowser']['version']			= '0.1';
   $this->registry->plugins['mediabrowser']['enable']			= 1;
   $this->registry->plugins['mediabrowser']['author']			= 'Pim Snel';
   $this->registry->plugins['mediabrowser']['description']		= 
   'browse the media object   ';
   $this->registry->plugins['mediabrowser']['element_type']	= 'lay-out';//no defined element type is automaticly table_field
   $this->registry->plugins['mediabrowser']['xxdb_field_hooks']	= array
   (
	  'text',
	  'string'
   );

   $this->registry->plugins['mediabrowser']['config2'] = array
   (
	  'objname' => array(
		 'name' => 'objname',
		 'label' => lang('Name of media site object'),
		 'type' => 'text',
		 'size' => 100
	  ),
   );


   $this->registry->plugins['mediabrowser']['config_execute']		= false;

?>
