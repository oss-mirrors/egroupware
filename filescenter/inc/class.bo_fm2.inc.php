<?php
  /***************************************************************************\
  * eGroupWare - File Center                                                  *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Vinicius Cubas Brand <viniciuscb@users.sourceforge.net>                *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  * Description: BO Class for file center                                     *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	class bo_fm2
	{
		var $so;
		var $vfs;

		var $rootdir;
		var $fakebase;
		var $appname;
		var $filesdir;
		var $hostname;
		var $userinfo = Array();
		var $homedir;
		var $sep;
		var $now;

		var $file_attributes;

		var $other_file_attributes = array(
			'custom_id',
			'custom_fields',
			'history'
		);

		function bo_fm2()
		{
			$this->vfs = CreateObject('phpgwapi.vfs');
	
			$this->so  = CreateObject('filescenter.so_fm2');

			error_reporting (4);

			### Start Configuration Options ###
			### These are automatically set in phpGW - do not edit ###

			$this->sep = SEP;
			$this->rootdir = $this->vfs->basedir;
			$this->fakebase = $this->vfs->fakebase;
			$this->appname = $GLOBALS['phpgw_info']['flags']['currentapp'];

			$this->now = date('Y-m-d');
		
			if (stristr ($this->rootdir, PHPGW_SERVER_ROOT))
			{
				$this->filesdir = substr ($this->rootdir, strlen (PHPGW_SERVER_ROOT));
			}
			else
			{
				unset ($this->filesdir);
			}

			$this->hostname = $GLOBALS['phpgw_info']['server']['webserver_url'] . $this->filesdir;

			###
			# Note that $userinfo["username"] is actually the id number, not
			# the login name
			###

			$this->userinfo['username'] = $GLOBALS['phpgw_info']['user']['account_id'];
			$this->userinfo['account_lid'] = $GLOBALS['phpgw']->accounts->id2name ($this->userinfo['username']);
			$this->userinfo['hdspace'] = 10000000000; // to settings
			$this->homedir = $this->fakebase.'/'.$this->userinfo['account_lid'];

			### End Configuration Options ###

			if (!defined ('NULL'))
			{
				define ('NULL', '');
			}

			###
			# Define the list of file attributes.  Format is "internal_name" =>
			# "Displayed name" This is used both by internally and externally
			# for things like preferences
			###

			$this->file_attributes = Array(
				'name' => lang('File Name'),
				'mime_type' => lang('MIME Type'),
				'size' => lang('Size'),
				'created' => lang('Created'),
				'modified' => lang('Modified'),
				'owner' => lang('Owner'),
				'createdby_id' => lang('Created by'),
				'modifiedby_id' => lang('Created by'),
				'modifiedby_id' => lang('Modified by'),
				'app' => lang('Application'),
				'comment' => lang('Comment'),
				'version' => lang('Version'),
				'proper_id' => lang('File ID')
			);

		}


		/*!
		 @function check_base_dir
		 @abstract Checks if base directory is available. If not, 
		           try to create it, if possible.
		*/
		function check_base_dir()
		{
			$test=$this->vfs->get_real_info(array('string' => $this->basedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
			if($test[mime_type]!='Directory')
			{
				die('Base directory does not exist, Ask adminstrator to check the global configuration.');
			}

			$test=$this->vfs->get_real_info(array('string' => $this->basedir.$this->fakebase, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
			if($test[mime_type]!='Directory')
			{

				$this->vfs->override_acl = 1;

				$this->vfs->mkdir(array(
					'string' => $this->fakebase,
					'relatives' => array(RELATIVE_NONE)
				));
				
				$this->vfs->override_acl = 0;

				//test one more time
				$test=$this->vfs->get_real_info(array('string' => $this->basedir.$this->fakebase, 'relatives' => array(RELATIVE_NONE), 'relative' => False));

				if($test[mime_type]!='Directory')
				{
					die('Fake Base directory does not exist and could not be created, please ask the adminstrator to check the global configuration.');
				}
				else
				{
					//FIXME previous version in UI used ui->messages
					$messages[]= $GLOBALS['phpgw']->common->error_list(array(
						lang('Fake Base Dir did not exist, eGroupWare created a new one.') 
					));
				}
			}

			$test=$this->vfs->get_real_info(array('string' => $this->basedir.$this->homedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
			if($test[mime_type]!='Directory')
			{
				$this->vfs->override_acl = 1;

				$this->vfs->mkdir(array(
					'string' => $this->homedir,
					'relatives' => array(RELATIVE_NONE)
				));
				
				$this->vfs->override_acl = 0;

				//test one more time
				$test=$this->vfs->get_real_info(array('string' => $this->basedir.$this->homedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));

				if($test['mime_type']!='Directory')
				{
					die('Your Home Dir does not exist and could not be created, please ask the adminstrator to check the global configuration.');
				}
				else
				{
					//FIXME previous version in UI used ui->messages
					$messages[]= $GLOBALS['phpgw']->common->error_list(array(
						lang('Your Home Dir did not exist, eGroupWare created a new one.')
					));
					// FIXME we just created a fresh home dir so we know there
					// nothing in it so we have to remove all existing content
				}
			}
		}


		/*!
		 @function get_path_content
		 @abstract Gets the path content (file listing) of $path, returning it
		           in a vfs-like format
		 @param string   $path    the path
		 @param string   $sortby  sort by field
		 @param boolean  $nosub   true => if it is a directory, retrieves only
		                          info about it, not about its content
		 @returns  vfs->ls like array
		*/
		function get_path_content($path,$sortby='',$nosub=false)
		{
			# This is the old ui->readFilesInfo() with minor modifications
			#TODO I will have to rethink file sharing and rewrite this

			if (!$sortby)
			{
				$sortby = 'name';
			}

			// start files info

			# Read in file info from database to use in the rest of the script
			# $fakebase is a special directory.  In that directory, we list the
			# user's home directory and the directories for the groups they're
			# in
			// $this->numoffiles = 0; //NUMBER OF FILES info willnot be calc now
/*			if($path == $this->fakebase)
			{
				// FIXME this test can be removed
				if(!$this->vfs->file_exists(array('string' => $this->homedir, 'relatives' => array(RELATIVE_NONE))))
				{
					$this->vfs->mkdir(array('string' => $this->homedir, 'relatives' => array(RELATIVE_NONE)));
				}

				$ls_array = $this->vfs->ls(array(
					'string' => $this->homedir,
					'relatives' => array(RELATIVE_NONE),
					'checksubdirs' => False,
					'nofiles' => True
				));
				$this->files_array[] = $ls_array[0];
//				print_r($ls_array);
				
				$this->numoffiles++;

				reset($this->readable_groups);
				while(list($num, $group_array) = each($this->readable_groups))
				{
					# If the group doesn't have access to this app, we don't show it
					if(!$this->groups_applications[$group_array['account_name']][$this->bo->appname]['enabled'])
					{
						continue;
					}


					if(!$this->vfs->file_exists(array('string' => $this->fakebase.'/'.$group_array['account_name'],'relatives'	=> array(RELATIVE_NONE))))
					{
						$this->vfs->override_acl = 1;
						$this->vfs->mkdir(array(
							'string' => $this->fakebase.'/'.$group_array['account_name'],
							'relatives' => array(RELATIVE_NONE)
						));

						// FIXME we just created a fresh group dir so we know there nothing in it so we have to remove all existing content
						
						
						$this->vfs->override_acl = 0;

						$this->vfs->set_attributes(array('string' => $this->fakebase.'/'.$group_array['account_name'],'relatives'	=> array(RELATIVE_NONE),'attributes' => array('owner_id' => $group_array['account_id'],'createdby_id' => $group_array['account_id'])));
					}

					$ls_array = $this->vfs->ls(array('string' => $this->fakebase.'/'.$group_array['account_name'],'relatives'	=> array(RELATIVE_NONE),'checksubdirs' => False,'nofiles' => True));

					$this->files_array[] = $ls_array[0];

					//$this->numoffiles++;
				}
			}
			else 
			{*/


				if ($nosub)
				{

					$ls_array = $this->vfs->ls(array(
						'string' => $path,
						'relatives'	=> array(RELATIVE_NONE),
						'checksubdirs' => False,
						'nofiles'	=> True,
						'orderby'	=> $sortby
					));

				}
				else
				{

					$ls_array = $this->vfs->ls(array(
						'string' => $path,
						'relatives'	=> array(RELATIVE_NONE),
						'checksubdirs' => False,
						'nofiles'	=> False,
						'orderby'	=> $sortby
					));

				}

			//}

			return ($ls_array) ? $ls_array : array();
/*
			if(!is_array($this->files_array))
			{
				$this->files_array = array();
			}
			// end file count
*/		

		}


		/*!
		 @function decode
		 @abstract Decodes an encoded variable received by get/post
		           Useful for decrypting file names etc
		 @returns  vfs->ls array
		*/
		function decode($var)
		{
			return stripslashes(base64_decode(urldecode(($var))));
		}

		/*!
		 @function encode
		 @abstract Encodes a variable to become a get/post var.
		           Useful for encrypting file names etc
		 @returns  vfs->ls array
		*/
		function encode($var)
		{
			return base64_encode($var);
		}


		###
		# Calculate and display B or KB
		# And yes, that first if is strange, 
		# but it does do something
		###

		function borkb ($size, $enclosed = NULL, $return = 1)
		{
			if (!$size)
			$size = 0;

			if ($enclosed)
			{
				$left = '(';
				$right = ')';
			}

			if ($size < 1024)
			$rstring = $left . $size . ' B' . $right;
			elseif ($size < 1024^2)
			$rstring = $left . round($size/1024) . ' KB' . $right;
			elseif ($size < 1024^3)
			$rstring = $left . round($size/(1024^2),1) . ' MB' . $right;
			else
			$rstring = $left . round($size/(1024^3),2) . ' GB' . $right;

			return ($this->eor ($rstring, $return));
		}

		###
		# Check for and return the first unwanted character
		###

		function bad_chars ($string, $all = True, $return = 0)
		{
			if ($all)
			{
				if (preg_match("-([\\/<>\'\"\&])-", $string, $badchars))
				$rstring = $badchars[1];
			}
			else
			{
				if (preg_match("-([\\/<>])-", $string, $badchars))
				$rstring = $badchars[1];
			}

			return trim (($this->eor ($rstring, $return)));
		}

		###
		# Match character in string using ord ().
		###

		function ord_match ($string, $charnum)
		{
			for ($i = 0; $i < strlen ($string); $i++)
			{
				$character = ord (substr ($string, $i, 1));

				if ($character == $charnum)
				{
					return True;
				}
			}

			return False;
		}

		###
		# Decide whether to echo or return.  Used by HTML functions
		###

		function eor ($rstring, $return)
		{
			if ($return)
			return ($rstring);
			else
			{
				$this->html_text ($rstring . "\n");
				return (0);
			}
		}
		
		function html_text ($string, $times = 1, $return = 0, $lang = 0)
		{
			if ($lang)
			$string = lang($string);

			if ($times == NULL)
			$times = 1;
			for ($i = 0; $i != $times; $i++)
			{
				if ($return)
				$rstring .= $string;
				else
				echo $string;
			}
			if ($return)
			return ($rstring);
		}

		###
		# URL encode a string First check if its a query string, then if its
		# just a URL, then just encodes it all Note: this is a hack.  It was
		# made to work with form actions, form values, and links only, but
		# should be able to handle any normal query string or URL
		###

		function string_encode ($string, $return = False)
		{
			//var_dump($string);
			if (preg_match ("/=(.*)(&|$)/U", $string))
			{
				$rstring = $string;

				preg_match_all ("/=(.*)(&|$)/U", $string, $matches, PREG_SET_ORDER);//FIXME matches not defined

				reset ($matches);//FIXME matches not defined

				while (list (,$match_array) = each ($matches))//FIXME matches not defined

				{
					$var_encoded = rawurlencode (base64_encode ($match_array[1]));
					$rstring = str_replace ($match_array[0], '=' . $var_encoded . $match_array[2], $rstring);
				}
			}
			elseif ($this->hostname != "" && ereg('^'.$this->hostname, $string))
//			elseif (ereg ('^'.$this->hostname, $string))
			{
				$rstring = ereg_replace ('^'.$this->hostname.'/', '', $string);
				$rstring = preg_replace ("/(.*)(\/|$)/Ue", "rawurlencode (base64_encode ('\\1')) . '\\2'", $rstring);
				$rstring = $this->hostname.'/'.$rstring;
			}
			else
			{
				$rstring = rawurlencode ($string);

				/* Terrible hack, decodes all /'s back to normal */  
				$rstring = preg_replace ("/%2F/", '/', $rstring);
			}

			return ($this->eor ($rstring, $return));
		}

		function string_decode ($string, $return = False)
		{
			$rstring = rawurldecode ($string);

			return ($this->eor ($rstring, $return));
		}

		###
		# HTML encode a string This should be used with anything in an HTML tag
		# that might contain < or >
		###

		function html_encode ($string, $return)
		{
			$rstring = htmlspecialchars ($string);

			return ($this->eor ($rstring, $return));
		}

		/*!
		 @function createdir
		 @abstract Creates a folder
		 
		 @param string $path         path (relative-vfs) of creation
		 @param string $foldername   the name of folder created
		*/
		function createdir($path,$foldername)
		{
//			if($this->newdir_x && $this->newfile_or_dir)
//			{
				$foldername = trim($foldername);
				if($badchar = $this->bad_chars($foldername, True, True))
				{
					$messages[]= $GLOBALS['phpgw']->common->error_list(array($this->bo->html_encode(lang('Directory names cannot contain "%1"', $badchar), 1)));
				}
				
				$ls_array = $this->vfs->ls(array(
					'string'	=> $path . '/' . $foldername,
					'relatives'	=> array(RELATIVE_NONE),
					'checksubdirs'	=> False,
					'nofiles'	=> True
				));

				$fileinfo = $ls_array[0];
			

				# If Directory Exists
				if(!empty($fileinfo['name']))
				{
					if($fileinfo['mime_type'] != 'Directory')
					{
						$messages[]= $GLOBALS['phpgw']->common->error_list(array(
							lang('%1 already exists as a file',
							$fileinfo['name'])
						));
					}
					else
					{
						$messages[]= $GLOBALS['phpgw']->common->error_list(array(lang('Directory %1 already exists', $fileinfo['name'])));
					}
				}
				else
				{
					if($this->vfs->mkdir(array(
							'string' => $path.$this->sep.$foldername,
							'relatives' => array (RELATIVE_NONE) )))
					{
						$messages[]=lang('Created directory %1', $path.$this->sep.$foldername);
					}
					else
					{
						$messages[]=$GLOBALS['phpgw']->common->error_list(array(lang('Could not create %1', $path.$this->sep.$foldername)));
					}
				}
//			}
		}


		/*!
		 @function delete
		 @abstract Deletes a file or a folder with all its contents (BEWARE!)
		 
		 @param array $files    array with string with full filenames
		*/
		function delete($files)
		{
			if( is_array($files))
			{
				foreach($files as $filename)
				{
					if($this->vfs->delete(array(
						'string'    => $filename,
						'relatives' => RELATIVE_NONE )))
					{
						$messages[]= lang('Deleted %1', $filename).'<br/>';
					}
					else
					{
						$messages[]=$GLOBALS['phpgw']->common->error_list(array(lang('Could not delete %1', $filename)));
					}
				}
			}
			else
			{
				// make this a javascript func for quicker respons
				$messages[]=$GLOBALS['phpgw']->common->error_list(array(lang('Please select a file to delete.')));
			}
		}

		/*!
		 @function cut
		 @abstract put files in $cut session var
		 
		 @param array $files    array with string with filenames
		*/
		function cut($files)
		{
			if (is_array($files))
			{
				$GLOBALS['phpgw']->session->appsession('cut','filescenter',$files);
				#unset copied files
				$GLOBALS['phpgw']->session->appsession('copy','filescenter',false);

			}
		}

		/*!
		 @function copy
		 @abstract put files in $copy session var
		 
		 @param array $files    array with string with filenames
		*/
		function copy($files)
		{
			if (is_array($files))
			{
				$GLOBALS['phpgw']->session->appsession('copy','filescenter',$files);
				#unset cutted files
				$GLOBALS['phpgw']->session->appsession('cut','filescenter',false);
			}
		}

		/*!
		 @function get_copied
		 @abstract returns data stored in session var
		*/
		function get_copied()
		{
			return $GLOBALS['phpgw']->session->appsession('copy','filescenter');	
		}



		/*!
		 @function get_cutted
		 @abstract returns data stored in session var
		*/
		function get_cutted()
		{
			return $GLOBALS['phpgw']->session->appsession('cut','filescenter');
		}



		/*!
		 @function paste
		 @abstract moves files from $cut session var, copies files from $copy 
		           session var, unsets $cut session var
		 
		 @param array $path    Path (folder) of pasting
		*/
		function paste($path)
		{
			$a = $this->get_path_content($path,'',True);
			
			# if path exists && is a directory
			if (!empty($a) && $a[0]['mime_type'] == 'Directory')
			{
				$cutted = $GLOBALS['phpgw']->session->appsession('cut','filescenter');
				$copied = $GLOBALS['phpgw']->session->appsession('copy','filescenter');	

				if ($cutted)
				{
					$i = 0;
					foreach ($cutted as $file)
					{
						$cut_array[$i++] = array(
							'from' => $file,
							'to'   => $path . $this->sep . basename($file)
						);
					}

					$this->move($cut_array); #FIXME error handling

					#unset cut session var
					$GLOBALS['phpgw']->session->appsession('cut','filescenter',false);
				}

				if ($copied)
				{
					$i = 0;
					foreach ($copied as $file)
					{
						$copy_array[$i++] = array(
							'from' => $file,
							'to'   => $path . $this->sep . basename($file)
						);
					}

					$this->copyTo($copy_array); #FIXME error handling
				}

			}
			else
			{
				#error: invalid path
			}
		}


		/*!
		 @function   copyTo
		 @abstract   Handles copy and paste of directories
		 @prototype  copyTo(string $from,string $to)
		             @param $from   complete relative path of file being copied
		             @param $to     complete relative destination filename
		 @prototype  copyTo(array $from)
		             @param $from   array (
		                              [0] => array('from'=>x,'to'=>y),
		                              [1] => array('from'=>z,'to'=>w) ...
		 @note       $from and $to are complete namefiles, like dir/dir/filename
		*/
		function copyTo($from,$to='')
		{
			if (is_string($from) && is_string($to))
			{
				$from = array( array( 
					'from' => $from,
					'to'   => $to
				));
			}

			while(list($num, $file) = each($from))
			{
				if($this->vfs->cp(array(
					'from'	    => $file['from'],
					'to'	    => $file['to'],
					'relatives' => array(RELATIVE_NONE,RELATIVE_NONE)
				)))
				{
					$copied++;
					$messages[] = lang('Copied %1 to %2', $file['from'],$file['to']);
				}
				else
				{
					$messages[] = $GLOBALS['phpgw']->common->error_list(array(lang('Could not copy %1 to %2', $file['from'], $file['to'])));
				}
			}
			return True; #FIXME
		}


		function get_file_history($file)
		{
			if(is_string($file)) 
			{
				$journal_array = $this->vfs->get_journal(array(
					'string'	=> $file,
					'relatives'	=> array(RELATIVE_ROOT)
				));

				return $journal_array;
			}
			return false;
		}


		/*!
		 @function   move
		 @abstract   Moves one file to another (use it to rename files too)
		 @prototype  move(string $from,string $to)
		             @param $from   complete relative path of file being copied
		             @param $to     complete relative destination file
		 @prototype  move(array $from)
		             @param $from   array (
		                              [0] => array('from'=>x,'to'=>y),
		                              [1] => array('from'=>z,'to'=>w) ...
		 @note       $from and $to are complete namefiles, like dir/dir/filename
		*/

		function move($from,$to='')
		{

			if (is_string($from) && is_string($to))
			{
				$from = array( array( 
					'from' => $from,
					'to'   => $to
				));
			}


			reset($from);
			while(list($num, $file) = each($from))
			{
				if($this->vfs->mv(array(
					'from'       => $file['from'],
					'to'         => $file['to'],
					'relatives'  => array(RELATIVE_ROOT,RELATIVE_ROOT)
				)))
				{
					$messages[] = lang('Moved %1 to %2', $file['from'],$file['to']);
				}
				else
				{
					$messages[] = $GLOBALS['phpgw']->common->error_list(array(lang('Could not move %1 to %2', $file['from'], $file['to'])));
				}
			}
			return True; #FIXME
		}

		

		/*!
		@function   view
		@abstract   Use it to view or download a file
		@param string $file  The complete path/filename
		*/
		function view($file)
		{
			if($file) //FIXME
			{

				$GLOBALS['phpgw']->browser = CreateObject('phpgwapi.browser');

				$ls_array = $this->vfs->ls(array(
					'string'	=> $file,//FIXME
					'relatives'	=> array(RELATIVE_NONE),
					'checksubdirs'	=> False,
					'nofiles'	=> True
				));

				if($ls_array[0]['mime_type'])
				{
					$mime_type = $ls_array[0]['mime_type'];
				}
				elseif($this->prefs['viewtextplain'])
				{
					$mime_type = 'text/plain';
				}
				$viewable = array('','text/plain','text/csv','text/html','text/text');

				//TRICKY Just a little fix to ensure downloading of file of
				//unknown type.
				if (!$mime_type)
				{
					$mime_type = "application/OCTET-STREAM";
				}

				if(in_array($mime_type,$viewable) && !$_GET['download'])
				{
	   				
				    header('Content-type: ' . $mime_type);
					header('Content-disposition: filename="' . basename($file) . '"');//FIXME
					Header("Pragma: public");
				}
				else
				{
					$GLOBALS['phpgw']->browser->content_header(basename($file),$mime_type);//FIXME
				}
				echo $this->vfs->read(array(
					'string'	=> $file,//FIXME
					'relatives'	=> array(RELATIVE_NONE)
				));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
		}

		# Handle File Uploads
		function fileUpload($path)
		{
			$filesn =& $GLOBALS['_FILES'];
			$otherelms =& $GLOBALS['_POST'];

			if($path != '/' && $path != $this->fakebase)
			{
				foreach($filesn as $formname => $files)
				{
					$file_number = ereg_replace('^file','',$formname);
					
					if($badchar = $this->bad_chars($files['name'], True, True))
					{
						$messages[]= $GLOBALS['phpgw']->common->error_list(array($this->html_encode(lang('File names cannot contain "%1"', $badchar), 1)));

						continue;
					}

					# Check to see if the file exists in the database, and get
					# its info at the same time
					$ls_array = $this->vfs->ls(array(
						'string'=> $path . '/' . $files['name'],
						'relatives'	=> array(RELATIVE_NONE),
						'checksubdirs'	=> False,
						'nofiles'	=> True
					));

					$fileinfo = $ls_array[0];

					if($fileinfo['name'])
					{
						if($fileinfo['mime_type'] == 'Directory')
						{
							$messages[]= $GLOBALS['phpgw']->common->error_list(array(lang('Cannot replace %1 because it is a directory', $fileinfo['name'])));
							continue;
						}
					}

					#if file not empty
					if($files['size'] > 0)
					{
						#overwriting
						if($fileinfo['name'] && $fileinfo['deleteable'] != 'N')
						{
							$tmp_arr=array(
								'string'=> $files['name'],
								'relatives'	=> array(RELATIVE_ALL),
								'attributes'	=> array(
									'owner_id' => $this->userinfo['username'],
									'modifiedby_id' => $this->userinfo['username'],
									'modified' => $this->now,
									'size' => $files['size'],
									'mime_type' => $files['type'],
									'deleteable' => 'Y',
									'comment' => stripslashes($GLOBALS['_POST']['file_comment'])
									#if overwriting, do not change.
									#TODO rethink/decide policy for that
									#'prefix' => $otherelms['prefix'.$file_number])
									
								)
							);
							$this->vfs->set_attributes($tmp_arr);

							$tmp_arr=array(
								'from'	=> $files['tmp_name'],
								'to'	=> $files['name'],
								'relatives'	=> array(RELATIVE_NONE|VFS_REAL, RELATIVE_ALL)
							);
							$this->vfs->cp($tmp_arr);

							$messages[]=lang('Replaced %1', $path.'/'.$files['name']);
						}
						else #creating a new file
						{
							$this->vfs->cp(array(
								'from'=> $files['tmp_name'],
								'to'=> $files['name'],
								'relatives'	=> array(RELATIVE_NONE|VFS_REAL, RELATIVE_ALL)
							));

							$this->vfs->set_attributes(array(
								'string'=> $files['name'],
								'relatives'	=> array(RELATIVE_ALL),
								'attributes'=> array(
									'mime_type' => $files['type'],
									'comment' => stripslashes($GLOBALS['_POST']['file_comment']),
									'prefix' => $otherelms['prefix'.$file_number],
									'ptype' => $otherelms['type'.$file_number]
								)
							));

							$messages[]=lang('Created %1,%2', $path.'/'.$files['name'], $files['size']);
						}
					}
					elseif($files['name']) #file uploaded is empty but exists
					{
						$this->vfs->touch(array(
							'string'=> $files['name'],
							'relatives'	=> array(RELATIVE_ALL)
						));

						$this->vfs->set_attributes(array(
							'string'=> $files['name'],
							'relatives'	=> array(RELATIVE_ALL),
							'attributes'=> array(
								'mime_type' => $files['type'],
								'comment' => stripslashes($GLOBALS['_POST']['file_comment']),
								'prefix' => $otherelms['prefix'.$file_number],
								'ptype' => $otherelms['type'.$file_number]
							)
						));

						$messages[]=lang('Created %1,%2', $path.'/'.$files['name'], $files['size']);
					}
				}
			}
		}


		/*!
		@function   fileCompress
		@abstract   Handle file compression
		
		@param array   $files      List of files that go in archive
		@param string  $archname   Name of archive
		@param string  $type       Compression type
		@param string  $archprefix Prefix of the file ID of archive
		@param string  $archptype  Type in File ID prefix
		@param string  $archpath   Path where archive will be placed

		@author Vinicius Cubas Brand
		*/
		function fileCompress($files,$archname,$type,$archpath,$archprefix=false,$archptype=false)
		{
			$numfiles = count($files);

			//count one more, because dest is RELATIVE_ROOT too
			for ($i = 0; $i<=$numfiles; $i++)
			{
				$rel_array[] = RELATIVE_ROOT;
			}

			if($badchar = $this->bad_chars($archname, True, True))
			{
				$messages[]= $GLOBALS['phpgw']->common->error_list(array($this->html_encode(lang('File names cannot contain "%1"', $badchar), 1)));
				return false;
			}
		
			$this->vfs->compress(array(
				'files' => $files,
				'name'  => $archpath.'/'.$archname,
				'type'  => $type,
				'relatives' => $rel_array,
				'prefix' => $archprefix,
				'ptype' => $archptype
				
				));

		}


		/*!
		@function   fileDecompress
		@abstract   Handle file decompression
		
		@param string  $archname   Name of archive
		@param string  $archprefix Prefix of the file ID of archive
		@param string  $archpath   Path where archive will be placed

		@author Vinicius Cubas Brand
		*/
		function fileDecompress($archname,$destpath,$filesprefix=false,$filesptype=false)
		{
			$this->vfs->extract(array(
				'name'      => $archname,
				'dest'      => $destpath,
				'relatives' => array(RELATIVE_ROOT,RELATIVE_ROOT)
			));
		}

		/*!
		@function   update_custom
		@abstract   Update custom fields for a given file
		
		@param string  $filename, full filename,with relativity to RELATIVE_ROOT
		@param array   $custom_array

		@author Vinicius Cubas Brand
		*/
		function update_custom($filename,$custom_array)
		{
			$file_list = $this->vfs->ls(array(
				'string' => $filename,
				'relatives' => array(RELATIVE_ROOT),
				'nofiles' => true
			));

			$this->vfs->vfs_customfields->store_fields(array(
				$file_list[0]['file_id'] => $custom_array
			));

		}


		/*!
		@function   create_archive
		@abstract   Compresses files
		@param array $files  Array values are the complete relative file names
							 of files being compressed
		@param string $archname   Complete relative file name of the archive,
		                          without extension (this will be put by the 
		                          program)
		@param string $ctype      type of compression: ("bzip"/"gzip"/"zip")
		@author Vinicius Cubas Brand
		*/
		function create_archive($files,$archname,$ctype)
		{
			if (!is_array($files))
			{
				$messages[] = lang('$files format not recognized in bo->create_archive()');
				return;
			}

			if($badchar = $this->bad_chars($archname, True, True))
			{
				$messages[]= $GLOBALS['phpgw']->common->error_list(array($this->html_encode(lang('Archive name cannot contain "%1"', $badchar), 1)));

				return;
			}

/*
			if ($this->bad_chars($archname))
			if (isset($_POST['archive_files']))
			{
			  $name = trim($_POST['name']);
			  if ($name == '')
			  {
			$feedback = '<p class="Error">'.$error_missing_field.'</p>';
			$task = 'create_archive';
			  }else
			  {

			switch ($_POST['compression_type'])
			{
			  case 'zip':
				if (get_extension($name) != $_POST['compression_type'])
				{
				  $name .= '.'.$_POST['compression_type'];
				}
				require($GO_CONFIG->class_path.'pclzip.class.inc');
				$zip = new PclZip($path.$GO_CONFIG->slash.$name);
				$zip->create($_POST['archive_files'], PCLZIP_OPT_REMOVE_PATH, $path);
				break;

			  default:
				if (get_extension($name) != $_POST['compression_type'])
				{
				  $name .= '.tar.'.$_POST['compression_type'];
				}
				require($GO_CONFIG->class_path.'pearTar.class.inc');
				$tar = new Archive_Tar($path.$GO_CONFIG->slash.$name, $_POST['compression_type']);

				if (!$tar->createModify($_POST['archive_files'], '', $path.$GO_CONFIG->slash))
				{
				  $feedback = '<p class="Error">'.$fb_failed_to_create.'</p>';
				  $task = 'create_archive';
				}
				break;
			}
			  }
			}
			*/
		}
//END FUNCTION



		/*!
		@function   get_dir_tree
		@abstract   Returns tree with folders of directory
					Use it carefully If the file repository is a DAV type, it
					will last a lot of time to get all dir tree
		@param string $dir  the complete relative dir
		@param bool $supress_path   if supress_path == True, then it will
		                            not show folders above $dir (for example,
		                            supress /home/admin if path=/home/admin)
		@author Vinicius Cubas Brand
		*/
		function get_dir_tree($dir,$supress_path=True)
		{

			if (!empty($dir))
			{

				$ls_array = $this->vfs->ls(array(
					'string'       => $dir,
					'relatives'    => array(RELATIVE_NONE),
					'checksubdirs' => True,
					'orderby'      => 'name',
					'nofiles'	   => False,
					'mime_type'    => 'Directory'
				));

				if ($ls_array)
				{

					#generates a temp array with dir names
					foreach ($ls_array as $num => $file)
					{
						$dirlist[] = $file['directory'].$this->sep.$file['name'];
					}
					$ftree = array();
					foreach ($dirlist as $val)
					{
						if ($supress_path)
						{
							$val = ereg_replace("^$dir",'',$val);
						}
						$val = ereg_replace('^/','',$val);
						$val = ereg_replace('/$','',$val);
						$val = explode('/',$val);
						$this->add_dir_tree($ftree,$val);
					}
					
					return $ftree;
				}
				else
				{
					return false;
				}
			}
		}

		/*!
		@function   add_dir_tree
		@abstract   Used only by get_dir_tree. Recursive.
		@author     Vinicius Cubas Brand
		*/
		function add_dir_tree(&$tree, $dir)
		{
			if (empty($dir))
			{
				return;
			}

			$first = array_shift($dir);

			if (is_int($tree))
			{
				$tree = array ($first => 1);
			}

			if ($tree[$first])
			{
				$this->add_dir_tree($tree[$first],$dir);
			}
			else
			{
				$tree[$first] = 1;
				$this->add_dir_tree($tree[$first],$dir);
			}
		}

		function search($keyword,$account_id)
		{

			$res = $this->vfs->search($keyword);
	
            $result = array();
            //verify file permissions for each file
            foreach ($res as $file_id)
            {
	/*			will find another way to do this
				print_r( $this->vfs->vfs_sharing->get_file_permissions($account_id,$file_id));	

                if ($this->vfs->vfs_sharing->get_file_permissions($account_id,$file_id) & PHPGW_ACL_READ)
                {*/
                    $result[$file_id] = $this->vfs->id2name($file_id);
//                }

            }

            return $result;

		}

		function set_current_path(&$path,$must_be_a_dir=False)
		{
			if(!$path)
			{
				$path = $this->vfs->pwd();

				if(!$path || $this->vfs->pwd(array('full' => False)) == '')
				{
					$path = $this->homedir; 
				}
			}

			if ($must_be_a_dir)
			{
				$file_info = $this->vfs->ls(array(
					'relatives' => array(RELATIVE_NONE),
					'nofiles' => true,
					'string' => $path
				));

				if ($file_info[0]['mime_type'] != 'Directory')
				{
					$path = dirname($path);
				}
				
			}

			$this->vfs->cd(array('string' => False, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
			$this->vfs->cd(array('string' => $path, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
		}

		function exist_records_vfs2()
		{
			$db =& $GLOBALS['phpgw']->db;
			
			$db->select('phpgw_vfs2_files','*','',__LINE__,__FILE__);
			if ($db->num_rows() <= 1)
			{
				return false;
			}
			return true;
		}
		
		function exist_records_vfs()
		{
			$db =& $GLOBALS['phpgw']->db;
			
			$db->select('phpgw_vfs','*','',__LINE__,__FILE__);
			if ($db->num_rows() <= 2)
			{
				return false;
			}
			return true;
		}

		function import_vfs2()
		{
			if (method_exists($this->vfs,'import_vfs'))
			{
				$this->vfs->import_vfs();
			}
		}

	}


?>
