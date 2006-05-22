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
   $this->registry->plugins['layout_image']['name']			= 'layout_image';
   $this->registry->plugins['layout_image']['title']			= 'Image';
   $this->registry->plugins['layout_image']['version']			= '0.1';
   $this->registry->plugins['layout_image']['enable']			= 1;
   $this->registry->plugins['layout_image']['author']			= 'Pim Snel';
   $this->registry->plugins['layout_image']['description']		= 
   'This plugin simply renders a vertical line in the form';
   $this->registry->plugins['layout_image']['element_type']	= 'lay-out';//no defined element type is automaticly table_field

   // We need a template in stead of this auto configure array
   $this->registry->plugins['layout_image']['config2']		= array
   (
	  'imagefile' => array(
		 'name' => 'imagefile',
		 'label' => lang('Image file'),
		 'type' => 'sitefile',
		 'subdir' =>'layoutimage',
		 'allowempty' => false
	  ),

   );

?>
