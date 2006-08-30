<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Author:	Pim Snel for Lingewoud
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
   $this->registry->plugins['tinymce']['version']			= '0.3';
   $this->registry->plugins['tinymce']['enable']			= 1;
   $this->registry->plugins['tinymce']['author']			= 'Pim Snel';
   $this->registry->plugins['tinymce']['description']		= 'The tinymce plugin is based on tinymce from tinymce.moxiecode.com. Tinymce is a rich-textarea replacement for the textarea formwidget.';
   $this->registry->plugins['tinymce']['db_field_hooks']	= array
   (
	  'blob',
	  'string'
   );

   $normal_options_arr=array(
	  'enable_font_properties'=>lang('Enable font properties'),
	  'enable_colors'=>lang('Enable colors'),
	  'enable_link'=>lang('Enable links'),
	  'enable_simple_image'=>lang('Enable simple image'),
	  'enable_styles'=>lang('Enable styles'),
	  'enable_tables'=>lang('Enable tables'),
	  'enable_fullscreen'=>lang('Enable fullscreen mode'),
   );

   $plugins_arr=array(
	  'ibrowser'=>lang('iBrowser'),
	  'advhr'=>lang('advhr'),
	  'advlink'=>lang('advlink'),
	  'insertdatetime'=>lang('Insert Date / Time'),
	  'preview'=>lang('Preview'),
	  'zoom'=>lang('Zoom'),
	  'searchreplace'=>lang('Search and replace'),
	  'print'=>lang('Print'),
	  'directionality'=>lang('directionality'),
	  'contextmenu'=>lang('Right Button Content Menu'),
	  'paste'=>lang('Paste'),
   );
   $size_arr=array(
	  'Custom'=>'Custom',
	  'Small'=>'Small (100% x 200px)',
	  'Medium'=>'Medium (100% x 400px)',
	  'Large'=>'Large (100% x 600px)',
	  'XXL'=>'XXL (500px x 1200px)',
   );

   $advanced_arr=array(
	  'relative_urls'=>lang('Relative paths to images'),
   );
   
   $this->registry->plugins['tinymce']['config2']		= array
   (
	  'select_theme' => array(
		 'name' => 'select_theme',
		 'label' => lang('Select theme'),
		 'type' => 'select',
		 'select_arr' => array('default','simple','advanced')
	  ),
	  'standard_options' => array(
		 'name' => 'standard_options',
		 'label' => lang('Standard Editor Options'),
		 'type' => 'checkbox',
		 'checkbox_arr' => $normal_options_arr
	  ),
	  'plugins' => array(
		 'name' => 'plugins',
		 'label' => lang('Activated Plugins'),
		 'type' => 'checkbox',
		 'checkbox_arr' => $plugins_arr
	  ),
	  'size_of_area' => array(
		 'name' => 'size_of_area',
		 'label' => lang('Area size'),
		 'type' => 'select',
		 'select_arr' => $size_arr
	  ),
	  'custom_width' => array(
		 'name' => 'custom_width',
		 'label' => lang('Custom Width (e.g. 100%, 300px, 70em)'),
		 'type' => 'text',
		 'size' =>'6'
	  ),
	  'custom_height' => array(
		 'name' => 'custom_height',
		 'label' => lang('Custom Height (e.g. 100%, 300px, 70em)'),
		 'type' => 'text',
		 'size' =>'6'
	  ),
	  'content_css_file' => array(
		 'name' => 'content_css_file',
		 'label' => lang('Custom CSS File for area content'),
		 'type' => 'sitefile',
		 'subdir' =>'tinymce',
		 'allowempty' => true
	  ),
	  'advanced_settings' => array(
		 'name' => 'advanced_settings',
		 'label' => lang('Advanced Settings'),
		 'type' => 'checkbox',
		 'checkbox_arr' => $advanced_arr
	  ),
/*	  'document_base' => array(
		 'name' => 'document_base',
		 'label' => lang('DocumentBase for links to link to'),
		 'type' => 'text',
		 'allowempty' => true
	  ),
*/

   );



?>
