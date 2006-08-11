<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Authors: Pim Snel, Lex Vogelaar for Lingewoud
   Copyright (C)2002, 2004 Pim Snel <pim@lingewoud.nl>

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
   */

   /**
   * db_fields_plugin_filemanager 
   * 
   * @todo show extra file information in box (size,format,filetype,name etc...)
   * @todo create missing thumbs for listview
   * @todo more html to templates
   * @todo integrate code as JiNN main filemanager
   * @todo make more uploadbox possible in filemanager window
   * @todo edit images...
   * @todo files with comma's are not listed correctly
   * @test with mulitple OK
   * @test in all browsers IE6, MOZILLA OK
   * @test unknown and flash
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class db_fields_plugin_filemanager
   {
	  var $javascript_inserted = false;
	  var $spacer = 'jinn/plugins/db_fields_plugins/__filemanager/img/spacer.gif';
	  var $unknown = "jinn/plugins/db_fields_plugins/__filemanager/img/unknown.png";
	  var $unknown_style = '';
	  var $filetypes;
	  var $tplsav2;
	  var $fm_helper;

	  function db_fields_plugin_filemanager()
	  {
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');

		 require_once 'class.filemanager_helper.inc.php';
		 $this->fm_helper = new filemanager_helper();

		 require_once 'class.filetypes.php';
		 $this->filetypes = new filetypes();
	  }

	  function formview_edit($field_name,$value,$config,$attr_arr)
	  {	
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
		 $this->tplsav2->assign('config',$config);

		 $upload_path = $this->local_bo->cur_upload_path();
		 $helper_id   = $this->local_bo->plug->registry->plugins['filemanager']['helper_fields_substring'];

		 // Check if everything is set to upload files
		 if(!$upload_path)
		 {
			$this->tplsav2->error_msg=lang('The path to upload images is not set, please contact your JiNN administrator.');
			return $this->tplsav2->fetch('filemanager.error.tpl.php');
		 }
		 elseif(!file_exists($upload_path))
		 {
			$this->tplsav2->error_msg=lang('The path to upload images is not correct, please contact your JiNN administrator.');
			return $this->tplsav2->fetch('filemanager.error.tpl.php');
		 }


		 if(!$this->javascript_inserted)
		 {
			$input .= $this->add_javascript();
		 }

		 if (is_numeric($config[Max_files])) 
		 {
			$num_input =$config[Max_files];
		 }
		 else 
		 {
			$num_input=10;
		 }

		 $stripped_name=substr($field_name,6);	//the real field name
		 $prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
		 $prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)

		 $this->tplsav2->assign('prefix',$prefix);
		 $this->tplsav2->assign('stripped_name',$stripped_name);
		 $this->tplsav2->assign('field_name',$field_name);

		 if(trim($value))
		 {
			$this->tplsav2->assign('value',$value);
			$value_arr=explode(';',$value);
		 }
		 else
		 {
			$value_arr = array();
		 }

		 if($num_input <  count($value_arr))
		 {
			$counter =count($value_arr);
		 }
		 else
		 {
			$counter = $num_input;
		 }
		 $this->tplsav2->assign('counter',$counter);

		 $m2o_sess = $this->local_bo->session['m2o_obj_id'];
		 if($m2o_sess)
		 {
			$this->tplsav2->assign('curr_obj_id',$m2o_sess);
		 }

		 $this->tplsav2->assign('value_arr',$value_arr);

		 if(is_array($value_arr) && count($value_arr)>0)
		 {
			$i=0;

			foreach($value_arr as $file_path)
			{
			   $i++;

			   $showfile = $this->show_file($file_path, true, $field_name, $i);
			   $this->tplsav2->assign('showfile',$showfile);
			   $this->tplsav2->assign('i',$i);

			   $fullrows.=$this->tplsav2->fetch('filemanager.fullrows.tpl.php');
			}
		 }
		 $this->tplsav2->assign('fullrows',$fullrows);

		 if(count($value_arr) < $num_input)
		 {
			for($i = (count($value_arr)+1); $i < ($num_input+1); $i++)
			{
			   $name = $prefix.'_IMG_'.$stripped_name.$i;
			   $span_id = $prefix.'_PATH_'.$stripped_name.$i;

			   $showfile .= '<img id="'.$name.'" src="'.$this->spacer.'" '.$this->spacer_style.' />';
			   $showfile .= '<br/><span id="'.$span_id.'"></span>';

			   $this->tplsav2->assign('i',$i);
			   $this->tplsav2->assign('showfile',$showfile);
			   $this->tplsav2->assign('slot',$slot);

			   $empt_rows.=$this->tplsav2->fetch('filemanager.fullrows.tpl.php');
			}
		 }

		 $this->tplsav2->assign('empt_rows',$empt_rows);

		 /* add extra images file container here */

		 $input.=$this->tplsav2->fetch('filemanager.formview_edit.tpl.php');

		 return $input;
	  }

	  function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  {
		 $stripped_name=substr($field_name,6);	//the real field name
		 $prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
		 $helper_id = $this->local_bo->plug->registry->plugins['filemanager']['helper_fields_substring'];
		 $prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)

		 $upload_path=$this->local_bo->cur_upload_path();
		 $upload_url =$this->local_bo->cur_upload_url ();

		 $images_array=explode(';',$HTTP_POST_VARS[$prefix.'_IMG_ORG_'.$stripped_name]);
		 $images_edited=$this->local_bo->filter_array_with_prefix($HTTP_POST_VARS, $prefix.'_IMG_EDIT_'.$stripped_name);

		 if(is_array($images_edited))
		 {
			foreach($images_edited as $key => $value)
			{
			   if($value == 'delete')
			   {
				  unset ($images_array[$key]);
			   }
			   else if($value != '') //add new image
			   {
				  $value = str_replace($upload_url, '', $value);
				  $images_array[$key] = $value;
			   }
			}
		 }

		 if(is_array($images_array))
		 {
			//check max images
			if($config['Allow_more_then_max_files']=='False')
			{
			   if( count($images_array) > $config[Max_files] )
			   {
				  $images_array=array_slice($images_array, 0, $config[Max_files]);
			   }	
			}
			foreach ($images_array as $image_string)
			{
			   $image_string = str_replace('//','/',$image_string); //fixme: this fixes a bug in the popup. Better solve that..
			   if($image_path_new) $image_path_new .= ';';
			   $image_path_new.=$image_string;
			}						
		 }
		 // make return array for storage
		 if($image_path_new)
		 {
			return $image_path_new;
		 }
		 elseif($image_path_changed)
		 {
			return null;
		 }

		 return '-1'; // return -1 when there no value to give but the function finished succesfully
	  }

	  function formview_read($value,$config)
	  {
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
		 if(trim($value))
		 {
			$value=explode(';',$value);
			if (is_array($value) && count($value)>0)
			{
			   foreach($value as $file_path)
			   {
				  $this->tplsav2->files[]=$this->show_file($file_path);
			   }
			}
			else
			{
			   $this->tplsav2->files=array();
			}
		 }

		 return $this->tplsav2->fetch('filemanager.formview_read.tpl.php');
	  }

	  function listview_read($value,$config,$where_val_enc)
	  {
		 $imgiconsrc=$GLOBALS[phpgw]->common->image('jinn','imageicon');
		 $stripped_name=substr($field_name,6);	

		 $upload_url =$this->local_bo->cur_upload_url ();
		 $upload_path=$this->local_bo->cur_upload_path();

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
				  if(is_file($upload_path . SEP . $file_path))
				  {
					 $imglink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path.SEP.$file_path);

					 // FIXME move code to class
					 $image_size=getimagesize($upload_path . SEP. $file_path);
					 $pop_width = ($image_size[0]+50);
					 $pop_height = ($image_size[1]+50);

					 $popup = "img_popup('".base64_encode($imglink)."','$pop_width','$pop_height');";
				  }

				  unset($thumblink); 

				  $path_array = explode('/', $file_path);
				  $path_array[count($path_array)-1] = '..'.$path_array[count($path_array)-1];
				  $thumb_path = implode('/', $path_array);

				  /* check for thumb and create previewlink */
				  if(is_file($upload_path . SEP . $thumb_path))
				  {
					 $thumblink='<img src="'.$upload_url . SEP . $thumb_path.'" alt="'.$i.'">';
				  }
				  else
				  {
					 $thumblink='<img src="'.$imgiconsrc.'" alt="'.$i.'">';
				  }

				  if($imglink) $display.='<a href="javascript:'.$popup.'">'.$thumblink.'</a>';
				  else $display.=' '.$thumblink;
				  $display.=' ';

			   }
			}
		 }

		 return $display;
	  }

	  function show_file($file_path, $edit=false, $field_name='', $i='')
	  {
		 $upload_path	= $this->local_bo->cur_upload_path();
		 $helper_id		= $this->local_bo->plug->registry->plugins['filemanager']['helper_fields_substring'];

		 $stripped_name = substr($field_name,6);		//the real field name
		 $prefix = substr($field_name,0,6); 			//the prefix used to identify records in a multi record view
		 $prefix .= $helper_id;							//the helper id will help identifying which post vars to ignore when saving the record(s)
		 $this->tplsav2->name = $name = $prefix.'_IMG_'.$stripped_name.$i;
		 $this->tplsav2->span_id = $span_id = $prefix.'_PATH_'.$stripped_name.$i; //????

		 $absolute_file_path=realpath($upload_path.SEP.$file_path);	
		 $file_name=basename($file_path);
		 $this->tplsav2->file_name = $file_name;
		 $dir_name=dirname($absolute_file_path);

		 //retrieve file info
		 $file_info_arr = $this->fm_helper->get_file_info($absolute_file_path);

		 if($file_info_arr['not_exist'])
		 {
			//$ret= '<img id="'.$name.'" src="'.$this->spacer.'" '.$this->spacer_style.' />';
			//$ret.= '<br/><span id="'.$span_id.'"></span>';

			$this->tplsav2->imglink=$this->spacer;
			$this->tplsav2->error_msg=lang('File does not exist on server, (%1)',$file_path);
			$this->tplsav2->file_name=$this->tplsav2->fetch('filemanager.error.tpl.php');

			$ret.= $this->tplsav2->fetch('filemanager.showfile_img.tpl.php');
			return $ret;
		 }
		 elseif($file_info_arr['type_gifjpgpng'])
		 {
			$absolute_thumb_path=$dir_name.'/.'.$file_name;

			// if thumb exist
			if(is_file($absolute_thumb_path))
			{
			   $this->tplsav2->imglink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$absolute_thumb_path);
			   $this->tplsav2->is_thumb=true;
			}
			else
			{
			   $this->tplsav2->imglink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$absolute_file_path);
			}

			if($file_info_arr['img_width']>150)
			{
			   $pop_width = ($file_info_arr['img_width']+50);
			   $pop_height = ($file_info_arr['img_height']+50);
			   $imglink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$absolute_file_path);

			   $this->tplsav2->popup = "img_popup('".base64_encode($imglink)."','$pop_width','$pop_height');";
			}

			return $this->tplsav2->fetch('filemanager.showfile_img.tpl.php');
		 }
		 elseif($file_info_arr['type_flash'])
		 {
			$this->tplsav2->span_id = &$span_id;	
			$this->tplsav2->fileurl=$this->local_bo->cur_upload_url().'/'.$file_path;

			//test this streaming link for flash
			$this->tplsav2->filelink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$absolute_file_path);

			$this->tplsav2->file_spec = @GetImageSize($absolute_file_path);						
			$this->tplsav2->file_width = ($file_spec[0]>=$file_spec[1]) ? 80 : round($file_spec[0]/($file_spec[1]/80)) ;
			$this->tplsav2->file_height = ($file_spec[1]>=$file_spec[0]) ? 80 : round($file_spec[1]/($file_spec[0]/80)) ;

			$this->tplsav2->flash_icon=$GLOBALS['phpgw_info']['server']['webserver_url'].'/jinn/plugins/db_fields_plugins/__filemanager/img/flash.png';
			$this->tplsav2->flash_js=$GLOBALS['phpgw_info']['server']['webserver_url'].'/jinn/plugins/db_fields_plugins/__filemanager/js/flash.js';


			return $this->tplsav2->fetch('filemanager.showfile_flash.tpl.php');
		 }
		 elseif($file_info_arr['type_unknown'])
		 {
			$this->tplsav2->filelink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$absolute_file_path);
			$this->tplsav2->unknown_icon = &$this->unknown;
			$this->tplsav2->linkid = &$linkid;	
			$this->tplsav2->span_id = &$span_id;	

			return $this->tplsav2->fetch('filemanager.showfile_unknown.tpl.php');
		 }
	  }

	  function add_javascript()
	  {
		 $this->javascript_inserted = true;
		 $this->tplsav2->assign('type_id_image',$this->filetypes->type_id_image);
		 $this->tplsav2->assign('type_id_other',$this->filetypes->type_id_other);
		 $this->tplsav2->assign('unknown',$this->unknown);
		 $this->tplsav2->assign('spacer',$this->spacer);
		 return $this->tplsav2->fetch('filemanager.topjavascript.tpl.php');
	  }
   }
?>
