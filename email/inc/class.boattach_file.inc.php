<?php
	/**************************************************************************\
	* AngleMail - email BO Class for Attaching Files						*
	* http://www.anglemail.org									*
	* File adapted directly from phpGroupWare file email/attach_file.php		*
	* http://www.egroupware.org									*
	* That file was authored by Joseph Engo <jengo@phpgroupware.org>		*
	* Previous Maintainer notes that server side file handling was borrowed 	*
	* from Squirrelmail circa 2000-2001								*
	* http://www.squirrelmail.org									*
	* Some refinements and code modernization to that file were made by 		*
	* Angelo "Angles" Puglisi <angles@aminvestments.com>				*
	* Then Anglemail made the code a "BO" class object. (this file)			*
	* AngleMail appreciates all the work of previous authors of this file.		*
	* --------------------------------------------							*
	*  This program is free software; you can redistribute it and/or modify it		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your		*
	*  option) any later version.								*
	\**************************************************************************/

	/* $Id$ */

	class boattach_file
	{
		var $public_functions = array(
			'attach'	=> True
			//'show_ui'	=> True
		);
		var $debug = 0;
		//var $debug = 3;
		//var $debug = 4;
		
		var $msg_bootstrap;
		var $var_holder='##NOTHING##';
		
		var $uploaddir;
		
		var $file_data=array();
		var $control_data=array();
		
		// this may or may not be the GLOBAL template
		// but this reference lets us use the same var no matter what
		// ths UI fill will fill this for us(give us a good reference)
		// but if it is NOT a reference ("##NOTHING##") then the 
		// template class we will use only to store variables for us.
		//var $ref_TPL='##NOTHING##';
		
		
		/*!
		@function boattach_file *CONSTRUCTOR*
		@abstract checks and makes sure we have a X->msg object to work with and initialized some blank data
		*/
		function boattach_file()
		{
			// we need a msg object BUT NO LOGIN IS NEEDED
			$this->msg_bootstrap = CreateObject("email.msg_bootstrap");
			$this->msg_bootstrap->set_do_login(False);
			$this->msg_bootstrap->ensure_mail_msg_exists('email: boattach_file.constructor', 0);
			
			// initialize $this->control_data[], this is more for issustration of what this array does
			$this->control_data= array();
			$this->control_data['action'] = '';
			$this->control_data['delete'] = array();
			
			// initialize $this->file_data
			$this->file_data= array();
			$this->file_data['file_tmp_name'] = '';
			$this->file_data['file_name'] = '';
			$this->file_data['file_size'] = '';
			$this->file_data['file_type'] = '';
			
			// using return has something to do with how inherited classes use this constructor
			// so do not use it until I fogure out if it is good or bad to do so
			//return;
		}
		
		
		/*!
		@function set_ref_var_holder UNDEDER DEVELOPMENT
		@abstract set the thing to hold the values the "attach" function produces, i.e. the UI Template. IT MUST BE AVAILABLE 
		WITHIN THE SCOPE OF THIS FUNCTION, for example GLOBALSphpgw->template is available. 
		@param REFERENCE to something to hold values, ONLY THE TEMPLATE CLASS WORKS FOR NOW 
		@result (boolean) True is param is good AND if this->var_holder previouly was "##NOTHING##", or returns 
		False if we did not set the var_holder for reasons noted previously. 
		@author Angles 
		@discussion 3 tier means this class can wither fill an HTML template or return raw data. 
		Currently only the phpGW original template class is coded for, but anything could be plugged in. 
		REMEMBER this is a reference, this function declare the param as a reference, DO NOT CALL 
		this function WITH AN AMPERSAND before the param. PHP is phasing that out. The function declaration 
		identifies the param as a reference and that is that, no other amperdsands needed. Right now the only 
		useful way to use this is to call it like bo->set_ref_var_holder(GLOBALS["phpgw"]->template) that is edited 
		to show up in the inline doc parser of course.
		*/
		function set_ref_var_holder(&$ref_template)
		{
			// NOT IMPLEMENTED YET
			if ($this->debug > 1) { echo 'emai1.boattach_file.set_ref_var_holder ('.__LINE__.'): param (a reference) is gettype '.serialize(gettype($ref_template)).' and param\'s class name is ['.get_class($ref_template).'] <br>'; } 
			if ($this->debug > 2) { echo 'emai1.boattach_file.set_ref_var_holder ('.__LINE__.'): param (a reference) DUMP<pre>'; print_r($ref_template);  echo '</pre>'; }
			if ( (isset($ref_template))
			&& ($this->var_holder != '##NOTHING') )
			{
				// declared as a reference above, no need for ampersand here.
				$this->var_holder = $ref_template;
				return True;
			}
			else
			{
				return False;
			}
		}
		
		/*!
		@function wbasename
		@abstract returns a filename with the path stripped off
		@param $input (string) filename with or without the path. If path is there it will be stripped. 
		@authors Angles and some help from php.net manual
		@discussion Netscape 6 sometimes passes file_name with a full path, we need to extract just the filename. 
		WHY use this insead of the buildin PHP function, I DO NOT KNOW. 
		*/
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
		
		/*!
		@function fill_control_data_gpc
		@abstract grab gpc POST vars used in this script for "this->action" and "this->delete" values.
		@authors Angles
		@param none
		@return none, this is a class OOP call. 
		@abstract there are 2 vars this script needs to do somethig, "this->action" and "this->delete", so 
		we use this function to fill those vars from GPC values here, but in the future they could be filled 
		via some external method.  In the days before superglobals, these were simple vars "$action" and 
		also "$delete", but such simple days are over. 
		*/
		function fill_control_data_gpc()
		{
			if ($this->debug > 2) { echo 'emai.boattach_file.attach ('.__LINE__.'): $GLOBALS[phpgw]->msg->ref_POST data DUMP<pre>'; print_r($GLOBALS['phpgw']->msg->ref_POST);  echo '</pre>'; }
			
			$this->control_data['action'] = htmlentities($GLOBALS['phpgw']->msg->ref_POST['action']);
			$this->control_data['delete'] = $GLOBALS['phpgw']->msg->ref_POST['delete'];
			
			if ($this->debug > 2) { echo 'emai.boattach_file.attach ('.__LINE__.'): $this->control_data DUMP<pre>'; print_r($this->control_data);  echo '</pre>'; }
		}
		
		
		/*!
		@function fill_file_data_gpc
		@abstract fill this->file_data array from gpc sources, php FILES POST data 
		@authors Angles, Chris Wiess, Dave Hall, Lex
		@discussion UNDER DEVELOPMENT Some server side attachment upload handling code is borrowed from
		Squirrelmail <Luke Ehresman> http://www.squirrelmail.org, particularly the 
		moving, temporary naming, and the ".info" file code. 
		*/
		function fill_file_data_gpc()
		{
			if ($this->debug > 0) { echo 'ENTERING emai.boattach_file.fill_file_data_gpc ('.__LINE__.') <br>'; }
			/*
			//PHP VARIABLES NOTES: 
			// $uploadedfile was the name of the file box in the submitted form, and php3 gives it additional properties:
			// $uploadedfile_name   $uploadedfile_size   $uploadedfile_type
			// php4 also does this, but the preffered way is to use the new (for php4) $HTTP_POST_FILES global array
			// $HTTP_POST_FILES['uploadedfile']['name']   .. .['type']   ... ['size']  ... ['tmp_name']
			// note that $uploadedfile_type and $HTTP_POST_FILES['uploadedfile']['type'] *may* not be correct filled
			// UPDATE: php > 4.2 prefers "superglobal" $_FILES, actually 4.1+ can use that $_FILES
			// 
			// FILE SIZE NOTES:
			// file size limits may depend on: (a) <input type="hidden" name="MAX_FILE_SIZE" value="whatever">
			// (b) these values in php.ini: "post_max_size" "upload_max_filesize" "memory_limit" "max_execution_time"
			// also see http://www.php.net/bugs.php?id=8377  for the status of an upload bug not fixed as of 4.0.4
			// also note that uploading file to *memory* is wasteful
			*/
			
			// probably UNNECESSARY debug code, delete it after this is all stable
			if (($GLOBALS['phpgw']->msg->minimum_version("4.1.0"))
			&& (!isset($GLOBALS['phpgw']->msg->ref_FILES)))
			{
				echo 'emai1.boattach_file.fill_file_data_gpc ('.__LINE__.'): ERROR: $GLOBALS[phpgw]->msg->ref_FILES should be set here, but it IS NOT set<br>'; 
			}
			
			// the following code only applies to php < 4.1.0 where that superglobal was not available
			// thanks Dave Hall for this code suggestion
			if (
			  (! (isset($HTTP_POST_FILES) || isset($GLOBALS['HTTP_POST_FILES'])) )
			  && ($GLOBALS['phpgw']->msg->minimum_version("4.1.0") == False)
			)
			{
				$_FILES = $GLOBALS['HTTP_POST_FILES'];
				global $_FILES;
				// REDEFINE THE REFERENCE TO THE FILES DATA
				$GLOBALS['phpgw']->msg->ref_FILES = &$_FILES;
			}
			// yes I am aware that the above code and the below code kind of deal with the same thing
			// if  the above code still does not give a good reference to FILES data, below there is "oldschool" fallback code
			// also I do not want to force global something every script run when it is only needed here
			
			
			// clean / prepare PHP provided file info
			// note that "uploadedfile" is the POST submit form identification for the file
			if ( ($GLOBALS['phpgw']->msg->minimum_version("4.1.0"))
			// or we may have otherwise obtained a good reference above
			|| (isset($GLOBALS['phpgw']->msg->ref_FILES['uploadedfile'])) )
			{
				if ($this->debug > 1) { echo 'emai.boattach_file.fill_file_data_gpc ('.__LINE__.'): using msg->ref_FILES to fill $this->file_data[] <br>'; } 
				if ($this->debug > 2) { echo 'emai.boattach_file.fill_file_data_gpc ('.__LINE__.'): msg->ref_FILE dump: '.htmlspecialchars(serialize($GLOBALS['phpgw']->msg->ref_FILES)).'<br>'; } 
				$this->file_data['file_tmp_name'] = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($GLOBALS['phpgw']->msg->ref_FILES['uploadedfile']['tmp_name']));
				$this->file_data['file_name'] = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($GLOBALS['phpgw']->msg->ref_FILES['uploadedfile']['name']));
				$this->file_data['file_size'] = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($GLOBALS['phpgw']->msg->ref_FILES['uploadedfile']['size']));
				$this->file_data['file_type'] = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($GLOBALS['phpgw']->msg->ref_FILES['uploadedfile']['type']));
			}
			else
			{
				// real OLD STYLE way to get this info
				if ($this->debug > 1) { echo 'emai.boattach_file.fill_file_data_gpc ('.__LINE__.'): no valid msg->ref_FILES available, using ANCIENT old, bad (we have to global 4 vars) to fill this->file_data[] <br>'; } 
				global $uploadedfile, $uploadedfile_name, $uploadedfile_size, $uploadedfile_type;
				// php less then 4.1 uses these pre-superglobals enviornment vars
				$this->file_data['file_tmp_name'] = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($uploadedfile));
				$this->file_data['file_name'] = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($uploadedfile_name));
				$this->file_data['file_size'] = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($uploadedfile_size));
				$this->file_data['file_type'] = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($uploadedfile_type));	
			}
			
			// sometimes PHP is very clue-less about MIME types, and gives NO file_type
			// rfc default for unknown MIME type is:
			$mime_type_default = 'application/octet-stream';
			// so if PHP did not pass any file_type info, then substitute the rfc default value
			if (trim($this->file_data['file_type']) == '')
			{
				$this->file_data['file_type'] = $mime_type_default;
			}
			
			// Netscape 6 passes file_name with a full path, we need to extract just the filename
			if ($this->debug > 1) { echo 'emai.boattach_file.fill_file_data_gpc ('.__LINE__.'): file_name (pre-wbasename): ' .$this->file_data['file_name'] .'<br>'; } 
			$this->file_data['file_name'] = $this->wbasename($this->file_data['file_name']);
			if ($this->debug > 1) { echo 'emai.boattach_file.fill_file_data_gpc ('.__LINE__.'): file_name (post-wbasename): ' .$this->file_data['file_name'] .'<br>'; } 
			
			if ($this->debug > 2) { echo 'emai.boattach_file.fill_file_data_gpc ('.__LINE__.'): filled $this->file_data DUMP<pre>'; print_r($this->file_data);  echo '</pre>'; } 
			if ($this->debug > 0) { echo 'LEAVING emai.boattach_file.fill_file_data_gpc ('.__LINE__.')<br>'; }
		}
		
		
		/*!
		@function attach
		@abstract conversion of attach_file.php into a bo class object for attaching files. 
		@authors Many, see file banner, credits to Joseph Engo, Squirrelmail, Angles, Chris Wiess, Dave Hall, Lex
		@discussion Some server side attachment upload handling code is borrowed from
		Squirrelmail <Luke Ehresman> http://www.squirrelmail.org, particularly the 
		moving, temporary naming, and the ".info" file code. 
		*/
		function attach()
		{			
			if ($this->debug > 0) { echo 'ENTERING emai.boattach_file.attach'.'<br>'; }
			if ($this->debug > 2) { echo 'emai.boattach_file.attach: initial $GLOBALS[phpgw_info][flags] DUMP<pre>'; print_r($GLOBALS['phpgw_info']['flags']);  echo '</pre>'; }
			
			// TRICK1: use the GLOBAL template established in the UI file (called first)
			// TRICK2: if for some reason we were not called by the UIATTACH_FILE
			//  we will still use those same commands to have a private template object 
			//  act only as a place to keep out important variables.
			// that is NOT IMPLEMENTED YET
			// THIS IS A HACK LINE to remind calling proc to give us a template or something to hold our data
			// probably should remove this line later on
			if (isset($this->var_holder) == False)
			{
				echo 'emai.boattach_file.attach ('.__LINE__.'): ERROR: initial $this->var_holder needs to be set by this point in the code <br>';
			}
			
			// initialize some variables
			$alert_msg = '';
			$totalfiles = 0;
		
			// ensure existance of PHPGROUPWARE temp dir
			// note: this is different from apache temp dir, and different from any other temp file location set in php.ini
			//if (!file_exists($GLOBALS['phpgw_info']['server']['temp_dir']))
			if (!is_dir($GLOBALS['phpgw_info']['server']['temp_dir']))
			{
				mkdir($GLOBALS['phpgw_info']['server']['temp_dir'],0700);
			}
		
			// if we were NOT able to create this temp directory, then make an ERROR report
			//if (!file_exists($GLOBALS['phpgw_info']['server']['temp_dir']))
			if (!is_dir($GLOBALS['phpgw_info']['server']['temp_dir']))
			{
				$alert_msg .= 'Error:'.'<br>'
					. 'Server is unable to access phpgw tmp directory'.'<br>'
					. $GLOBALS['phpgw_info']['server']['temp_dir'].'<br>'
					. 'Please check your configuration'.'<br>'
					. '<br>';
			}
		
			//if (!file_exists($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['phpgw_info']['user']['sessionid']))
			//if (!is_dir($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['phpgw_info']['user']['sessionid']))
			//{
			//	mkdir($GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['phpgw_info']['user']['sessionid'],0700);
			//}
		
			//$this->uploaddir = $GLOBALS['phpgw_info']['server']['temp_dir'] . SEP . $GLOBALS['phpgw_info']['user']['sessionid'] . SEP;
			$this->uploaddir = $GLOBALS['phpgw']->msg->att_files_dir;
			if (!is_dir($this->uploaddir))
			{
				mkdir($this->uploaddir,0700);
			}
			
			// if we were NOT able to create this temp directory, then make an ERROR report
			//if (!file_exists($this->uploaddir))
			if (!is_dir($this->uploaddir))
			{
				$alert_msg .= 'Error:'.'<br>'
					. 'Server is unable to access phpgw email tmp directory'.'<br>'
					. $this->uploaddir.'<br>'
					. 'Please check your configuration'.'<br>'
					. '<br>';
			}
			
			// grab externally provided information
			$this->fill_control_data_gpc();
			$this->fill_file_data_gpc();
			
			// Some server side attachment upload handling code is borrowed from
			// Squirrelmail <Luke Ehresman> http://www.squirrelmail.org
			// particularly the moving, temporary naming, and the ".info" file code.
			
			if ($this->control_data['action'] == lang('Delete')
			|| $this->control_data['action'] == htmlentities(lang('Delete')))
			{
				if ($this->debug > 1) { echo 'boattach_file.attach ('.__LINE__.'): <b>REQUEST TO DELETE</b> detected $this->control_data[action] ('.$this->control_data['action'].') == lang(Delete) ('.lang('Delete').'): <br>'; } 
				// sometimes $this->control_data[delete][] seems to have multiple entries for the same filename
				for ($i=0; $i<count($this->control_data['delete']); $i++)
				{
					$full_fname_attachment = $this->uploaddir.SEP.$this->control_data['delete'][$i];
					$full_fname_metafile = $this->uploaddir.SEP.$this->control_data['delete'][$i] . '.info';
					if (file_exists($full_fname_attachment))
					{
						if ($this->debug > 1) { echo 'boattach_file.attach ('.__LINE__.'): loop['.$i.'] deleting file: ['.$full_fname_attachment.']: <br>'; } 
						unlink($full_fname_attachment);
					}
					else
					{
						if ($this->debug > 1) { echo 'boattach_file.attach ('.__LINE__.'): loop['.$i.'] request to deleting NON-EXISTING file: ['.$full_fname_attachment.']: <br>'; } 
					}
					// and the associated ".info" metafile
					if (file_exists($full_fname_metafile))
					{
						if ($this->debug > 1) { echo 'boattach_file.attach ('.__LINE__.'): loop['.$i.'] deleting related meta file: ['.$full_fname_metafile.']: <br>'; } 
						unlink($full_fname_metafile);
					}
					else
					{
						if ($this->debug > 1) { echo 'boattach_file.attach ('.__LINE__.'): loop['.$i.'] request to deleting NON-EXISTING file: ['.$full_fname_metafile.']: <br>'; } 
					}
				}
			}
			
			if (($this->control_data['action'] == lang('Attach File')
				|| $this->control_data['action'] == htmlentities(lang('Attach File')))
			&& ($this->file_data['file_tmp_name'] != '')
			&& ($this->file_data['file_tmp_name'] != 'none'))
			{
				srand((double)microtime()*1000000);
				$random_number = rand(100000000,999999999);
				$newfilename = md5($this->file_data['file_tmp_name'].', '.$this->file_data['file_name'].', '.$GLOBALS['phpgw_info']['user']['sessionid'].time().getenv('REMOTE_ADDR').$random_number);
		
				// Check for uploaded file of 0-length, or no file (patch from Zone added by Milosch)
				//if ($this->file_data['file_tmp_name'] == "none" && $this->file_data['file_size'] == 0) This could work also
				if ($this->file_data['file_size'] == 0)
				{
					touch ($this->uploaddir.SEP.$newfilename);
				}
				else
				{
					copy($this->file_data['file_tmp_name'], $this->uploaddir.SEP.$newfilename);
				}
		
				$ftp = fopen($this->uploaddir.SEP.$newfilename . '.info','wb');
				fputs($ftp,$this->file_data['file_type']."\n".$this->file_data['file_name']."\n");
				fclose($ftp);
			}
			elseif (($this->control_data['action'] == lang('Attach File')) &&
				(($this->file_data['file_tmp_name'] == '') || ($this->file_data['file_tmp_name'] == 'none')))
			{
				$langed_attach_file = lang("Attach File");
				$alert_msg = lang('Input Error:').'<br>'
					. lang('Please submit a filename to attach').'<br>'
					. lang('You must click %1 for the file to actually upload','"'.lang('Attach File').'"').'.<br>'
					. '<br>';
			}
		
			$dh = opendir($this->uploaddir);
			//while ($file = readdir($dh)) // http://www.php.net/manual/en/function.readdir.php says this is wrong ... 
			while (false !== ($file = readdir($dh))) // is correct according to the manual but only works with 4.0.0RC2+
			{
				if (($file != '.')
				&& ($file != '..')
				&& (ereg("\.info",$file)))
				{
					$file_info = file($this->uploaddir.SEP.$file);
					
					//get filesize in kb, but do not tell user a file is 0kb, because it is probably closer to 1kb
					// actual 0kb files are probably an error, and are detected in the actual upload code (HOPEFULLY) 
					$real_file = str_replace('.info','',$file);
					$real_file_size = ((int) (@filesize($this->uploaddir.SEP.$real_file)/1024));
					if ($real_file_size < 1)
					{
						$real_file_size = 1;
					}
					
					if ($this->debug > 2) { echo 'FILE contents DUMP: <pre>'; print_r(file($this->uploaddir.SEP.$real_file)); echo '</pre>'; } 
					// for every file, fill the file list template with it
					$GLOBALS['phpgw']->template->set_var('ckbox_delete_name','delete[]');
					$GLOBALS['phpgw']->template->set_var('ckbox_delete_value',substr($file,0,-5));
					$GLOBALS['phpgw']->template->set_var('hidden_delete_name',substr($file,0,-5));
					$GLOBALS['phpgw']->template->set_var('hidden_delete_filename', $file_info[1]);
					$GLOBALS['phpgw']->template->set_var('ckbox_delete_filename', 
							$file_info[1].' ('.$real_file_size.'k)'); //also shows file size in kb
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
				// fill the delete submit form
				$GLOBALS['phpgw']->template->set_var('btn_delete_name','action');
				$GLOBALS['phpgw']->template->set_var('btn_delete_value',lang('Delete'));
				$GLOBALS['phpgw']->template->parse('V_delete_btn','B_delete_btn');
			}
		
			$body_tags = 'bgcolor="'.$GLOBALS['phpgw_info']['theme']['bg_color'].'" alink="'.$GLOBALS['phpgw_info']['theme']['alink'].'" link="'.$GLOBALS['phpgw_info']['theme']['link'].'" vlink="'.$GLOBALS['phpgw_info']['theme']['vlink'].'"';
			if (!$GLOBALS['phpgw_info']['server']['htmlcompliant'])
			{
				$body_tags .= ' topmargin="0" marginheight="0" marginwidth="0" leftmargin="0"';
			}
		
			// begin DEBUG INFO (this is old, needs updating)
			$debuginfo .= '--uploadedfile info: <br>'
				. '$GLOBALS[phpgw_info][server][temp_dir]: '.$GLOBALS['phpgw_info']['server']['temp_dir'].'<br>'
				. '$GLOBALS[phpgw_info][user][sessionid]: '.$GLOBALS['phpgw_info']['user']['sessionid'].'<br>'
				. '$this->uploaddir: '.$this->uploaddir.'<br>'
				. 'file_tmp_name: ' .$this->file_data['file_tmp_name'] .'<br>'
				. 'file_name: ' .$this->file_data['file_name'] .'<br>'
				. 'file_size: ' .$this->file_data['file_size'] .'<br>'
				. 'file_type: ' .$this->file_data['file_type'] .'<br>'
				. '<br>'
				. 'totalfiles: ' .$totalfiles .'<br>'
				. 'file_info_count: '.count($file_info) .'<br>'
				. '<br>';
			if (count($file_info) > 0)
			{
				$debuginfo .= '<br> file_info[0]='.$file_info[0] .'<br> file_info[1]='.$file_info[1];
			}
			$debuginfo .= '<br>';
			//print_debug('$debuginfo', $debuginfo);
			if ($this->debug > 1) { echo '$debuginfo: '.$debuginfo.'<br>'; } 
			// end DEBUG INFO
			
			// where to submit the form to
			$form_action = $GLOBALS['phpgw']->link('/index.php',
				array(
					'menuaction' => 'email.uiattach_file.attach'
				)
			);
			
			$charset = $GLOBALS['phpgw']->translation->charset();
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
			$GLOBALS['phpgw']->template->set_var('form_action',$form_action);
			$GLOBALS['phpgw']->template->set_var('text_attachfile',lang('Attach file'));
			$GLOBALS['phpgw']->template->set_var('text_currattached',lang('Current attachments (%1)',$totalfiles));
			$GLOBALS['phpgw']->template->set_var('txtbox_upload_desc',lang('File'));
			$GLOBALS['phpgw']->template->set_var('txtbox_upload_name','uploadedfile');
			$GLOBALS['phpgw']->template->set_var('btn_attach_name','action');
			$GLOBALS['phpgw']->template->set_var('btn_attach_value',lang('Attach File'));
			$GLOBALS['phpgw']->template->set_var('btn_done_name','done');
			$GLOBALS['phpgw']->template->set_var('btn_done_value',lang('Done'));
			$GLOBALS['phpgw']->template->set_var('btn_done_js','copyback()');
			$GLOBALS['phpgw']->template->set_var('form1_name','doit');
			
			// DAMN, THIS SHOULD BE IN THE UI FILE
			//$GLOBALS['phpgw']->template->pfp('out','T_attach_file');
			
			// IF called bu UI, then UI takes care of this
			/* MOVED to UI
			//$GLOBALS['phpgw']->common->phpgw_exit();
			if (is_object($GLOBALS['phpgw']->msg))
			{
				// close down ALL mailserver streams
				$GLOBALS['phpgw']->msg->end_request();
				// destroy the object
				$GLOBALS['phpgw']->msg = '';
				unset($GLOBALS['phpgw']->msg);
			}
			
			// shut down this transaction
			$GLOBALS['phpgw']->common->phpgw_exit(False);
			*/
			
			if ($this->debug > 0) { echo 'LEAVING emai.boattach_file.attach'.'<br>'; }
		
		}
	
	
	}
?>
