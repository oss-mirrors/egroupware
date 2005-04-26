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

   $this->registry->plugins['htmlarea']['name']				= 'htmlarea';
   $this->registry->plugins['htmlarea']['title']			= 'htmlArea';
   $this->registry->plugins['htmlarea']['version']			= '0.9.0.3';
   $this->registry->plugins['htmlarea']['enable']			= 1;
   $this->registry->plugins['htmlarea']['author']			= 'Pim Snel';
   $this->registry->plugins['htmlarea']['description']		= 'The htmlArea plugin is based on htmlArea 3.0rc from interactivetools.com licenced under the BSD licence.<p/>   HtmlArea is a rich-textarea replacement for the textarea formwidget.';
   $this->registry->plugins['htmlarea']['db_field_hooks']	= array
   (
	  'blob',
	  'string'
   );

   $this->registry->plugins['htmlarea']['config']		= array
   (
	  'enable_font_options'=>array(array('Yes','No'),'select',''),
	  'enable_tables_button'=>array(array('Yes','No'),'select',''),
	  'enable_fullscreen_editor_button'=>array(array('Yes','No'),'select',''),
	  'enable_image_button'=>array(array('Yes','No'),'select',''),
	  'enable_context_menu'=>array(array('Yes','No'),'select',''),
	  'enable_image_upload_button'=>array(array('Yes','No'),'select',''),
	  'image_upload_max_height' => array('','text','maxlength="4" size="4"'),
	  'image_upload_max_width' => array('','text','maxlength="4" size="4"'),
	  'size_of_area'=>array(array('Small','Medium','Large','XXL'),'select',''),
	  'custom_css'=>array('','area','')
   );

   $this->registry->plugins['htmlarea']['config_help'] = array
   (
	  'enable_image_upload_button'=>'This is still experimental.',
	  'size_if_area'=>'This set the size of the htmlarea window. You can overrule this by using custom_css',
	  'custom_css'=> 'Put valid CSS-code here that will replace the default css used by htmlArea'
   );

?>