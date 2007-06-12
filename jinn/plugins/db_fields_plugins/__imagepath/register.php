<?php
   /**
   JiNN - A Database Application Development Toolkit
   Author:	Pim Snel for Lingewoud
   Copyright (C) 2007 Pim Snel <pim@lingewoud.nl>
   License http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   JiNN is part of eGroupWare - http://www.egroupware.org
   */

   $this->registry->plugins['imagepath']['name']			= 'imagepath';
   $this->registry->plugins['imagepath']['title']			= 'ImagePath plugin';
   $this->registry->plugins['imagepath']['author']			= 'Pim Snel';
   $this->registry->plugins['imagepath']['version']			= '0.9.8';
   $this->registry->plugins['imagepath']['enable']			= 1;

   $this->registry->plugins['imagepath']['description']		= '
   plugin for uploading/resizing images and storing their imagepaths in
   to database, using default uploadpath for site or object';

   $this->registry->plugins['imagepath']['db_field_hooks']	= array
   (
	  'string',
	  'text'
   );

   $this->registry->plugins['imagepath']['config2'] = array
   (
	  'Max_files' => array(
		 'name' => 'Max_files',
		 'label' => lang('Amount of files to upload'),
		 'type' => 'text',
		 'size' =>2 
	  ),
	  'Max_image_width' => array(
		 'name' => 'Max_image_width',
		 'label' => lang('Max image width'),
		 'type' => 'text',
		 'size' =>4
	  ),
	  'Max_image_height' => array(
		 'name' => 'Max_image_height',
		 'label' => lang('Max_image_height'),
		 'type' => 'text',
		 'size' =>4
	  ),
	  'Image_filetype' => array(
		 'name' => 'Image_filetype',
		 'label' => lang('Image filetype'),
		 'type' => 'select',
		 'select_arr' => array('png'=>lang('png'),'gif'=>lang('gif'),'jpg'=>lang('jpg'))
	  ), 
	  'Allow_other_images_sizes' => array(
		 'name' => 'Allow_other_images_sizes',
		 'label' => lang('Allow other images sizes'),
		 'type' => 'select',
		 'select_arr' => array('False'=>lang('no'),'True'=>lang('yes'))
	  ),
	  'Generate_thumbnail' => array(
		 'name' => 'Generate_thumbnail',
		 'label' => lang('Generate thumbnail'),
		 'type' => 'select',
		 'select_arr' => array('False'=>lang('no'),'True'=>lang('yes'))
	  ),
	  'Max_thumbnail_width' => array(
		 'name' => 'Max_thumbnail_width',
		 'label' => lang('Max thumbnail width'),
		 'type' => 'text',
		 'size' =>4
	  ),
	  'Max_thumbnail_height' => array(
		 'name' => 'Max_thumbnail_height',
		 'label' => lang('Max thumbnail height'),
		 'type' => 'text',
		 'size' =>4
	  ),
   );

