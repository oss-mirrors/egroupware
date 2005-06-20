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

   $this->registry->plugins['tinymce']['name']				= 'tinymce';
   $this->registry->plugins['tinymce']['title']				= 'TinyMCE Editor';
   $this->registry->plugins['tinymce']['version']			= '0.1';
   $this->registry->plugins['tinymce']['enable']			= 1;
   $this->registry->plugins['tinymce']['author']			= 'Pim Snel';
   $this->registry->plugins['tinymce']['description']		= 'The tinymce plugin is based on tinymce from tinymce.moxiecode.com. Tinymce is a rich-textarea replacement for the textarea formwidget.';
   $this->registry->plugins['tinymce']['db_field_hooks']	= array
   (
	  'blob',
	  'string'
   );

   $this->registry->plugins['tinymce']['config']		= array
   (
	  'select_theme'=>array(array('default','simple','advanced'),'select',''),
	  'enable_font_selection_options'=>array(array('Yes','No'),'select',''),
	  'enable_font_size_options'=>array(array('Yes','No'),'select',''),
	  'enable_block_formatting_options'=>array(array('Yes','No'),'select',''),
	  'enable_font_mode'=>array(array('Yes','No'),'select',''),
	  'enable_font_special'=>array(array('Yes','No'),'select',''),
	  'enable_copy_paste'=>array(array('Yes','No'),'select',''),
	  'enable_undo_redo'=>array(array('Yes','No'),'select',''),
  	  'enable_justify'=>array(array('Yes','No'),'select',''),
	  'enable_lists'=>array(array('Yes','No'),'select',''),
	  'enable_indent'=>array(array('Yes','No'),'select',''),
	  'enable_colors'=>array(array('Yes','No'),'select',''),
	  'enable_hr'=>array(array('Yes','No'),'select',''),
	  'enable_link'=>array(array('Yes','No'),'select',''),
	  'enable_image_button'=>array(array('Yes','No'),'select',''),
	  'enable_tables_button'=>array(array('Yes','No'),'select',''),
  	  'enable_html_mode'=>array(array('Yes','No'),'select',''),
	  'enable_fullscreen_editor_button'=>array(array('Yes','No'),'select',''),
	  'enable_context_menu'=>array(array('Yes','No'),'select',''),
	  'enable_image_upload_button'=>array(array('Yes','No'),'select',''),
	  'image_upload_max_height' => array('','text','maxlength="4" size="4"'),
	  'image_upload_max_width' => array('','text','maxlength="4" size="4"'),
	  'size_of_area'=>array(array('Small','Medium','Large','XXL'),'select',''),
	  'custom_css'=>array('','area','')
   );

   $this->registry->plugins['tinymce']['config_help'] = array
   (
	  'enable_font_selection_options'=>'Select font face.',
	  'enable_font_size_options'=>'Select font size.',
	  'enable_block_formatting_options'=>'Select paragraph formatting.',
	  'enable_font_mode'=>'Bold, Italic, Underline.',
	  'enable_font_special'=>'Strikethrough, Subscript, Superscript.',
	  'enable_copy_paste'=>'Cut, Copy, Paste.',
	  'enable_undo_redo'=>'Undo, Redo.',
	  'enable_justify'=>'Justify Left, Center, Right, Full.',
	  'enable_lists'=>'Ordered and Unordered lists.',
	  'enable_indent'=>'Indent and Outdent.',
	  'enable_colors'=>'Set Fore- and Background colors.',
	  'enable_hr'=>'Insert horizontal rule.',
	  'enable_link'=>'Create Link.',
	  'enable_image_button'=>'Insert image.',
	  'enable_tables_button'=>'Insert table.',
	  'enable_html_mode'=>'Switch to HTML mode.',
	  'enable_fullscreen_editor_button'=>'Popup fullscreen editor.',
	  'enable_image_upload_button'=>'This is still experimental.',
	  'size_of_area'=>'This set the size of the tinymce window. You can overrule this by using custom_css',
	  'custom_css'=> 'Put valid CSS-code here that will replace the default css used by tinymce'
   );

?>
