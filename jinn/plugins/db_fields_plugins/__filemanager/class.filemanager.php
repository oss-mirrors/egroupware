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
	  var $spacer = 'jinn/plugins/db_fields_plugins/plugin_images/spacer.gif';
	  var $unknown = "jinn/plugins/db_fields_plugins/__filemanager/popups/ImageManager/unknown.png";
	  var $unknown_style = '';
	  var $filetypes;
	  var $tplsav2;

	  function db_fields_plugin_filemanager()
	  {
		 require_once 'class.filetypes.php';
		 $this->filetypes = new filetypes();

		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
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
			$input=lang('The path to upload images is not set, please contact your JiNN administrator.');
			return $input;
		 }
		 elseif(!file_exists($upload_path))
		 {
			$input=lang('The path to upload images is not correct, please contact your JiNN administrator.');
			return $input;
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
		 //_debug_array($this->local_bo->site_object[object_id]);
		 
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
		 //_debug_array($m2o_sess);
		 
		 $this->tplsav2->assign('value_arr',$value_arr);

		 if(is_array($value_arr) && count($value_arr)>0)
		 {
			$i=0;

			foreach($value_arr as $img_path)
			{
			   $i++;

			   $showfile = $this->show_file($img_path, true, $field_name, $i);
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

			   $showfile = $this->show_slot(true, 'path', $name, $this->spacer, $this->spacer_style, $span_id, '');
			   $this->tplsav2->assign('i',$i);
			   $this->tplsav2->assign('showfile',$showfile);
			   $this->tplsav2->assign('slot',$slot);

			   $empt_rows.=$this->tplsav2->fetch('filemanager.fullrows.tpl.php');

			}
		 }
		 
		 $this->tplsav2->assign('empt_rows',$empt_rows);

		 /* add extra images file container here */

		 $input.=$this->tplsav2->fetch('filemanager.formview_edit.tpl.php');

		 
		 /*		 if($config['Zip_file_box']=='True')
		 {
			$input.= '<table>';
			   $input.='<tr><td>'.lang('Add your ZIP-file with images here').'<input type="file" name="IMG_ZIP'.$field_name.'" value=""></td></tr>';
			   $input.='</table>';
		 }
		 */
		 return $input;
	  }

	  function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  /****************************************************************************\
	  * main image data function                                                   *
	  \****************************************************************************/
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
		 $table_style='';
		 $cell_style='style="border-width:1px;border-style:solid;border-color:grey"';

		 $input.='<table '.$table_style.' cellpadding="3" width="100%">';
			if(trim($value))// FIXME or rather TESTME
			{
			   //$input.='<input type="hidden" name="IMG_ORG'.$field_name.'" value="'.$value.'">';
			   $value=explode(';',$value);
			   if (is_array($value) && count($value)>0)
			   {
				  $i=0;
				  foreach($value as $img_path)
				  {
					 $i++;
					 $input .= '<tr><td '.$cell_style.' valign="top">'.$i.'.</td><td '.$cell_style.'>';
						   $input .= $this->show_file($img_path);
						   $input .= '</td></tr>';							  
				  }
			   }
			}
			$input.='</table>';

		 return $input;
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
			   foreach($value as $img_path)
			   {
				  $i++;

				  unset($imglink); 
				  unset($popup); 

				  /* check for image and create previewlink */
				  if(is_file($upload_path . SEP . $img_path))
				  {
					 $imglink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path.SEP.$img_path);

					 // FIXME move code to class
					 $image_size=getimagesize($upload_path . SEP. $img_path);
					 $pop_width = ($image_size[0]+50);
					 $pop_height = ($image_size[1]+50);

					 $popup = "img_popup('".base64_encode($imglink)."','$pop_width','$pop_height');";
				  }

				  unset($thumblink); 

				  $path_array = explode('/', $img_path);
				  $path_array[count($path_array)-1] = '..'.$path_array[count($path_array)-1];
				  $thumb_path = implode('/', $path_array);

				  /* check for thumb and create previewlink */
				  if(is_file($upload_path . SEP . $thumb_path))
				  {
					 $thumblink='<img src="'.$upload_url . SEP . $thumb_path.'" alt="'.$i.'">';

				  }
				  else
				  {
					 //$thumblink=$i;
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

	  function show_file($img_path, $edit=false, $field_name='', $i='')
	  {
		 $upload_path	= $this->local_bo->cur_upload_path();
		 $max_prev		= $this->local_bo->read_preferences('max_prev');
		 $helper_id		= $this->local_bo->plug->registry->plugins['filemanager']['helper_fields_substring'];

		 if($max_prev == '') $max_prev = -1; //default we want to see all preview images
		 $stripped_name = substr($field_name,6);	//the real field name
		 $prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
		 $prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)
		 $img_style = 'style="border-style:solid;border-width:1px;border-color:#000000"';
		 $name    = $prefix.'_IMG_'.$stripped_name.$i;
		 $span_id = $prefix.'_PATH_'.$stripped_name.$i;

		 $input='';
		 //check if file exists
		 if(is_file($upload_path . SEP . $img_path))
		 {
			$image_info = getimagesize($upload_path . SEP. $img_path);
			$text = '<b>'.$img_path.'</b>';
			if(is_array($image_info)&& $image_info['mime'] != 'application/x-shockwave-flash')
			{
			   //process as image

			   // create previewlink
			   $imglink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path.SEP.$img_path);
			   // FIXME move code to class
			   $image_size=getimagesize($upload_path . SEP. $img_path);
			   $pop_width = ($image_size[0]+50);
			   $pop_height = ($image_size[1]+50);
			   $popup   = "img_popup('".base64_encode($imglink)."','$pop_width','$pop_height');";


			   $path_array = explode('/', $img_path);
			   $path_array[count($path_array)-1] = '.'.$path_array[count($path_array)-1];
			   $thumb_path = implode('/', $path_array);

			   /* check for thumb and create previewlink */
			   if(is_file($upload_path . SEP . $thumb_path))
			   {
				  $thumblink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path . SEP . $thumb_path);
			   }

			   // if URL exists show link or if set show image in form
			   if($this->local_bo->read_preferences('prev_img')!='no' &&  ($max_prev>=$i || $max_prev==-1) && $imglink) 
			   {	
				  if($this->local_bo->read_preferences('prev_img')=='yes')
				  {
					 if($thumblink)
					 {
						$input .= $this->show_slot($edit, 'thumblink', $name, $thumblink, $img_style, $span_id, $text, $popup);
					 }
					 else
					 {
						$input .= $this->show_slot($edit, 'image', $name, $imglink, $img_style, $span_id, $text, $popup);
					 }
				  }
				  elseif($this->local_bo->read_preferences('prev_img')=='only_tn' && $thumblink)
				  {
					 $input .= $this->show_slot($edit, 'thumblink', $name, $thumblink, $img_style, $span_id, $text, $popup);
				  }
				  else
				  {
					 $input .= $this->show_slot($edit, 'pathlink', $name, $this->spacer, $this->spacer_style, $span_id, $text, $popup);
				  }
			   }
			   else  
			   {
				  if($imglink)
				  {
					 $input .= $this->show_slot($edit, 'pathlink', $name, $this->spacer, $this->spacer_style, $span_id, $text, $popup);
				  }
				  else
				  {
					 $input .= $this->show_slot($edit, 'path', $name, $this->spacer, $this->spacer_style, $span_id, $text);
				  }
			   }
			}
			elseif($image_info['mime'] == 'application/x-shockwave-flash')
			{
			   $input .= $this->show_slot($edit, 'swflash', $name, $img_path, $this->unknown_style, $span_id, $text);
			}
			else
			{
			   $filepath=$upload_path . SEP. $img_path; 
			   /* check for thumb and create previewlink */
			   if(is_file($filepath))
			   {
				  $filelink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$filepath);
			   }

			   //process as unknown filetype
			   $input .= $this->show_slot($edit, 'unknown', $name, $this->unknown, $this->unknown_style, $span_id, $text,$filelink);
			}
		 }
		 else
		 {
			$text = '<b>error: file does not exist on server ('.$img_path.')</b>';
			$input .= $this->show_slot($edit, 'path', $name, $this->spacer, $this->spacer_style, $span_id, $text);
		 }
		 return $input;
	  }

	  function show_slot($edit, $type, $name, $src, $style, $span_id, $text, $link='')
	  {
		 $input = '';
		 if($edit)
		 {
			switch($type)
			{
			   case 'path':
			   $input .= '<img id="'.$name.'" src="'.$src.'" '.$style.' />';
			   $input .= '<span id="'.$span_id.'">'.$text.'</span>';
			   break;
			   case 'pathlink':
			   $input .= '<img id="'.$name.'" src="'.$src.'" '.$style.' />';
			   $input .= '<span id="'.$span_id.'"><a href="javascript:'.$link.'">'.$text.'</a></span>';
			   break;
			   case 'thumblink':
			   $linkid=str_replace('_IMG_','_IMGLINK_',$name);
			   $input .= '<a id="'.$linkid.'" href="javascript:'.$link.'"><img id="'.$name.'" src="'.$src.'" alt="preview" '.$style.' /></a>';
			   $input .= '<span id="'.$span_id.'"></span>';
			   break;
			   case 'image':
			   $input .= '<img id="'.$name.'" src="'.$src.'" alt="preview" '.$style.' />';
			   $input .= '<span id="'.$span_id.'"></span>';
			   break;
			   case 'swflash':
			   $file_spec = @GetImageSize($this->local_bo->cur_upload_path().$src);						
			   $file_width = ($file_spec[0]>=$file_spec[1]) ? 80 : round($file_spec[0]/($file_spec[1]/80)) ;
			   $file_height = ($file_spec[1]>=$file_spec[0]) ? 80 : round($file_spec[1]/($file_spec[0]/80)) ;
			   $input .= '<script language="JavaScript" type="text/JavaScript" src="'.$GLOBALS['phpgw_info']['server']['webserver_url'].'/jinn/plugins/db_fields_plugins/__filemanager/popups/flash.js"></script>';
			   $input .= '<script language="JavaScript" type="text/JavaScript">';
				  $input .= 'if(flashcompattest()==true)';
				  $input .= '{';
				  $input .= '	writeFlash('.$file_width.','.$file_height.',\''.$this->local_bo->cur_upload_url().$src.'\',\''.$name.'\');';
				  $input .= '}';
				  $input .= 'else';
				  $input .= '{';
				  $input .= '	document.write(\'<img id="swflash" src="'.$GLOBALS['phpgw_info']['server']['webserver_url'].'/jinn/plugins/db_fields_plugins/__filemanager/popups/ImageManager/flash.png" alt="preview" '.$style.' />\');';
				  $input .= '}';
				  $input .= '</script>';						
			   $input .= '<span id="'.$span_id.'">'.$text.'</span>';
			   break;
			case 'unknown':
			   $input .= '<img id="'.$name.'" src="'.$src.'" alt="file of unknown type" '.$style.' />';
			   if($link)
			   {
				  $input .= '<a id="'.$linkid.'" href="'.$link.'"><span id="'.$span_id.'">'.$text.'</span></a>';
			   }
			   else
			   {
				  $input .= '<span id="'.$span_id.'">'.$text.'</span>';
			   }
			   break;
			}
		 }
		 else
		 {
			switch($type)
			{
			   case 'path':
			   $input .= $text;
			   break;
			   case 'pathlink':
			   $input .= '<a href="javascript:'.$link.'">'.$text.'</a>';
			   break;
			   case 'thumblink':
			   $input .= '<a href="javascript:'.$link.'"><img src="'.$src.'" alt="preview" '.$style.' /></a>';
			   break;
			   case 'image':
			   $input .= '<img src="'.$src.'" alt="preview" '.$style.' />';
			   break;
			   case 'swflash':
			   $file_spec = @GetImageSize($this->local_bo->cur_upload_path().$src);						
			   $file_width = ($file_spec[0]>=$file_spec[1]) ? 80 : round($file_spec[0]/($file_spec[1]/80)) ;
			   $file_height = ($file_spec[1]>=$file_spec[0]) ? 80 : round($file_spec[1]/($file_spec[0]/80)) ;
			   $input .= '<script language="JavaScript" type="text/JavaScript" src="'.$GLOBALS['phpgw_info']['server']['webserver_url'].'/jinn/plugins/db_fields_plugins/__filemanager/popups/flash.js"></script>';
			   $input .= '<script language="JavaScript" type="text/JavaScript">';
				  $input .= 'if(flashcompattest()==true)';
				  $input .= '{';
				  $input .= '	writeFlash('.$file_width.','.$file_height.',\''.$this->local_bo->cur_upload_url().$src.'\',\''.$name.'\');';
				  $input .= '}';
				  $input .= 'else';
				  $input .= '{';
				  $input .= '	document.write(\'<img name="swflash" src="'.$GLOBALS['phpgw_info']['server']['webserver_url'].'/jinn/plugins/db_fields_plugins/__filemanager/popups/ImageManager/flash.png" alt="preview" '.$style.' />\');';
				  $input .= '}';
				  $input .= '</script>';						
			   $input .= '<span id="'.$span_id.'">'.$text.'</span>';
			   break;
			case 'unknown':

			   $input .= '<img id="'.$name.'" src="'.$src.'" alt="file of unknown type" '.$style.' /><br/>';
			   if($link)
			   {
				  $input .= '<a id="'.$linkid.'" href="'.$link.'"><span id="'.$span_id.'">'.$text.'</span></a>';
			   }
			   else
			   {
				  $input .= '<span id="'.$span_id.'">'.$text.'</span>';
			   }

			   break;
			}
		 }
		 return $input;
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
