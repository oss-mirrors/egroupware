<?php
	/***************************************************************************\
	* eGroupWare - Files Center                                                 *
	* http://www.egroupware.org                                                 *
	* Written by:                                                               *
	*  - Vinicius Cubas Brand <viniciuscb@users.sourceforge.net>                *
	*  sponsored by Think.e - http://www.think-e.com.br                         *
	* ------------------------------------------------------------------------- *
	* Description: BO Class for file center                                     *
	* ------------------------------------------------------------------------- *
	*  This program is free software; you can redistribute it and/or modify it  *
	*  under the terms of the GNU General Public License as published by the    *
	*  Free Software Foundation; either version 2 of the License, or (at your   *
	*  option) any later version.                                               *
	\***************************************************************************/

		class tree_node
		{
				var $path;
				var $name;
				var $contents;
				var $icon;
		}

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
				var $publicdir = '/public';
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
			$this->vfs =& CreateObject('phpgwapi.vfs');
	
			$this->so  =& CreateObject('filescenter.so_fm2');

			error_reporting (4);

			### Start Configuration Options ###
			### These are automatically set in phpGW - do not edit ###

			$this->sep = '/'; //SEP
			$this->rootdir = $this->vfs->basedir;
			$this->fakebase = $this->vfs->fakebase;
			$this->appname = $GLOBALS['egw_info']['flags']['currentapp'];

			$this->now = date('Y-m-d');
		
			if (stristr ($this->rootdir, EGW_SERVER_ROOT))
			{
				$this->filesdir = substr ($this->rootdir, strlen (EGW_SERVER_ROOT));
			}
			else
			{
				unset ($this->filesdir);
			}

			$this->hostname = $GLOBALS['egw_info']['server']['webserver_url'] . $this->filesdir;

			###
			# Note that $userinfo["username"] is actually the id number, not
			# the login name
			###

			if (!is_object($GLOBALS['egw']->accounts))
			{
				$GLOBALS['egw']->accounts =& CreateObject('phpgwapi.accounts');
			}

			$this->userinfo['username'] = $GLOBALS['egw_info']['user']['account_id'];
			$this->userinfo['account_lid'] = $GLOBALS['egw']->accounts->id2name ($this->userinfo['username']);
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
				'comment' => lang('Comment'),
				'mime_type' => lang('MIME Type'),
				'size' => lang('Size'),
				'created' => lang('Created'),
				'modified' => lang('Modified'),
				'owner' => lang('Owner'),
				'createdby_id' => lang('Created by'),
				'modifiedby_id' => lang('Created by'),
				'modifiedby_id' => lang('Modified by'),
				'app' => lang('Application'),
				'version' => lang('Version'),
				'proper_id' => lang('File ID')
			);

		}


		/**
		  * Checks if base directory is available. If not, 
		  *
							  * try to create it, if possible.
		 */
		function check_base_dir()
		{
			$test=$this->vfs->get_real_info(array('string' => $this->basedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
			if($test[mime_type]!='Directory')
			{
				return false;
				//die('Base directory does not exist, Ask adminstrator to check the global configuration.');
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
					return false;
					//die('Fake Base directory does not exist and could not be created, please ask the adminstrator to check the global configuration.');
				}
				else
				{
					//FIXME previous version in UI used ui->messages
					$messages[]= $GLOBALS['egw']->common->error_list(array(
						lang('Fake Base Dir did not exist, eGroupWare created a new one.') 
					));
				}
			}

						//Home dir
			$test=$this->vfs->get_real_info(array('string' => $this->basedir.$this->homedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
			if($test[mime_type]!='Directory')
			{
				$this->vfs->override_acl = 1;

				$this->vfs->mkdir(array(
					'string' => $this->homedir,
					'relatives' => array(RELATIVE_NONE)
				));

								//creating templates dir inside home dir
				$this->vfs->mkdir(array(
					'string' => $this->homedir.'/templates',
					'relatives' => array(RELATIVE_NONE)
				));
				
				$this->vfs->override_acl = 0;

				//test one more time
				$test=$this->vfs->get_real_info(array('string' => $this->basedir.$this->homedir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));

				if($test['mime_type']!='Directory')
				{
					return false;
					//die('Your Home Dir does not exist and could not be created, please ask the adminstrator to check the global configuration.');
					//die('Trying to access your home dir: '.$this->basedir.$this->homedir.'. Your Home Dir does not exist and could not be created, please ask the adminstrator to check the global configuration. '."\n\nresponse:".print_r($test,true));
				}
				else
				{
					// FIXME we just created a fresh home dir so we know there
					// nothing in it so we have to remove all existing content
				}
			}

						//Public dir
			$test=$this->vfs->get_real_info(array('string' => $this->basedir.$this->publicdir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
			if($test[mime_type]!='Directory')
			{
				$override_acl = $this->vfs->override_acl;

				$this->vfs->override_acl = 1;

				$this->vfs->mkdir(array(
					'string' => $this->publicdir,
					'relatives' => array(RELATIVE_NONE)
				));

				$this->vfs->set_sharing(array(
					'string' => $this->publicdir,
					'relatives' => array(RELATIVE_NONE),
					'permissions' => array(0 => EGW_ACL_READ | EGW_ACL_ADD | EGW_ACL_EDIT | EGW_ACL_DELETE)
					));

								//creating templates dir in public folder
				$this->vfs->mkdir(array(
					'string' => $this->publicdir.'/templates',
					'relatives' => array(RELATIVE_NONE)
				));

				//restoring original override_acl
				$this->vfs->override_acl = $override_acl;

				//test one more time
				$test=$this->vfs->get_real_info(array('string' => $this->basedir.$this->publicdir, 'relatives' => array(RELATIVE_NONE), 'relative' => False));
				if($test['mime_type']!='Directory')
				{
					return false;
					//die('Your Public Dir does not exist and could not be created, please ask the adminstrator to check the global configuration.');
				}
			}

			return true;
		}


		/**
		  * Gets the path content (file listing) of $path, returning it
		  *
							  * in a vfs-like format
		  * @param string   $path    the path
		  * @param string   $sortby  sort by field
		  * @param boolean  $nosub   true => if it is a directory, retrieves only
															info about it, not about its content
		  *  vfs->ls like array
		 */
		function get_path_content($path,$sortby='',$nosub=false)
		{
			if (!$sortby)
			{
				$sortby = 'name';
			}

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
			return ($ls_array) ? $ls_array : array();
		}


		/**
		  * Decodes an encoded variable received by get/post
		  *
							  * Useful for decrypting file names etc
		  *  vfs->ls array
		 */
		function decode($var)
		{
			return stripslashes(base64_decode(urldecode(($var))));
		}

		/**
		  * Encodes a variable to become a get/post var.
		  *
							  * Useful for encrypting file names etc
		  *  vfs->ls array
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

		/**
		  * Creates a folder
		  *
		  * 
		  * @param string $path         path (relative-vfs) of creation
		  * @param string $foldername   the name of folder created
		 */
		function createdir($path,$foldername)
		{
//			if($this->newdir_x && $this->newfile_or_dir)
//			{
				$foldername = trim($foldername);
				if($badchar = $this->bad_chars($foldername, True, True))
				{
//					$messages[]= $GLOBALS['egw']->common->error_list(array($this->bo->html_encode(lang('Directory names cannot contain "%1"', $badchar), 1)));
					return lang('Directory names cannot contain "%1"', $badchar);
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
						/*$messages[]= $GLOBALS['egw']->common->error_list(array(
							lang('%1 already exists as a file',
							$fileinfo['name'])
						));
						return $messages;*/

						return lang('%1 already exists as a file',$fileinfo['name']);
					}
					else
					{
						/*$messages[]= $GLOBALS['egw']->common->error_list(array(lang('Directory %1 already exists', $fileinfo['name'])));
						return $messages;*/
						return lang('Directory %1 already exists', $fileinfo['name']);
					}
				}
				else
				{
					if($this->vfs->mkdir(array(
							'string' => $path.'/'.$foldername,
							'relatives' => array (RELATIVE_NONE) )))
					{
						//$messages[]=lang('Created directory %1', $path.$this->sep.$foldername);
						return true;
					}
					else
					{
						/*$messages[]=$GLOBALS['egw']->common->error_list(array(lang('Could not create %1', $path.$this->sep.$foldername)));
						return $messages;*/
							return lang('Could not create %1', $path.'/'.$foldername);
					}
				}
//			}
		}


		/**
		  * Deletes a file or a folder with all its contents (BEWARE!)
		  *
		  * 
		  * @param array $files    array with string with full filenames(filescenter) or file identificator(other appl)
		  * @param string $app     application name
		 */
		function delete($files,$app='filescenter')
		{
			$deleted = false;
			$messages = array();
			if( is_array($files))
			{
				foreach($files as $filename)
				{
					$filename = $this->get_app_path($filename,$app);
				
					if($this->vfs->delete(array(
						'string'    => $filename,
						'relatives' => RELATIVE_NONE )))
					{
						$deleted = true;
						//$messages[]= lang('Deleted %1', $filename).'<br/>';
					}
					else
					{
						//$messages[]=$GLOBALS['egw']->common->error_list(array(lang('Could not delete %1', $filename)));
						$messages[] = lang('Could not delete %1', $filename);
					}
				}
			}

			if (!$deleted)
			{
				return false;
			}
			else if ($messages)
			{
				return $messages;
			}
			else
			{
				return true;
			}
		}

		/**
		  * put files in $cut session var
		  *
		  * 
		  * @param array $files    array with string with filenames
		 */
		function cut($files)
		{
			if (is_array($files))
			{
				$GLOBALS['egw']->session->appsession('cut','filescenter',$files);
				#unset copied files
				$GLOBALS['egw']->session->appsession('copy','filescenter',false);

			}
			return true;
		}

		/**
		  * put files in $copy session var
		  *
		  * 
		  * @param array $files    array with string with filenames
		 */
		function copy($files)
		{
			if (is_array($files))
			{
				$GLOBALS['egw']->session->appsession('copy','filescenter',$files);
				#unset cutted files
				$GLOBALS['egw']->session->appsession('cut','filescenter',false);
			}
			return true;
		}

		/**
		  * returns data stored in session var
		  *
		 */
		function get_copied()
		{
			return $GLOBALS['egw']->session->appsession('copy','filescenter');	
		}



		/**
		  * returns data stored in session var
		  *
		 */
		function get_cutted()
		{
			return $GLOBALS['egw']->session->appsession('cut','filescenter');
		}


		/**
		  * moves files from $cut session var, copies files from $copy 
		  *
							  * session var, unsets $cut session var
		  * 
		  * @param array $path    Path (folder) of pasting
		 */
		function paste($path)
		{
			$a = $this->get_path_content($path,'',True);
			
			# if path exists && is a directory
			if (!empty($a) && $a[0]['mime_type'] == 'Directory')
			{
				$cutted = $GLOBALS['egw']->session->appsession('cut','filescenter');
				$copied = $GLOBALS['egw']->session->appsession('copy','filescenter');	

				if ($cutted)
				{
					$i = 0;
					foreach ($cutted as $file)
					{
						$cut_array[$i++] = array(
							'from' => $file,
							'to'   => $path . '/' . basename($file)
						);
					}

					$this->move($cut_array); #FIXME error handling

					#unset cut session var
					$GLOBALS['egw']->session->appsession('cut','filescenter',false);
				}

				if ($copied)
				{
					$i = 0;
					foreach ($copied as $file)
					{
						$copy_array[$i++] = array(
							'from' => $file,
							'to'   => $path . '/' . basename($file)
						);
					}

					$this->copyTo($copy_array); #FIXME error handling
				}

			}
			else
			{
				return false;
				#error: invalid path
			}
			return true;
		}


		/**
		  *   Handles copy and paste of directories
		  *
		  *  copyTo(string $from,string $to)
								  * @param $from   complete relative path of file being copied
								  * @param $to     complete relative destination filename
		  *  copyTo(array $from)
								  * @param $from   array (
																	[0] => array('from'=>x,'to'=>y),
																	[1] => array('from'=>z,'to'=>w) ...
		  *       $from and $to are complete namefiles, like dir/dir/filename
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
					$messages[] = $GLOBALS['egw']->common->error_list(array(lang('Could not copy %1 to %2', $file['from'], $file['to'])));
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


		/**
		  *   Moves one file to another (use it to rename files too)
		  *
		  *  move(string $from,string $to)
								  * @param $from   complete relative path of file being copied
								  * @param $to     complete relative destination file
		  *  move(array $from)
								  * @param $from   array (
																	[0] => array('from'=>x,'to'=>y),
																	[1] => array('from'=>z,'to'=>w) ...
		  *       $from and $to are complete namefiles, like dir/dir/filename
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
					$messages[] = $GLOBALS['egw']->common->error_list(array(lang('Could not move %1 to %2', $file['from'], $file['to'])));
				}
			}
			return True; #FIXME
		}

		

		/**
		 *   Use it to view or download a file
		 *
		 * @param string $file  The complete path/filename
		 */
		function view($file)
		{
			if($file) //FIXME
			{

				$GLOBALS['egw']->browser =& CreateObject('phpgwapi.browser');

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

				$contents = $this->vfs->read(array(
					'string'	=> $file,//FIXME
					'relatives'	=> array(RELATIVE_NONE)
				));
				
				if(in_array($mime_type,$viewable) && !$_GET['download'])
				{
						header('Content-type: ' . $mime_type);
					header('Content-disposition: filename="' . basename($file) . '"');//FIXME
					header('Content-Length: '.strlen($contents));
					Header("Pragma: public");
				}
				else
				{
					$GLOBALS['egw']->browser->content_header(basename($file),$mime_type,strlen($contents));//FIXME
				}
				echo $contents;
				$GLOBALS['egw']->common->egw_exit();
			}
		}

		# Handle File Uploads
		function fileUpload($path,$filesn,$otherelms=array(),$app='filescenter')
		{
//			$filesn =& $GLOBALS['_FILES'];
//			$otherelms =& $GLOBALS['_POST'];

			$path = $this->get_app_path($path,$app);

			if($path != '/' && $path != $this->fakebase)
			{
				foreach($filesn as $formname => $files)
				{
					$file_number = ereg_replace('^file','',$formname);
					
					if($badchar = $this->bad_chars($files['name'], True, True))
					{
						$messages[]= $GLOBALS['egw']->common->error_list(array($this->html_encode(lang('File names cannot contain "%1"', $badchar), 1)));

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
							$messages[]= $GLOBALS['egw']->common->error_list(array(lang('Cannot replace %1 because it is a directory', $fileinfo['name'])));
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
									'prefix' => $otherelms['prefix'.$file_number] ? $otherelms['prefix'.$file_number] : $GLOBALS['egw_info']['user']['account_lid'],
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
								'prefix' => $otherelms['prefix'.$file_number] ? $otherelms['prefix'.$file_number] : $GLOBALS['egw_info']['user']['account_lid'],
								'ptype' => $otherelms['type'.$file_number]
							)
						));

						$messages[]=lang('Created %1,%2', $path.'/'.$files['name'], $files['size']);
					}
				}
			}

			//treat custom fields
			foreach($otherelms as $key => $val)
			{
				if (ereg('^fcfile(.*)$',$key,$matches))
				{
					$this->vfs->cp(array(
						'from' => $val,
						'to' => $path.'/'.basename($val),
						'relatives' => array(RELATIVE_ROOT,RELATIVE_ROOT)
						));

					$this->vfs->set_attributes(array(
						'string' => $path.'/'.basename($val),
						'relatives' => array(RELATIVE_ALL),
						'attributes' => array(
							'prefix' => ($otherelms['prefix'.$matches[1]]) ? $otherelms['prefix'.$matches[1]] : $GLOBALS['egw_info']['user']['account_lid'],
							'ptype' => ($otherelms['type'.$matches[1]]) 
							)
						));
				}
			}
		}


		/**
		 *   Saves a file in a specified dir in virtual path
		 *
		 * @param array $params  An array, consisting of the following indexes:
		 * 		 * 		 * 'string' => the destination path+filename in virtual file system
		 * 		 * 		 * 'mime_type' => the mime type of the file, as defined in RFC. If not specified the system will try to find one.
		 * 		 * 		 * 'prefix' => the file prefix id, optional, can be not specified or be some string
		 * 		 * 		 * 'comment' => file comment
		 * 		 * 		 * 'content' => the file content
		 * @author viniciuscb
		 */
		function save_file($params)
		{

						$filename = $params['string'];
						$filename = array_pop(explode('/',$params['string']));
		
			if($params['string'] != '/' && $params['string'] != $this->fakebase)
			{
								if($badchar = $this->bad_chars($filename, True, True))
								{
										$this->messages[]= $GLOBALS['egw']->common->error_list(array($this->html_encode(lang('File names cannot contain "%1"', $badchar), 1)));
										return false;
								}

								# Check to see if the file exists in the database, and get
								# its info at the same time
								$ls_array = $this->vfs->ls(array(
										'string'=> $params['string'],
										'relatives'	=> array(RELATIVE_ROOT),
										'checksubdirs'	=> False,
										'nofiles'	=> True
								));

								$fileinfo = $ls_array[0];

								if($fileinfo['name'])
								{
										if($fileinfo['mime_type'] == 'Directory')
										{
												$this->messages[]= $GLOBALS['egw']->common->error_list(array(lang('Cannot replace %1 because it is a directory', $fileinfo['name'])));
												return false;
										}
								}

								if($this->vfs->write(array(
										'string' => $params['string'],
										'relatives' => array(RELATIVE_ROOT),
										'content' => &$params['content']
										)))
								{

										$attr = array(
							'owner_id' => $this->userinfo['username'],
						'modifiedby_id' => $this->userinfo['username'],
						'modified' => $this->now,
						'size' => strlen($params['content']),
						'deleteable' => 'Y'
										);

										if ($params['mime_type'])
										{
												$attr['mime_type'] = $params['mime_type'];
										}

										if ($params['comment'])
										{
												$attr['comment'] = $params['comment'];
										}

										if ($params['prefix'])
										{
												$attr['prefix'] = $params['prefix'];
										}

										if ($params['ptype'])
										{
												$attr['ptype'] = $params['ptype'];
										}

										$this->vfs->set_attributes(array(
												'string'=> $params['string'],
												'relatives'	=> array(RELATIVE_ROOT),
												'attributes'=> $attr
											 )
										);

										$this->messages[]=lang('Created %1', $params['string']);

								}
								else
								{
										$this->messages[]=lang('Cannot write file $1',$params['string']);
										return false;
								}
			}
						else
						{
								$this->messages[]=lang('No permission to write file $1',$params['string']);
								return false;
						}
						return true;
		}


		/**
		 *   Handle file compression
		 *
		 * 
		 * @param array   $files      List of files that go in archive
		 * @param string  $archname   Name of archive
		 * @param string  $type       Compression type
		 * @param string  $archprefix Prefix of the file ID of archive
		 * @param string  $archptype  Type in File ID prefix
		 * @param string  $archpath   Path where archive will be placed

		 * @author Vinicius Cubas Brand
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
				$messages[]= $GLOBALS['egw']->common->error_list(array($this->html_encode(lang('File names cannot contain "%1"', $badchar), 1)));
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


		/**
		 *   Handle file decompression
		 *
		 * 
		 * @param string  $archname   Name of archive
		 * @param string  $archprefix Prefix of the file ID of archive
		 * @param string  $archpath   Path where archive will be placed

		 * @author Vinicius Cubas Brand
		 */
		function fileDecompress($archname,$destpath,$filesprefix=false,$filesptype=false)
		{
			if ($this->vfs->extract_support)
			{
				return $this->vfs->extract(array(
					'name'      => $archname,
					'dest'      => $destpath,
					'relatives' => array(RELATIVE_ROOT,RELATIVE_ROOT)
				));
			}
			else
			{
				return false;
			}
		}

		/**
		 *   Update custom fields for a given file
		 *
		 * 
		 * @param string  $filename, full filename,with relativity to RELATIVE_ROOT
		 * @param array   $custom_array

		 * @author Vinicius Cubas Brand
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


		/**
		 *   Compresses files
		 *
		 * @param array $files  Array values are the complete relative file names
		 * 		 * 		 * 	 of files being compressed
		 * @param string $archname   Complete relative file name of the archive,
		 * 		 * 		 * 		 * 		 * 		 * 		 * 	without extension (this will be put by the 
		 * 		 * 		 * 		 * 		 * 		 * 		 * 	program)
		 * @param string $ctype      type of compression: ("bzip"/"gzip"/"zip")
		 * @author Vinicius Cubas Brand
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
				$messages[]= $GLOBALS['egw']->common->error_list(array($this->html_encode(lang('Archive name cannot contain "%1"', $badchar), 1)));

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
				$zip =& new PclZip($path.$GO_CONFIG->slash.$name);
				$zip->create($_POST['archive_files'], PCLZIP_OPT_REMOVE_PATH, $path);
				break;

				default:
				if (get_extension($name) != $_POST['compression_type'])
				{
					$name .= '.tar.'.$_POST['compression_type'];
				}
				require($GO_CONFIG->class_path.'pearTar.class.inc');
				$tar =& new Archive_Tar($path.$GO_CONFIG->slash.$name, $_POST['compression_type']);

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

		/**
		 *   Returns tree with all shared folders inside it
		 *
		 * 		 * 	Use it carefully If the file repository is a DAV type, it
		 * 		 * 	will last a lot of time to get all dir tree
		 * @author Vinicius Cubas Brand
		 */
		function get_shared_tree()
		{
						$tree = array(
								'root' => new tree_node
								);
						$tree['root']->name = lang('Shared');
						$tree['root']->path = '';
						$tree['root']->contents = array();

			$vfs_sharing =& CreateObject('phpgwapi.vfs_sharing');

			$other_shares = $vfs_sharing->get_shares($GLOBALS['egw_info']['user']['account_id'],false,EGW_ACL_READ,array($this->vfs->get_appfiles_root(),$this->publicdir));

						if ($other_shares)
						{
								foreach ($other_shares as $share)
								{
										$GLOBALS['egw']->accounts->get_account_name($share['owner_id'],$lid,$fname,$lname);
										if (!array_key_exists($lid,$tree['root']->contents))
										{
												$tree['root']->contents[$lid] =& new tree_node;
										
												$tree['root']->contents[$lid]->name = $fname.' '.$lname;
												$tree['root']->contents[$lid]->path = '';
												$tree['root']->contents[$lid]->contents = array();
										}

										$user_node =& $tree['root']->contents[$lid];

										if (!$subtree = $this->get_dir_tree($share['directory'].'/'.$share['name'],$share['name']))
										{
												$user_node->contents[] =& new tree_node();
												$j =& $user_node->contents[count($user_node->contents)-1];
												$j->name = $share['name'];
												$j->path = $share['directory'].'/'.$share['name'];
												$j->contents = array();
										}
										else
										{
												$user_node->contents[] =& $subtree['root'];
										}
								}
						}
						return $tree;
		}


		/**
		 *   Returns tree with folders of directory
		 *
		 * @param string $dir  the complete relative dir
		 * @param bool $supress_path   if supress_path == True, then it will
		 * 		 * 		 * 		 * 		 * 		 * 		 * 		 * not show folders above $dir (for example,
		 * 		 * 		 * 		 * 		 * 		 * 		 * 		 * supress /home/admin if path=/home/admin)
		 * @author Vinicius Cubas Brand
		 */
		function get_dir_tree($dir,$tree_name='Home',$supress_path=True,$extra_nodes=array())
		{
			if (!empty($dir))
			{
								$ftree = array(
										'root' => new tree_node
										);
								$ftree['root']->path = $dir;
								$ftree['root']->name = $tree_name;
								$ftree['root']->contents = 1;

				$ls_array = $this->vfs->ls(array(
					'string'       => $dir,
					'relatives'    => array(RELATIVE_ROOT),
					'checksubdirs' => True,
					'nofiles'	   => False,
					'mime_type'    => 'Directory'
				));

				if ($ls_array)
				{
					#generates a temp array with dir names
					foreach ($ls_array as $num => $file)
					{
						$dirlist[] = $file['directory'].'/'.$file['name'];
					}
					foreach ($dirlist as $val_orig)
					{
												$val = $val_orig;
						if ($supress_path)
						{
							$val = ereg_replace("^$dir",'',$val_orig);
						}
						$val = ereg_replace('^/','',$val);
						$val = ereg_replace('/$','',$val);
						$val = explode('/',$val);
						$this->add_dir_tree($ftree['root']->contents,$val,$val_orig);
					}
				}
			}
						return $ftree;
		}

		/**
		 *   Used only by get_dir_tree. Recursive.
		 *
		 * @author     Vinicius Cubas Brand
		 */
		function add_dir_tree(&$tree, $dir, $path)
		{
			if (empty($dir))
			{
				return;
			}

			$first = array_shift($dir);

			if (is_int($tree))
			{
								$tree = array(
										$first => new tree_node
										);
										
								$tree[$first]->path = ereg_replace('/'.implode('/',$dir).'$','',$path);
								$tree[$first]->name = $first;
								$tree[$first]->contents = 1;
//				$tree = array ($first => 1);
			}

						//tree is array and already have index named first
			if ($tree[$first])
			{
				$this->add_dir_tree($tree[$first]->contents,$dir,$path);
			}
			else
			{
				$tree[$first] =& new tree_node;
								$tree[$first]->path = ereg_replace('/'.implode('/',$dir).'$','',$path);
								$tree[$first]->name = $first;
								$tree[$first]->contents = 1;
				$this->add_dir_tree($tree[$first]->contents,$dir,$path);
			}
		}

		/**
		 * Function: get_applications_tree
		 *
		 *		Returns tree with files/folders related to applications
		 *
		 * Author: Vinicius Cubas Brand
		*/
		function get_applications_tree()
		{
			$resp = $this->vfs->get_external_files_info();

			//now build the tree of dirs.	
			$tree = array();

			$root_node =& $this->create_node('',lang('Applications'),0);

						$tree['root'] =& $root_node;

			$appfiles_root = $this->vfs->get_appfiles_root();

			$tree['root']->act_as = $appfiles_root;

			foreach ($resp as $application => $files_info)
			{
				if ($files_info)
				{
					foreach($files_info as $external_id => $external_info)
					{
						//if user has permissions in dir, and dir has files
						if (($external_info['permissions'] & EGW_ACL_READ) &&
							$this->vfs->file_exists(array(
								'string' => $appfiles_root.'/'.$application.'/'.$external_info['id'],
								'relatives' => array(RELATIVE_ROOT)))
						/*&& 
							$this->external_has_files($application,$external_info['id'])*/)
						{
							if (!$tree['root']->contents[$application])
							{
								$tree['root']->contents[$application] = $this->create_node('',$GLOBALS['egw_info']['user']['apps'][$application]['title'],$application.'_'.$external_info['id']);
								$tree['root']->contents[$application]->act_as = $appfiles_root.'/'.$application;

							}
						
							$this->add_applications_tree($tree['root']->contents[$application],$external_info,$files_info,$application);
						}
					}
				}
			}

			return $tree;
		}

		function add_applications_tree(&$tree,&$external_info,&$files_info,$application)
		{
			if (!$external_info)
			{
				return false;
			}

			$node_id = $application.'_'.$external_info['id'];

			$dirname = $this->vfs->get_appfiles_root().'/'.$application.'/'.$external_info['id'];

			$node_path = (($external_info['permissions'] & EGW_ACL_READ) 
				&& $this->vfs->file_exists(array(
					'string' => $dirname,
					'relatives' => array(RELATIVE_ROOT)))) ? $dirname
				 : '';
			$node_caption = $external_info['caption'];

			$node =& $this->create_node($node_path,$node_caption,$node_id);

			if ($external_info['parent']) //has parent
			{
				//see if parent is available
				if ($files_info[$external_info['parent']])
				{
					//see if parent is not stored in tree
					if (!$files_info[$external_info['parent']]['node'])
					{
						//try to add parent in tree
						$this->add_applications_tree($tree,$files_info[$external_info['parent']],$files_info,$application);
					}

					//if parent is stored in tree
					if ($files_info[$external_info['parent']]['node'])
					{
						//add it under parent
						$files_info[$external_info['parent']]['node']->contents[$node_id] =& $node;
						$files_info[$external_info['id']]['node'] =& $node;
						return true;
					}
				}
			}

			//nodes that have no parent are stored in root
			if ($external_info['parent'] == 0)
			{
				$tree->contents[$node_id] =& $node;
				$files_info[$external_info['id']]['node'] =& $node;
				return true;
			}
			
			//nodes which parent is not available are discarded.
			unset($node);
			return false;
		}

		function external_has_files($application,$id)
		{
			$lala = $this->vfs->ls(array(
				'string' => $this->vfs->get_appfiles_root().'/'.$application.'/'.$id,
				'relatives' => array(RELATIVE_ROOT)
				));

			if (count($lala))
			{
				return true;
			}
			return false;
		}

		/**
		 * Function: external_directory_name
		 * 
		 * 		Retrieves the name of a external directory based on the full
		 * 		path name of the directory and the external hooks.
		 * 
		 * Parameters:
		 * 
		 * 		path - a string of the form '/appfiles/projects/9', in other
		 * 		       words, a string of an application folder. 
		 */
		function external_directory_name($path)
		{
			$path = str_replace($this->vfs->get_appfiles_root(),'',$path);
			$path = ereg_replace('^\/*','',$path);
			$path = ereg_replace('\/*$','',$path);

			$path = explode('/',$path);

			$ret['appname'] = $GLOBALS['egw_info']['user']['apps'][$path[0]]['title']; 
			$ret['dirname'] = $this->vfs->get_external_name($path[0],$path[1]);
			
			return $ret;
		}
		
		/**
		 * Function is_application_dir
		 * 
		 * 		Returns true if $path is an application dir. False otherwise.
		 * 
		 * Parameters:
		 * 
		 * 		path - a string of the form '/appfiles/projects/9', in other
		 * 		       words, a string of an application folder. 
		 */
		function is_application_dir($path)
		{
			if (ereg('^'.$this->vfs->get_appfiles_root(),$path))
			{
				$path = str_replace($this->vfs->get_appfiles_root(),'',$path);
				$path = ereg_replace('^\/*','',$path);
				$path = ereg_replace('\/*$','',$path);
			
				$path = explode('/',$path);
				
				if (count($path) == 2)
				{				
					return true;
				}
			}
			return false;
		}

		function &create_node($path,$name,$id=null,$icon='')
		{
			$node =& new tree_node;
						$node->name = $name;
						$node->path = $path;
						$node->contents = array();
			if ($id)
			{
				$node->id = $id;
			}
			return $node;
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

								if ($this->vfs->vfs_sharing->get_file_permissions($account_id,$file_id) & EGW_ACL_READ)
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
			$db =& $GLOBALS['egw']->db;
			
			$db->select('phpgw_vfs2_files','*','',__LINE__,__FILE__);
			if ($db->num_rows() <= 1)
			{
				return false;
			}
			return true;
		}
		
		function exist_records_vfs()
		{
			$db =& $GLOBALS['egw']->db;

			$tables_def = $GLOBALS['egw']->db->get_table_definitions('phpgwapi');

			if (array_key_exists('egw_vfs',$tables_def))
			{
				$old_vfs_table = 'egw_vfs';
			}
			else
			{
				$old_vfs_table = 'phpgw_vfs';
			}
			
			$db->select($old_vfs_table,'*','',__LINE__,__FILE__);
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
				/*
						function allowed_access
						@description Returns True if access is allowed to $dir. 
				*/
				function allowed_access($dir,$permission=EGW_ACL_READ)
				{
						return $this->vfs->acl_check(array(
								'string'    => $dir,
								'relatives' => array(RELATIVE_ROOT),
								'operation' => $permission,
								'must_exist' => true));
				}

		function clean_tmp_folder($app)
		{
			$tmppath = $this->homedir."/tmp/$app";
			if ($this->vfs->file_exists(array(
				'string' => $tmppath,
				'relatives' => array(RELATIVE_ROOT))))
			{
				$resp = $this->vfs->rm(array(
					'string' => $tmppath,
					'relatives' => array(RELATIVE_ROOT)));
				return $resp;
			}
			else
			{
				return true;
			}
		}

		//sees if an application have temporary files for user...
		function has_temporary_files($app)
		{
			$tmppath = $this->homedir."/tmp/$app";
			if ($this->vfs->file_exists(array(
					'string' => $tmppath,
					'relatives' => array(RELATIVE_ROOT))) &&
				count($this->vfs->ls(array(
					'string' => $tmppath,
					'relatives' => array(RELATIVE_ROOT)))))
			{
				return true;
			}
			return false;
		}

		//moves files of applications from a temporary path (for instance,
		//a new appointment) to an application folder
		//To be used after a $id=='new' was passed to $this->get_app_path
		function confirm_app_id($id,$app)
		{
			$appfiles_root = $this->vfs->get_appfiles_root();

			$oldpath = $this->homedir."/tmp/$app";
			$newpath = "$appfiles_root/$app/$id";

			if ($this->vfs->file_exists(array(
					'string' => $oldpath,
					'relatives' => array(RELATIVE_ROOT)) &&
				!$this->vfs->file_exists(array(
					'string' => $newpath,
					'relatives' => array(RELATIVE_ROOT))
					)))
			{
				$ov_acl = $this->vfs->override_acl;
				$this->vfs->override_acl = 1;
				$res = $this->vfs->mv(array(
					'from' => $oldpath,
					'to' => $newpath,
					'relatives' => array(RELATIVE_ROOT,RELATIVE_ROOT)
					));
				$this->vfs->override_acl = $ov_acl;
				return $res;
			}
			else
			{
				return false;
			}
		}

		//gets path for application
		function get_app_path($id,$app)
		{
			//TODO: make verification if $id and $app are valid

			$appfiles_root = $this->vfs->get_appfiles_root();

			if (in_array($app,array('file','filescenter','filemanager')))
			{
				return $id;
			}

			if ($id != 'new')
			{
				$path = "$appfiles_root/$app/$id";
			}
			else //stores in a temporary place for applications that have yet no id...
			{
				$path = $this->homedir."/tmp/$app";
			}
			
			#creates path if not exists

			$this->vfs->override_acl = 1;

			$this->vfs->mkdir(array(
				'string' => $path,
				'relatives' => array(RELATIVE_ROOT)
				));

			$this->vfs->override_acl = 0;

			return $path;
		}

		function create_images_dir()
		{
			$images_dir_path = $this->homedir.'/images';
			if (!$this->vfs->file_exists(array(
				'string' => $images_dir_path,
				'relatives' => array(RELATIVE_ROOT)
				)))
			{
				$ov = $this->vfs->override_acl;
				$this->vfs->override_acl = 1;
				$this->vfs->mkdir(array(
					'string' => $images_dir_path,
					'relatives' => array(RELATIVE_ROOT)
					));

								$file_id = $this->vfs->get_file_id(array(
					'string' => $images_dir_path,
					'relatives' => array(RELATIVE_NONE)
								));

					$vfs_sharing =& CreateObject('phpgwapi.vfs_sharing');

								$vfs_sharing->set_permissions(array(
										$file_id => array(
												0 => EGW_ACL_ADD
											 )
								));

				$this->vfs->override_acl = $ov;
			}
			return $images_dir_path;
		}
		//gets path for displaying
		function get_disppath($path)
		{
			if (!$this->vfs->is_appfolder($path))
			{
				return array(
					'caption' => $path
					);
			}
			else
			{
				$resp =& $this->vfs->get_external_files_info();

				$exp_path = explode('/',$path);
				$application = $exp_path[2];
				$id = $exp_path[3];
				$apptitle = $GLOBALS['egw_info']['user']['apps'][$application]['title'];
				$caption = $resp[$application][$id]['caption'];

				unset($exp_path[0]);
				unset($exp_path[1]);
				unset($exp_path[2]);
				unset($exp_path[3]);

				$path = $exp_path ? implode('/',$exp_path) : '/';

				return array(
					'caption' => "[$apptitle] $caption ($path)",
					'link' => $resp[$application][$id]['url']
					);
					
			}
		}

		function get_all_trees()
		{
			//TODO modularize this someway
						$image_root = $GLOBALS['egw_info']['server']['webserver_url'].'/filescenter/templates/default/images/22x22';
			
			$ftree = array('root' => new tree_node);
			$ftree['root']->path = '';
			$ftree['root']->name = lang('FilesCenter');
			$ftree['root']->contents = array();

			$bo_home_tree = $this->get_dir_tree($this->homedir,lang('Home'));			
			reset($bo_home_tree);
			list($key,$val1) = each($bo_home_tree);
			$val1->icon = $image_root.'/home.png';
			$ftree['root']->contents['home'] =& $val1;

			/** Uncomment this when the so_db_highlevel be migrated to egroupware
			$bo_subsc_tree = $this->get_subscriptions_tree();
			reset($bo_subsc_tree);
			list($key,$val2) = each($bo_subsc_tree);
			$val2->icon = $image_root . '/subs.png';
			$ftree['root']->contents['home']->contents = array("subs" => $val2) + $ftree['root']->contents['home']->contents;*/

						$bo_shared_tree = $this->get_shared_tree();
			reset($bo_shared_tree);
			list($key,$val2) = each($bo_shared_tree);
			$val2->icon = $image_root . '/shared.png';
			$ftree['root']->contents['shared'] =& $val2;

						$bo_public_tree = $this->get_dir_tree($this->publicdir,lang('Public'));
						reset($bo_public_tree);
			list($key,$val3) = each($bo_public_tree);
			$val3->icon = $image_root . '/public.png';
			$ftree['root']->contents['publ'] =& $val3;

			$bo_applications_tree = $this->get_applications_tree();
						reset($bo_applications_tree);
			list($key,$val4) = each($bo_applications_tree);
			$val4->icon = $image_root . '/appfolder.png';
			$ftree['root']->contents['appl'] =& $val4;

			return $ftree;
		}

		/**
		 * Method: subscribe
		 *
		 *	Subscribe an user (current, default) to a set of files
		 *
		 * Parameters:
		 *
		 *	files - an array with path to files
		 *	operation - {subscribe,unsubscribe}
		 *	account_id - defaults to current user
		 */
		function subscribe($params)
		{
			$default_values = array(
				'files' => array(),
				'operation' => 'subscribe',
				'account_id' => $GLOBALS['egw_info']['user']['account_id']
				);
			$params = array_merge($default_values,$params);

			$account =& $GLOBALS['egw']->db_hl->get_entity(array(
				'entity_type' => 'account',
				'info_data_id' => $params['account_id']
				));

			if (!$account)
			{
				return false;
			}

			foreach ($params['files'] as $file_name)
			{
				$file_id = $this->vfs->get_file_id(array(
					'string' => $file_name,
					'relatives' => array(RELATIVE_ROOT)
					));

				unset($file);

				$file =& $GLOBALS['egw']->db_hl->get_entity(array(
					'entity_type' => 'file',
					'info_data_id' => $file_id
					));

				if (!$file)
				{
					continue;
				}

				//this must be 'subscribe' or 'unsubscribe'
				$file->$params['operation'](array('account' => &$account));
				$file->write();
			}
			return true;
		}

		function get_subscriptions_tree()
		{
			$account =& $GLOBALS['egw']->db_hl->get_entity(array(
				'entity_type' => 'account',
				'info_data_id' => $GLOBALS['egw_info']['user']['account_id']
				));

			//this is that way because the unique way account is relating with
			//file is by subscription...
			$files = $account->get_related(array(
				'entity_type' => 'file',
				'other' => array(
					'order' => 'file.name'
					)
				));
	
			$root_node =& $this->create_node('',lang('Subscriptions'),0);

			$tree['root'] = $root_node;

			foreach ($files as $key => $f)
			{
				unset($node);
				$node =& $this->create_node($files[$key]->get('directory').'/'.$files[$key]->get('name'),$files[$key]->get('name'),$key);
				$tree['root']->contents[] =& $node;
			}
			return $tree;
		}
	}

?>
