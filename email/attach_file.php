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
	
	$phpgw_info['flags'] = $phpgw_flags;

	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array(		
		'T_attach_file' => 'attach_file.tpl',
		'T_attach_file_blocks' => 'attach_file_blocks.tpl'
	));
	$t->set_block('T_attach_file_blocks','B_alert_msg','V_alert_msg');
	$t->set_block('T_attach_file_blocks','B_attached_list','V_attached_list');
	$t->set_block('T_attach_file_blocks','B_attached_none','V_attached_none');
	$t->set_block('T_attach_file_blocks','B_delete_btn','V_delete_btn');

	// initialize some variables
	$alert_msg = '';
	$totalfiles = 0;

	if (!file_exists($phpgw_info['server']['temp_dir']))
	{
		mkdir($phpgw_info['server']['temp_dir'],0700);
	}

	if (!file_exists($phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid']))
	{
		mkdir($phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid'],0700);
	}

	// Some on the methods were borrowed from
	// Squirrelmail <Luke Ehresman> http://www.squirrelmail.org

	$uploaddir = $phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid'] . SEP;

	if ($action == 'Delete')
	{
		for ($i=0; $i<count($delete); $i++)
		{
			unlink($uploaddir . $delete[$i]);
			unlink($uploaddir . $delete[$i] . '.info');
		}
	}

	//if ($action == 'Attach File')
	if (($action == 'Attach File')
	&& ($uploadedfile != '')
	&& ($uploadedfile != 'none'))
	{
		srand((double)microtime()*1000000);
		$random_number = rand(100000000,999999999);
		$newfilename = md5($uploadedfile.', '.$uploadedfile_name.', '.$phpgw_info['user']['sessionid'].time().getenv('REMOTE_ADDR').$random_number);

		// Check for uploaded file of 0-length, or no file (patch from Zone added by Milosch)
		//if ($uploadedfile == "none" && $uploadedfile_size == 0) This could work also
		if ($uploadedfile_size == 0)
		{
			touch ($uploaddir . $newfilename);
		}
		else
		{
			copy($uploadedfile, $uploaddir . $newfilename);
		}

		$ftp = fopen($uploaddir . $newfilename . '.info','wb');
		fputs($ftp,$uploadedfile_type."\n".$uploadedfile_name."\n");
		fclose($ftp);
	}
	elseif (($action == 'Attach File')
	&& (($uploadedfile == '') || ($uploadedfile == 'none')))
	{
		$alert_msg = 'Please submit a filename to attach';
	}

	$dh = opendir($phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid']);
	while ($file = readdir($dh))
	{
		if (($file != '.')
		&& ($file != '..')
		&& (ereg("\.info",$file)))
		{
			$file_info = file($uploaddir . $file);
			// for every file, fill the file list template with it
			$t->set_var('ckbox_delete_name','delete[]');
			$t->set_var('ckbox_delete_value',substr($file,0,-5));
			$t->set_var('ckbox_delete_filename',$file_info[1]);
			$t->parse('V_attached_list','B_attached_list',True);
			$totalfiles++;
		}
	}
	closedir($dh);
	if ($totalfiles == 0)
	{
		// there is no list of files, clear that block
		$t->set_var('V_attached_list','');
		// there is no delete button because there are no files to delete, clear that block
		$t->set_var('V_delete_btn','');
		// show the none block
		$t->set_var('text_none',lang('None'));
		$t->parse('V_attached_none','B_attached_none');
	}
	else
	{
		// we have files, clear the "no files" block
		$t->set_var('V_attached_none','');
		// fill the delete sublit form
		$t->set_var('btn_delete_name','action');
		$t->set_var('btn_delete_value','Delete');
		$t->parse('V_delete_btn','B_delete_btn');
	}

	$body_tags = 'bgcolor="'.$phpgw_info['theme']['bg_color'].'" alink="'.$phpgw_info['theme']['alink'].'" link="'.$phpgw_info['theme']['link'].'" vlink="'.$phpgw_info['theme']['vlink'].'"';
	if (!$phpgw_info['server']['htmlcompliant'])
	{
		$body_tags .= ' topmargin="0" marginheight="0" marginwidth="0" leftmargin="0"';
	}

	/*$debuginfo = $phpgw_info['server']['temp_dir'] . SEP . $phpgw_info['user']['sessionid'];
	if (count($file_info) > 0)
	{
	}
	$debuginfo .= '<br> uploadedfile: ' .$uploadedfile .'   totalfiles: ' .$totalfiles 
		.'<br>file_info_count: '.count($file_info);
	if (count($file_info) > 0)
	{
		$debuginfo .= ' file_info[0]='.$file_info[0] .' file_info[1]='.$file_info[1];
	}
	$t->set_var('debuginfo',$debuginfo);
	*/

	$charset = lang('charset');
	$t->set_var('charset',$charset);
	$t->set_var('page_title',$phpgw_flags['currentapp'] . ' - ' .'File attachment');
	$t->set_var('font_family',$phpgw_info["theme"]["font"]);
	$t->set_var('body_tags',$body_tags);
	if ($alert_msg != '')
	{
		$t->set_var('alert_msg',$alert_msg);
		$t->parse('V_alert_msg','B_alert_msg');
	}
	else
	{
		$t->set_var('V_alert_msg','');
	}
	$t->set_var('form_method','POST');
	$t->set_var('form_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/attach_file.php'));
	$t->set_var('text_attachfile','Attach file');
	$t->set_var('text_currattached','Current attachments');
	$t->set_var('txtbox_upload_desc','File');
	$t->set_var('txtbox_upload_name','uploadedfile');
	$t->set_var('btn_attach_name','action');
	$t->set_var('btn_attach_value','Attach File');
	$t->set_var('btn_done_name','done');
	$t->set_var('btn_done_value','Done');
	$t->set_var('btn_done_js','window.close()');


	$t->pparse('out','T_attach_file');

	$phpgw->common->phpgw_exit();
?>
