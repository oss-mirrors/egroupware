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
   class db_fields_plugin_gen_menu_img
   {
	  /**
	  * Constructor
	  */
	  function db_fields_plugin_gen_menu_img()
	  {
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
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
	  function formview_edit($field_name, $value, $config,$attr_arr)
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

		 $this->tplsav2->assign('upload_url',$upload_url.SEP);
		 $this->tplsav2->assign('img_src_arr',array_slice($img_arr,1));

		 $this->tplsav2->transbggrid=$GLOBALS['phpgw']->common->image('jinn','transbggrid.png');
		 

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
	  function on_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
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
		 
		 $i=1; 
		 foreach($config['multi1'] as $imgversion)
		 {
			$i++;
			$fontfile=$imgversion['fontfile'];
			$fontpath=$siterootdir . SEP .$plug_conf_arr['multi1'][items]['fontfile'][subdir].SEP.$fontfile;
			$fontsize=$imgversion['fontsize'];
			$fontcolor=$imgversion['fontcolor'];
			$bgimg=$imgversion['bgimg'];
			$bgimgpath = ($bgimg?$siterootdir . SEP .$plug_conf_arr['multi1'][items]['bgimg'][subdir].SEP.$bgimg:'');
			$bgcolor=$imgversion['bgcolor'];
			$imgheight=$imgversion['imgheight'];
			$toppadding=$imgversion['paddingtop'];
			$leftpadding=$imgversion['paddingleft'];
			$rightpadding=$imgversion['paddingright'];
			$tmpfile=$this->generate_image_from_text($text,$fontpath,$fontsize,$fontcolor,$bgimgpath,$bgcolor,$imgheight,$toppadding,$leftpadding,$rightpadding);

			$newimgfilename=$uniquename.'_'.$i.'.png';

			rename($tmpfile,$upload_path.SEP.$newimgfilename);

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
	  function listview_read($value,$config,$where_val_enc)
	  {
		 $upload_url=$this->local_bo->cur_upload_url();
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');

		 if($value) $img_arr=unserialize(base64_decode($value));

		 $this->tplsav2->assign('value',$value);
		 $this->tplsav2->assign('text',$img_arr[0]);
		 $this->tplsav2->assign('upload_url',$upload_url.SEP);
		 $this->tplsav2->assign('img_src_arr',array_slice($img_arr,1));

		 $this->tplsav2->transbggrid=$GLOBALS['phpgw']->common->image('jinn','transbggrid.png');
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
	   function formview_read($value,$config)
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

		  $this->tplsav2->assign('upload_url',$upload_url.SEP);
		  $this->tplsav2->assign('img_src_arr',array_slice($img_arr,1));

		  $this->tplsav2->transbggrid=$GLOBALS['phpgw']->common->image('jinn','transbggrid.png');

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
	  function generate_image_from_text($TEXT,$FONTFILE,$FONTSIZE,$FONTCOLOR,$BGIMG,$BGCOLOR,$IMGHEIGHT,$TOPPADDING,$LEFTPADDING,$RIGHTPADDING)
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
		 elseif(!$BGCOLOR || $BGCOLOR=='none')
		 {
		//	if($BGCOLOR=='none')
		//	{
			   imageSaveAlpha($newbgimg, true);
			   ImageAlphaBlending($newbgimg, false);
			   $white = imagecolorallocate($image, 255, 255, 255);
			   $_backgcolor = imagecolorallocatealpha($newbgimg, 255,255, 255, 127);
			   //$_backgcolor = imagecolortransparent($image, $white);
		//	}
		//	else
		//	{
//			   $_bcolor=$this->hex2RGB($BGCOLOR);
//			   $_backgcolor = ImageColorAllocate($image,$_bcolor[0],$_bcolor[1],$_bcolor[2]);
		//	}
			//TODO proper numbers in retangle function
			ImageFilledRectangle($newbgimg,0,0,$new_width,$IMGHEIGHT,$_backgcolor);
		 }
		 else
		 {
			$_bcolor=$this->hex2RGB($BGCOLOR);
			$_backgcolor = ImageColorAllocate($image,$_bcolor[0],$_bcolor[1],$_bcolor[2]);
			ImageFilledRectangle($newbgimg,0,0,$new_width,$IMGHEIGHT,$_backgcolor);
			/*			imageSaveAlpha($newbgimg, true);
			ImageAlphaBlending($newbgimg, false);
			$white = imagecolorallocate($newbgimg, 5, 5, 5);
			#			$_backgcolor = imagecolortransparent($newbgimg, $white);
			$_backgcolor = imagecolorallocatealpha($newbgimg, 255,255, 255, 127);
			#$_backgcolor = imagecolortransparent($newbgimg);
			ImageFilledRectangle($newbgimg,0,0,$new_width,$IMGHEIGHT,$_backgcolor);
			*/
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
	  function hex2RGB($hexcolor) 
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
		 function config_dialog($config,$form_action)
		 {
			$this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
			$this->tplsav2->addPath('template',$this->plug_root.'/tpl');
			$this->tplsav2->assign('config',$config);
			$this->tplsav2->assign('action',$form_action);
			$this->tplsav2->display('gen_menu_img.config.tpl.php');
		 }
	  }	
   ?>
