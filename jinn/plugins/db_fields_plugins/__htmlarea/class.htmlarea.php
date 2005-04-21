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
   
	class db_fields_plugin_htmlarea
	{
	
		function db_fields_plugin_htmlarea()
		{
		}
	
	   /**
	   @function plg_fi_htmlArea
	   @todo add special class selectbox
	   @todo add SpellChecker,HtmlTidy plugins in config and htmlarea call
	   @todo add config options for the rest of the buttons
	   */
	   function formview_edit($field_name, $value, $config,$attr_arr)
	   {
		  global $local_bo;
	
		  if($local_bo->read_preferences('disable_htmlarea')=='yes')
		  {
			 return;
		  }
	
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
		  
	
		  if($config[enable_font_options]=='Yes') $bar_font = '"fontname", "space" , "fontsize", "space" ,';
		  if($config[enable_image_button]=='Yes') $bar_image = '"insertimage",';
		  if($config[enable_tables_button]=='Yes') $bar_table = '"inserttable",';
		  if($config[enable_fullscreen_editor_button]=='Yes') $bar_fullscreen = '"popupeditor",';
	
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
			 $plugins.='ContextMenu ';
		  }
		  /* Do stuff to activate uploadImage Plugin in htmlArea */
		  if($config[enable_image_upload_button]=='Yes')
		  {
			 $sessdata = array(
				'UploadImageBaseDir' =>   $upload_path, 
				'UploadImageBaseURL' =>   $upload_url,
				'UploadImageMaxWidth' =>  $config[image_upload_max_width],
				'UploadImageMaxHeight' => $config[image_upload_max_height],
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
	
	   function formview_read($value, $config)
	   {
			return $value;   		
	   }
	
	   function listview_read($value, $config,$attr_arr)
	   {
		  if(strlen($value)>20)
		  {
			  $value = strip_tags(htmlentities($value));
	
			 $title = substr($value,0,200);
			 
			  $value = '<span title="'.$title.'">' . substr($value,0,20). ' ...' . '</span>';
		  }
		  return $value;   		
	   }
	}
?>
