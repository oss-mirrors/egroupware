<?php
	/**************************************************************************\
	* phpGroupWare - email BO Class	for Message Actions				*
	* http://www.phpgroupware.org							*
	* Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
	* --------------------------------------------							*
	*  This program is free software; you can redistribute it and/or modify it		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your		*
	*  option) any later version.								*
	\**************************************************************************/

	/* $Id$ */

	class boaction
	{
		var $public_functions = array(
			'delmov'	=> True,
			'get_attach'	=> True,
			'view_html'	=> True
		);
		//var $debug = True;
		var $debug = False;
		var $xml_functions = array();
		var $xi = array();
		var $redirect_to = '';
		var $redirect_if_error = '';
		var $error_str = '';
		
		function boaction()
		{
			// initialize an error reporting action
			//$this->redirect_if_error = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->get_arg_value('index_menuaction'));
		}
		
		function delmov()
		{
			// attempt (or not) to reuse an existing mail_msg object LEAVING here and going back to index
			// that index page will attempt to reuse the object we create in this function, i.e. we create it always
			// because it's not supported to reuse mail_msg object that already exists when ENTERING into this function
			
			//$attempt_reuse = True;			
			$attempt_reuse = False;
			
			if ($this->debug) { echo 'emai.boaction.delmov: ENTERED, about to create mail_msg object, attempt to reuse (outgoing): '.serialize($attempt_reuse ).'<br>'; }
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug) { echo 'emai.boaction.delmov: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug) { echo 'emai.boaction.delmov: is_object test: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			$args_array = Array();
			$args_array['do_login'] = True;
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', delmov()');
			}
			
			// make an error report URL
			$this->redirect_if_error = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->get_arg_value('index_menuaction'));
			
			$folder_info = array();
			$folder_info = $GLOBALS['phpgw']->msg->get_folder_status_info();
			$totalmessages = $folder_info['number_all'];
			
			// ---- MOVE (Multiple) Messages from folder to folder   -----
			if ($GLOBALS['phpgw']->msg->get_arg_value('what') == "move")
			{
				if ($this->debug) { echo 'emai.boaction.delmov: get_arg_value(what) == "move") <br>'; }
				// called by the "move selected messages to" listbox onChange action
				
				/*
				$fromacctnum = (int)$GLOBALS['phpgw']->msg->get_arg_value('acctnum');
				$tofolder = $GLOBALS['phpgw']->msg->prep_folder_in($GLOBALS['phpgw']->msg->get_arg_value('tofolder'));
				$toacctnum = (int)$GLOBALS['phpgw']->msg->get_arg_value('toacctnum');
				// report number messages moved (will be made = 0 if error below)
				//$tm = count($GLOBALS['phpgw']->msg->get_arg_value('msglist'));
				// mail_move accepts a single number (5); a comma seperated list of numbers (5,6,7,8); or a range with a colon (5:8)
				//$msgs = $GLOBALS['phpgw']->msg->get_arg_value('msglist') ? implode($GLOBALS['phpgw']->msg->get_arg_value('msglist'), ",") : $GLOBALS['phpgw']->msg->get_arg_value('msglist');
				$delmov_list = $GLOBALS['phpgw']->msg->get_arg_value('delmov_list');
				// tm = "Total Moved" indicator
				$tm = count($delmov_list);
				$msgs = '';
				for($i=0;$i<count($delmov_list);$i++)
				{
					if ($msgs != '')
					{
						$prefix = ',';
					}
					$msgs .= $prefix . $delmov_list[$i]['msgball']['msgnum'];
				}
				$did_move = $GLOBALS['phpgw']->msg->phpgw_mail_move($msgs, $tofolder);
				*/
				
				$delmov_list = $GLOBALS['phpgw']->msg->get_arg_value('delmov_list');
				$to_fldball = $GLOBALS['phpgw']->msg->get_arg_value('to_fldball');
				$to_fldball['folder'] = $GLOBALS['phpgw']->msg->prep_folder_in($to_fldball['folder']);
				$to_fldball['acctnum'] = (int)$to_fldball['acctnum'];
				// tm = "Total Moved" indicator
				$tm = count($delmov_list);
				for ($i = 0; $i < count($delmov_list); $i++)
				{
					if ($this->debug) { echo 'email.boaction.delmov: in mail move loop ['.(string)($i+1).'] of ['.$tm.']<br>'; }
					$mov_msgball = $delmov_list[$i];
					$mov_msgball['folder'] = $GLOBALS['phpgw']->msg->prep_folder_in($mov_msgball['folder']);
					$mov_msgball['acctnum'] = (int)$mov_msgball['acctnum'];
					$did_move = False;
					if ($this->debug) { echo 'email.boaction.delmov: calling  $GLOBALS[phpgw]->msg->interacct_mail_move('.serialize($mov_msgball).', '.serialize($to_fldball).'<br>'; }
					$did_move = $GLOBALS['phpgw']->msg->interacct_mail_move($mov_msgball, $to_fldball);
					if ($did_move == False)
					{
						// error
						if ($this->debug) { echo 'email.boaction.delmov: ***ERROR**** $GLOBALS[phpgw]->msg->interacct_mail_move() returns FALSE, ERROR, break out of loop<br>'
								.' * * Server reports error: '.$GLOBALS['phpgw']->msg->phpgw_server_last_error().'<br>'; }
						break;
					}
					else
					{
						if ($this->debug) { echo 'email.boaction.delmov: $GLOBALS[phpgw]->msg->interacct_mail_move() returns True, calling $GLOBALS[phpgw]->msg->phpgw_expunge('.$mov_msgball['acctnum'].')<br>'; }
						$did_expunge = False;
						$did_expunge = $GLOBALS['phpgw']->msg->phpgw_expunge($mov_msgball['acctnum']);
						if ($this->debug) { echo 'email.boaction.delmov: $GLOBALS[phpgw]->msg->phpgw_expunge() returns '.serialize($did_expunge).'<br>'; }
					}
				}
				
				if (! $did_move)
				{
					// ERROR: report ZERO messages moved
					$tm = 0;
					//echo 'Server reports error: '.$GLOBALS['phpgw']->msg->dcom->server_last_error();
				}
				// report folder messages were moved to
				$tf = $GLOBALS['phpgw']->msg->prep_folder_out($to_fldball['folder']);
				// folder we should go back to
				$return_to_fldball['folder'] = $GLOBALS['phpgw']->msg->prep_folder_out($delmov_list[0]['folder']);
				$return_to_fldball['acctnum'] = $delmov_list[0]['acctnum'];
				
				$this->redirect_to = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uiindex.index'
								.'&fldball[folder]='.$return_to_fldball['folder']
								.'&fldball[acctnum]='.$return_to_fldball['acctnum']
								.'&tm='.$tm
								.'&tf='.$tf
								.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
								.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
								.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start'));
				
				$goto_args=array( 
					'folder'  => $return_to_fldball['folder'],
					'acctnum' => $return_to_fldball['acctnum'],
					'tm'	=> $tm,
					'tf'	=> $tf,
					'sort'  => $GLOBALS['phpgw']->msg->get_arg_value('sort'),
					'order'  => $GLOBALS['phpgw']->msg->get_arg_value('order'),
					'start'  => $GLOBALS['phpgw']->msg->get_arg_value('start')
				);
				// end session if we are not going to reuse the current object
				if ($attempt_reuse == False)
				{
					$GLOBALS['phpgw']->msg->end_request();
				}
			}
			// ---- DELETE (MULTIPLE) MESSAGES ----
			elseif ($GLOBALS['phpgw']->msg->get_arg_value('what') == 'delall')
			{
				if ($this->debug) { echo 'emai.boaction.delmov: get_arg_value(what) == "delall") <br>'; }
				// this is called from the index pge after you check some boxes and click "delete" button
				
				$delmov_list = $GLOBALS['phpgw']->msg->get_arg_value('delmov_list');
				$loops = count($delmov_list);
				for ($i = 0; $i < $loops; $i++)
				{
					if ($this->debug) { echo 'email.boaction.delmov: (delete) in mail delete loop ['.(string)($i+1).'] of ['.$loops.']<br>'; }
					$this_msgnum = $delmov_list[$i]['msgnum'];
					// was_in_folder is used in Trash handling in the ->phpgw_delete function
					// if a message "was_in_folder" Trash, it gets deleted for real, no option to move to Trash in that case
					$was_in_folder = $delmov_list[$i]['folder'];
					$was_in_folder_acctnum = (int)$delmov_list[$i]['acctnum'];
					$did_delete = $GLOBALS['phpgw']->msg->phpgw_delete($this_msgnum,'',$was_in_folder);
					if ($did_delete == False)
					{
						// error
						if ($this->debug) { echo 'email.boaction.delmov: (delete) ***ERROR**** $GLOBALS[phpgw]->msg->phpgw_delete() returns FALSE, ERROR, break out of loop<br>'
								.' * * Server reports error: '.$GLOBALS['phpgw']->msg->phpgw_server_last_error().'<br>'; }
						break;
					}
					else
					{
						if ($this->debug) { echo 'email.boaction.delmov: (delete) $GLOBALS[phpgw]->msg->phpgw_delete() returns True, calling $GLOBALS[phpgw]->msg->phpgw_expunge('.$delmov_list[$i]['acctnum'].')<br>'; }
						$did_expunge = False;
						$did_expunge = $GLOBALS['phpgw']->msg->phpgw_expunge((int)$delmov_list[$i]['acctnum']);
						if ($this->debug) { echo 'email.boaction.delmov: (delete) $GLOBALS[phpgw]->msg->phpgw_expunge() returns '.serialize($did_expunge).'<br>'; }
					}
				}
				$totaldeleted = $i;
				//$GLOBALS['phpgw']->msg->phpgw_expunge();
				$this->redirect_to = $GLOBALS['phpgw']->link(
								'/index.php',
								 'menuaction=email.uiindex.index'
								.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out($was_in_folder)
								.'&fldball[acctnum]='.$was_in_folder_acctnum
								.'&td='.$totaldeleted
								.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
								.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
								.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start'));
				$goto_args=array(
					// go back to WHERE>>>> ????
					'folder'  => $GLOBALS['phpgw']->msg->prep_folder_out($was_in_folder),
					'acctnum' => $was_in_folder_acctnum,
					'td'	=> $totaldeleted,
					'sort'  => $GLOBALS['phpgw']->msg->get_arg_value('sort'),
					'order'  => $GLOBALS['phpgw']->msg->get_arg_value('order'),
					'start'  => $GLOBALS['phpgw']->msg->get_arg_value('start')
				);
				// end session if we are not going to reuse the current object
				if ($attempt_reuse == False)
				{
					$GLOBALS['phpgw']->msg->end_request();
				}
			}
			// ---- DELETE A SINGLE MESSAGE  ----
			elseif ($GLOBALS['phpgw']->msg->get_arg_value('what') == "delete_single_msg")
			{
				if ($this->debug) { echo 'emai.boaction.delmov: get_arg_value(what) == "delete_single_msg") <br>'; }
				// called by clicking the "X" dutton while reading an individual message
				$msgball = $GLOBALS['phpgw']->msg->get_arg_value('msgball');
				$GLOBALS['phpgw']->msg->phpgw_delete($msgball['msgnum'],'',$msgball['folder'], (int)$msgball['acctnum']);
				
				
				$nav_data = $GLOBALS['phpgw']->msg->prev_next_navigation($folder_info['number_all']);
				if ($this->debug > 2) { echo 'emai.boaction.delmov: $nav_data[] dump <pre>: '; print_r($nav_data); echo '</pre>'; }
				
				// ----  "Go To Previous Message" Handling  -----
				if ($nav_data['prev_msg'] != $not_set)
				{
					$this->redirect_to = $GLOBALS['phpgw']->link(
						'/index.php',
						 'menuaction=email.uimessage.message'
						.'&'.$nav_data['prev_msg']['msgball']['uri']
						.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
						.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
						.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start'));
				}
				else
				{
					// go back to index page
					$this->redirect_to = $GLOBALS['phpgw']->link(
						'/index.php',
						 'menuaction=email.uiindex.index'
						.'&fldball[folder]='.$GLOBALS['phpgw']->msg->prep_folder_out($msgball['folder'])
						.'&fldball[acctnum]='.$msgball['acctnum']
						.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
						.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
						.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start'));
				}
				
				$GLOBALS['phpgw']->msg->phpgw_expunge((int)$msgball['acctnum']);
				
				// reuse NOT yet supported
				$goto_args=array();
				
				// end session if we are not going to reuse the current object
				// not supported YET for going to message.php
				//if ($attempt_reuse == False)
				//{
					$GLOBALS['phpgw']->msg->end_request();
				//}
			}
			else
			{
				if ($this->debug) { echo 'emai.boaction.delmov: get_arg_value(what) == unknown_value<br>'; }
				$error_str = '<p><center><b>'.lang('UNKNOWN ACTION')."<br> \r\n"
						.'called from '.$GLOBALS['PHP_SELF'].', delmov()'."<br> \r\n"
						.'</b></center></p>'."<br> \r\n";
				$this->redirect_to = $this->redirect_if_error;
				// error report not yet n-tier'd
				$goto_args=array();
				
				// end session if we are not going to reuse the current object
				// not supported YET for error reporting
				//if ($attempt_reuse == False)
				//{
					$GLOBALS['phpgw']->msg->end_request();
				//}
			}
			
			
			if (($attempt_reuse == True)
			&& (count($goto_args) > 0))
			{
				if ($this->debug) { echo 'emai.boaction.delmov: LEAVING, gonna try to reuse existing mail_msg for the upcoming page view<br>'; }
				// attempting to reuse existing object msg
				$obj = CreateObject('email.uiindex');
				$obj->index($goto_args);
				exit;
			}
			elseif ($this->redirect_to != '')
			{
				if ($this->debug) { echo 'emai.boaction.delmov: LEAVING, redirecting to: '.$GLOBALS['phpgw']->redirect($this->redirect_to).'<br>'; }
				$GLOBALS['phpgw']->redirect($this->redirect_to);
				exit;
			}
			else
			{
				if ($this->debug) { echo 'emai.boaction.delmov: LEAVING, with ERROR, unhandled "where to go from here" condition<br>'; }
				echo 'error: mo redirect specified in '.$GLOBALS['PHP_SELF'].', delmov()'."<br> \r\n"
					.'error_str: '.$error_str."<br> \r\n";
				return False;
			}
		}
		
		
		function get_attach()
		{
			$GLOBALS['phpgw_info']['flags']['noheader'] = True;
			$GLOBALS['phpgw_info']['flags']['nonavbar'] = True;
			$GLOBALS['phpgw_info']['flags']['nofooter'] = True;
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug) { echo 'emai.boaction.get_attach: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug) { echo 'emai.boaction.get_attach: is_object test: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			$args_array = Array();
			$args_array['do_login'] = True;
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', get_attach()');
			}
			
			if (!is_object($GLOBALS['phpgw']->browser))
			{
				if ($this->debug) { echo 'emai.boaction.get_attach: is_object test: $GLOBALS[phpgw]->browser needs to be created <br>'; }
				$GLOBALS['phpgw']->browser = CreateObject("phpgwapi.browser");
			}
			
			$msgball = $GLOBALS['phpgw']->msg->get_arg_value('msgball');
			if (!isset($msgball['part_no']))
			{
				$part_no = $GLOBALS['phpgw']->msg->get_arg_value('part_no');
				$msgball['part_no'] = $part_no;
			}
			
			$mime = strtolower($GLOBALS['phpgw']->msg->get_arg_value('type')) .'/' .strtolower($GLOBALS['phpgw']->msg->get_arg_value('subtype'));
			$GLOBALS['phpgw']->browser->content_header($GLOBALS['phpgw']->msg->get_arg_value('name'), $mime);
			
			//echo 'get all args dump<pre>'; print_r($GLOBALS['phpgw']->msg->get_all_args()); echo '</pre>';
			//echo '$mime: ['.$mime.']<br>';
			//echo '$GLOBALS[phpgw]->msg->get_arg_value(encoding): ['.$GLOBALS['phpgw']->msg->get_arg_value('encoding').']<br>';
			
			if ($GLOBALS['phpgw']->msg->get_arg_value('encoding') == 'base64')
			{
				//echo $GLOBALS['phpgw']->msg->de_base64($GLOBALS['phpgw']->msg->phpgw_fetchbody($part_no));
				echo $GLOBALS['phpgw']->msg->de_base64($GLOBALS['phpgw']->msg->phpgw_fetchbody($msgball));
			}
			elseif ($GLOBALS['phpgw']->msg->get_arg_value('encoding') == 'qprint')
			{
				//echo $GLOBALS['phpgw']->msg->qprint($GLOBALS['phpgw']->msg->phpgw_fetchbody($part_no));
				echo $GLOBALS['phpgw']->msg->qprint($GLOBALS['phpgw']->msg->phpgw_fetchbody($msgball));
			}
			else
			{
				//echo $GLOBALS['phpgw']->msg->phpgw_fetchbody($part_no);
				echo $GLOBALS['phpgw']->msg->phpgw_fetchbody($msgball);
			}
			
			$GLOBALS['phpgw']->msg->end_request();
			$GLOBALS['phpgw']->common->phpgw_footer();
		}
		
		function view_html()
		{
			$GLOBALS['phpgw_info']['flags']['noheader'] = True;
			$GLOBALS['phpgw_info']['flags']['nonavbar'] = True;
			$GLOBALS['phpgw_info']['flags']['nofooter'] = True;
			$GLOBALS['phpgw_info']['flags']['noappheader'] = True;
			$GLOBALS['phpgw_info']['flags']['noappfooter'] = True;
			
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug) { echo 'emai.boaction.view_html: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug) { echo 'emai.boaction.view_html: is_object test: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			$args_array = Array();
			$args_array['do_login'] = True;
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', view_html()');
			}
			
			if (!is_object($GLOBALS['phpgw']->browser))
			{
				if ($this->debug) { echo 'emai.boaction.view_html: is_object test: $GLOBALS[phpgw]->browser needs to be created <br>'; }
				$GLOBALS['phpgw']->browser = CreateObject("phpgwapi.browser");
			}
			
			//$GLOBALS['phpgw']->browser->content_header($name,$mime);
			if ((($GLOBALS['phpgw']->msg->get_isset_arg('html_part')))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('html_part') != ''))
			{
				$GLOBALS['phpgw']->browser->content_header('','');
				$html_part = $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('html_part'));
				echo $GLOBALS['phpgw']->msg->de_base64($html_part);
				$GLOBALS['phpgw']->msg->end_request();
			}
			elseif ((($GLOBALS['phpgw']->msg->get_isset_arg('html_reference')))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('html_reference') != ''))
			{
				$html_reference = $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('html_reference'));
				$GLOBALS['phpgw']->msg->end_request();
				//header('Location: ' . $html_reference);
				$GLOBALS['phpgw']->redirect($html_reference);
				$GLOBALS['phpgw']->common->phpgw_footer();
			}
			else
			{
				$GLOBALS['phpgw']->msg->end_request();
				$GLOBALS['phpgw']->common->phpgw_footer();
			}
		}
	
	
	}
?>
