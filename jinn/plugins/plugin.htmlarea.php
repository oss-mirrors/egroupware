<?php
   /*************************************************************************\
   * eGroupWare - HTMLAREA-form-plugin for eGW-jinn                          *
   * The original script is written by interactivetools.com, inc.            *
   * Ported to eGroupWare by Pim Snel info@lingewoud.nl                      *
   * --------------------------------------------                            *
   * http://www.egroupware.org                                               *
   * http://www.interactivetools.com/                                        *
   * http://www.lingewoud.nl                                                 *
   * --------------------------------------------                            *
   * The original script HTMLAREA is distributed under a Open Source-licence *
   * See the readme.txt in the htmlarea-directory for the complete licence   *
   * text.                                                                   *
   * eGroupWare and the jinn are free software; you can                      *
   * redistribute it and/or modify it under the terms of the GNU General     *
   * Public License as published by the Free Software Foundation;            *
   * Version 2 of the License.                                               *
   \*************************************************************************/

   /* $id$ */	
   
   $description = '
   the htmlArea plugin is based on htmlArea v3beta from interactivetools.com
   licenced under the BSD licence.<P>
   htmlArea is a WYSIWYG editor replacement for any textarea field. Instead
   of teaching your software users how to code basic HTML to format their
   content.
   ';

   $this->plugins['htmlArea']['name']			= 'htmlArea';
   $this->plugins['htmlArea']['title']			= 'htmlArea';
   $this->plugins['htmlArea']['version']		= '0.9.0.2';
   $this->plugins['htmlArea']['enable']			= 1;
   $this->plugins['htmlArea']['author']			= 'Pim Snel';
   $this->plugins['htmlArea']['description']	= $description;
   $this->plugins['htmlArea']['db_field_hooks']	= array
   (
	  'blob',
	  'longtext',
	  'text'
   );

   $this->plugins['htmlArea']['config']		= array
   (
	  'UploadImageBaseDir' => array('','text','maxlength=200 size=30'),
	  'UploadImageBaseURL' => array('','text','maxlength=200 size=30'),
	  'UploadImageRelativePath' => array('','text','maxlength=200 size=30'),
	  'enable_font_options'=>array(array('Yes','No'),'select',''),
	  /*
	  'enable_alignment_buttons'=>array(array('Yes','No'),'select',''),
	  'enable_list_buttons'=>array(array('Yes','No'),'select',''),
	  'enable_html_source_button'=>array(array('Yes','No'),'select',''),
	  'enable_color_buttons'=>array(array('Yes','No'),'select',''),
	  'enable_horizontal_ruler_button'=>array(array('Yes','No'),'select',''),
	  'enable_link_button'=>array(array('Yes','No'),'select',''),*/
	  'enable_tables_button'=>array(array('Yes','No'),'select',''),
	  'enable_fullscreen_editor_button'=>array(array('Yes','No'),'select',''),
	  'enable_image_button'=>array(array('Yes','No'),'select',''),
	  'enable_context_menu'=>array(array('Yes','No'),'select',''),
	  'enable_image_upload_button'=>array(array('Yes','No'),'select',''),
	  'size_of_area'=>array(array('Small','Medium','Large','XXL'),'select',''),
	  'custom_css'=>array('','area','')
   );

   $this->plugins['htmlArea']['config_help'] = array
   (
	  'enable_image_upload_button'=>'This is still experimental.',
	  'size_if_area'=>'This set the size of the htmlarea window. You can overrule this by using custom_css',
	  'custom_css'=> 'Put valid CSS-code here that will replcae the default css used by htmlArea'
   );
   

   /**
   @function plg_fi_htmlArea
   @todo add special class selectbox
   @todo add SpellChecker,HtmlTidy plugins in config and htmlarea call
   @todo add config options for the rest of the buttons
   */
   function plg_fi_htmlArea($field_name, $value, $config,$attr_arr)
   {
	  global $local_bo;

	  if($local_bo->common->so->config[server_type]=='dev')
	  {
		 $field_prefix='dev_';
	  }

	  if($local_bo->site_object[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site_object[$field_prefix.'upload_path'];
		 $upload_url=$local_bo->site_object[$field_prefix.'upload_url'];
	  }
	  elseif($local_bo->site[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site[$field_prefix.'upload_path'];
		 $upload_url=$local_bo->site[$field_prefix.'upload_url'];
	  }

		//_debug_array($local_bo->site);
	  //die();
	  
	  if($local_bo->read_preferences('disable_htmlarea')=='yes')
	  {
		 return;
	  }

	  if($config[enable_font_options]=='Yes') $bar_font = '"fontname", "space" , "fontsize", "space" ,';
	  if($config[enable_image_button]=='Yes') $bar_image = '"insertimage",';
	  if($config[enable_tables_button]=='Yes') $bar_table = '"inserttable",';
	  if($config[enable_fullscreen_editor_button]=='Yes') $bar_fullscreen = '"popupeditor",';

/*	  if($config[enable_html_source_button]!='No') $bar_html = '"htmlmode",';
	  if($config[enable_alignment_buttons]!='No') $bar_align = '[ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator" ],';

	  if($config[enable_list_buttons]!='No') $bar_list = '[ "orderedlist", "unorderedlist", "outdent", "indent", "separator" ],';
	  if($config[enable_color_buttons]!='No') $bar_colors = '[ "forecolor", "backcolor", "textindicator", "separator" ],';
	  if($config[enable_horizontal_ruler_button]!='No') $bar_ruler = '"horizontalrule",';
	  if($config[enable_link_button]!='No') $bar_link = '"createlink",';
	  */

	  /* toolbar configuration */
	  $custom_toolbar = '[
	  [ '.$bar_font.'
	  "formatblock", "space",
	  "bold", "italic", "underline", "separator",
	  "strikethrough", "subscript", "superscript", "separator",
	  "copy", "cut", "paste", "space", "undo", "redo" ],

	  [ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator",
	  "orderedlist", "unorderedlist", "outdent", "indent", "separator",
	  "forecolor", "hilitecolor", "textindicator", "separator",
	  "inserthorizontalrule", "createlink", '.$bar_image.' '.$bar_table.' "htmlmode", "separator",
	  '.$bar_fullscreen.' "separator", "showhelp", "about" ]
	  ];';
	  
	  
	  if($config[custom_css]) 
	  {
		 $style = $config[custom_css];
	  }

	  if(!eregi('height',$config[custom_css]))
	  {
		 if($config[size_of_area])
		 {
			switch($config[size_of_area])	
			{
			   case 'Small': $style.='width:500px; min-width:500px; height:200px;';Break;
			   case 'Medium': $style.='width:500px; min-width:500px; height:400px;';Break;
			   case 'Large': $style.='width:600px; min-width:500px; height:600px;';Break;
			   case 'XXL': $style.='width:800px; min-width:500px; height:1200px;';Break;
			}
		 }
	  }
		
	  /* Do stuff to activate ContextMenu Plugin in htmlArea */
	  if($config[enable_image_upload_button]=='Yes')
	  {
		 if($plugins) $plugins.=',';
		 $plugins.='ContextMenu';
	  }

	  /* Do stuff to activate uploadImage Plugin in htmlArea */
	  if($config[enable_image_upload_button]=='Yes')
	  {
/*		 $sessdata = array(
			'UploadImageBaseDir' => $config[UploadImageBaseDir], 
			'UploadImageBaseURL' => $config[UploadImageBaseURL],
			//			'UploadImageRelativePath' => $config[UploadImageRelativePath]
			
		 );
		 */
		 
		 $sessdata = array(
			'UploadImageBaseDir' => $upload_path, 
			'UploadImageBaseURL' => $upload_url,
//			'UploadImageRelativePath' => $config[UploadImageRelativePath]
		 );

		 $GLOBALS['phpgw']->session->appsession('UploadImage','phpgwapi',$sessdata);
		 if($plugins) $plugins.=',';
		 $plugins.='UploadImage';
	  }
	  
	  if (!is_object($GLOBALS['phpgw']->html))
	  {
		 $GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
	  }

	  /* Make sure no plugins are loaded by setting a space */
	  if(!$plugins) $plugins=' ';
	  $input = $GLOBALS['phpgw']->html->htmlarea($field_name, $value,$style,false,$plugins,$custom_toolbar);

	  return $input;
   }

   function plg_ro_htmlArea($value, $config)
   {
		return $value;   		
   }

   function plg_bv_htmlArea($value, $config,$attr_arr)
   {
	  if(strlen($value)>20)
	  {
		 $value = strip_tags($value);

		 $value = '<span title="'.substr($value,0,200).'">' . substr($value,0,20). ' ...' . '</span>';

	  }
	  return $value;   		
   }

?>
