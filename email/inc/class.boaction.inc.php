<?php
	/**************************************************************************\
	* phpGroupWare - email BO Class	for Message Lists				*
	* http://www.phpgroupware.org							*
	* Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
	* xml-rpc and soap code template by Milosch and others				*
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
			'action'	=> True
		);
		var $debug = False;
		var $xml_functions = array();
		var $xi = array();
		var $redirect_to = '';
		var $redirect_if_error = '';
		var $error_str = '';
		
		var $soap_functions = array(
			'action' => array(
				'in'  => array('array'),
				'out' => array('int')
			)
		);
		
		function boaction()
		{
			$this->redirect_if_error = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->index_menuaction);
			
		}
		
		function list_methods($_type='xmlrpc')
		{
			/*
			  This handles introspection or discovery by the logged in client,
			  in which case the input might be an array.  The server always calls
			  this function to fill the server dispatch map using a string.
			*/
			if (is_array($_type))
			{
				$_type = $_type['type'] ? $_type['type'] : $_type[0];
			}
			switch($_type)
			{
				case 'xmlrpc':
					$xml_functions = array(
						'get_langed_labels' => array(
							'function'  => 'action',
							'signature' => array(array(xmlrpcStruct,xmlrpcInt)),
							'docstring' => lang('Move or Delete Messages')
						)
					);
					return $xml_functions;
					break;
				case 'soap':
					return $this->soap_functions;
					break;
				default:
					return array();
					break;
			}
		}
		
		function action()
		{
			// attempt (or not) to reuse an existing mail_msg object LEAVING here and going back to index
			// that index page will attempt to reuse the object we create in this function, i.e. we create it always
			// because it's not supported to reuse mail_msg object that already exists when ENTERING into this function
			$attempt_reuse = True;			
			//$attempt_reuse = False;
			
			$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			$GLOBALS['phpgw']->msg->grab_class_args_gpc();
			$args_array = Array();
			if (isset($GLOBALS['HTTP_POST_VARS']['folder']))
			{
				$args_array['folder'] = $GLOBALS['HTTP_POST_VARS']['folder'];
			}
			elseif (isset($GLOBALS['HTTP_GET_VARS']['folder']))
			{
				$args_array['folder'] = $GLOBALS['HTTP_GET_VARS']['folder'];
			}
			$args_array['do_login'] = True;
			$GLOBALS['phpgw']->msg->begin_request($args_array);
			if (($args_array['do_login'] == True)
			&& (!$GLOBALS['phpgw']->msg->mailsvr_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', action()');
			}
			// base http URI on which we will add other stuff down below
			//$this->index_base_link = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->index_menuaction);
			//$this->xi['sortbox_action'] = $this->index_base_link.'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('');




			$folder_info = array();
			$folder_info = $GLOBALS['phpgw']->msg->folder_status_info();
			$totalmessages = $folder_info['number_all'];
			
			// ---- MOVE Messages from folder to folder   -----
			if ($GLOBALS['phpgw']->msg->args['what'] == "move")
			{
				// called by the "move selected messages to" listbox onChange action
				$tofolder = $GLOBALS['phpgw']->msg->prep_folder_in($GLOBALS['phpgw']->msg->args['tofolder']);
				// report number messages moved (will be made = 0 if error below)
				$tm = count($GLOBALS['phpgw']->msg->args['msglist']);
				$msgs = $GLOBALS['phpgw']->msg->args['msglist'] ? implode($GLOBALS['phpgw']->msg->args['msglist'], ",") : $GLOBALS['phpgw']->msg->args['msglist'];
				// mail_move accepts a single number (5); a comma seperated list of numbers (5,6,7,8); or a range with a colon (5:8)
				/*
				if (count($GLOBALS['phpgw']->msg->args['msglist']) > 1)
				{
					$msgs = implode($GLOBALS['phpgw']->msg->args['msglist'], ",");
				}
				else
				{
					$msgs = $GLOBALS['phpgw']->msg->args['msglist'];
				}
				*/
				if (! $GLOBALS['phpgw']->msg->phpgw_mail_move($msgs, $tofolder))
				{
					// ERROR: report ZERO messages moved
					$tm = 0;
					//echo 'Server reports error: '.$GLOBALS['phpgw']->msg->dcom->server_last_error();
				}
				else
				{
					// expunge moved messages in from folder, they are marked as expungable after the move
					$GLOBALS['phpgw']->msg->phpgw_expunge();
				}
				// report folder messages were moved to
				$tf = $GLOBALS['phpgw']->msg->prep_folder_out($tofolder);
				// end session if we are not going to reuse the current object
				if ($attempt_reuse == False)
				{
					$GLOBALS['phpgw']->msg->end_request();
				}
				$this->redirect_to = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->index_menuaction
							.'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
							.'&tm='.$tm
							.'&tf='.$tf
							.'&sort='.$GLOBALS['phpgw']->msg->sort
							.'&order='.$GLOBALS['phpgw']->msg->order
							.'&start='.$GLOBALS['phpgw']->msg->start);
				$goto_args=array( 
					'folder'  => $GLOBALS['phpgw']->msg->prep_folder_out(''),
					'tm'	=> $tm,
					'tf'	=> $tf,
					'sort'  => $GLOBALS['phpgw']->msg->sort,
					'order'  => $GLOBALS['phpgw']->msg->order,
					'start'  => $GLOBALS['phpgw']->msg->start
				);
			}
			elseif ($GLOBALS['phpgw']->msg->args['what'] == 'delall')
			{
				// this is called from the index pge after you check some boxes and click "delete" button
				for ($i = 0; $i < count($GLOBALS['phpgw']->msg->args['msglist']); $i++)
				{
					$GLOBALS['phpgw']->msg->phpgw_delete($GLOBALS['phpgw']->msg->args['msglist'][$i],'',$GLOBALS['phpgw']->msg->folder);
				}
				$totaldeleted = $i;
				$GLOBALS['phpgw']->msg->phpgw_expunge();
				// end session if we are not going to reuse the current object
				if ($attempt_reuse == False)
				{
					$GLOBALS['phpgw']->msg->end_request();
				}
				$this->redirect_to = $GLOBALS['phpgw']->link('/index.php',$GLOBALS['phpgw']->msg->index_menuaction
								.'&folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
								.'&td='.$totaldeleted
								.'&sort='.$GLOBALS['phpgw']->msg->sort
								.'&order='.$GLOBALS['phpgw']->msg->order
								.'&start='.$GLOBALS['phpgw']->msg->start);
				$goto_args=array(
					'folder'  => $GLOBALS['phpgw']->msg->prep_folder_out(''),
					'td'	=> $totaldeleted,
					'sort'  => $GLOBALS['phpgw']->msg->sort,
					'order'  => $GLOBALS['phpgw']->msg->order,
					'start'  => $GLOBALS['phpgw']->msg->start
				);
			}
			elseif ($GLOBALS['phpgw']->msg->args['what'] == "delete")
			{
				// called by clicking the "X" dutton while reading an individual message
				//$GLOBALS['phpgw']->dcom->delete($GLOBALS['phpgw']->msg->mailsvr_stream, $GLOBALS['phpgw']->msg->msgnum,'',$GLOBALS['phpgw']->msg->folder);
				$GLOBALS['phpgw']->msg->phpgw_delete($GLOBALS['phpgw']->msg->msgnum,'',$GLOBALS['phpgw']->msg->folder);
				if (($totalmessages != $GLOBALS['phpgw']->msg->msgnum)
				|| ($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting'] == 'new_old'))
				{
					if ($GLOBALS['phpgw_info']['user']['preferences']['email']['default_sorting'] == 'new_old')
					{
						$nm = $GLOBALS['phpgw']->msg->msgnum - 1;
					}
					else
					{
						$nm = $GLOBALS['phpgw']->msg->msgnum;
					}
				}
				$GLOBALS['phpgw']->msg->phpgw_expunge();
				// end session if we are not going to reuse the current object
				// not supported YET for going to message.php
				//if ($attempt_reuse == False)
				//{
					$GLOBALS['phpgw']->msg->end_request();
				//}
				$this->redirect_to = $GLOBALS['phpgw']->link('/'.$GLOBALS['phpgw_info']['flags']['currentapp'].'/message.php',
								 'folder='.$GLOBALS['phpgw']->msg->prep_folder_out('')
								.'&msgnum='.$nm
								.'&sort='.$GLOBALS['phpgw']->msg->sort
								.'&order='.$GLOBALS['phpgw']->msg->order);
				// message.php not yet n-tier's
				$goto_args=array();
			}
			else
			{
				$error_str = '<p><center><b>'.lang('UNKNOWN ACTION')."<br> \r\n"
						.'called from '.$GLOBALS['PHP_SELF'].', action()'."<br> \r\n"
						.'</b></center></p>'."<br> \r\n";
				// end session if we are not going to reuse the current object
				// not supported YET for error reporting
				//if ($attempt_reuse == False)
				//{
					$GLOBALS['phpgw']->msg->end_request();
				//}
				$this->redirect_to = $this->redirect_if_error;
				// error report not yet n-tier'd
				$goto_args=array();
			}
			
			if (($attempt_reuse == True)
			&& (count($goto_args) > 0))
			{
				// attempting to reuse existing object msg
				$obj = CreateObject('email.uiindex');
				$obj->index($goto_args);
				exit;
			}
			elseif ($this->redirect_to != '')
			{
				$GLOBALS['phpgw']->redirect($this->redirect_to);
				exit;
			}
			else
			{
				echo 'error: mo redirect specified in '.$GLOBALS['PHP_SELF'].', action()'."<br> \r\n"
					.'error_str: '.$error_str."<br> \r\n";
				return False;
			}
		}
	}
?>
