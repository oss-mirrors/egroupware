<?php
   /**
   JiNN - A Database Application Development Toolkit
   Author:	Pim Snel for Lingewoud
   Copyright (C) 2005-2007 Pim Snel <pim@lingewoud.nl>
   License http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   JiNN is part of eGroupWare - http://www.egroupware.org
   */

   $this->registry->plugins['tinymce']['name']				= 'tinymce';
   $this->registry->plugins['tinymce']['title']				= 'WYSIWYG TinyMCE Editor';
   $this->registry->plugins['tinymce']['version']			= '0.5';
   $this->registry->plugins['tinymce']['enable']			= 1;
   $this->registry->plugins['tinymce']['author']			= 'Pim Snel';
   $this->registry->plugins['tinymce']['noajax']			= true; //this plugin cannot be used with ajax in listview
   $this->registry->plugins['tinymce']['description']		= 'The TinyMCE plugin is based on tinymce from tinymce.moxiecode.com.
   Tinymce is a rich-textarea replacement for the textarea formwidget.';
   $this->registry->plugins['tinymce']['db_field_hooks']	= array
   (
	  'text',
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
	  'Medium'=>'Medium (100% x 400px)',
	  'Small'=>'Small (100% x 200px)',
	  'Large'=>'Large (100% x 600px)',
	  'XXL'=>'XXL (500px x 1200px)',
	  'Custom'=>'Custom',
   );

   $advanced_arr=array(
	  'relative_urls'=>lang('Relative paths to images'),
	  'cleanup'=>lang('Clean up my HTML-code, it can mean a los of layout'),
	  'save_filer'=>lang('Disable "font size" with span and style tag'),
   );
   
   $this->registry->plugins['tinymce']['config2']		= array
   (
/*	  'select_theme' => array(
		 'name' => 'select_theme',
		 'label' => lang('Select theme'),
		 'type' => 'select',
		 'select_arr' => array('default','simple','advanced')
	  ),*/
	  'standard_options' => array(
		 'name' => 'standard_options',
		 'label' => lang('Standard Editor Options'),
		 'type' => 'checkbox',
		 'checkbox_arr' => $normal_options_arr
	  ),
/*	  'plugins' => array(
		 'name' => 'plugins',
		 'label' => lang('Activated Plugins'),
		 'type' => 'checkbox',
		 'checkbox_arr' => $plugins_arr
	  ),*/
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
/*	  'content_css_file' => array(
		 'name' => 'content_css_file',
		 'label' => lang('Custom CSS File for area content'),
		 'type' => 'sitefile',
		 'subdir' =>'tinymce',
		 'allowempty' => true
	  ),*/
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
