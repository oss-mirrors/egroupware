<?php
/**************************************************************************\
* phpGroupWare - Ftp Module                                                *
* http://www.phpgroupware.org                                              *
* Written by Scott Moser <smoser@brickies.net>                             *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

	/* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'              => 'ftp',
		'enable_nextmatchs_class' => True
	);

	if ($action == 'get' || $action == 'view')
	{
		$phpgw_info['flags']['nonavbar'] = True;
		$phpgw_info['flags']['noheader'] = True;
	}
	include('../header.inc.php');

	if (! $start)
	{
		$start = 0;
	}

	$default_login  = $phpgw_info['user']['account_lid'];
	$default_pass   = $phpgw->session->appsession('password','phpgwapi');
	$default_server = $phpgw_info['server']['default_ftp_server'];

	$sessionUpdated=false;

	$em_bg=$phpgw_info["theme"]["table_bg"];
	$em_bg_text=$phpgw_info["theme"]["table_text"];
	$bgcolor[0]=$phpgw_info["theme"]["row_on"];
	$bgcolor[1]=$phpgw_info["theme"]["row_off"];
	$tempdir=$phpgw_info["server"]["temp_dir"];

	$target='/'.$phpgw_info['flags']['currentapp'].'/'.basename($SCRIPT_FILENAME);

	$t = $phpgw->template;
	$t->set_file(array(
			"main_" => "main.tpl",
			"login" => "login.tpl",
			"rename" => "rename.tpl",
			"confirm_delete" => "confirm_delete.tpl",
			"bad_connect" => "bad_connection.tpl"
		));
	$t->set_var(array(
			"em_bgcolor" => $em_bg,
			"em_text_color" => $em_bg_text,
			"bgcolor" => $bgcolor[0]
		));

	$t->set_block('main_','main');
	$t->set_block('main_','row');
	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('row_on',$phpgw_info['theme']['row_on']);
	$t->set_var('row_off',$phpgw_info['theme']['row_off']);

	$t->set_var("module_name",lang("module name"));

	if ($action=="" || $action=="login") 
	{
		// if theres no action, try to login to default host with user and pass
		if ($action=="login") 
		{
			// username, ftpserver and password should have been passed in
			// via POST
			$connInfo["username"]=$username;
			$connInfo["password"]=$password;
			$connInfo["ftpserver"]=$ftpserver;
		}
		else
		{
			// try to default with session id and passwd
			if (!($connInfo=getConnectionInfo())) 
			{
				$connInfo["username"]=$default_login;
				$connInfo["password"]=$default_pass;
				$connInfo["ftpserver"]=$default_server;

				$tried_default=true;
			}
		}
		updateSession($connInfo);
		$sessionUpdated=true;
	} 

	if ($action != "newlogin") 
	{
		if (empty($connInfo)) 
		{
			$connInfo=getConnectionInfo();
		}
		$ftp=@phpftp_connect($connInfo["ftpserver"],$connInfo["username"],$connInfo["password"]);
		if ($ftp) 
		{
			$homedir=ftp_pwd($ftp);
			$retval=ftp_pasv($ftp,1);
			if ($action == "delete" || $action == "rmdir") 
			{
				if ($confirm) 
				{
					if ($action=="delete") 
					{
						$retval=ftp_delete($ftp,$olddir . "/" . $file);
					}
					else 
					{
						$retval=ftp_rmdir($ftp,$olddir . "/" . $file);
					}
					if ($retval) 
					{
						$t->set_var("misc_data",lang("deleted","$olddir/$file"), true);
					}
					else
					{
						$t->set_var("misc_data",lang("failed to delete", "$olddir/$file"), true);
					}
				} else if (!$cancel) 
				{
					$t->set_var("misc_data",confirmDeleteForm($t,$session,$file,$olddir),true);
				}
			}

			if ($action == "rename") 
			{
				if ($confirm) 
				{
					if (ftp_rename($ftp,$olddir . "/" . $filename, $olddir . "/" . $newfilename)) 
					{
						$t->set_var("misc_data",lang("renamed",
								"$filename", "$newfilename"), true);
					} 
					else 
					{
						$t->set_var("misc_data",lang("failed to rename",
								"$filename", "$newfilename"), true);
					}
				}
				else
				{
					$t->set_var("misc_data", renameForm($t,$session,$file,$olddir), true);
				}
			}
			if ($action == "get") 
			{
				phpftp_get($ftp,$tempdir,$olddir,$file);
				exit();
			} 
			if ($action == "view") 
			{
				phpftp_view($ftp,$tempdir,$olddir,$file);
				exit();
			}
			if ($action == "upload") 
			{
				$newfile=$olddir . "/" . $uploadfile_name;
				if (ftp_put($ftp,$newfile, $uploadfile, FTP_BINARY)) 
				{
					$t->set_var("misc_data",lang("uploaded",$newfile), true);
				}
				else 
				{
					$t->set_var("misc_data",lang("failed to upload",$newfile), true);
				}
				unlink($uploadfile);
			}
			if ($action == "mkdir") 
			{
				if ($newdirname!="") 
				{
					if (ftp_mkdir($ftp,$olddir . "/" . $newdirname)) 
					{
						$t->set_var("misc_data",lang("created directory",
								"$olddir/$newdirname"), true);
					}
					else 
					{
						$t->set_var("misc_data",lang("failed to mkdir",
								"$olddir/$newdirname"), true);
					}
				}
				else 
				{
					$t->set_var("misc_data",lang("empty dirname"),true);
				}
			}

			// heres where most of the work takes place
			if ($action == 'cwd')
			{
				if ($olddir == $newdir)
				{
					ftp_chdir($ftp,$newdir);
				}
				else
				{
					ftp_chdir($ftp,$olddir . '/' . $newdir);
						$olddir = $olddir . '/' . $newdir;
				}
			}
			else if ($action == '' && $connInfo['cwd'] != '')
			{
				// this must have come back from another module, try to 
				// get into the old directory
				ftp_chdir($ftp,$connInfo['cwd']);
			}
			else if ($olddir)
			{
				ftp_chdir($ftp,$olddir);
			}

			if (! $olddir)
			{
				$olddir = ftp_pwd($ftp);
			}
			$cwd = ftp_pwd($ftp);
			$connInfo['cwd'] = $cwd;

			// set up the upload form
			$ul_form_open="<form name=\"upload\" action=\"" . 
			createLink($target) . "\" enctype=\"multipart/form-data\"" . "method=post>\n" . 
				"<input type=\"hidden\" name=\"olddir\" value=\"$cwd\">\n" .
				"<input type=\"hidden\" name=\"action\" value=\"upload\">\n";
			$ul_select="<input type=\"file\" name=\"uploadfile\" size=30>\n" ;
			$ul_submit="<input type=\"submit\" name=\"upload\"" . "value=\"Upload\">\n";
			$ul_form_close="</form>\n";

			// set up the create directory
			$crdir_form_open="<form name=\"mkdir\" action=\"" . 
			createLink($target) . "\" method=\"post\" >\n" .
				"\t<input type=\"hidden\" name=\"olddir\" value=\"$cwd\">\n" .
				"\t<input type=\"hidden\" name=\"action\" value=\"mkdir\">\n";

			$crdir_form_close="</form>\n";
			$crdir_textfield="\t<input type=\"text\" size=\"30\"" . 
				"name=\"newdirname\" value=\"\">\n";
			$crdir_submit="\t<input type=\"submit\" name=\"submit\"" .
				"value=\"Create New Dir\">\n";
			$ftp_location="ftp://" . $connInfo["username"] . "@" .
				$connInfo["ftpserver"] . $cwd;

			$newdir=""; $temp=$olddir; $olddir=$homedir; 
			$home_link= macro_get_Link("cwd","<img border=0 src=" .
				"\"../images/home.gif\">") . "\n";
			$olddir=$temp;

			// set up all the global variables for the template
			$t->set_var(array(
				"ftp_location" => $ftp_location,
				"relogin_link"=> macro_get_Link("newlogin",lang("relogin")),
				"home_link" => $home_link,
				"ul_select" => $ul_select, 
				"ul_submit" => $ul_submit,
				"ul_form_open" => $ul_form_open, "ul_form_close" => $ul_form_close,
				"crdir_form_open" => $crdir_form_open,
				"crdir_form_close" => $crdir_form_close,
				"crdir_textfield" => $crdir_textfield,
				"crdir_submit" => $crdir_submit
			));

			$total = count(ftp_rawlist($ftp,''));
			$t->set_var('nextmatchs_left',$phpgw->nextmatchs->left('/ftp/index.php',$start,$total));
			$t->set_var('nextmatchs_right',$phpgw->nextmatchs->right('/ftp/index.php',$start,$total));

			$contents = phpftp_getList($ftp,'.',$start);

			$t->set_var('lang_name',lang('Name'));
			$t->set_var('lang_owner',lang('Owner'));
			$t->set_var('lang_group',lang('Group'));
			$t->set_var('lang_permissions',lang('Permissions'));
			$t->set_var('lang_size',lang('Size'));
			$t->set_var('lang_delete',lang('Delete'));
			$t->set_var('lang_rename',lang('Rename'));


			$newdir = $olddir;
			$t->set_var('name',macro_get_link('cwd','..'));
			$t->set_var('del_link','&nbsp;');
			$t->set_var('rename_link','&nbsp;');
			$t->set_var('owner','');
			$t->set_var('group','');
			$t->set_var('permissions','');
			$t->fp('rowlist_dir','row',True);

			while (list(,$fileinfo) = each($contents))
			{
//				echo '<pre>'; print_r($fileinfo); echo '</pre>';
				$newdir = $fileinfo['name'];
				$t->set_var('owner',$fileinfo['owner']);
				$t->set_var('group',$fileinfo['group']);
				$t->set_var('permissions',$fileinfo['permissions']);

/*				if ($fileinfo['size'] < 1024)
				{
					$fileinfo['size'] = $fileinfo['size'] . ' b';
				}
				else */
				if ($fileinfo['size'] < 999999)
				{
					$fileinfo['size'] = round(10*($fileinfo['size']/1024))/10 .' k';
				}
				else
				{
					//  round to W.XYZ megs by rounding WX.YZ
					$fileinfo['size'] = round($fileinfo['size']/(1024*100));
					// then bring it back one digit and add the MB string
					$fileinfo['size'] = ($fileinfo['size']/10) .' MB';
				}
				if (substr($fileinfo['permissions'],0,1) == 'd')
				{
					$file = $fileinfo['name'];
					$t->set_var('name',macro_get_link('cwd',$fileinfo['name']));
					$t->set_var('del_link',macro_get_link('rmdir',lang('Delete')));
					$t->set_var('size','');
				}
				else
				{
					$file = $fileinfo['name'];
					$t->set_var('del_link',macro_get_link('delete',lang('Delete')));
					$t->set_var('name',macro_get_link('get',$fileinfo['name']));
					$t->set_var('size',$fileinfo['size']);
				}
				$t->set_var('rename_link',macro_get_link('rename',lang('Rename')));
				$t->fp('rowlist_dir','row',True);
			}
			ftp_quit($ftp);
			$t->pfp('out','main');
		} 
		else 
		{
			updateSession();
			$sessionUpdated=true;
			if (!$tried_default) 
			{
				// don't put out an error on the default login
				for($i=0;$i<strlen($connInfo["password"]);$i++)
				{
					 $pass.="*"; 
				}
				$t->set_var("error_message", lang("bad connection", 
					$connInfo["ftpserver"], $connInfo["username"], $pass), true);
				$t->parse("out","bad_connect",false);
				$t->p("out");
			}
			newLogin($t,$connInfo["ftpserver"],$connInfo["username"],"");
		}

	}
	else 
	{

		// set the login and such to ""
		updateSession("");
		$sessionUpdated=true;
		// $phpgw->modsession(
		newLogin($t,$default_server,$default_login,"");
	}
	if (!$sessionUpdated && $action=="cwd") 
	{
		// echo "updating session with new cwd<BR>\n";
		updateSession($connInfo);
	}

$phpgw->common->phpgw_footer();
?>
