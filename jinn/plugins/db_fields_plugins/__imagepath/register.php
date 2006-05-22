<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Author:	Pim Snel
   Copyright (C)2006 Pim Snel <pim@lingewoud.nl>

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
   $this->registry->plugins['imagepath']['name']			= 'imagepath';
   $this->registry->plugins['imagepath']['title']			= 'ImagePath plugin';
   $this->registry->plugins['imagepath']['author']			= 'Pim Snel';
   $this->registry->plugins['imagepath']['version']			= '0.9.7';
   $this->registry->plugins['imagepath']['enable']			= 1;

   $this->registry->plugins['imagepath']['description']		= '
   plugin for uploading/resizing images and storing their imagepaths in
   to database, using default uploadpath for site or object';

   $this->registry->plugins['imagepath']['db_field_hooks']	= array
   (
	  'string',
	  'blob'
   );

   /* ATTENTION: spaces and special character are not allowed in config array 
   use underscores for spaces */
   $this->registry->plugins['imagepath']['config']		= array
   (
	  /* array('default value','input field type', 'extra html properties')*/
	  'Max_files' => array('3','text','maxlength=2 size=2'),  
	  'Allow_more_then_max_files'=> array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
	  'Zip_file_box'=> array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
	  'Max_image_width' => array('','text','maxlength=4 size=4'),
	  'Max_image_height' => array('','text','maxlength=4 size=4'),
	  'Image_filetype' => array(array('png','gif','jpg'),'select','maxlength=3 size=3'),
	  'Generate_thumbnail' => array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
	  'Max_thumbnail_width' => array('100','text','maxlength=3 size=3'),
	  'Max_thumbnail_height'=> array('100','text','maxlength=3 size=3'),
	  'Allow_other_images_sizes'=> array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
   );


