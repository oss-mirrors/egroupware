<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; version 2 of the License.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   /* 
   plugin.attachmentpath.php contains the standard attachment-upload plugin for 
   JiNN number off standardly available 
   plugins for JiNN. 
   */

   $this->plugins['attachpath']['name']				= 'attachpath';
   $this->plugins['attachpath']['title']			= 'AttachmentPath plugin';
   $this->plugins['attachpath']['version']			= '0.8.8';
   $this->plugins['attachpath']['author']			= 'Pim Snel';
   $this->plugins['attachpath']['enable']			= 1;
   $this->plugins['attachpath']['description']		= 'Plugin with can upload files of any type and store the paths in the database seperated by semicolons.';
   $this->plugins['attachpath']['db_field_hooks']	= array
   (
	  'string',
	  'blob'
   );

   /* ATTENTION: spaces and special character are not allowed in config array 
   use underscores for spaces */
   $this->plugins['attachpath']['config']		= array
   (
	  'Max_files' => array('3','text','maxlength=2 size=2'), 
	  'Max_attachment_size_in_megabytes_Leave_empty_to_have_no_limit' => array('','text','maxlength=3 size=3'),
	  //	  'Alternative_upload_path_Leave_empty_to_use_normal_path' => array('','text','maxlength=200 size=30'),
	  'Activate_manual_path_input' => array( array('False','True'),'select',''),
	  'Store_full_path' => array( array('True','False'),'select','')
   );

   $this->plugins['attachpath']['config_help']		= array
   (
	  'Max_files' => 'Defaults to three files', 
	  //	  'Max_attachment_size_in_megabytes_Leave_empty_to_have_no_limit' => array('','text','maxlength=3 size=3'),
	  //	  'Alternative_upload_path_Leave_empty_to_use_normal_path' => array('','text','maxlength=200 size=30'),
	  'Activate_manual_path_input' => 'With manual path the user can point to an existing file',
	  'Store_full_path' => 'If you select True, the complete path is stored in the database, else a path relative to the upload path is stored ' 
   );

   /*!
   @function plg_fi_attachpath
   @fixme remove attachments dir
   @fixme add file selector for remote files
   @fixme add mimetype icons
   */
   function plg_fi_attachpath($field_name,$value,$config,$attr_arr)
   {	
	  global $local_bo;

	  if($local_bo->common->so->config[server_type]=='dev')
	  {
		 $field_prefix='dev_';
	  }

	  if($config['Alternative_upload_path_Leave_empty_to_use_normal_path'])
	  {
		 $upload_path=$config['Alternative_upload_path_Leave_empty_to_use_normal_path'];
	  }
	  elseif($local_bo->site_object[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site_object[$field_prefix.'upload_path'];
	  }
	  elseif($local_bo->site[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site[$field_prefix.'upload_path'];
	  }

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
		 $input .=' <input type="file" name="ATT_SRC'.$field_name.$i.'">';

		 if($config[Activate_manual_path_input]=='True')
		 {
			$input.='<br/><br/>'.lang('Manually enter a new relative file path').'<input type="text" name="ATT_MAN'.$field_name.$i.'" style="width:300px"><br/>';
		 }

	  }

	  $input.='<hr/><input type="checkbox" value="'.$att_path.'" name="ATT_FLUSH"> '.lang('Remove all').'<br/>';
	  $input.='<input type="hidden" name="'.$field_name.'" value="TRUE">';

	  return $input;
   }



   function plg_sf_attachpath($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
   /****************************************************************************\
   * main image data function                                                   *
   \****************************************************************************/
   {
	  global $local_bo;

	  /* remove all */
	  if($_POST[ATT_FLUSH]) 
	  {
		 return -1;
	  }

	  if($local_bo->common->so->config[server_type]=='dev')
	  {
		 $field_prefix='dev_';
	  }

	  if($config['Alternative_upload_path_Leave_empty_to_use_normal_path'])
	  {
		 $upload_path=$config['Alternative_upload_path_Leave_empty_to_use_normal_path'];
	  }
	  elseif($local_bo->site_object[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site_object[$field_prefix.'upload_path'];
	  }
	  elseif($local_bo->site[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site[$field_prefix.'upload_path'];
	  }

	  if($config['Store_full_path'])
	  {
		 $path_in_db=$upload_path.SEP;
	  }
	  else
	  {
		 $path_in_db='';//$download_path=$upload_path.SEP;
	  }


	  $atts_to_delete=$local_bo->common->filter_array_with_prefix($_POST,'ATT_DEL');

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
	  $atts_to_add=$local_bo->common->filter_array_with_prefix($HTTP_POST_FILES,'ATT_SRC');

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

			   $target_att_name = time().ereg_replace("[^a-zA-Z0-9_.]", '_', $add_att['name']);

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
	  $man_atts_to_add=$local_bo->common->filter_array_with_prefix($HTTP_POST_VARS,'ATT_MAN');
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

   function plg_ro_attachpath($value,$config)
   {
	  global $local_bo;

	  if($local_bo->common->so->config[server_type]=='dev')
	  {
		 $field_prefix='dev_';
	  }
	  if($local_bo->site_object[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site_object[$field_prefix.'upload_path'];
	  }
	  elseif($local_bo->site[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site[$field_prefix.'upload_path'];
	  }

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
   function plg_bv_attachpath($value,$config,$where_val_enc)
   {

	  global $local_bo;
	  $stripped_name=substr($field_name,6);	

	  if($local_bo->common->so->config[server_type]=='dev')
	  {
		 $field_prefix='dev_';
	  }

	  if($local_bo->site_object[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site_object[$field_prefix.'upload_path'];
	  }
	  elseif($local_bo->site[$field_prefix.'upload_path'])
	  {
		 $upload_path=$local_bo->site[$field_prefix.'upload_path'];
	  }

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






?>
