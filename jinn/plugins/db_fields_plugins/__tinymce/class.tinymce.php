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
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
		}
	
	   /**
	   @function plg_fi_tinymce
	   @todo add special class selectbox
	   @todo add SpellChecker,HtmlTidy plugins in config and tinymce call
	   @todo add config options for the rest of the buttons
	   */
	   function formview_edit($field_name, $value, $config,$attr_arr)
	   {
		  if($config[custom_css]) 
		  {
			 $style = $config[custom_css];
		  }
	
		  if($config[size_of_area]!='Custom')
		  {
			 switch($config[size_of_area])	
			 {
				case 'Small': $style.='width:100%;  height:200px;';Break;
				case 'Medium': $style.='width:100%; height:400px;';Break;
				case 'Large': $style.='width:100%;  height:600px;';Break;
				case 'XXL': $style.='width:800px; min-width:500px; height:1200px;';Break;
			 }
		  }
		  elseif($config[custom_width] && $config[custom_height])
		  {
			 $style.='width:'.$config[custom_width].'; height:'.$config[custom_height].';';
		  }
		  else
		  {
			 $style.='width:100%; height:400px;';
		  }

/*		  if($this->local_bo->read_preferences('disable_tinymce')=='yes')
		  {
			 return $input='<textarea name="'.$field_name.'" style="'.$style.'">'.$value.'</textarea>';
		  }
		  */

		  if (!is_object($GLOBALS['phpgw']->html))
		  {
			 $GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
		  }
	
		  $options=$this->setOptions($config);
		  $options.=",
		  theme_advanced_resize_horizontal : false,\n
		  theme_advanced_resizing : true,\n
		  strict_loading_mode : true,\n
		  ";
		  #_debug_array($options);

		  $this->tplsav2->init_options=$options;
		  $this->tplsav2->name=$field_name;
		  $this->tplsav2->content=$value;
		  $this->tplsav2->style=$style;
		  $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
		  return $this->tplsav2->fetch('edit_field.tpl.php');

		  //m2o ajax workaround 
		  //TODO check where we come from

//		  $input .= $GLOBALS['phpgw']->html->tinymce($field_name,$value,$style,$options);
//		  $input .= "";
		  
//		  return $input;
	   }
	
	   function formview_read($value, $config)
	   {
			return $value;   		
	   }
	
	   function listview_read($value, $config,$attr_arr)
	   {
		  if(strlen($value)>20)
		  {
			 //  $value = strip_tags(htmlentities($value));

			 $value = strip_tags($value);
			 $value = trim($value);
			 $title = substr($value,0,200);

			 $value = '<span title="'.$title.'">' . substr($value,0,20). ' ...' . '</span>';
		  }
		  return $value;   		
	   }

   
	   function setOptions($config)
	   {
		  //font family
		  if($config[standard_options][enable_font_properties])
		  {
			 $bar.= 'theme_advanced_buttons1_add_before : "fontselect",';
			 $bar.="\n";
			 $bar.= 'theme_advanced_buttons1_add: "fontsizeselect",';
			 $bar.="\n";
		  }
		  else
		  {
		 	$disable[]='fontselect'; 
			$disable[]='fontsizeselect'; 
			$disable[]='bold'; 
			$disable[]='italic'; 
			$disable[]='underline'; 
			$disable[]='strikethrough'; 
			$disable[]='sub'; 
			$disable[]='sup'; 
		 }

		  //tables
		  if($config[standard_options][enable_tables])
		  {
			 $bar.= 'theme_advanced_buttons3_add_before : "tablecontrols",';
			 $bar.="\n";
			 $plugins[]='table';
		  }

		  //fullscreen
		  if($config[standard_options][enable_fullscreen])
		  {
			 $plugins[]='fullscreen';
		  }

		  //not working
		  if($config[notworking])
		  {
			 $disable[]='justifyleft'; 
			 $disable[]='justifyright'; 
			 $disable[]='justifycenter'; 
			 $disable[]='justifyfull'; 
			 $disable[]='formatselect'; 
			 $disable[]='cut'; 
			 $disable[]='copy'; 
			 $disable[]='paste'; 
			 $disable[]='undo'; 
			 $disable[]='redo'; 
			 $disable[]='bullist'; 
			 $disable[]='numlist'; 
			 $disable[]='indent'; 
			 $disable[]='outdent'; 
			 $disable[]='hr'; 
		  	 $disable[]='code'; 
		  }
	
		  //fixme doesnt work
		  if(!$config[standard_options][enable_colors])
		  {
			 $disable[]='forecolor'; 
			 $disable[]='backcolor'; 
		  }

		  if(!$config[standard_options][enable_link])
		  {
			 $disable[]='link'; 
			 $disable[]='unlink'; 
			 $disable[]='anchor'; 
		  }

		  if(!$config[standard_options][enable_simple_image])
		  {
			 $disable[]='image'; 
		  }

		  if(is_array($config[plugins]))
		  {
			 foreach($config[plugins] as $plugin)
			 {
				$plugins[]=$plugin; 
			 }
		  }

		  if($config[plugins][ibrowser])
		  {
			 if($this->local_bo->so->config[server_type]=='dev')
			 {
				$field_prefix='dev_';
			 }
			 if($this->local_bo->site_object[$field_prefix.'upload_path'])
			 {
				$upload_path=$this->local_bo->site_object[$field_prefix.'upload_path'];
				$upload_url=$this->local_bo->site_object[$field_prefix.'upload_url'];
			 }
			 elseif($this->local_bo->site[$field_prefix.'upload_path'])
			 {
				$upload_path=$this->local_bo->site[$field_prefix.'upload_path'];
				$upload_url=$this->local_bo->site[$field_prefix.'upload_url'];
			 }

			 $sessdata = array(
				'upload_dir' =>   $upload_path, 
				'upload_url' =>   $upload_url,
			 );

			 $GLOBALS['phpgw']->session->appsession('iBrowser','phpgwapi',$sessdata);
		  }





		  if(!$config[select_theme])
		  {
			 $config[select_theme]='advanced';
		  }

		  if(is_array($plugins))
		  {
			 $plug_str.=implode(',',$plugins);
		  }

		  $bar.='plugins : "'.$plug_str.'",'; $bar.="\n";

		  $bar.= 'theme : "'.$config[select_theme].'",'; $bar.="\n";

		  $bar.= 'theme_advanced_toolbar_location : "top",'; $bar.="\n";
		  $bar.= 'theme_advanced_path_location : "bottom",'; $bar.="\n";
		  $bar.= 'theme_advanced_layout_manager : "SimpleLayout",'; $bar.="\n";
		  $bar.= 'theme_advanced_toolbar_align : "left",'; 

		  if(is_array($disable))
		  {
			 $disab_str.=implode(',',$disable);
		  }

		  if($config[content_css_file]) 
		  {
			 $site_fs= createobject('jinn.site_fs');
			 $siterootdir=$site_fs->get_jinn_sitefile_url($this->local_bo->site[site_id]);
			 $content_css_file=$siterootdir . SEP .'tinymce'.SEP.$config[content_css_file];

			 $bar.= 'content_css : "'.$content_css_file.'",'; 
		  }

/*		  $bar.='	  
		  theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor",
		  theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
		  theme_advanced_buttons3_add : "ibrowser,emotions,iespell,flash,advhr,separator,print,separator,ltr,rtl,separator,fullscreen",
		  plugin_insertdate_dateFormat : "%Y-%m-%d",
		  plugin_insertdate_timeFormat : "%H:%M:%S",
		  ';*/
		  $bar.='	  
		  theme_advanced_buttons2_add : "insertdate,inserttime,preview,zoom,forecolor,backcolor",
		  theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,search,replace",
		  theme_advanced_buttons3_add : "ibrowser,emotions,iespell,flash,advhr,print,ltr,rtl,fullscreen",
		  plugin_insertdate_dateFormat : "%Y-%m-%d",
		  plugin_insertdate_timeFormat : "%H:%M:%S",
		  ';

		  $bar.='theme_advanced_disable : "'.$disab_str.'"';
		  
		  if(!$config['advanced_settings']['relative_urls'])
		  {
			 $bar.="\n,relative_urls : false\n";
		  }
		  /*
		  if($config[document_base] != '')
		  {
			 $bar .="\n,document_base_url : '{$config[document_base]}'\n";
		  }
		  */
		  return $bar;

	 }
  }
?>
