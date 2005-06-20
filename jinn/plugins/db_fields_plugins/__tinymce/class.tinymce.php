<?php
   /*************************************************************************\
   * eGroupWare - tinymce-form-plugin for eGW-jinn                          *
   * The original script is written by interactivetools.com, inc.            *
   * Ported to eGroupWare by Pim Snel info@lingewoud.nl                      *
   * --------------------------------------------                            *
   * http://www.egroupware.org                                               *
   * http://www.interactivetools.com/                                        *
   * http://www.lingewoud.nl                                                 *
   * --------------------------------------------                            *
   * The original script tinymce is distributed under a Open Source-licence *
   * See the readme.txt in the tinymce-directory for the complete licence   *
   * text.                                                                   *
   * eGroupWare and the jinn are free software; you can                      *
   * redistribute it and/or modify it under the terms of the GNU General     *
   * Public License as published by the Free Software Foundation;            *
   * Version 2 of the License.                                               *
   \*************************************************************************/

   /* $Id$ */	
   
	class db_fields_plugin_tinymce
	{
	
		function db_fields_plugin_tinymce()
		{
		}
	
	   /**
	   @function plg_fi_tinymce
	   @todo add special class selectbox
	   @todo add SpellChecker,HtmlTidy plugins in config and tinymce call
	   @todo add config options for the rest of the buttons
	   */
	   function formview_edit($field_name, $value, $config,$attr_arr)
	   {
		  global $local_bo;
	
		  if($local_bo->read_preferences('disable_tinymce')=='yes')
		  {
			 return;
		  }
		  
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
				   case 'Small': $style.='width:100%;  height:200px;';Break;
				   case 'Medium': $style.='width:100%; height:400px;';Break;
				   case 'Large': $style.='width:100%;  height:600px;';Break;
				   case 'XXL': $style.='width:800px; min-width:500px; height:1200px;';Break;
				}
			 }
		  }
			
		  
		  if (!is_object($GLOBALS['phpgw']->html))
		  {
			 $GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
		  }
	
		  /* Make sure no plugins are loaded by setting a space */
		  if(!$plugins) $plugins=' ';
		 
		  //		  $input = $GLOBALS['phpgw']->html->tinymce($field_name, $value,$style,false,$plugins,$custom_toolbar);

		  $options=$this->setOptions($config);

		  $input = $GLOBALS['phpgw']->html->tinymce($field_name,$value,$style,$options);
	
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

   
	   function setOptions($config)
	   {

		  //font family
		  if($config[enable_font_selection_options]	!='No') 
		  {
			 $bar.= 'theme_advanced_buttons1_add_before : "fontselect",';
			 $bar.="\n";
		  }
		  else
		  {
		 	$disable[]='fontselect'; 
		  }

		 //font size 
		  if($config[enable_font_size_options]			!='No') 
		  {
			 $bar.= 'theme_advanced_buttons1_add: "fontsizeselect",';
			 $bar.="\n";
		  }
		  else
		  {
			 $disable[]='fontsizeselect'; 
		  }

		  //tables
		  if($config[enable_tables_button]				!='No') {
			 $bar.= 'theme_advanced_buttons3_add_before : "tablecontrols,separator",';
			 $bar.="\n";
			 $plugins[]='table';
		  }

		 //fullscreen
		  if($config[enable_fullscreen_editor_button]	!='No') 
		  {
			 $plugins[]='fullscreen';
		  }

		  //contextmenu
		  if($config[enable_context_menu] == 'Yes')
		  {
			 $plugins[]='contextmenu ';
		  }

		  //font_mode
		  if($config[enable_font_mode]	!='No') 
		  {
		  	//
		  }
		  else
		  {
		 	$disable[]='bold'; 
			$disable[]='italic'; 
			$disable[]='underline'; 
		  }

		  //font_special
		  if($config[enable_font_special] !='No')
		  {
			 //
		  }
		  else
		  {
			 $disable[]='strikethrough'; 
			 $disable[]='sub'; 
			 $disable[]='sup'; 
		  }

		  if($config[enable_justify] !='No')
		  {
		  	//
		  }
		  else
		  {
			 $disable[]='justifyleft'; 
			 $disable[]='justifyright'; 
			 $disable[]='justifycenter'; 
			 $disable[]='justifyfull'; 
		  }

		  if($config[enable_styles] !='No')
		  {
			 //
		  }
		  else
		  {
			 $disable[]='styleselect'; 
		  }

		  if($config[enable_block_formatting_options] !='No')
		  {
			 //
		  }
		  else
		  {
			 $disable[]='formatselect'; 
		  }

		  //fixme doesnt work
		  if($config[enable_copy_paste]	!='No') 
		  {
			 $plugins[]='paste';
		  }
		  else
		  {
			 $disable[]='cut'; 
			 $disable[]='copy'; 
			 $disable[]='paste'; 

		  }
		
		  if($config[enable_undo_redo]					!='No') 
		  {
			 //
		  }
		  else
		  {
			 $disable[]='undo'; 
			 $disable[]='redo'; 
		  }
		  if($config[enable_lists]						!='No') 
		  {
			 //
		  }
		  else
		  {
			 $disable[]='bullist'; 
			 $disable[]='numlist'; 
		  }

		  if($config[enable_indent]						!='No') 
		  {
			 //
		  }
		  else
		  {
			 $disable[]='indent'; 
			 $disable[]='outdent'; 
		  }
		 
		  //fixme doesnt work
		  if($config[enable_colors]						!='No') 
		  {
			 //
		  }
		  else
		  {
			 $disable[]='forecolor'; 
			 $disable[]='backcolor'; 
		  }

		  if($config[enable_hr]	!='No') 
		  {
			 //
		  }
		  else
		  {
			 $disable[]='hr'; 
		  }

		  if($config[enable_link]	!='No') 
		  {
			 //
		  }
		  else
		  {
			 $disable[]='link'; 
			 $disable[]='unlink'; 
			 $disable[]='anchor'; 
		  }

		  if($config[enable_image_button]!='No')
		  {
			 //
		  }
		  else
		  {
			 $disable[]='image'; 
		  }

			//fixme doesnt work
		  if($config[enable_html_mode]!='No') 
		  {
			 //
		  }
		  else
		  {
			 $disable[]='code'; 
		  }


		  if(!$config[select_theme]) $config[select_theme]='advanced';
		  $bar.=  'theme : "'.$config[select_theme].'",'; $bar.="\n";

		  $bar.= 'theme_advanced_toolbar_location : "top",'; $bar.="\n";
		  $bar.= 'theme_advanced_path_location : "bottom",'; $bar.="\n";
		  $bar.= 'theme_advanced_layout_manager : "SimpleLayout",'; $bar.="\n";
		  $bar.= 'theme_advanced_toolbar_align : "left",'; 

		  $plugins[]='advhr';
		  $plugins[]='advlink';
		  $plugins[]='insertdatetime';
		  $plugins[]='preview';
		  $plugins[]='zoom';
		  $plugins[]='searchreplace';
		  $plugins[]='print';
		  $plugins[]='directionality';
		  
		  if(is_array($plugins))
		  {
			 $plug_str.=implode(',',$plugins);
		  }

		  $bar.="\n";
		  $bar.='plugins : "'.$plug_str.'",';
		  
		  if(is_array($disable))
		  {
			 $disab_str.=implode(',',$disable);
		  }


		  $bar.='	  
		  // theme_advanced_buttons1_add_before : "save,newdocument,separator",
		  theme_advanced_buttons1_add : "fontselect,fontsizeselect",
		  theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor",
		  theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
		  theme_advanced_buttons3_add_before : "tablecontrols,separator",
		  theme_advanced_buttons3_add : "emotions,iespell,flash,advhr,separator,print,separator,ltr,rtl,separator,fullscreen",
		  // content_css : "example_full.css",
		  plugin_insertdate_dateFormat : "%Y-%m-%d",
		  plugin_insertdate_timeFormat : "%H:%M:%S",
		  // extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		  // external_link_list_url : "example_link_list.js",
		  // external_image_list_url : "example_image_list.js",
		  // flash_external_list_url : "example_flash_list.js",
		  // file_browser_callback : "fileBrowserCallBack"
		  ';

		  $bar.='theme_advanced_disable : "'.$disab_str.'"';
		  
		  return $bar;

	 }
  }
?>
