<?php
	/**************************************************************************\
	* -------------------------------------------------------------------------*
	* This library is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU Lesser General Public License as published by *
	* the Free Software Foundation; either version 2.1 of the License,         *
	* or any later version.                                                    *
	* This library is distributed in the hope that it will be useful, but      *
	* WITHOUT ANY WARRANTY; without even the implied warranty of               *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
	* See the GNU Lesser General Public License for more details.              *
	* You should have received a copy of the GNU Lesser General Public License *
	* along with this library; if not, write to the Free Software Foundation,  *
	* Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
	\**************************************************************************/

	/* $Id$ */

	class uifilemanager
	{

		var $public_functions = array(
			'index'	=> True,
		);

		var $bo;
//		var $pref;
		var $imgroot;

		function uifilemanager()
		{
			$this->bo = CreateObject('filemanager.bofilemanager');
			
			//FIXME remove this one
			$this->imgroot='filemanager/templates/default/';
		
		
		}


		function index()
		{
			global $download,$path,$sortby,$op,$go,$file,$show_upload_boxes;

			###
			# Enable this to display some debugging info
			###

			$phpwh_debug = 0;

			@reset ($GLOBALS['HTTP_POST_VARS']);
			while (list ($name,) = @each ($GLOBALS['HTTP_POST_VARS']))
			{
				$$name = $GLOBALS['HTTP_POST_VARS'][$name];
			}

			//			var_dump($GLOBALS['HTTP_POST_VARS']);


			@reset ($GLOBALS['HTTP_GET_VARS']);
			while (list ($name,) = @each ($GLOBALS['HTTP_GET_VARS']))
			{
				$$name = $GLOBALS['HTTP_GET_VARS'][$name];
			}

			$to_decode = array
			(
				/*
				Decode
				'var'	when	  'avar' == 'value'
				or
				'var'	when	  'var'  is set
				*/
				'op'	=> array ('op' => ''),
				'path'	=> array ('path' => ''),
				'file'	=> array ('file' => ''),
				'sortby'	=> array ('sortby' => ''),
				'fileman'	=> array ('fileman' => ''),
				'messages'	=> array ('messages'	=> ''),
				'help_name'	=> array ('help_name' => ''),
				'renamefiles'	=> array ('renamefiles' => ''),
				'comment_files'	=> array ('comment_files' => ''),
				'show_upload_boxes'	=> array ('show_upload_boxes' => '')
			);

			// FIXME (pim) decode doesn't work and I don't know where its for

			/*

			reset ($to_decode);
			while (list ($var, $conditions) = each ($to_decode))
			{
				while (list ($condvar, $condvalue) = each ($conditions))
				{
					if (isset ($$condvar) && ($condvar == $var || $$condvar == $condvalue))
					{
						if (is_array ($$var))
						{
							$temp = array ();
							//some fixes in this section were supplied by Michael Totschnig
							while (list ($varkey, $varvalue) = each ($$var))
							{
								if (is_int ($varkey))
								{
									$temp[$varkey] = stripslashes (base64_decode(urldecode(($varvalue))));
								}
								else
								{
									$temp[stripslashes (base64_decode(urldecode(($varkey))))] = $varvalue;
								}
							}
							$$var = $temp;
						}
						elseif (isset ($$var))
						{
							$$var = stripslashes (base64_decode(urldecode ($$var)));
						}
					}
				}
			}

			*/

			//FIXME re-enable this here above

			if ($noheader || $nofooter || ($download && (count ($fileman) > 0)) || ($op == 'view' && $file) || ($op == 'history' && $file) || ($op == 'help' && $help_name))
			{
				$noheader = True;
				$nofooter = True;
			}

			$GLOBALS['phpgw_info']['flags'] = array
			(
				'currentapp'	=> 'filemanager',
				'noheader'	=> $noheader,
				'nofooter'	=> $nofooter,
				'noappheader'	=> False,
				'enable_browser_class'	=> True
			);

//			var_dump($GLOBALS['phpgw_info']['flags']);
			$GLOBALS['phpgw']->common->phpgw_header();


			if ($execute && $command_line)
			{
				if ($result = $this->bo->vfs->command_line (array ('command_line' => stripslashes ($command_line))))
				{
					$messages = $this->html_text_bold (lang('Command sucessfully run'),1);
					if ($result != 1 && strlen ($result) > 0)
					{
						$messages .= $this->html_break (2, NULL, 1) . $result;
					}
				}
				else
				{
					$messages = $GLOBALS['phpgw']->common->error_list (array (lang('Error running command')));
				}
			}

			###
			# Page to process users
			# Code is fairly hackish at the beginning, but it gets better
			# Highly suggest turning wrapping off due to long SQL queries
			###

			###
			# Some hacks to set and display directory paths correctly
			###

			if ($go)
			{
				$path = $todir;
			}

			//			var_dump($GLOBALS['phpgw_info']);
			//			var_dump($GLOBALS['phpgw']);
			//			die();

			if (!$path)
			{
				$path = $this->bo->vfs->pwd ();

				if (!$path || $this->bo->vfs->pwd (array ('full' => False)) == '')
				{
					$path = $GLOBALS['homedir'];
				}
			}



			$this->bo->vfs->cd (array ('string' => False, 'relatives' => array (RELATIVE_NONE), 'relative' => False));
			$this->bo->vfs->cd (array ('string' => $path, 'relatives' => array (RELATIVE_NONE), 'relative' => False));

			$pwd = $this->bo->vfs->pwd ();

			if (!$cwd = substr ($path, strlen ($GLOBALS['homedir']) + 1))
			{
				$cwd = '/';
			}
			else
			{
				$cwd = substr ($pwd, strrpos ($pwd, '/') + 1);
			}

			$disppath = $path;

			/* This just prevents // in some cases */
			if ($path == '/')
			$dispsep = '';
			else
			$dispsep = '/';

			if (!($lesspath = substr ($path, 0, strrpos ($path, '/'))))
			$lesspath = '/';

			$now = date ('Y-m-d');

			if ($phpwh_debug)
			{
				echo "<b>PHPWebHosting debug:</b><br>
				path: $path<br>
				disppath: $disppath<br>
				cwd: $cwd<br>
				lesspath: $lesspath
				<p>
				<b>phpGW debug:</b><br>
				real getabsolutepath: " . $this->bo->vfs->getabsolutepath (array ('target' => False, 'mask' => False, 'fake' => False)) . "<br>
				fake getabsolutepath: " . $this->bo->vfs->getabsolutepath (array ('target' => False)) . "<br>
				appsession: " . $GLOBALS['phpgw']->session->appsession ('vfs','') . "<br>
				pwd: " . $this->bo->vfs->pwd () . "<br>";
			}

			###
			# Get their readable groups to be used throughout the script
			###

			$groups = array ();

			$groups = $GLOBALS['phpgw']->accounts->get_list ('groups');

			$readable_groups = array ();

			while (list ($num, $account) = each ($groups))
			{
				if ($this->bo->vfs->acl_check (array (
					'owner_id' => $account['account_id'],
					'operation' => PHPGW_ACL_READ
				))
			)
			{
				$readable_groups[$account['account_lid']] = Array('account_id' => $account['account_id'], 'account_name' => $account['account_lid']);
			}
		}

		$groups_applications = array ();

		while (list ($num, $group_array) = each ($readable_groups))
		{
			$group_id = $GLOBALS['phpgw']->accounts->name2id ($group_array['account_name']);

			$applications = CreateObject('phpgwapi.applications', $group_id);
			$groups_applications[$group_array['account_name']] = $applications->read_account_specific ();
		}

		###
		# We determine if they're in their home directory or a group's directory,
		# and set the VFS working_id appropriately
		###

		if ((preg_match ('+^'.$GLOBALS['fakebase'].'\/(.*)(\/|$)+U', $path, $matches)) && $matches[1] != $GLOBALS['userinfo']['account_lid'])
		{
			$this->bo->vfs->working_id = $GLOBALS['phpgw']->accounts->name2id ($matches[1]);
		}
		else
		{
			$this->bo->vfs->working_id = $GLOBALS['userinfo']['username'];
		}

		if ($path != $GLOBALS['homedir']
		&& $path != $GLOBALS['fakebase']
		&& $path != '/'
		&& !$this->bo->vfs->acl_check (array (
			'string' => $path,
			'relatives' => array (RELATIVE_NONE),
			'operation' => PHPGW_ACL_READ
		))
	)
	{
		echo $GLOBALS['phpgw']->common->error_list (array (lang('You do not have access to %1', $path)));
		$this->html_break (2);
		$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$GLOBALS['homedir'], lang('Go to your home directory'));
		$this->html_page_close ();
	}

	$GLOBALS['userinfo']['working_id'] = $this->bo->vfs->working_id;
	$GLOBALS['userinfo']['working_lid'] = $GLOBALS['phpgw']->accounts->id2name ($GLOBALS['userinfo']['working_id']);

	###
	# If their home directory doesn't exist, we create it
	# Same for group directories
	###

	if (($path == $GLOBALS['homedir'])
	&& !$this->bo->vfs->file_exists (array (
		'string' => $GLOBALS['homedir'],
		'relatives' => array (RELATIVE_NONE)
	))
)
{
	$this->bo->vfs->override_acl = 1;

	if (!$this->bo->vfs->mkdir (array ('string' => $GLOBALS['homedir'], 'relatives' => array (RELATIVE_NONE))))
	{
		// FIXME (pim) ??
		$p = $this->bo->vfs->path_parts (array ('string' => $GLOBALS['homedir'], 'relatives' => array (RELATIVE_NONE)));
		echo $GLOBALS['phpgw']->common->error_list (array (lang('Could not create directory %1', $GLOBALS['homedir'] . ' (' . $p->real_full_path . ')')));
	}

	$this->bo->vfs->override_acl = 0;
}

###
# Verify path is real
###

if ($path != $GLOBALS['homedir'] && $path != '/' && $path != $GLOBALS['fakebase'])
{
	if (!$this->bo->vfs->file_exists (array ('string' => $path, 'relatives' => array (RELATIVE_NONE))))
	{
		echo $GLOBALS['phpgw']->common->error_list (array (lang('Directory %1 does not exist', $path)));
		$this->html_break (2);
		$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$GLOBALS['homedir'], lang('Go to your home directory'));
		$this->html_break (2);
		$this->html_link_back ();
		$this->html_page_close ();
	}
}

/* Update if they request it, or one out of 20 page loads */
srand ((double) microtime() * 1000000);
if ($update || rand (0, 19) == 4)
{
	$this->bo->vfs->update_real (array ('string' => $path, 'relatives' => array (RELATIVE_NONE)));
}

###
# Check available permissions for $path, so we can disable unusable operations in user interface
###

if ($this->bo->vfs->acl_check (array (
	'string'        => $path,
	'relatives' => array (RELATIVE_NONE),
	'operation' => PHPGW_ACL_ADD
))
			)
			{
				$can_add = True;
			}

			###
			# Default is to sort by name
			###

			if (!$sortby)
			{
				$sortby = 'name';
			}

			###
			# Decide how many upload boxes to show
			###
			//var_dump($show_upload_boxes);
			//die();

			if (!$show_upload_boxes || $show_upload_boxes <= 0)
			{
				if (!$show_upload_boxes = $GLOBALS['settings']['show_upload_boxes'])
				{
					$show_upload_boxes = 5;
				}
			}

			###
			# Read in file info from database to use in the rest of the script
			# $fakebase is a special directory.  In that directory, we list the user's
			# home directory and the directories for the groups they're in
			###

			$numoffiles = 0;
			if ($path == $GLOBALS['fakebase'])
			{
				if (!$this->bo->vfs->file_exists (array ('string' => $GLOBALS['homedir'], 'relatives' => array (RELATIVE_NONE))))
				{
					$this->bo->vfs->mkdir (array ('string' => $GLOBALS['homedir'], 'relatives' => array (RELATIVE_NONE)));
				}

				$ls_array = $this->bo->vfs->ls (array (
					'string'	=> $GLOBALS['homedir'],
					'relatives'	=> array (RELATIVE_NONE),
					'checksubdirs'	=> False,
					'nofiles'	=> True
				)
			);
			$files_array[] = $ls_array[0];
			$numoffiles++;
			//	$files_array = $ls_array;
			//	$numoffiles = count($ls_array);

			reset ($readable_groups);
			while (list ($num, $group_array) = each ($readable_groups))
			{
				###
				# If the group doesn't have access to this app, we don't show it
				###

				if (!$groups_applications[$group_array['account_name']][$GLOBALS['appname']]['enabled'])
				{
					continue;
				}

				if (!$this->bo->vfs->file_exists (array (
					'string'	=> $GLOBALS['fakebase'].'/'.$group_array['account_name'],
					'relatives'	=> array (RELATIVE_NONE)
				))
			)
			{
				$this->bo->vfs->override_acl = 1;
				$this->bo->vfs->mkdir (array (
					'string'	=> $GLOBALS['fakebase'].'/'.$group_array['account_name'],
					'relatives'	=> array (RELATIVE_NONE)
				)
			);
			$this->bo->vfs->override_acl = 0;

			$this->bo->vfs->set_attributes (array (
				'string'	=> $GLOBALS['fakebase'].'/'.$group_array['account_name'],
				'relatives'	=> array (RELATIVE_NONE),
				'attributes'	=> array (
					'owner_id' => $group_array['account_id'],
					'createdby_id' => $group_array['account_id']
				)
			)
		);
	}

	$ls_array = $this->bo->vfs->ls (array (
		'string'	=> $GLOBALS['fakebase'].'/'.$group_array['account_name'],
		'relatives'	=> array (RELATIVE_NONE),
		'checksubdirs'	=> False,
		'nofiles'	=> True
	)
);

$files_array[] = $ls_array[0];

$numoffiles++;
				}
			}
			else
			{
				$ls_array = $this->bo->vfs->ls (array (
					'string'	=> $path,
					'relatives'	=> array (RELATIVE_NONE),
					'checksubdirs'	=> False,
					'nofiles'	=> False,
					'orderby'	=> $sortby
				)
			);

			if ($phpwh_debug)
			{
				echo '# of files found in "'.$path.'" : '.count($ls_array).'<br>'."\n";
			}

			while (list ($num, $file_array) = each ($ls_array))
			{
				$numoffiles++;
				$files_array[] = $file_array;
				if ($phpwh_debug)
				{
					echo 'Filename: '.$file_array['name'].'<br>'."\n";
				}
			}
		}

		if (!is_array ($files_array))
		{
			$files_array = array ();
		}

		if ($download)
		{
			for ($i = 0; $i != $numoffiles; $i++)
			{
				if (!$fileman[$i])
				{
					continue;
				}

				$download_browser = CreateObject ('phpgwapi.browser');
				$download_browser->content_header ($fileman[$i]);
				echo $this->bo->vfs->read (array ('string' => $fileman[$i]));
				$GLOBALS['phpgw']->common->phpgw_exit ();
			}
		}

		if ($op == 'view' && $file)
		{
			$ls_array = $this->bo->vfs->ls (array (
				'string'	=> $path.'/'.$file,
				'relatives'	=> array (RELATIVE_ALL),
				'checksubdirs'	=> False,
				'nofiles'	=> True
			)
		);

		if ($ls_array[0]['mime_type'])
		{
			$mime_type = $ls_array[0]['mime_type'];
		}
		elseif ($GLOBALS['settings']['viewtextplain'])
		{
			$mime_type = 'text/plain';
		}

		header('Content-type: ' . $mime_type);
		echo $this->bo->vfs->read (array (
			'string'	=> $path.'/'.$file,
			'relatives'	=> array (RELATIVE_NONE)
		)
	);
	$GLOBALS['phpgw']->common->phpgw_exit ();
}

if ($op == 'history' && $file)
{
	$journal_array = $this->bo->vfs->get_journal (array (
		'string'	=> $file,
		'relatives'	=> array (RELATIVE_ALL)
	)
);

if (is_array ($journal_array))
{
	$this->html_table_begin ();
	$this->html_table_row_begin ();
	$this->html_table_col_begin ();
	$this->html_text_bold (lang('Date'));
	$this->html_table_col_end ();
	$this->html_table_col_begin ();
	$this->html_text_bold (lang('Version'));
	$this->html_table_col_end ();
	$this->html_table_col_begin ();
	$this->html_text_bold (lang('Who'));
	$this->html_table_col_end ();
	$this->html_table_col_begin ();
	$this->html_text_bold (lang('Operation'));
	$this->html_table_col_end ();
	$this->html_table_row_end ();

	while (list ($num, $journal_entry) = each ($journal_array))
	{
		$this->html_table_row_begin ();
		$this->html_table_col_begin ();
		$this->bo->html_text ($journal_entry['created'] . $this->html_nbsp (3, 1));
		$this->html_table_col_end ();
		$this->html_table_col_begin ();
		$this->bo->html_text ($journal_entry['version'] . $this->html_nbsp (3, 1));
		$this->html_table_col_end ();
		$this->html_table_col_begin ();
		$this->bo->html_text ($GLOBALS['phpgw']->accounts->id2name ($journal_entry['owner_id']) . $this->html_nbsp (3, 1));
		$this->html_table_col_end ();
		$this->html_table_col_begin ();
		$this->bo->html_text ($journal_entry['comment']);
		$this->html_table_col_end ();
	}

	$this->html_table_end ();
	$this->html_page_close ();
}
else
{
	$this->html_text_bold (lang('No version history for this file/directory'));
}

			}

			if ($newfile && $createfile)
			{
				if ($badchar = $this->bo->bad_chars ($createfile, True, True))
				{
					echo $GLOBALS['phpgw']->common->error_list (array ($this->bo->html_encode (lang('File names cannot contain "%1"',$badchar), 1)));
					$this->html_break (2);
					$this->html_link_back ();
					$this->html_page_close ();
				}

				if ($this->bo->vfs->file_exists (array (
					'string'	=> $createfile,
					'relatives'	=> array (RELATIVE_ALL)
				))
			)
			{
				echo $GLOBALS['phpgw']->common->error_list (array (lang('File %1 already exists. Please edit it or delete it first.', $createfile)));
				$this->html_break (2);
				$this->html_link_back ();
				$this->html_page_close ();
			}

			if ($this->bo->vfs->touch (array (
				'string'	=> $createfile,
				'relatives'	=> array (RELATIVE_ALL)
			))
		)
		{
			$fileman = array ();
			$fileman[0] = $createfile;
			$edit = 1;
			$numoffiles++;
		}
		else
		{
			echo $GLOBALS['phpgw']->common->error_list (array (lang('File %1 could not be created.', $createfile)));
		}
	}

	if ($op == 'help' && $help_name)
	{
		while (list ($num, $help_array) = each ($help_info))
		{
			if ($help_array[0] != $help_name)
			continue;

			$help_array[1] = preg_replace ("/\[(.*)\|(.*)\]/Ue", "html_help_link ('\\1', '\\2', False, True)", $help_array[1]);
			$help_array[1] = preg_replace ("/\[(.*)\]/Ue", "html_help_link ('\\1', '\\1', False, True)", $help_array[1]);

			$this->html_font_set ('4');
			$title = ereg_replace ('_', ' ', $help_array[0]);
			$title = ucwords ($title);
			$this->bo->html_text ($title);
			$this->html_font_end ();

			$this->html_break (2);

			$this->html_font_set ('2');
			$this->bo->html_text ($help_array[1]);
			$this->html_font_end ();
		}

		$GLOBALS['phpgw']->common->phpgw_exit ();
	}

	###
	# Start Main Page
	###

	$this->html_page_begin (lang('Users').' :: '.$GLOBALS['userinfo']['username']);
	$this->html_page_body_begin (HTML_PAGE_BODY_COLOR);

	if ($messages)
	{
		$this->bo->html_text ($messages);
	}

	if (!count ($GLOBALS['settings']))
	{
		$GLOBALS[pref] = CreateObject ('phpgwapi.preferences', $GLOBALS['userinfo']['username']);
		$GLOBALS[pref]->read_repository (); 
		$GLOBALS['phpgw']->hooks->single ('add_def_pref', $GLOBALS['appname']);
		$GLOBALS[pref]->save_repository (True);
		$pref_array = $GLOBALS[pref]->read_repository ();
		$GLOBALS['settings'] = $pref_array[$GLOBALS['appname']];
	}

	###
	# Start Main Table 
	###

	if (!$op && !$delete && !$createdir && !$renamefiles && !$move && !$copy && !$edit && !$comment_files)
	{
		$this->html_table_begin ('100%');
		$this->html_table_row_begin ();
		$this->html_table_col_begin ('center', NULL, 'top');
		$this->html_align ('center');
		$this->html_form_begin ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$path);
		if ($numoffiles || $cwd)
		{
			while (list ($num, $name) = each ($GLOBALS['settings']))
			{
				if ($name)
				{
					$columns++;
				}
			}
			$columns++;
			$this->html_table_begin ();
			$this->html_table_row_begin (NULL, NULL, NULL, HTML_TABLE_FILES_HEADER_BG_COLOR);
			$this->html_table_col_begin ('center', NULL, NULL, NULL, $columns);
			$this->html_table_begin ('100%');
			$this->html_table_row_begin ();
			$this->html_table_col_begin ('left');

			if ($path != '/')
			{
				$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$lesspath, $this->html_image ($this->imgroot.'images/folder-up.png', lang('Up'), 'left', 0, NULL, 1));
				$this->html_help_link ('up');
			}

			$this->html_table_col_end ();
			$this->html_table_col_begin ('center');

			if ($cwd)
			{
				if ($path == $GLOBALS['homedir'])
				{
					$this->html_image ($this->imgroot.'images/folder-home.png', lang('Folder'), 'center');
				}
				else
				{
					$this->html_image ($this->imgroot.'images/folder.png', lang('Folder'), 'center');
				}
			}
			else
			{
				$this->html_image ($this->imgroot.'images/folder-home.png', lang('Home'));
			}

			$this->html_font_set (4, HTML_TABLE_FILES_HEADER_TEXT_COLOR);
			$this->html_text_bold ($disppath);
			$this->html_font_end ();
			$this->html_help_link ('directory_name');
			$this->html_table_col_end ();
			$this->html_table_col_begin ('right');

			if ($path != $GLOBALS['homedir'])
			{
				$this->html_link ('/index.php'.'menuaction=filemanager.uifilemanager.index&path='.$GLOBALS['homedir'], $this->html_image ($this->imgroot.'images/folder-home.png', lang('Home'), 'right', 0, NULL, 1));
				$this->html_help_link ('home');
			}

			$this->html_table_col_end ();
			$this->html_table_row_end ();
			$this->html_table_end ();
			$this->html_table_col_end ();
			$this->html_table_row_end ();
			$this->html_table_row_begin (NULL, NULL, NULL, HTML_TABLE_FILES_COLUMN_HEADER_BG_COLOR);

			###
			# Start File Table Column Headers
			# Reads values from $file_attributes array and preferences
			###

			$this->html_table_col_begin ();
			$this->bo->html_text (lang('Sort by:') . $this->html_nbsp (1, 1), NULL, NULL, 0);
			$this->html_help_link ('sort_by');
			$this->html_table_col_end ();

			reset ($this->bo->file_attributes);
			while (list ($internal, $displayed) = each ($this->bo->file_attributes))
			{
				if ($GLOBALS['settings'][$internal])
				{
					$this->html_table_col_begin ();
					$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$path.'&sortby='.$internal, $this->html_text_bold ($displayed, 1, 0));
					$this->html_help_link (strtolower (ereg_replace (' ', '_', $displayed)));
					$this->html_table_col_end ();
				}
			}

			$this->html_table_col_begin ();
			$this->html_table_col_end ();
			$this->html_table_row_end ();

			if ($GLOBALS['settings']['dotdot'] && $GLOBALS['settings']['name'] && $path != '/')
			{
				$this->html_table_row_begin ();
				$this->html_table_col_begin ();
				$this->html_table_col_end ();

				/* We can assume the next column is the name */
				$this->html_table_col_begin ();
				$this->html_image ($this->imgroot.'images/folder.png', lang('Folder'));
				$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$lesspath, '..');
				$this->html_table_col_end ();

				if ($GLOBALS['settings']['mime_type'])
				{
					$this->html_table_col_begin ();
					$this->bo->html_text (lang('Directory'));
					$this->html_table_col_end ();
				}

				$this->html_table_row_end ();
			}

			###
			# List all of the files, with their attributes
			###

			reset ($files_array);
			for ($i = 0; $i != $numoffiles; $i++)
			{
				$files = $files_array[$i];

				if ($rename || $edit_comments)
				{
					unset ($this_selected);
					unset ($renamethis);
					unset ($edit_this_comment);

					for ($j = 0; $j != $numoffiles; $j++)
					{
						if ($fileman[$j] == $files['name'])
						{
							$this_selected = 1;
							break;
						}
					}

					if ($rename && $this_selected)
					{
						$renamethis = 1;
					}
					elseif ($edit_comments && $this_selected)
					{
						$edit_this_comment = 1;
					}
				}

				if (!$GLOBALS['settings']['dotfiles'] && ereg ("^\.", $files['name']))
				{
					continue;
				}

				$this->html_table_row_begin (NULL, NULL, NULL, HTML_TABLE_FILES_BG_COLOR);

				###
				# Checkboxes
				###

				$this->html_table_col_begin ('right');

				if (!$rename && !$edit_comments && $path != $GLOBALS['fakebase'] && $path != '/')
				{
					$this->html_form_input ('checkbox', 'fileman['.$i.']', base64_encode ($files['name']));
				}
				elseif ($renamethis)
				{
					$this->html_form_input ('hidden', 'fileman[' . base64_encode ($files['name']) . ']', $files['name'], NULL, NULL, 'checked');
				}
				else
				{
					$this->html_nbsp();
				}

				$this->html_table_col_end ();

				###
				# File name and icon
				###

				if ($GLOBALS['settings']['name'])
				{
					if ($phpwh_debug)
					{
						echo 'Setting file name: '.$files['name'].'<br>'."\n";
					}

					$this->html_table_col_begin ();

					if ($renamethis)
					{
						if ($files['mime_type'] == 'Directory')
						{
							$this->html_image ($this->imgroot.'images/folder.png', lang('Folder'));
						}
						$this->html_form_input ('text', 'renamefiles[' . base64_encode ($files['name']) . ']', $files['name'], 255);
					}
					else
					{
						if ($files['mime_type'] == 'Directory')
						{
							$this->html_image ($this->imgroot.'images/folder.png', lang('Folder'));		
							$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$path.$dispsep.$files['name'], $files['name']);
						}
						else
						{
							if ($GLOBALS['settings']['viewinnewwin'])
							{
								$target = '_new';
							}

							if ($GLOBALS['settings']['viewonserver'] && isset ($GLOBALS['filesdir']) && !$files['link_directory'])
							{
								#FIXME
								$clickview = $GLOBALS['filesdir'].$pwd.'/'.$files['name'];

								if ($phpwh_debug)
								{
									echo 'Setting clickview = '.$clickview.'<br>'."\n";
									$this->html_link ($clickview, '',$files['name'], 0, 1, 0, '');
								}
							}
							else
							{
								#FIXME??
								//								$clickview = $GLOBALS['appname'].'/index.php?op=view&file='.$files['name'].'&path='.$path;
								$this->html_link ('index.php','menuaction=filemanager.uifilemanager.index&op=view&file='.$files['name'].'&path='.$path, $files['name'], 0, 1, 0, $target);
							}


							#FIXME
							//$this->html_link ($clickview, $files['name'], 0, 1, 0, $target);
						}
					}

					$this->html_table_col_end ();
				}

				###
				# MIME type
				###

				if ($GLOBALS['settings']['mime_type'])
				{
					$this->html_table_col_begin ();
					$this->bo->html_text ($files['mime_type']);
					$this->html_table_col_end ();
				}

				###
				# File size
				###

				if ($GLOBALS['settings']['size'])
				{
					$this->html_table_col_begin ();

					$size = $this->bo->vfs->get_size (array (
						'string'	=> $files['directory'] . '/' . $files['name'],
						'relatives'	=> array (RELATIVE_NONE)
					)
				);

				$this->bo->borkb ($size);

				$this->html_table_col_end ();
			}

			###
			# Date created
			###
			if ($GLOBALS['settings']['created'])
			{
				$this->html_table_col_begin ();
				$this->bo->html_text ($files['created']);
				$this->html_table_col_end ();
			}

			###
			# Date modified
			###

			if ($GLOBALS['settings']['modified'])
			{
				$this->html_table_col_begin ();
				if ($files['modified'] != '0000-00-00')
				{
					$this->bo->html_text ($files['modified']);
				}
				$this->html_table_col_end ();
			}

			###
			# Owner name
			###

			if ($GLOBALS['settings']['owner'])
			{
				$this->html_table_col_begin ();
				$this->bo->html_text ($GLOBALS['phpgw']->accounts->id2name ($files['owner_id']));
				$this->html_table_col_end ();
			}

			###
			# Creator name
			###

			if ($GLOBALS['settings']['createdby_id'])
			{
				$this->html_table_col_begin ();
				if ($files['createdby_id'])
				{
					$this->bo->html_text ($GLOBALS['phpgw']->accounts->id2name ($files['createdby_id']));
				}
				$this->html_table_col_end ();
			}

			###
			# Modified by name
			###

			if ($GLOBALS['settings']['modifiedby_id'])
			{
				$this->html_table_col_begin ();
				if ($files['modifiedby_id'])
				{
					$this->bo->html_text ($GLOBALS['phpgw']->accounts->id2name ($files['modifiedby_id']));
				}
				$this->html_table_col_end ();
			}

			###
			# Application
			###

			if ($GLOBALS['settings']['app'])
			{
				$this->html_table_col_begin ();
				$this->bo->html_text ($files['app']);
				$this->html_table_col_end ();
			}

			###
			# Comment
			###

			if ($GLOBALS['settings']['comment'])
			{
				$this->html_table_col_begin ();
				if ($edit_this_comment)
				{
					$this->html_form_input ('text', 'comment_files[' . base64_encode ($files['name']) . ']', $this->bo->html_encode ($files['comment'], 1), 255);
				}
				else
				{
					$this->bo->html_text ($files['comment']);
				}
				$this->html_table_col_end ();
			}

			###
			# Version
			###

			if ($GLOBALS['settings']['version'])
			{
				$this->html_table_col_begin ();
				$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&op=history&file='.$files['name'].'&path='.$path, $files['version'], NULL, True, NULL, '_new');
				$this->html_table_col_end ();
			}

			###
			# Deleteable (currently not used)
			###

			if ($GLOBALS['settings']['deleteable'])
			{
				if ($files['deleteable'] == 'N')
				{
					$this->html_table_col_begin ();
					$this->html_image ($this->imgroot.'images/locked.png', lang('Locked'));
					$this->html_table_col_end ();
				}
				else
				{
					$this->html_table_col_begin ();
					$this->html_table_col_end ();
				}
			}

			$this->html_table_row_end ();

			if ($files['mime_type'] == 'Directory')
			{
				$usedspace += $fileinfo[0];
			}
			else
			{
				$usedspace += $files['size'];
			}
		}

		$this->html_table_end ();
		$this->html_break (2);

		if ($path != '/' && $path != $GLOBALS['fakebase'])
		{
			if (!$rename && !$edit_comments)
			{
				$this->html_form_input ('submit', 'edit', lang('Edit'));
				$this->html_help_link ('edit');
				$this->html_nbsp (3);
			}

			if (!$edit_comments)
			{
				$this->html_form_input ('submit', 'rename', lang('Rename'));
				$this->html_help_link ('rename');
				$this->html_nbsp (3);
			}

			if (!$rename && !$edit_comments)
			{
				$this->html_form_input ('submit', 'delete', lang('Delete'));
				$this->html_help_link ('delete');
				$this->html_nbsp (3);
			}

			if (!$rename)
			{
				$this->html_form_input ('submit', 'edit_comments', lang('Edit comments'));
				$this->html_help_link ('edit_comments');
			}
		}
	}

	###
	# Display some inputs and info, but not when renaming or editing comments
	###

	if (!$rename && !$edit_comments)
	{
		###
		# Begin Copy to/Move to selection
		###

		$this->html_break (1);
		$this->html_form_input ('submit', 'go', lang('Go to:'));
		$this->html_help_link ('go_to');

		if ($path != '/' && $path != $GLOBALS['fakebase'])
		{
			$this->html_form_input ('submit', 'copy', lang('Copy to:'));
			$this->html_help_link ('copy_to');
			$this->html_form_input ('submit', 'move', lang('Move to:'));
			$this->html_help_link ('move_to');
		}

		$this->html_form_select_begin ('todir');

		$this->html_break (1);

		###
		# First we get the directories in their home directory
		###

		$dirs = array ();
		$dirs[] = array ('directory' => $GLOBALS['fakebase'], 'name' => $GLOBALS['userinfo']['account_lid']);

		$ls_array = $this->bo->vfs->ls (array (
			'string'	=> $GLOBALS['homedir'],
			'relatives'	=> array (RELATIVE_NONE),
			'checksubdirs'	=> True,
			'mime_type'	=> 'Directory'
		)
	);

	while (list ($num, $dir) = each ($ls_array))
	{
		$dirs[] = $dir;
	}


	###
	# Then we get the directories in their readable groups' home directories
	###

	reset ($readable_groups);
	while (list ($num, $group_array) = each ($readable_groups))
	{
		###
		# Don't list directories for groups that don't have access
		###

		if (!$groups_applications[$group_array['account_name']][$GLOBALS['appname']]['enabled'])
		{
			continue;
		}

		$dirs[] = array ('directory' => $GLOBALS['fakebase'], 'name' => $group_array['account_name']);

		// FIXME?? (pim)
		$ls_array = $this->bo->vfs->ls (array (
			'string'	=> $GLOBALS['fakebase'].'/'.$group_array['account_name'],
			'relatives'	=> array (RELATIVE_NONE),
			'checksubdirs'	=> True,
			'mime_type'	=> 'Directory'
		)
	);
	while (list ($num, $dir) = each ($ls_array))
	{
		$dirs[] = $dir;
	}
}

reset ($dirs);
while (list ($num, $dir) = each ($dirs))
{
	if (!$dir['directory'])
	{
		continue;
	}

	###
	# So we don't display //
	###

	if ($dir['directory'] != '/')
	{
		$dir['directory'] .= '/';
	}

	###
	# No point in displaying the current directory, or a directory that doesn't exist
	###

	if ((($dir['directory'] . $dir['name']) != $path)
	&& $this->bo->vfs->file_exists (array (
		'string'	=> $dir['directory'] . $dir['name'],
		'relatives'	=> array (RELATIVE_NONE)
	))
)
{
	$this->html_form_option ($dir['directory'] . $dir['name'], $dir['directory'] . $dir['name']);
}
					}

					$this->html_form_select_end ();
					$this->html_help_link ('directory_list');

					if ($path != '/' && $path != $GLOBALS['fakebase'])
					{
						$this->html_break (1);

						$this->html_form_input ('submit', 'download', lang('Download'));
						$this->html_help_link ('download');
						$this->html_nbsp (3);

						if ($can_add)
						{
							$this->html_form_input ('text', 'createdir', NULL, 255, 15);
							$this->html_form_input ('submit', 'newdir', lang('Create Folder'));
							$this->html_help_link ('create_folder');
						}
					}

					$this->html_break (1);
					$this->html_form_input ('submit', 'update', lang('Update'));
					$this->html_help_link ('update');

					if ($path != '/' && $path != $GLOBALS['fakebase'] && $can_add)
					{
						$this->html_nbsp (3);
						$this->html_form_input ('text', 'createfile', NULL, 255, 15);
						$this->html_form_input ('submit', 'newfile', lang('Create File'));
						$this->html_help_link ('create_file');
					}

					if ($GLOBALS['settings']['show_command_line'])
					{
						$this->html_break (2);
						$this->html_form_input ('text', 'command_line', NULL, NULL, 50);
						$this->html_help_link ('command_line');

						$this->html_break (1);
						$this->html_form_input ('submit', 'execute', lang('Execute'));
						$this->html_help_link ('execute');
					}

					$this->html_form_end ();

					$this->html_help_link ('file_stats');
					$this->html_break (1);
					$this->html_text_bold (lang('Files').': ');
					$this->bo->html_text ($numoffiles);
					$this->html_nbsp (3);

					$this->html_text_bold (lang('Used space').': ');
					$this->bo->html_text ($this->bo->borkb ($usedspace, NULL, 1));
					$this->html_nbsp (3);

					if ($path == $GLOBALS['homedir'] || $path == $GLOBALS['fakebase'])
					{
						$this->html_text_bold (lang('Unused space').': ');
						$this->bo->html_text ($this->bo->borkb ($GLOBALS['userinfo']['hdspace'] - $usedspace, NULL, 1));

						$ls_array = $this->bo->vfs->ls (array (
							'string'	=> $path,
							'relatives'	=> array (RELATIVE_NONE)
						)
					);

					$i = count ($ls_array);

					$this->html_break (2);
					$this->html_text_bold (lang('Total Files').': ');
					$this->bo->html_text ($i);
				}

				###
				# Show file upload boxes. Note the last argument to html ().  Repeats $show_upload_boxes times
				###

				if ($path != '/' && $path != $GLOBALS['fakebase'] && $can_add)
				{
					$this->html_break (2);
					$this->html_form_begin ('/index.php','menuaction=filemanager.uifilemanager.index&op=upload&path='.$path, 'post', 'multipart/form-data');
					$this->html_table_begin ();
					$this->html_table_row_begin ('center');
					$this->html_table_col_begin ();
					$this->html_text_bold (lang('File'));
					$this->html_help_link ('upload_file');
					$this->html_table_col_end ();
					$this->html_table_col_begin ();
					$this->html_text_bold (lang('Comment'));
					$this->html_help_link ('upload_comment');
					$this->html_table_col_end ();
					$this->html_table_row_end ();

					$this->html_table_row_begin ();
					$this->html_table_col_begin ();
					$this->html_form_input ('hidden', 'show_upload_boxes', base64_encode ($show_upload_boxes));
					$this->html ($this->html_form_input ('file', 'upload_file[]', NULL, 255, NULL, NULL, NULL, 1) . $this->html_break (1, NULL, 1), $show_upload_boxes);
					$this->html_table_col_end ();
					$this->html_table_col_begin ();
					$this->html ($this->html_form_input ('text', 'upload_comment[]', NULL, NULL, NULL, NULL, NULL, 1) . $this->html_break (1, NULL, 1), $show_upload_boxes);
					$this->html_table_col_end ();
					$this->html_table_row_end ();
					$this->html_table_end ();
					$this->html_form_input ('submit', 'upload_files', lang('Upload files'));
					$this->html_help_link ('upload_files');
					$this->html_break (2);
					$this->bo->html_text (lang('Show') . $this->html_nbsp (1, True));
					$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&show_upload_boxes=5', '5');
					$this->html_nbsp ();
					$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&show_upload_boxes=10', '10');
					$this->html_nbsp ();
					$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&show_upload_boxes=20', '20');
					$this->html_nbsp ();
					$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&show_upload_boxes=50', '50');
					$this->html_nbsp ();
					$this->bo->html_text (lang('upload fields'));
					$this->html_nbsp ();
					$this->html_help_link ('show_upload_fields');
					$this->html_form_end ();
				}
			}

			$this->html_table_col_end ();
			$this->html_table_row_end ();
			$this->html_table_end ();
			$this->html_page_close ();
		}

		###
		# Handle Editing files
		###

		if ($edit)
		{
			###
			# If $edit is "Edit", we do nothing, and let the for loop take over
			###

			if ($edit_file)
			{
				$edit_file_content = stripslashes ($edit_file_content);
			}

			if ($edit_preview)
			{
				$content = $edit_file_content;

				$this->html_break (1);
				$this->html_text_bold (lang('Preview of %1', $path.'/'.$edit_file));
				$this->html_break (2);

				$this->html_table_begin ('90%');
				$this->html_table_row_begin ();
				$this->html_table_col_begin ();
				$this->bo->html_text (nl2br ($content));
				$this->html_table_col_end ();
				$this->html_table_row_end ();
				$this->html_table_end ();
			}
			elseif ($edit_save)
			{
				$content = $edit_file_content;

				if ($this->bo->vfs->write (array (
					'string'	=> $edit_file,
					'relatives'	=> array (RELATIVE_ALL),
					'content'	=> $content
				))
			)
			{
				$this->html_text_bold (lang('Saved %1', $path.'/'.$edit_file));
				$this->html_break (2);
				$this->html_link_back ();
			}
			else
			{
				$this->html_text_error (lang('Could not save %1', $path.'/'.$edit_file));
				$this->html_break (2);
				$this->html_link_back ();
			}
		}

		/* This doesn't work just yet
		elseif ($edit_save_all)
		{
			for ($j = 0; $j != $numoffiles; $j++)
			{
				$fileman[$j];

				$content = $fileman[$j];
				echo 'fileman['.$j.']: '.$fileman[$j].'<br><b>'.$content.'</b><br>';
				continue;

				if ($this->bo->vfs->write (array (
					'string'	=> $fileman[$j],
					'relatives'	=> array (RELATIVE_ALL),
					'content'	=> $content
				))
			)
			{
				$this->html_text_bold (lang('Saved %1', $path.'/'.$fileman[$j]));
				$this->html_break (1);
			}
			else
			{
				$this->html_text_error (lang('Could not save %1', $path.'/'.$fileman[$j]));
				$this->html_break (1);
			}
		}

		$this->html_break (1);
	}
	*/

	###
	# Now we display the edit boxes and forms
	###

	for ($j = 0; $j != $numoffiles; $j++)
	{
		###
		# If we're in preview or save mode, we only show the file
		# being previewed or saved
		###

		if ($edit_file && ($fileman[$j] != $edit_file))
		{
			continue;
		}

		if ($fileman[$j] && $this->bo->vfs->file_exists (array (
			'string'	=> $fileman[$j],
			'relatives'	=> array (RELATIVE_ALL)
		))
	)
	{
		if ($edit_file)
		{
			$content = stripslashes ($edit_file_content);
		}
		else
		{
			$content = $this->bo->vfs->read (array ('string' => $fileman[$j]));
		}

		$this->html_table_begin ('100%');
		$this->html_form_begin ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$path);
		$this->html_form_input ('hidden', 'edit', True);
		$this->html_form_input ('hidden', 'edit_file', $fileman[$j]);

		###
		# We need to include all of the fileman entries for each file's form,
		# so we loop through again
		###

		for ($i = 0; $i != $numoffiles; $i++)
		{
			$this->html_form_input ('hidden', 'fileman['.$i.']', base64_encode ($fileman[$i]));
		}

		$this->html_table_row_begin ();
		$this->html_table_col_begin ();
		$this->html_form_textarea ('edit_file_content', 35, 75, $content);
		$this->html_table_col_end ();
		$this->html_table_col_begin ('center');
		$this->html_form_input ('submit', 'edit_preview', lang('Preview %1', $this->bo->html_encode ($fileman[$j], 1)));
		$this->html_break (1);
		$this->html_form_input ('submit', 'edit_save', lang('Save %1', $this->bo->html_encode ($fileman[$j], 1)));
		//			$this->html_break (1);
		//			$this->html_form_input ('submit', 'edit_save_all', lang('Save all'));
		$this->html_table_col_end ();
		$this->html_table_row_end ();
		$this->html_break (2);
		$this->html_form_end ();
		$this->html_table_end ();
	}
}
			}

			###
			# Handle File Uploads
			###

			elseif ($op == 'upload' && $path != '/' && $path != $GLOBALS['fakebase'])
			{
				for ($i = 0; $i != $show_upload_boxes; $i++)
				{
					if ($badchar = $this->bo->bad_chars ($_FILES['upload_file']['name'][$i], True, True))
					{
						echo $GLOBALS['phpgw']->common->error_list (array ($this->bo->html_encode (lang('File names cannot contain "%1"', $badchar), 1)));

						continue;
					}

					###
					# Check to see if the file exists in the database, and get its info at the same time
					###

					$ls_array = $this->bo->vfs->ls (array (
						'string'	=> $path . '/' . $_FILES['upload_file']['name'][$i],
						'relatives'	=> array (RELATIVE_NONE),
						'checksubdirs'	=> False,
						'nofiles'	=> True
					)
				);

				$fileinfo = $ls_array[0];

				if ($fileinfo['name'])
				{
					if ($fileinfo['mime_type'] == 'Directory')
					{
						echo $GLOBALS['phpgw']->common->error_list (array (lang('Cannot replace %1 because it is a directory', $fileinfo['name'])));
						continue;
					}
				}

				if ($_FILES['upload_file']['size'][$i] > 0)
				{
					if ($fileinfo['name'] && $fileinfo['deleteable'] != 'N')
					{
						$this->bo->vfs->set_attributes (array (
							'string'	=> $_FILES['upload_file']['name'][$i],
							'relatives'	=> array (RELATIVE_ALL),
							'attributes'	=> array (
								'owner_id' => $GLOBALS['userinfo']['username'],
								'modifiedby_id' => $GLOBALS['userinfo']['username'],
								'modified' => $now,
								'size' => $_FILES['upload_file']['size'][$i],
								'mime_type' => $_FILES['upload_file']['type'][$i],
								'deleteable' => 'Y',
								'comment' => stripslashes ($upload_comment[$i])
							)
						)
					);

					$this->bo->vfs->cp(array (
						'from'	=> $_FILES['upload_file']['tmp_name'][$i],
						'to'	=> $_FILES['upload_file']['name'][$i],
						'relatives'	=> array (RELATIVE_NONE|VFS_REAL, RELATIVE_ALL)
					)
				);

				$this->html_text_summary(lang('Replaced %1', $disppath.'/'.$_FILES['upload_file']['name'][$i]), $_FILES['upload_file']['size'][$i]);
			}
			else
			{
				$this->bo->vfs->cp (array (
					'from'		=> $_FILES['upload_file']['tmp_name'][$i],
					'to'			=> $_FILES['upload_file']['name'][$i],
					'relatives'	=> array (RELATIVE_NONE|VFS_REAL, RELATIVE_ALL)
				)
			);

			$this->bo->vfs->set_attributes (array (
				'string'	=> $_FILES['upload_file']['name'][$i],
				'relatives'	=> array (RELATIVE_ALL),
				'attributes'	=> array (
					'mime_type' => $_FILES['upload_file']['type'][$i],
					'comment' => stripslashes ($upload_comment[$i])
				)
			)
		);

		$this->html_text_summary(lang('Created %1', $disppath.'/'.$_FILES['upload_file']['name'][$i]), $_FILES['upload_file']['size'][$i]);
	}
}
elseif ($_FILES['upload_file']['name'][$i])
{
	$this->bo->vfs->touch (array (
		'string'	=> $_FILES['upload_file']['name'][$i],
		'relatives'	=> array (RELATIVE_ALL)
	)
);

$this->bo->vfs->set_attributes (array (
	'string'	=> $_FILES['upload_file']['name'][$i],
	'relatives'	=> array (RELATIVE_ALL),
	'attributes'	=> array (
		'mime_type' => $_FILES['upload_file']['type'][$i],
		'comment' => $upload_comment[$i]
	)
)
						);

						$this->html_text_summary(lang('Created %1', $disppath.'/'.$_FILES['upload_file']['name'][$i]), $file_size[$i]);
					}
				}

				$this->html_break (2);
				$this->html_link_back ();
			}

			###
			# Handle Editing comments
			###

			elseif ($comment_files)
			{
				while (list ($file) = each ($comment_files))
				{
					if ($badchar = $this->bo->bad_chars ($comment_files[$file], False, True))
					{
						echo $GLOBALS['phpgw']->common->error_list (array ($this->html_text_italic ($file, 1) . $this->bo->html_encode (': ' . lang('Comments cannot contain "%1"', $badchar), 1)));
						continue;
					}

					$this->bo->vfs->set_attributes (array (
						'string'	=> $file,
						'relatives'	=> array (RELATIVE_ALL),
						'attributes'	=> array (
							'comment' => stripslashes ($comment_files[$file])
						)
					)
				);

				$this->html_text_summary (lang('Updated comment for %1', $path.'/'.$file));
			}

			$this->html_break (2);
			$this->html_link_back ();
		}

		###
		# Handle Renaming Files and Directories
		###

		elseif ($renamefiles)
		{
			while (list ($from, $to) = each ($renamefiles))
			{
				if ($badchar = $this->bo->bad_chars ($to, True, True))
				{
					echo $GLOBALS['phpgw']->common->error_list (array ($this->bo->html_encode (lang('File names cannot contain "%1"', $badchar), 1)));
					continue;
				}

				if (ereg ("/", $to) || ereg ("\\\\", $to))
				{
					echo $GLOBALS['phpgw']->common->error_list (array (lang("File names cannot contain \\ or /")));
				}
				elseif (!$this->bo->vfs->mv (array (
					'from'	=> $from,
					'to'	=> $to
				))
			)
			{
				echo $GLOBALS['phpgw']->common->error_list (array (lang('Could not rename %1 to %2', $disppath.'/'.$from, $disppath.'/'.$to)));
			}
			else 
			{
				$this->html_text_summary (lang('Renamed %1 to %2', $disppath.'/'.$from, $disppath.'/'.$to));
			}
		}

		$this->html_break (2);
		$this->html_link_back ();
	}

	###
	# Handle Moving Files and Directories
	###

	elseif ($move)
	{
		while (list ($num, $file) = each ($fileman))
		{
			if ($this->bo->vfs->mv (array (
				'from'	=> $file,
				'to'	=> $todir . '/' . $file,
				'relatives'	=> array (RELATIVE_ALL, RELATIVE_NONE)
			))
		)
		{
			$moved++;
			$this->html_text_summary (lang('Moved %1 to %2', $disppath.'/'.$file, $todir.'/'.$file));
		}
		else
		{
			echo $GLOBALS['phpgw']->common->error_list (array (lang('Could not move %1 to %2', $disppath.'/'.$file, $todir.'/'.$file)));
		}
	}

	if ($moved)
	{
		$this->html_break (2);
		$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index?path='.$todir, lang('Go to %1', $todir));
	}

	$this->html_break (2);
	$this->html_link_back ();
}

###
# Handle Copying of Files and Directories
###

elseif ($copy)
{
	while (list ($num, $file) = each ($fileman))
	{
		if ($this->bo->vfs->cp (array (
			'from'	=> $file,
			'to'	=> $todir . '/' . $file,
			'relatives'	=> array (RELATIVE_ALL, RELATIVE_NONE)
		))
	)
	{
		$copied++;
		$this->html_text_summary (lang('Copied %1 to %2', $disppath.'/'.$file, $todir.'/'.$file));
	}
	else
	{
		echo $GLOBALS['phpgw']->common->error_list (array (lang('Could not copy %1 to %2', $disppath.'/'.$file, $todir.'/'.$file)));
	}
}

if ($copied)
{
	$this->html_break (2);
	$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$todir, lang('Go to %1', $todir));
}

$this->html_break (2);
$this->html_link_back ();
			}

			###
			# Handle Deleting Files and Directories
			###

			elseif ($delete)
			{
				for ($i = 0; $i != $numoffiles; $i++)
				{
					if ($fileman[$i])
					{
						if ($this->bo->vfs->delete (array ('string' => $fileman[$i])))
						{
							$this->html_text_summary (lang('Deleted %1', $disppath.'/'.$fileman[$i]), $fileinfo['size']);
						}
						else
						{
							$GLOBALS['phpgw']->common->error_list (array (lang('Could not delete %1', $disppath.'/'.$fileman[$i])));
						}
					}
				}

				$this->html_break (2);
				$this->html_link_back ();
			}

			elseif ($newdir && $createdir)
			{
				if ($this->bo->badchar = $this->bo->bad_chars ($createdir, True, True))
				{
					echo $GLOBALS['phpgw']->common->error_list (array ($this->bo->html_encode (lang('Directory names cannot contain "%1"', $badchar), 1)));
					$this->html_break (2);
					$this->html_link_back ();
					$this->html_page_close ();
				}

				if ($createdir[strlen($createdir)-1] == ' ' || $createdir[0] == ' ')
				{
					echo $GLOBALS['phpgw']->common->error_list (array (lang('Cannot create directory because it begins or ends in a space')));
					$this->html_break (2);
					$this->html_link_back ();
					$this->html_page_close ();
				}

				$ls_array = $this->bo->vfs->ls (array (
					'string'	=> $path . '/' . $createdir,
					'relatives'	=> array (RELATIVE_NONE),
					'checksubdirs'	=> False,
					'nofiles'	=> True
				)
			);

			$fileinfo = $ls_array[0];

			if ($fileinfo['name'])
			{
				if ($fileinfo['mime_type'] != 'Directory')
				{
					echo $GLOBALS['phpgw']->common->error_list (array (lang('%1 already exists as a file', $fileinfo['name'])));
					$this->html_break (2);
					$this->html_link_back ();
					$this->html_page_close ();
				}
				else
				{
					echo $GLOBALS['phpgw']->common->error_list (array (lang('Directory %1 already exists', $fileinfo['name'])));
					$this->html_break (2);
					$this->html_link_back ();
					$this->html_page_close ();
				}
			}
			else
			{
				if ($this->bo->vfs->mkdir (array ('string' => $createdir)))
				{
					$this->html_text_summary (lang('Created directory %1', $disppath.'/'.$createdir));
					$this->html_break (2);
					$this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$disppath.'/'.$createdir, lang('Go to %1', $disppath.'/'.$createdir));
				}
				else
				{
					echo $GLOBALS['phpgw']->common->error_list (array (lang('Could not create %1', $disppath.'/'.$createdir)));
				}
			}

			$this->html_break (2);
			$this->html_link_back ();
		}

		$this->html_page_close ();
	}

	function html_form_begin ($action,$args, $method = 'post', $enctype = NULL, $string = HTML_FORM_BEGIN_STRING, $return = 0)
	{
		$action = $this->bo->string_encode ($action, 1);
		$action = SEP . $action;
		//FIXME
		$text = 'action="'.$this->html_link ($action, $args,NULL, 1, 0, 1).'"';

		if ($method == NULL)
		{
			$method = 'post';
		}
		$text .= ' method="'.$method.'"';

		if ($enctype != NULL && $enctype)
		{
			$text .= ' enctype="'.$enctype.'"';
		}

		$rstring = '<form '.$text.' '.$string.'>';
		return ($this->bo->eor ($rstring, $return));
	}

	function html_form_input ($type = NULL, $name = NULL, $value = NULL, $maxlength = NULL, $size = NULL, $checked = NULL, $string = HTML_FORM_INPUT_STRING, $return = 0)
	{
		$text = ' ';
		if ($type != NULL && $type)
		{
			if ($type == 'checkbox')
			{
				$value = $this->bo->string_encode ($value, 1);
			}
			$text .= 'type="'.$type.'" ';
		}
		if ($name != NULL && $name)
		{
			$text .= 'name="'.$name.'" ';
		}
		if ($value != NULL && $value)
		{
			$text .= 'value="'.$value.'" ';
		}
		if (is_int ($maxlength) && $maxlength >= 0)
		{
			$text .= 'maxlength="'.$maxlength.'" ';
		}
		if (is_int ($size) && $size >= 0)
		{
			$text .= 'size="'.$size.'" ';
		}
		if ($checked != NULL && $checked)
		{
			$text .= 'checked ';
		}

		$rstring = '<input'.$text.$string.'>';
		return ($this->bo->eor ($rstring, $return));
	}

	function html_form_textarea ($name = NULL, $rows = NULL, $cols = NULL, $value = NULL, $string = HTML_FORM_TEXTAREA_STRING, $return = 0)
	{
		$text =' ';
		if ($name != NULL && $name)
		{
			$text .= 'name="'.$name.'" ';
		}
		if (is_int ($rows) && $rows >= 0)
		{
			$text .= 'rows="'.$rows.'" ';
		}
		if (is_int ($cols) && $cols >= 0)
		{
			$text .= 'cols="'.$cols.'" ';
		}
		$rstring = '<textarea'.$text.$string.'>'.$value.'</textarea>';
		return ($this->bo->eor ($rstring, $return));
	}

	function html_form_select_begin ($name = NULL, $return = 0)
	{
		$text = ' ';
		if ($name != NULL && $name)
		{
			$text .= 'name="'.$name.'" ';
		}
		$rstring = '<select'.$text.'>';
		return ($this->bo->eor ($rstring, $return));
	}

	function html_form_select_end ($return = 0)
	{
		$rstring = '</select>';
		return ($this->bo->eor ($rstring, $return));
	}

	function html_form_option ($value = NULL, $displayed = NULL, $selected = NULL, $return = 0)
	{
		$text = ' ';
		if ($value != NULL && $value)
		{
			$text .= ' value="'.$value.'" ';
		}
		if ($selected != NULL && $selected)
		{
			$text .= ' selected';
		}
		$rstring = '<option'.$text.'>'.$displayed.'</option>';
		return ($this->bo->eor ($rstring, $return));
	}

	function html_form_end ($return = 0)
	{
		$rstring = '</form>';
		return ($this->bo->eor ($rstring, $return));
	}

	function html_nbsp ($times = 1, $return = 0)
	{
		if ($times == NULL)
		{
			$times = 1;
		}
		for ($i = 0; $i != $times; $i++)
		{
			if ($return)
			{
				$rstring .= '&nbsp;';
			}
			else
			{
				echo '&nbsp;';
			}
		}
		if ($return)
		{
			return ($rstring);
		}
	}

	function html ($string, $times = 1, $return = 0)
	{
		for ($i = 0; $i != $times; $i++)
		{
			if ($return)
			{
				$rstring .= $string;
			}
			else
			{
				echo $string;
			}
		}
		if ($return)
		{
			return ($rstring);
		}
	}

	function html_break ($break, $string = '', $return = 0)
	{
		switch($break)
		{
			case 1:
				$break_str = '<br>';
				break;
			case 2:
				$break_str = '<p>';
				break;
			case 5:
				$break_str = '<hr>';
				break;
			}
			return ($this->bo->eor ($break_str . $string, $return));
		}

		function html_page_begin ($title = NULL, $return = 0)
		{
			//	$rstring = HTML_PAGE_BEGIN_BEFORE_TITLE . $title . HTML_PAGE_BEGIN_AFTER_TITLE;
			return ($this->bo->eor ($rstring, $return));
		}

		function html_page_body_begin ($bgcolor = HTML_PAGE_BODY_COLOR, $background = NULL, $text = NULL, $link = NULL, $vlink = NULL, $alink = NULL, $string = HTML_PAGE_BODY_STRING, $return = 0)
		{
			$text_out = ' ';
			if ($bgcolor != NULL && $bgcolor)
			{
				$text_out .= 'bgcolor="'.$bgcolor.'" ';
			}
			if ($background != NULL && $background)
			{
				$text_out .= 'background="'.$background.'" ';
			}
			if ($text != NULL && $text)
			{
				$text_out .= 'text="'.$text.'" ';
			}
			if ($link != NULL && $link)
			{
				$text_out .= 'link="'.$link.'" ';
			}
			if ($vlink != NULL && $vlink)
			{
				$text_out .= 'vlink="'.$vlink.'" ';
			}
			if ($alink != NULL && $alink)
			{
				$text_out .= 'alink="'.$alink.'" ';
			}
			//	$rstring = '<body'.$text_out.$string.'>';
			return ($this->bo->eor ($rstring, $return));
		}

		function html_page_body_end ($return = 0)
		{
			//	$rstring = '</body>';
			return ($this->bo->eor ($rstring, $return));
		}

		function html_page_end ($return = 0)
		{
			//	$rstring = '</html>';
			return ($this->bo->eor ($rstring, $return));
		}

		function html_page_close ()
		{
			//	html_page_body_end ();
			//	html_page_end ();
			$GLOBALS['phpgw']->common->phpgw_footer ();
			$GLOBALS['phpgw']->common->phpgw_exit ();
		}
		function html_text_bold ($text = NULL, $return = 0, $lang = 0)
		{
			if ($lang)
			{
				$text = $this->bo->translate ($text);
			}
			$rstring = '<b>'.$text.'</b>';	
			return ($this->bo->eor ($rstring, $return));
		}

		function html_text_underline ($text = NULL, $return = 0, $lang = 0)
		{
			if ($lang)
			{
				$text = $this->bo->translate ($text);
			}
			$rstring = '<u>'.$text.'</u>';
			return ($this->bo->eor ($rstring, $return));
		}

		function html_text_italic ($text = NULL, $return = 0, $lang = 0)
		{
			if ($lang)
			{
				$text = $this->bo->translate ($text);
			}
			$rstring = '<i>'.$text.'</i>';
			return ($this->bo->eor ($rstring, $return));
		}

		function html_text_summary ($text = NULL, $size = NULL, $return = 0, $lang = 0)
		{
			if ($lang)
			{
				$text = $this->bo->translate ($text);
			}
			$rstring = $this->html_break (1, NULL, $return);
			$rstring .= $this->html_text_bold ($text, $return);
			$rstring .= $this->html_nbsp (3, $return);
			if ($size != NULL && $size >= 0)
			$rstring .= $this->bo->borkb ($size, 1, $return);

			$rstring = $this->bo->html_encode ($rstring, 1);

			if ($return)
			{
				return ($rstring);
			}
		}

		function html_text_summary_error ($text = NULL, $text2 = NULL, $size = NULL, $return = 0, $lang = 0)
		{
			if ($lang)
			{
				$text = $this->bo->translate ($lang);
			}
			$rstring = $this->html_text_error ($text, 1, $return);

			if (($text2 != NULL && $text2) || ($size != NULL && $size))
			{
				$rstring .= $this->html_nbsp (3, $return);
			}
			if ($text2 != NULL && $text2)
			{
				$rstring .= $this->html_text_error ($text2, NULL, $return);
			}
			if ($size != NULL && $size >= 0)
			{
				$rstring .= $this->bo->borkb ($size, 1, $return);
			}

			if ($return)
			{
				return ($rstring);
			}
		}

		function html_font_set ($size = NULL, $color = NULL, $family = NULL, $return = 0)
		{
			if ($size != NULL && $size)
			$size = "size=$size";
			if ($color != NULL && $color)
			$color = "color=$color";
			if ($family != NULL && $family)
			$family = "family=$family";

			$rstring = "<font $size $color $family>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_font_end ($return = 0)
		{
			$rstring = "</font>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_text_error ($errorwas = NULL, $break = 1, $return = 0)
		{
			if ($break)
			$rstring .= $this->html_break (1, NULL, 1);

			$rstring .= $this->html_font_set (NULL, HTML_TEXT_ERROR_COLOR, NULL, 1);
			$rstring .= $this->html_text_bold ($this->html_text_italic ($errorwas, 1), 1);
			$rstring .= $this->html_font_end (1);
			return ($this->bo->eor ($rstring, $return));
		}

		function html_page_error ($errorwas = NULL, $title = "Error", $return = 0)
		{
			$rstring = $this->html_page_begin ($title, $return);
			$rstring .= $this->html_page_body_begin (HTML_PAGE_BODY_COLOR, $return);
			$rstring .= $this->html_break (2, NULL, $return);
			$rstring .= $this->html_text_error ($errorwas, $return);
			$rstring .= $this->html_page_body_end ($return);
			$rstring .= $this->html_page_end ($return);
			if (!$return)
			$this->html_page_close ();
			else
			return ($rstring);
		}

		function html_link ($href = NULL, $args = NULL ,$text = NULL, $return = 0, $encode = 1, $linkonly = 0, $target = NULL)
		{
			if ($encode)
			$href = $this->bo->string_encode ($href, 1);

			//echo $encode;

			###
			# This decodes / back to normal
			###
			$href = preg_replace ("/%2F/", "/", $href);
			$text = trim ($text);

			/* Auto-detect and don't disturb absolute links */
			if (!preg_match ("|^http(.{0,1})://|", $href))
			{
				//Only add an extra / if there isn't already one there
		
				// die(SEP);
				if (!($href[0] == SEP))
				{
					$href = SEP . $href;
				}

				/* $phpgw->link requires that the extra vars be passed separately */
				//				$link_parts = explode ("?", $href);
				$address = $GLOBALS['phpgw']->link ($href, $args);
			}
			else
			{
				$address = $href;
			}

			/* If $linkonly is set, don't add any HTML */
			if ($linkonly)
			{
				$rstring = $address;
			}
			else
			{
				if ($target)
				{
					$target = 'target='.$target;
				}

				$rstring = '<a href="'.$address.'" '.$target.'>'.$text.'</a>';
			}

			return ($this->bo->eor ($rstring, $return));
		}

		function html_link_back ($return = 0)
		{
			global $path;

			$rstring .= $this->html_link ('/index.php','menuaction=filemanager.uifilemanager.index&path='.$path, HTML_TEXT_NAVIGATION_BACK_TO_USER, 1);

			return ($this->bo->eor ($rstring, $return));
		}

		function html_table_begin ($width = NULL, $border = NULL, $cellspacing = NULL, $cellpadding = NULL, $rules = NULL, $string = HTML_TABLE_BEGIN_STRING, $return = 0)
		{
			if ($width != NULL && $width)
			$width = "width=$width";
			if (is_int ($border) && $border >= 0)
			$border = "border=$border";
			if (is_int ($cellspacing) && $cellspacing >= 0)
			$cellspacing = "cellspacing=$cellspacing";
			if (is_int ($cellpadding) && $cellpadding >= 0)
			$cellpadding = "cellpadding=$cellpadding";
			if ($rules != NULL && $rules)
			$rules = "rules=$rules";

			$rstring = "<table $width $border $cellspacing $cellpadding $rules $string>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_link_email ($address = NULL, $text = NULL, $return = 0, $encode = 1)
		{
			if ($encode)
			$href = $this->bo->string_encode ($href, 1);

			$rstring = "<a href=mailto:$address>$text</a>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_table_end ($return = 0)
		{
			$rstring = "</table>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_table_row_begin ($align = NULL, $halign = NULL, $valign = NULL, $bgcolor = NULL, $string = HTML_TABLE_ROW_BEGIN_STRING, $return = 0)
		{
			if ($align != NULL && $align)
			$align = "align=$align";
			if ($halign != NULL && $halign)
			$halign = "halign=$halign";
			if ($valign != NULL && $valign)
			$valign = "valign=$valign";
			if ($bgcolor != NULL && $bgcolor)
			$bgcolor = "bgcolor=$bgcolor";
			$rstring = "<tr $align $halign $valign $bgcolor $string>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_table_row_end ($return = 0)
		{
			$rstring = "</tr>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_table_col_begin ($align = NULL, $halign = NULL, $valign = NULL, $rowspan = NULL, $colspan = NULL, $string = HTML_TABLE_COL_BEGIN_STRING, $return = 0)
		{
			if ($align != NULL && $align)
			$align = "align=$align";
			if ($halign != NULL && $halign)
			$halign = "halign=$halign";
			if ($valign != NULL && $valign)
			$valign = "valign=$valign";
			if (is_int ($rowspan) && $rowspan >= 0)
			$rowspan = "rowspan=$rowspan";
			if (is_int ($colspan) && $colspan >= 0)
			$colspan = "colspan=$colspan";

			$rstring = "<td $align $halign $valign $rowspan $colspan $string>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_table_col_end ($return = 0)
		{
			$rstring = "</td>";
			return ($this->bo->eor ($rstring, $return));
		}


		function html_text_header ($size = 1, $string = NULL, $return = 0, $lang = 0)
		{
			$rstring = "<h$size>$string</h$size>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_align ($align = NULL, $string = HTML_ALIGN_MAIN_STRING, $return = 0)
		{
			$rstring = "<p align=$align $string>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_image ($src = NULL, $alt = NULL, $align = NULL, $border = NULL, $string = HTML_IMAGE_MAIN_STRING, $return = 0)
		{
			if ($src != NULL && $src)
			$src = "src=$src";
			if ($alt != NULL && $alt)
			$alt = "alt=\"$alt\"";
			if ($align != NULL && $align)
			$align = "align=$align";
			if (is_int ($border) && $border >= 0)
			$border = "border=$border";
			$rstring = "<img $src $alt $align $border $string>";
			return ($this->bo->eor ($rstring, $return));
		}

		function html_help_link ($help_name = NULL, $text = "[?]", $target = "_new", $return = 0)
		{
			global $settings;
			global $appname;

			if (!$settings["show_help"])
			{
				return 0;
			}

			$rstring = $this->html_link ('index.php','menuaction=filemanager.uifilemanager.index&op=help&help_name=$help_name', $text, True, 1, 0, $target);

			return ($this->bo->eor ($rstring, $return));
		}


	}

