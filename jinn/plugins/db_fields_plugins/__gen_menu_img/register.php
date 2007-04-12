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
   $this->registry->plugins['gen_menu_img']['name']				= 'gen_menu_img';
   $this->registry->plugins['gen_menu_img']['title']			= 'TextToImage';
   $this->registry->plugins['gen_menu_img']['version']			= '0.4';
   $this->registry->plugins['gen_menu_img']['enable']			= 1;
   $this->registry->plugins['gen_menu_img']['author']			= 'Pim Snel';
   $this->registry->plugins['gen_menu_img']['description']		= 
   'With this plugin you can generate high quality images containing the text of your menu item. 
   The CMS developer has to upload font and background images. The Moderator can then just type 
   in the text and push the putton generate. The path to the images are stored in a serialized 
   associative array.
   ';
   $this->registry->plugins['gen_menu_img']['helper_fields_substring'] = 'GMI'; //this is for multiple records insert
   $this->registry->plugins['gen_menu_img']['db_field_hooks']	= array
   (
	  'blob',
	  'string'
   );

   $this->registry->plugins['gen_menu_img']['config2'] = array
   (
	  'subdir' => array(
		 'name' => 'subdir',
		 'label' => lang('Subdirectory to use'),
		 'type' => 'text',
		 'size' => 100
	  ),
	  'multi1'=>array(
		 'name'=>'multi1',
		 'type'=>'multi',
		 'items'=>array(
			'fontfile' => array(
			   'name' => 'fontfile',
			   'label' => lang('Font file for image'),
			   'type' => 'sitefile',
			   'subdir' =>'fonts',
			   'allowempty' => false
			),
			'fontsize' => array(
			   'name' => 'fontsize',
			   'label' => lang('Font size for image in pixels'),
			   'type' => 'text',
			   'size' => 4
			),
			'fontcolor' => array(
			   'name' => 'fontcolor',
			   'label' => lang('Text color'),
			   'type' => 'text',
			   'size' => 7
			),
			'bgcolor' => array(
			   'name' => 'bgcolor',
			   'label' => lang('Background color'),
			   'type' => 'text',
			   'size' => 7
			),
			'bgimg' => array(
			   'name' => 'bgimg',
			   'label' => lang('Background image'),
			   'type' => 'sitefile',
			   'subdir' =>'genmenu',
			   'allowempty' => true
			),
			'imgheight' => array(
			   'name' => 'imgheight',
			   'label' => lang('Height image in pixels'),
			   'type' => 'text',
			   'size' =>'3'
			),
			'paddingtop' => array(
			   'name' => 'paddingtop',
			   'label' => lang('Font padding on top of the image in pixels'),
			   'type' => 'text',
			   'size' =>'3'
			),
			'paddingright' => array(
			   'name' => 'paddingright',
			   'label' => lang('Font padding on right of the image in pixels'),
			   'type' => 'text',
			   'size' =>'3'
			),
			'paddingleft' => array(
			   'name' => 'paddingleft',
			   'label' => lang('Font padding on left of the image in pixels'),
			   'type' => 'text',
			   'size' =>'3'
			),
		 )
	  )
   );

?>
