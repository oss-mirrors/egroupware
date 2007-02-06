<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Copyright (C)2005 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN
   ---------------------------------------------------------------------
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

   /* $Id$ */

   /**
   * Generate Image Menu Items
   *
   * @package jinn_plugins
   * @author pim-AT-lingewoud-DOT-nl
   * @copyright (c) 2005 by Pim Snel
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class db_fields_plugin_attachpath
   {
	  /*!
	  @function plg_fi_attachpath
	  @fixme remove attachments dir
	  @fixme add file selector for remote files
	  @fixme add mimetype icons
	  */
	  function formview_edit($field_name, $value, $config,$attr_arr)
	  {	
		 $upload_path = $this->local_bo->cur_upload_path();

		 if($config['Store_full_path'])
		 {
			$download_path='';
		 }
		 else
		 {
			$download_path=$upload_path.SEP;
		 }

		 /* Check if everything is set to upload files */ 
		 if(!$upload_path)
		 {
			$input=lang('The path to upload files is not set, please contact your JiNN administrator.');
			return $input;
		 }
		 elseif(!file_exists($upload_path))
		 {
			$input=lang('The path to upload files is not correct, please contact your JiNN administrator.');
			return $input;
		 }
		 elseif(!is_dir($upload_path.SEP.'attachments') && !mkdir($upload_path.SEP.'attachments', 0755))
		 {
			$input=lang('The attachments subdirectory does not exist and cannot be created ...');
			$input.=lang('Please contact Administrator with this message.');
			return $input;
		 }
		 elseif(!touch($upload_path.SEP.'attachments'.SEP.'JiNN_write_test'))
		 {
			$input=lang('The attachments subdirectory is not writable by the webserver ...');
			$input.=lang('please contact Administrator with this message');
			return $input;
		 }

		 /* everything ok, remove temporary file */
		 unlink($upload_path.SEP.'attachments'.SEP.'JiNN_write_test');

		 $stripped_name=substr($field_name,6);	

		 /* if value is set, show existing files */	
		 if($value)
		 {
			$input='<input type="hidden" name="ATT_ORG'.$field_name.'" value="'.$value.'">';

			$value=explode(';',$value);

			/* there are more files */
			if (is_array($value))
			{
			   $i=0;
			   foreach($value as $att_path)
			   {
				  $i++;

				  $input.=$i.'. ';

				  $tmp_arr=explode(SEP,$att_path);
				  $name=$tmp_arr[count($tmp_arr)-1];
				  $filelink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$download_path.$att_path);

				  $input.='<b><a href="'.$filelink.'">'.$name.'</a></b>';


				  $input.=' <input type="checkbox" value="'.$att_path.'" name="ATT_DEL'.$field_name.$i.'"> '.lang('remove').'<br>';
			   }
			}
			/* there's just one image */
			else
			{
			   $input=$att_path.'<input type="checkbox" value="'.$att_path.'" name="ATT_DEL'.$field_name.'"> '.lang('remove').'<br>';
			}
		 }

		 /* get max attachments, set max 5 filefields */
		 if (is_numeric($config[Max_files])) 
		 {
			if ($config[Max_files]>30) $num_input=30;
			else $num_input =$config[Max_files];
		 }
		 else 
		 {
			$num_input=30;
		 }

		 $max_file_size=intval($config['Max_attachment_size_in_megabytes_Leave_empty_to_have_no_limit']);
		 if($max_file_size && is_int($max_file_size))
		 {
			$max_file_size_in_bytes=$max_file_size * 1024 * 1024;
			$input .=lang('The max. file upload size is %1 Mb.',$max_file_size);
			$input .='<input type="hidden" name="MAX_FILE_SIZE" value="'.$max_file_size_in_bytes.'">';
		 }
		 elseif($config['Max_attachment_size_in_megabytes_Leave_empty_to_have_no_limit'])
		 {
			$input .='<input type="hidden" name="MAX_FILE_SIZE" value="1048576">';
			$input .=lang('The administrator set an invalid max. file upload size. For safety reasons we now use a max. filesize of 1Mb. Please contact your JiNN administrator.');
		 }

		 for($i=1;$i<=$num_input;$i++)
		 {
			$input .='<br/><hr/>';
			$input .=($num_input==1?lang('add attachment'):lang('add attachment %1', $i));
			$input .=' <input  class="egwbutton" type="file" name="ATT_SRC'.$field_name.$i.'">';

			if($config[Activate_manual_path_input]=='True')
			{
			   $input.='<br/><br/>'.lang('Manually enter a new relative file path').'<input type="text" name="ATT_MAN'.$field_name.$i.'" style="width:300px"><br/>';
			}

		 }

		 $input.='<hr/><input type="checkbox" value="'.$att_path.'" name="ATT_FLUSH"> '.lang('Remove all').'<br/>';
		 $input.='<input type="hidden" name="'.$field_name.'" value="TRUE">';

		 return $input;
	  }

	  function on_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  //function plg_sf_attachpath($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  /****************************************************************************\
	  * main image data function                                                   *
	  \****************************************************************************/
	  {
		 /* remove all */
		 if($_POST[ATT_FLUSH]) 
		 {
			return -1;
		 }

		 $upload_path = $this->local_bo->cur_upload_path();

		 if($config['Store_full_path'])
		 {
			$path_in_db=$upload_path.SEP;
		 }
		 else
		 {
			$path_in_db='';
		 }


		 $atts_to_delete=$this->local_bo->filter_array_with_prefix($_POST,'ATT_DEL');

		 if (count($atts_to_delete)>0){

			$atts_path_changed=True;
			// delete from harddisk
			foreach($atts_to_delete as $att_to_delete)
			{
			   if (!@unlink($upload_path.SEP.$att_to_delete)) $unlink_error++;
			}

			$atts_org=explode(';',$HTTP_POST_VARS['ATT_ORG'.$field_name]);
			foreach($atts_org as $att_org)
			{
			   if (!in_array($att_org,$atts_to_delete))
			   {
				  if ($atts_path_new) $atts_path_new.=';';
				  $atts_path_new.=$att_org;
			   }
			}
		 }
		 else
		 {
			$atts_path_new.=$_POST['ATT_ORG'.$field_name];
		 }

		 /* make array again of the original attachment */
		 $atts_array=explode(';',$atts_path_new);
		 unset($atts_path_new);

		 /* finally adding new attachments */

		 $atts_to_add=$this->local_bo->filter_array_with_prefix($HTTP_POST_FILES,'ATT_SRC'.$field_name);

		 // quick check for new attchments
		 if(is_array($atts_to_add))
		 foreach($atts_to_add as $attscheck)
		 {
			if($attscheck['name']) $num_atts_to_add++;
		 }

		 if ($num_atts_to_add)
		 {
			$att_position=0;
			foreach($atts_to_add as $add_att)
			{
			   if($add_att['name'])
			   {
				  $new_temp_file=$add_att['tmp_name']; // just copy

				  $target_att_name = ereg_replace("[^a-zA-Z0-9_.]", '_', $add_att['name']);

				  /* prevent overwriting files with the same name */
				  $copynum=0;
				  while(file_exists($upload_path.SEP.'attachments'.SEP.$target_att_name))
				  {
					 if(substr($target_att_name,1,1)=='_') 
					 {
						$target_att_name=substr($target_att_name,2);
					 }
					 $target_att_name=$copynum++.'_'.$target_att_name;
				  }

				  // FIXME better use move
				  if (copy($new_temp_file, $upload_path.SEP.'attachments'.SEP.$target_att_name))
				  {
					 $atts_array[$att_position]=$path_in_db.'attachments'.SEP.$target_att_name;
				  }
				  else
				  {
					 die(lang('failed to copy: %1 to %2 ...',$new_temp_file,'$upload_path'.SEP.'attachments'.SEP.$target_att_name));
				  }
			   }

			   $att_position++;

			}
		 }

		 // FIXME check if a file was not uploaded for this att number
		 /* manual added files */
		 $man_atts_to_add=$this->local_bo->filter_array_with_prefix($HTTP_POST_VARS,'ATT_MAN');
		 if(is_array($man_atts_to_add))
		 {
			foreach($man_atts_to_add as $att_name)
			{
			   if($att_name)
			   {
				  $atts_array[]=$path_in_db.'attachments'.SEP.$att_name;
			   }
			}

		 }


		 if(is_array($atts_array))
		 {
			foreach ($atts_array as $atts_string)
			{

			   if($atts_path_new) $atts_path_new .= ';';
			   $atts_path_new.=$atts_string;
			}						
		 }


		 if($atts_path_new)
		 {
			return $atts_path_new;
		 }
		 elseif($atts_path_changed || !$_POST['ATT_ORG'.$field_name])
		 {
			return '-1';
		 }
		 else
		 {
			return null; /* return -1 when there no value to give but the function finished succesfully */
		 }
	  }

	  function formview_read($value,$config)
	  {

		 $upload_path = $this->local_bo->cur_upload_path();

		 if($config['Store_full_path'])
		 {
			$download_path='';
		 }
		 else
		 {
			$download_path=$upload_path.SEP;
		 }

		 if($value)
		 {
			$value=explode(';',$value);
		 }

		 if (is_array($value))
		 {
			$i=0;
			foreach($value as $att_path)
			{
			   $i++;

			   $input.=$i.'. ';

			   $tmp_arr=explode(SEP,$att_path);
			   $name=$tmp_arr[count($tmp_arr)-1];
			   $filelink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$download_path.$att_path);

			   $input.='<b><a href="'.$filelink.'">'.$name.'</a></b>';
			}
		 }
		 /* there's just one image */
		 else
		 {
			$input=$att_path;
		 }

		 return $input;
	  } 
	  
	  
	  function listview_read($value,$config,$where_val_enc)
	  {

		 $stripped_name=substr($field_name,6);	

		 
		 $upload_path = $this->local_bo->cur_upload_path();

		 if($config['Store_full_path'])
		 {
			$download_path='';
		 }
		 else
		 {
			$download_path=$upload_path.SEP;
		 }

		 /* if value is set, show existing images */	
		 if($value)
		 {
			$value=explode(';',$value);

			/* there are more images */
			if (is_array($value))
			{
			   $i=0;
			   foreach($value as $file_path)
			   {
				  $i++;

				  unset($imglink); 
				  unset($popup); 

				  /* check for image and create previewlink */
				  if(is_file($download_path . SEP . $file_path))
				  {
					 $tmp_arr=explode(SEP,$file_path);
					 $name=$tmp_arr[count($tmp_arr)-1];
					 $link=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$download_path.$file_path);
				  }

				  if($link) $display.='<a href="'.$link.'">'.$i.'</a>';
				  else $display.=' '.$i;
				  $display.=' ';

			   }
			}
		 }

		 return $display;


	  }








	  /**
	  * Form edit plugin which return html to use in the form
	  *
	  * This one askes for text input to generate the menu's from, 
	  * it also shows the available generated images
	  *
	  * @param string $field_name name of the calling field
	  * @param string $value value of the calling field
	  * @param array $config contains the stored configuration data of this field concerning this plugin
	  * @param array $attr_arr this can contain dynamicly added attributes when the field metadata is read which can change the behaviour of the plugin.
	  * @return string/array normally return the generated html to create the input
	  * @todo implement more than one image
	  */
	  function xxxformview_edit($field_name, $value, $config,$attr_arr)
	  {
		 $upload_url=$this->local_bo->cur_upload_url();

		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');

		 $stripped_name=substr($field_name,6);	//the real field name
		 $prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
		 $helper_id = $this->local_bo->plug->registry->plugins['gen_menu_img']['helper_fields_substring'];
		 $prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)

		 if($value) $img_arr=unserialize(base64_decode($value));

		 $img_arr[0]=strip_tags($img_arr[0]);
		 $img_arr[0]=str_replace('"','&quot;',$img_arr[0]);

		 $this->tplsav2->assign('prefix',$prefix);
		 $this->tplsav2->assign('stripped_name',$stripped_name);
		 $this->tplsav2->assign('field_name',$field_name);
		 $this->tplsav2->assign('value',$value);
		 $this->tplsav2->assign('text',$img_arr[0]);
		 $this->tplsav2->assign('input_text_name',$prefix.'_TXT_'.$stripped_name);
		 for($i=1;$i<($config[numimg]+1);$i++)
		 {
			$this->tplsav2->assign('img_src'.$i,$upload_url.SEP.$img_arr[$i]);
		 }

		 $input=$this->tplsav2->fetch('gen_menu_img.formview_edit.tpl.php');
		 return $input;
	  }

	  /**
	  * filter or manipulate the post value of a field before it is stored in 
	  * the database
	  *
	  * This one generates the images using the user submitted text and the 
	  * fonts and other data from the plugin config array
	  *
	  * @param string $key which referers to the fielddata in the _POST array
	  * @param array $HTTP_POST_VARS the _POST array as if the form contains one single record
	  * @param array $HTTP_POST_FILES the _FILES array as if the form contains one single record
	  * $param array $config the plugin config array of this field
	  * @return string returns a serialized array with all img paths and the first element contains the text as string
	  * @todo implement the last two images
	  * @todo tweak the parameters
	  * @todo prevent auto remove the old images garbage 
	  * @cleanup form lay-out
	  */
	  function xxxon_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  {
		 $upload_path=$this->local_bo->cur_upload_path();
		 $upload_url=$this->local_bo->cur_upload_url();

		 //fixme this must come from the localbo why can I not use it?
		 $site_fs= createobject('jinn.site_fs');

		 $siterootdir=$site_fs->get_jinn_sitefile_path($this->local_bo->site[site_id]);
		 $plug_conf_arr = $this->local_bo->plug->registry->plugins['gen_menu_img']['config2'];

		 $stripped_name=substr($key,6);	//the real field name
		 $prefix = substr($key,0,6); 	//the prefix used to identify records in a multi record view
		 $helper_id = $this->local_bo->plug->registry->plugins['gen_menu_img']['helper_fields_substring'];
		 $prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)

		 $text=$HTTP_POST_VARS[$prefix.'_TXT_'.$stripped_name];
		 $img[0] = $text;

		 $uniquename=uniqid('img');
		 for($i=1;$i<($config[numimg]+1);$i++)
		 {
			$fontfile=$config['fontfile'.$i];
			$fontpath=$siterootdir . SEP .$plug_conf_arr['fontfile'.$i][subdir].SEP.$fontfile;
			$fontsize=$config['fontsize'.$i];
			$fontcolor=$config['fontcolor'.$i];
			$bgimg=$config['bgimg'.$i];
			$bgimgpath = ($bgimg?$siterootdir . SEP .$plug_conf_arr['bgimg'.$i][subdir].SEP.$bgimg:'');
			$bgcolor=$config['bgcolor'.$i];
			$imgheight=$config['imgheight'.$i];
			$toppadding=$config['paddingtop'.$i];
			$leftpadding=$config['paddingleft'.$i];
			$rightpadding=$config['paddingright'.$i];
			$tmpfile=$this->generate_image_from_text($text,$fontpath,$fontsize,$fontcolor,$bgimgpath,$bgcolor,$imgheight,$toppadding,$leftpadding,$rightpadding);

			$newimgfilename=$uniquename.$i.'.png';

			rename($tmpfile,$upload_path.SEP.$newimgfilename);

			#$img[] = $upload_url.SEP.$newimgfilename;
			$img[] = $newimgfilename;
		 }

		 $output = base64_encode(serialize($img));
		 return $output;
	  }

	  /**
	  * listview_read: return data to be displayed in the list view screen
	  *
	  * @param string $value value from the database
	  * @param array $config array with plugin config array for this field
	  * @param string $where_val_enc ????????
	  * @return text which is stored in the first element
	  * @todo complete phpdoc 
	  */
	  function xxxlistview_read($value,$config,$where_val_enc)
	  {
		 $upload_url=$this->local_bo->cur_upload_url();
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');

		 if($value) $img_arr=unserialize(base64_decode($value));

		 $this->tplsav2->assign('value',$value);
		 $this->tplsav2->assign('text',$img_arr[0]);
		 for($i=1;$i<($config[numimg]+1);$i++)
		 {
			$this->tplsav2->assign('img_src'.$i,$upload_url.SEP.$img_arr[$i]);
		 }

		 $input=$this->tplsav2->fetch('gen_menu_img.listview_read.tpl.php');
		 return $input;
	  }

	  /**
	  * formview_read 
	  * 
	  * @param mixed $value 
	  * @param mixed $config 
	  * @access public
	  * @return void
	  */
	  function xxxformview_read($value,$config)
	  {
		 $upload_url=$this->local_bo->cur_upload_url();
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');

		 $stripped_name=substr($field_name,6);	//the real field name
		 $prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
		 $helper_id = $this->local_bo->plug->registry->plugins['gen_menu_img']['helper_fields_substring'];
		 $prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)

		 if($value) $img_arr=unserialize(base64_decode($value));

		 $this->tplsav2->assign('prefix',$prefix);
		 $this->tplsav2->assign('stripped_name',$stripped_name);
		 $this->tplsav2->assign('field_name',$field_name);
		 $this->tplsav2->assign('value',$value);
		 $this->tplsav2->assign('text',$img_arr[0]);
		 $this->tplsav2->assign('input_text_name',$prefix.'_TXT_'.$stripped_name);
		 for($i=1;$i<($config[numimg]+1);$i++)
		 {
			$this->tplsav2->assign('img_src'.$i,$upload_url.SEP.$img_arr[$i]);
		 }

		 $input=$this->tplsav2->fetch('gen_menu_img.formview_read.tpl.php');
		 return $input;
	  }


	  /**
	  * generate_image_from_text: created file from text
	  *
	  * @param string $TEXT text to render
	  * @param string $FONTFILE path to font file
	  * @param int $FONTSIZE font size
	  * @param string $FONTCOLOR  color of text in hex
	  * @param string $BGIMG path to background image
	  * @param string $BGCOLOR color of background in hex
	  * @param int $IMGHEIGHT image height in pixels
	  * @param int $TOPPADDING top text padding in pixels
	  * @param int $LEFTPADDING left text padding in pixels
	  * @param int $RIGHTPADDING right text padding in pixels
	  * @return path to created temp image file  
	  * @todo finetune / remove as many constants as possible
	  * @todo height does not work
	  * @todo implement solution for unique image name
	  */
	  function xxxgenerate_image_from_text($TEXT,$FONTFILE,$FONTSIZE,$FONTCOLOR,$BGIMG,$BGCOLOR,$IMGHEIGHT,$TOPPADDING,$LEFTPADDING,$RIGHTPADDING)
	  {
		 if (isset($FONTSIZE))
		 {
			$getsize = $FONTSIZE;
		 }
		 else 
		 {
			$getsize = 8;
		 }

		 $size = imagettfbbox($getsize+0, 0, $FONTFILE, $TEXT);

		 $image = imagecreatetruecolor(abs($size[2]) + abs($size[0]), abs($size[7]) + abs($size[1]));
		 $new_width= abs($size[2]) + abs($size[0] + abs($LEFTPADDING) + abs($RIGHTPADDING));

		 $_fcolor=$this->hex2RGB($FONTCOLOR);

		 $_fontcolor = imagecolorallocate($image, $_fcolor[0], $_fcolor[1], $_fcolor[2]);

		 $newbgimg= ImageCreateTrueColor($new_width,$IMGHEIGHT);
		 if(trim($BGIMG))
		 {
			$_bgimg = imagecreatefrompng($BGIMG); 

			// TODO measure sizes of source image to use in the imagecopyresized function

			$bgimgwidth=1;

			imagecopyresized($newbgimg, $_bgimg, 0, 0, 0, 0, $new_width, $IMGHEIGHT, $bgimgwidth, $IMGHEIGHT);
		 }
		 elseif($BGCOLOR)
		 {
			$_bcolor=$this->hex2RGB($BGCOLOR);
			$_backgcolor = ImageColorAllocate($image,$_bcolor[0],$_bcolor[1],$_bcolor[2]);
			//TODO proper numbers in retangle function
			ImageFilledRectangle($newbgimg,0,0,$new_width,$IMGHEIGHT,$_backgcolor);
		 }

		 imagettftext($newbgimg, $getsize, 0, $LEFTPADDING, $getsize+$TOPPADDING, $_fontcolor, $FONTFILE, $TEXT);

		 $tmpfile = tempnam ($GLOBALS[phpgw_info][server][temp_dir],$helper_id ); 
		 imagepng($newbgimg,$tmpfile);

		 imagedestroy($image);

		 return $tmpfile;
	  }

	  /***
	  * gex2RGB: converts a hex col to rgb
	  *
	  * If color can't be converted it returns black
	  *
	  * @param string $hexcolor the hex color string
	  * @return array with three elements containing the rgb values; 
	  * @todo this function has to move to a general place in the egroupware API
	  */
	  function xxxhex2RGB($hexcolor) 
	  { 
		 if(!eregi("^#", $hexcolor)) 
		 { 
			$hexcolor = "#".$hexcolor; 
		 } 
		 if(eregi("^#[a-f0-9]{6}$", $hexcolor))
		 { 
			$color[0] = hexdec(substr($hexcolor,1,2)); 
			$color[1] = hexdec(substr($hexcolor,3,2)); 
			$color[2] = hexdec(substr($hexcolor,5,2)); 
			return $color; 
		 } 
		 else 
		 { 
			return array(0, 0, 0); 
		 } 
	  } 

	  /**
	  * this function is called when we use a custom form to configure our plugin
	  *
	  * @param array $config plugin config array
	  * @param the action link of the form
	  * @note we dont use this function , its ment for custom configurations, it could usefull:(
		 * @todo we can use this when its finished to beautify the config form
		 */
		 function xxxconfig_dialog($config,$form_action)
		 {
			$this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
			$this->tplsav2->addPath('template',$this->plug_root.'/tpl');
			$this->tplsav2->assign('config',$config);
			$this->tplsav2->assign('action',$form_action);
			$this->tplsav2->display('gen_menu_img.config.tpl.php');
		 }
	  }	
   ?>
