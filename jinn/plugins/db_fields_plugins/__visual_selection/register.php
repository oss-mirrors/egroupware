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
   $this->registry->plugins['visual_selection']['name']			= 'visual_selection';
   $this->registry->plugins['visual_selection']['title']			= 'Visual Selection Plugin';
   $this->registry->plugins['visual_selection']['version']			= '0.1';
   $this->registry->plugins['visual_selection']['enable']			= 1;
   $this->registry->plugins['visual_selection']['author']			= 'Rob van Kraanen';
   $this->registry->plugins['visual_selection']['description']		= 
   'This plugin wil give the user the posibility to choose a picture which represent te value.
   This value will be stored in the database.';

   $this->registry->plugins['visual_selection']['help']              = '
   <p>
   You can select an image wich replesent tje value stored in the database
</p>
   ';
   
   $this->registry->plugins['visual_selection']['helper_fields_substring'] = 'GMI'; //this is for multiple records insert
   $this->registry->plugins['visual_selection']['db_field_hooks']	= array
   (
       'text',
	   'string'
	);
	$num_images = array(1,2,3,4);

	//
	// We need a template in stead of this auto configure array
	$this->registry->plugins['visual_selection']['config2']		= array
	(
	   'multi1'=>array(
		  'name'=>'multi1',
		  'type'=>'multi',
		  'items'=>array(
			 'imgfile' => array(
				'name' => 'imgfile',
				'label' => lang('Image for selection'),
				'type' => 'sitefile',
				'subdir' =>'visual_selection',
				'allowempty' => false
			 ),
			 'option_value' => array(
				'name' => 'option_value',
				'label' => lang('Value for option'),
				'type' => 'text',
				'size' => 50
			 )
		  )
	   )
	);

 ?>
