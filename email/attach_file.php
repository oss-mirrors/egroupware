<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * This file written by Joseph Engo <jengo@phpgroupware.org>                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$phpgw_flags = Array(
		'currentapp' =>	'email',
		'enable_network_class'	=> True,
		'noheader'   => True,
		'nonavbar'   => True
	);
	
	$GLOBALS['phpgw_info']['flags'] = $phpgw_flags;

	include('../header.inc.php');

	$GLOBALS['phpgw']->template->set_file(
		Array(		
			'T_attach_file' => 'attach_file.tpl',
			'T_attach_file_blocks' => 'attach_file_blocks.tpl'
		)
	);
	$GLOBALS['phpgw']->template->set_block('T_attach_file_blocks','B_alert_msg','V_alert_msg');
	$GLOBALS['phpgw']->template->set_block('T_attach_file_blocks','B_attached_list','V_attached_list');
	$GLOBALS['phpgw']->template->set_block('T_attach_file_blocks','B_attached_none','V_attached_none');
	$GLOBALS['phpgw']->template->set_block('T_attach_file_blocks','B_delete_btn','V_delete_btn');

	// initialize some variables
	$alert_msg = '';
	$totalfiles = 0;

	// ensure existance of PHPGROUPWARE temp dir
	// note: this is different from apache temp dir, and different from any other temp file location set in php.ini
	if (!file_exists($GLOBALS['phpgw_info']['server']['temp_dir']))
	{
		mkdir($GLOBALS['phpgw_info']['server']['temp_dir'],0700);
	}

	// if we were NOT able to create this temp directory, then make an ERROR report
	if (!file_exists($GLOBALS['phpgw_info']['server']['temp_dir']))
	{
		$alert_msg .= 'Error:'.'<br>'
			.'Server is unable to access phpgw tmp directory'.'<br>'
			.$GLOBALS['phpgw_info']['server']['temp_dir'].'<br>'
			.'Please check your configuration'.'<br>'
			.'<br>';
	}

	if (!file_exists($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['phpgw_info']['user']['sessionid']))
	{
		mkdir($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['phpgw_info']['user']['sessionid'],0700);
	}

	//$uploaddir = $GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['phpgw_info']['user']['sessionid'] . SEP;
	$uploaddir = $GLOBALS['phpgw']->msg->att_files_dir;
	
	// if we were NOT able to create this temp directory, then make an ERROR report
	if (!file_exists($uploaddir))
	{
		$alert_msg .= 'Error:'.'<br>'
			.'Server is unable to access phpgw email tmp directory'.'<br>'
			.$uploaddir.'<br>'
			.'Please check your configuration'.'<br>'
			.'<br>';
	}

	/*
	//PHP VARIABLES NOTES: 
	// $uploadedfile was the name of the file box in the submitted form, and php3 gives it additional properties:
	// $uploadedfile_name   $uploadedfile_size   $uploadedfile_type
	// php4 also does this, but the preffered way is to use the new (for php4) $HTTP_POST_FILES global array
	// $HTTP_POST_FILES['uploadedfile']['name']   .. .['type']   ... ['size']  ... ['tmp_name']
	// note that $uploadedfile_type and $HTTP_POST_FILES['uploadedfile']['type'] *may* not be correct filled
	// 
	// FILE SIZE NOTES:
	// file size limits may depend on: (a) <input type="hidden" name="MAX_FILE_SIZE" value="whatever">
	// (b) these values in php.ini: "post_max_size" "upload_max_filesize" "memory_limit" "max_execution_time"
	// also see http://www.php.net/bugs.php?id=8377  for the status of an upload bug not fixed as of 4.0.4
	// also note that uploading file to *memory* is wasteful
	*/
	
	// clean / prepare PHP provided file info
	if(floor(phpversion()) >= 4)
	{
		$file_tmp_name = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($HTTP_POST_FILES['uploadedfile']['tmp_name']));
		$file_name = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($HTTP_POST_FILES['uploadedfile']['name']));
		$file_size = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($HTTP_POST_FILES['uploadedfile']['size']));
		$file_type = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($HTTP_POST_FILES['uploadedfile']['type']));
	}
	else
	{
		$file_tmp_name = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($uploadedfile));
		$file_name = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($uploadedfile_name));
		$file_size = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($uploadedfile_size));
		$file_type = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($uploadedfile_type));
	}
	
	// sometimes PHP is very clue-less about MIME types, and gives NO file_type
	// rfc default for unknown MIME type is:
	$mime_type_default = 'application/octet-stream';
	// so if PHP did not pass any file_type info, then substitute the rfc default value
	if (trim($file_type) == '')
	{
		$file_type = $mime_type_default;
	}

	// Netscape 6 passes file_name with a full path, we need to extract just the filename
	function wbasename($input)
	{
		if (strstr($input, SEP) == False)
		{
			// no filesystem seperator is present
			return $input;
		}
		
		for($i=0; $i < strlen($input); $i++ )
		{
			$pos = strpos($input, SEP, $i);
			if ($pos != false)
			{
				$lastpos = $pos;
			}
		}
		return substr($input, $lastpos + 1, strlen($input));
	}
	//$debuginfo = 'file_name (pre-wbasename): ' .$file_name .'<br>';
	// Netscape 6 passes file_name with a full path, we need to extract just the filename
	$file_name = wbasename($file_name);


	// Some of the methods were borrowed from
	// Squirrelmail <Luke Ehresman> http://www.squirrelmail.org
	if ($action == lang('Delete'))
	{
		for ($i=0; $i<count($delete); $i++)
		{
			unlink($uploaddir.SEP.$delete[$i]);
			unlink($uploaddir.SEP.$delete[$i] . '.info');
		}
	}

	//if ($action == 'Attach File')
	if (($action == lang('Attach File'))
	&& ($file_tmp_name != '')
	&& ($file_tmp_name != 'none'))
	{
		srand((double)microtime()*1000000);
		$random_number = rand(100000000,999999999);
		$newfilename = md5($file_tmp_name.', '.$file_name.', '.$GLOBALS['phpgw_info']['user']['sessionid'].time().getenv('REMOTE_ADDR').$random_number);

		// Check for uploaded file of 0-length, or no file (patch from Zone added by Milosch)
		//if ($file_tmp_name == "none" && $file_size == 0) This could work also
		if ($file_size == 0)
		{
			touch ($uploaddir.SEP.$newfilename);
		}
		else
		{
			copy($file_tmp_name, $uploaddir.SEP.$newfilename);
		}

		$ftp = fopen($uploaddir.SEP.$newfilename . '.info','wb');
		fputs($ftp,$file_type."\n".$file_name."\n");
		fclose($ftp);
	}
	elseif (($action == lang('Attach File'))
	&& (($file_tmp_name == '') || ($file_tmp_name == 'none')))
	{
		$langed_attach_file = lang("Attach File");
		$alert_msg = lang('Input Error:').'<br>'
			.lang('Please submit a filename to attach').'<br>'
			.lang('You must click').' "'.lang('Attach File').'" '.lang('for the file to actually upload').'<br>'
			.'<br>';
	}

	$dh = opendir($uploaddir);
	while ($file = readdir($dh))
	{
		if (($file != '.')
		&& ($file != '..')
		&& (ereg("\.info",$file)))
		{
			$file_info = file($uploaddir.SEP.$file);
			// for every file, fill the file list template with it
			$GLOBALS['phpgw']->template->set_var('ckbox_delete_name','delete[]');
			$GLOBALS['phpgw']->template->set_var('ckbox_delete_value',substr($file,0,-5));
			$GLOBALS['phpgw']->template->set_var('ckbox_delete_filename',$file_info[1]);
			$GLOBALS['phpgw']->template->parse('V_attached_list','B_attached_list',True);
			$totalfiles++;
		}
	}
	closedir($dh);
	if ($totalfiles == 0)
	{
		// there is no list of files, clear that block
		$GLOBALS['phpgw']->template->set_var('V_attached_list','');
		// there is no delete button because there are no files to delete, clear that block
		$GLOBALS['phpgw']->template->set_var('V_delete_btn','');
		// show the none block
		$GLOBALS['phpgw']->template->set_var('text_none',lang('None'));
		$GLOBALS['phpgw']->template->parse('V_attached_none','B_attached_none');
	}
	else
	{
		// we have files, clear the "no files" block
		$GLOBALS['phpgw']->template->set_var('V_attached_none','');
		// fill the delete sublit form
		$GLOBALS['phpgw']->template->set_var('btn_delete_name','action');
		$GLOBALS['phpgw']->template->set_var('btn_delete_value',lang('Delete'));
		$GLOBALS['phpgw']->template->parse('V_delete_btn','B_delete_btn');
	}

	$body_tags = 'bgcolor="'.$GLOBALS['phpgw_info']['theme']['bg_color'].'" alink="'.$GLOBALS['phpgw_info']['theme']['alink'].'" link="'.$GLOBALS['phpgw_info']['theme']['link'].'" vlink="'.$GLOBALS['phpgw_info']['theme']['vlink'].'"';
	if (!$GLOBALS['phpgw_info']['server']['htmlcompliant'])
	{
		$body_tags .= ' topmargin="0" marginheight="0" marginwidth="0" leftmargin="0"';
	}

	/*
	// begin DEBUG INFO
	$debuginfo .= '--uploadedfile info: <br>'
		.'phpgw_info[server][temp_dir]: '.$GLOBALS['phpgw_info']['server']['temp_dir'].'<br>'
		.'$phpgw_info[user][sessionid]: '.$GLOBALS['phpgw_info']['user']['sessionid'].'<br>'
		.'uploaddir: '.$uploaddir.'<br>'
		.'file_tmp_name: ' .$file_tmp_name .'<br>'
		.'file_name: ' .$file_name .'<br>'
		.'file_size: ' .$file_size .'<br>'
		.'file_type: ' .$file_type .'<br>'
		.'<br>'
		.'totalfiles: ' .$totalfiles .'<br>'
		.'file_info_count: '.count($file_info) .'<br>'
		.'<br>';
	if (count($file_info) > 0)
	{
		$debuginfo .= '<br> file_info[0]='.$file_info[0] .'<br> file_info[1]='.$file_info[1];
	}
	$debuginfo .= '<br>';
	echo $debuginfo;
	// end DEBUG INFO
	*/

	$charset = lang('charset');
	$GLOBALS['phpgw']->template->set_var('charset',$charset);
	$GLOBALS['phpgw']->template->set_var('page_title',$GLOBALS['phpgw_flags']['currentapp'] . ' - ' .lang('File attachment'));
	$GLOBALS['phpgw']->template->set_var('font_family',$GLOBALS['phpgw_info']['theme']['font']);
	$GLOBALS['phpgw']->template->set_var('body_tags',$body_tags);
	if ($alert_msg != '')
	{
		$GLOBALS['phpgw']->template->set_var('alert_msg',$alert_msg);
		$GLOBALS['phpgw']->template->parse('V_alert_msg','B_alert_msg');
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('V_alert_msg','');
	}
	$GLOBALS['phpgw']->template->set_var('form_method','POST');
	$GLOBALS['phpgw']->template->set_var('form_action',$GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/attach_file.php'));
	$GLOBALS['phpgw']->template->set_var('text_attachfile',lang('Attach file'));
	$GLOBALS['phpgw']->template->set_var('text_currattached',lang('Current attachments'));
	$GLOBALS['phpgw']->template->set_var('txtbox_upload_desc',lang('File'));
	$GLOBALS['phpgw']->template->set_var('txtbox_upload_name','uploadedfile');
	$GLOBALS['phpgw']->template->set_var('btn_attach_name','action');
	$GLOBALS['phpgw']->template->set_var('btn_attach_value',lang('Attach File'));
	$GLOBALS['phpgw']->template->set_var('btn_done_name','done');
	$GLOBALS['phpgw']->template->set_var('btn_done_value',lang('Done'));
	$GLOBALS['phpgw']->template->set_var('btn_done_js','window.close()');

	$GLOBALS['phpgw']->template->pparse('out','T_attach_file');

	$GLOBALS['phpgw']->common->phpgw_exit();
?>
