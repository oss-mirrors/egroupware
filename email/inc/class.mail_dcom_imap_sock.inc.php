<?php
  /**************************************************************************\
  * phpGroupWare API - NNTP                                                  *
  * This file written by Mark Peters <skeeter@phpgroupware.org>              *
  * Handles specific operations in dealing with NNTP                         *
  * Copyright (C) 2001 Mark Peters                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
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

	class mail_dcom extends mail_dcom_base
	{		
		/**************************************************************************\
		*	Functions NOT YET IMPLEMENTED
		\**************************************************************************/
		function createmailbox($stream,$mailbox) 
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'imap: call to unimplemented socket function: createmailbox<br>'; }
			return true;
		}
		function deletemailbox($stream,$mailbox)
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'imap: call to unimplemented socket function: deletemailbox<br>'; }
			return true;
		}
		function expunge($stream)
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'imap: call to unimplemented socket function: expunge<br>'; }
			return true;
		}
		function listmailbox($stream,$ref,$pattern)
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'imap: call to unimplemented socket function: listmailbox (could also namespace discovery attempt)<br>'; }
			return False;
		}
		function mailcopy($stream,$msg_list,$mailbox,$flags)
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'imap: call to unimplemented socket function: mailcopy<br>'; }
			return False;
		}
		function mail_move($stream,$msg_list,$mailbox)
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'imap: call to unimplemented socket function: mail_move<br>'; }
			return False;
		}
		function reopen($stream,$mailbox,$flags = "")
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'imap: call to unimplemented socket function: reopen<br>'; }
			return False;
		}
		function append($stream, $folder = "Sent", $header, $body, $flags = "")
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'imap: call to unimplemented socket function: append<br>'; }
			return False;
		}
		function fetch_overview($stream,$sequence,$flags)
		{
			// not yet implemented
			if ($this->debug_dcom) { echo 'pop3: call to not-yet-implemented socket function: fetch_overview<br>'; }
			return False;
		}
		
		// OBSOLETED
		function login ($user,$passwd,$server,$port,$folder = '')
		{
			global $phpgw;
			
			if (!$this->open_port($server,$port,15))
			{
				echo "<p><center><b>" . lang("There was an error trying to connect to your IMAP server.<br>Please contact your admin to check the servername, username and password.")."</b></center>";
				$phpgw->common->phpgw_exit();
			}
			else
			{
				$this->read_port();
			}
			
			if(!$this->msg2socket('a001 LOGIN "'.quotemeta($user).'" "'.quotemeta($passwd).'"','^a001 OK',&$response))
			{
				$this->error();
			}
			
			if($folder != '')
			{
				$this->folder = $folder;
				$this->open_folder($folder);
				$this->num_msgs = $this->status_query($folder,'MESSAGES');
			}
			echo "Successful IMAP Login!<br>\n";
		}

		/**************************************************************************\
		*	OPEN and CLOSE Server Connection
		\**************************************************************************/
		function open ($fq_folder, $user, $pass, $flags='')
		{
			global $phpgw;
			
			if ($this->debug_dcom) { echo 'imap: Entering open<br>'; }
			
			// fq_folder is a "fully qualified folder", seperate the parts:
			$svr_data = array();
			$svr_data = $this->distill_fq_folder($fq_folder);
			$folder = $svr_data['folder'];
			$server = $svr_data['server'];
			$port = $svr_data['port'];
			if ($this->debug_dcom) { echo 'imap: open: svr_data:<br>'.serialize($svr_data).'<br>'; }
			
			if (!$this->open_port($server,$port,15))
			{
				echo '<p><center><b>' .lang('There was an error trying to connect to your IMAP server.<br>Please contact your admin to check the servername, username or password.') .'</b></center>';
				$phpgw->common->phpgw_exit();
			}
			else
			{
				$junk = $this->read_port();
				if ($this->debug_dcom_extra) { echo 'imap: open: open port junk: "' .htmlspecialchars($this->show_crlf($junk)) .'"<br>'; }
			}
			
			if ($this->debug_dcom_extra) { echo 'imap: open: msg2socket: issue: '. 'a001 LOGIN "'.quotemeta($user).'" "'.quotemeta($pass).'"' .'<br>'; }			
			if ($this->debug_dcom_extra) { echo 'imap: open: msg2socket: expect: '. '^a001 OK' .'<br>'; }
			
			if(!$this->msg2socket('a001 LOGIN "'.quotemeta($user).'" "'.quotemeta($pass).'"','^a001 OK',&$response))
			{
				if ($this->debug_dcom_extra) { echo 'imap: open: response: "'. htmlspecialchars($response) .'"<br>'; }
				if ($this->debug_dcom) { echo 'imap: Leaving open with Error<br>'; }
				$this->error();
				return False;
			}
			else
			{
				if ($this->debug_dcom_extra) { echo 'imap: open: response: '. htmlspecialchars($response) .'<br>'; }
				if ($this->debug_dcom) { echo 'imap: open: Successful IMAP Login<br>'; }
			}
			
			//if($folder != '')
			//{
			//	$this->folder = $folder;
			//	if ($this->debug_dcom) { echo 'imap: open: attempt to open folder ['.$folder.']<br>'; }
			//	$this->open_folder($folder);
			//	$this->num_msgs = $this->status_query($folder,'MESSAGES');
			//}
			
			if($folder != '')
			{
				$this->reopen('',$fq_folder);
			}
			if ($this->debug_dcom) { echo 'imap: Leaving open<br>'; }
			return $this->socket;
		}
		
		function close($flags="")
		{
			if ($this->debug_dcom) { echo 'imap: Entering Close<br>'; }
			/*
			if ($this->debug_dcom_extra) { echo 'imap: close: issuing: '. 'a001 LOGOUT' .'<br>'; }			
			if ($this->debug_dcom_extra) { echo 'imap: close: expecting: '. '^\001' .'<br>'; }
			if (!$this->msg2socket('a001 LOGOUT',"^\001",&$response))
			{
				if ($this->debug_dcom_extra) { echo 'imap: close: response: '. htmlspecialchars($response) .'<br>'; }
				if ($this->debug_dcom) { echo 'imap: close: Error<br>'; }
				//return False;
				//$this->error();
				
				// return TRUE for debugging purposes
				if ($this->debug_dcom) { echo 'imap: close: thinks there is an Error, return True anyway<br>'; }
				return True;
			}
			else
			{
				if ($this->debug_dcom_extra) { echo 'imap: close: response: '. htmlspecialchars($response) .'<br>'; }
				if ($this->debug_dcom) { echo 'imap: close: Successful IMAP Logout<br>'; }
				return True;
			}
			*/
			if ($this->debug_dcom_extra) { echo 'imap: close: write_port: '. 'a001 LOGOUT' .'<br>'; }
			if(!$this->write_port('a001 LOGOUT'))
			{
				if ($this->debug_dcom) { echo 'imap: close: could not write_port<br>'; }
				$this->error();
			}
			
			$expected = 'a001 OK';
			if ($this->debug_dcom_extra) { echo 'imap: close: set expected: "'. htmlspecialchars($expected) .'"<br>'; }
			
			// server can spew some bs goodbye bessage before the official response
			// so TRY THIS 3 TIMES before failing
			for ($i=1; $i<4; $i++)
			{
				if ($this->debug_dcom) { echo 'imap: close: reading port try # '.$i.'<br>'; }
				
				$response = $this->read_port();
				// do this for debugging, it will not effect this statement anyway
				$response = $this->show_crlf($response);
				if ($this->str_begins_with($response, $expected) == False)
				{
					if ($this->debug_dcom_extra) { echo 'imap: close: NOT expected: "'. htmlspecialchars($response) .'"<br>'; }
				}
				else
				{
					if ($this->debug_dcom_extra) { echo 'imap: close: got expected: "'. htmlspecialchars($response) .'"<br>'; }
					if ($this->debug_dcom) { echo 'imap: Leaving Close<br>'; }
					return True;
					// return implicitly breaks us out of this loop and exits this function
				}
			}
			
			if ($this->debug_dcom_extra) { echo 'imap: Leaving Close with Error: could not logout<br>'; }
			//return False;
		}
		
		/*!
		@function reopen
		@abstract implements last part of IMAP_OPEN and all of IMAP_REOPEN
		@param $stream_notused : socket class handles stream reference internally
		@param $fq_folder : string : "fully qualified folder" {SERVER_NAME:PORT/OPTIONS}FOLDERNAME
		@param $flags : Not Used in helper function
		@result boolean True on success or False on error
		@discussion  ?
		@author Angles
		@access	public
		*/
		function reopen($stream_notused, $fq_folder, $flags='')
		{
			if ($this->debug_dcom) { echo 'imap: Entering reopen<br>'; }
			
			// fq_folder is a "fully qualified folder", seperate the parts:
			$svr_data = array();
			$svr_data = $this->distill_fq_folder($fq_folder);
			$folder = $svr_data['folder'];
			if ($this->debug_dcom) { echo 'imap: reopen: folder value is: ['.$folder.']<br>'; }
			
			if(!$this->write_port('a001 SELECT "'.$folder.'"'))
			{
				if ($this->debug_dcom) { echo 'imap: Leaving reopen with error, could not write to port<br>'; }
				$this->error();
			}
			
			$expected = 'a001 OK';
			if ($this->debug_dcom_extra) { echo 'imap: reopen: set expected: "'. htmlspecialchars($expected) .'"<br>'; }
			
			$found = False;
			// DEBUG!!!!!  do this max 100 times
			for ($i=1; $i<101; $i++)
			{
				if ($this->debug_dcom) { echo 'imap: reopen: reading port try # '.$i.'<br>'; }
				
				$response = $this->read_port();
				// do this for debugging, it will not effect this statement anyway
				$response = $this->show_crlf($response);
				if ($this->str_begins_with($response, $expected) == False)
				{
					if ($this->debug_dcom_extra) { echo 'imap: reopen: NOT expected: "'. htmlspecialchars($response) .'"<br>'; }
				}
				else
				{
					if ($this->debug_dcom_extra) { echo 'imap: reopen: got expected: "'. htmlspecialchars($response) .'"<br>'; }
					$found = True;
					break;
				}
			}
			
			
			if ($this->debug_dcom) { echo 'imap: Leaving reopen<br>'; }
			return True;
			//return False;
		}
		
		function status_query($folder,$field)
		{
			if(!$this->write_port('a001 STATUS '.$folder.' ('.$field.')'))
			{
				$this->error();
			}
			$response = $this->read_port();
			//echo 'Response = '.$response."<br>\n";
			while(!ereg('OK STATUS completed',$response))
			{
				if(ereg("\($field ([0-9]+)\)",$response,$regs))
				{
					while(!ereg('OK STATUS completed',$response))
					{
						$response = $this->read_port();
					}
					return $regs[1];
				}
				$response = $this->read_port();
				//echo 'Response = '.$response."<br>\n";
			}
			return False;
		}
		
		
		function fix_folder($folder)
		{
			global $phpgw_info;
			
			switch($phpgw_info['user']['preferences']['email']['imap_server_type'])
			{
				case 'UW-Maildir':
					if (isset($phpgw_info['user']['preferences']['email']['mail_folder']))
					{
						if (empty($phpgw_info['user']['preferences']['email']['mail_folder']))
						{
							$folder = $folder;
						}
						else
						{
							$folder = $phpgw_info['user']['preferences']['email']['mail_folder'].$folder;
						}
					}
					break;
				case 'Cyrus':
					$folder = 'INBOX.'.$folder;
					break;
				default:
					$folder = 'mail/'.$folder;
					break;
			}
			return $folder;
		}
		
		/**************************************************************************\
		*	Mailbox Status and Information
		\**************************************************************************/
		
		function mailboxmsginfo($stream_notused='')
		{
			if ($this->debug_dcom) { echo 'imap: mailboxmsginfo<br>'; }
			return False;
		}
		
		/*
		function mailboxmsginfo($folder='')
		{
			$info = new msg_mb_info;
			if($folder=='' || $folder==$this->folder)
			{
				$info->messages = $this->num_msgs;
				if ($info->messages)
				{
					$info->size = $this->total($this->fetch_field(1,$info->messages,'RFC822.SIZE'));
					return $info;
				}
				else
				{
					return False;
				}
			}
			else
			{
				$mailbox = $folder;
			}
			
			$info->messages = $this->num_msgs($mailbox);
			$info->size  = $this->total($this->fetch_field(1,$info->messages,'RFC822.SIZE'));
			
			if ($info->messages)
			{
				return $info;
			}
			else
			{
				return False;
			}
		}
		*/
		
		function status($stream_notused='', $fq_folder='',$options=SA_ALL)
		{
			if ($this->debug_dcom) { echo 'imap: status<br>'; }
			return False;
		}
		
		/*
		function status($folder='',$options=SA_ALL)
		{
			if($folder == '')
			{
				$folder = $this->mailbox;
			}
			$info = new mailbox_status;
			$loop = Array(
				SA_MESSAGES	=> 'messages',
				SA_RECENT	=> 'recent',
				SA_UNSEEN	=> 'unseen',
				SA_UIDNEXT	=> 'uidnext',
				SA_UIDVALIDITY	=> 'uidvalidity'
			);
			@reset($loop);
			while(list($key,$value) = each($loop))
			{
				if($options & $key)
				{
					$info->$value = $this->status_query($folder,strtoupper($value));
				}
			}
			return $info;
		}
		*/
		
		function num_msg($folder='')
		{
			if($folder == '' || $folder == $this->folder)
			{
				return $this->num_msgs;
			}
			return $this->status_query($folder,'MESSAGES');
		}
	
		function total($field)
		{
			$total = 0;
			reset($field);
			while(list($key,$value) = each($field))
			{
				$total += intval($value);
			}
			return $total;
		}
					
		/**************************************************************************\
		*	Message Sorting
		\**************************************************************************/
		function sort($stream_notused='',$criteria=SORTARRIVAL,$reverse=False,$options='')
		{
			if ($this->debug_dcom) { echo 'imap: sort<br>'; }
			return False;
		}
		
		/*
		function sort($folder='',$criteria=SORTDATE,$reverse=False,$options='')
		{
			if($folder == '' || $folder == $this->mailbox)
			{
				$folder = $this->mailbox;
			$num_msgs = $this->num_msgs;
			}
			else
			{
				// WHAT ???
			}
			
			switch($criteria)
			{
				case SORTDATE:
					$old_list = $this->fetch_header(1,$this->num_msgs,'Date:');
					$field_list = $this->convert_date_array($old_list);
					break;
				case SORTARRIVAL:
					break;
				case SORTFROM:
					$field_list = $this->fetch_header(1,$this->num_msgs,'From:');
					break;
				case SORTSUBJECT:
					$field_list = $this->fetch_header(1,$this->num_msgs,'Subject:');
					break;
				case SORTTO:
					$field_list = $this->fetch_header(1,$this->num_msgs,'To:');
					break;
				case SORTCC:
					$field_list = $this->fetch_header(1,$this->num_msgs,'cc:');
					break;
				case SORTSIZE:
					$field_list = $this->fetch_field(1,$this->num_msgs,'RFC822.SIZE');
					break;
			}
			@reset($field_list);
			if($criteria == SORTSUBJECT)
			{
				if(!$reverse)
				{
					uasort($field_list,array($this,"ssort_ascending"));
				}
				else
				{
					uasort($field_list,array($this,"ssort_decending"));
				}			
			}
			elseif(!$reverse)
			{
				asort($field_list);
			}
			else
			{
				arsort($field_list);
			}
			$return_array = Array();
			@reset($field_list);
			$i = 1;
			while(list($key,$value) = each($field_list))
			{
				$return_array[] = $key;
				//echo '('.$i++.') Field: <b>'.$value."</b>\t\tMsg Num: <b>".$key."</b><br>\n";
			}
			@reset($return_array);
			return $return_array;
		}
		*/
		
		/**************************************************************************\
		*
		*	Message Structural Information
		*
		\**************************************************************************/
		function fetchstructure($stream_notused,$msg_num,$flags="")
		{
			// outer control structure for the multi-pass functions
			if ($this->debug_dcom) { echo 'imap: fetchstructure<br>'; }
			return False;
		}
		
		/*
		function fetchstructure($msgnum)
		{
			
			if(!$this->write_port('a001 FETCH '.$msgnum.' BODY[HEADER]'))
			//if(!$this->write_port('a001 FETCH '.$msgnum.' BODY.PEEK[HEADER.FIELDS (Date To From Cc Subject Message-Id X-Priority Content-Type)]'))
			{
				$this->error();
			}
			$this->header = Null;
			$response = $this->read_port();
			while(!ereg('^a001 OK FETCH completed',$response))
			{
				if(!ereg('^\* '.$msgnum.' FETCH \(BODY\[HEADER',$response) && chop($response) != '' && chop($response) != ')')
				{
					echo 'Response = '.$response."<br>\n";
					$this->create_header($response,&$this->header,"True");
				}
				$response = $this->read_port();
			}
			echo '<b>'.$msgnum.'</b> Completed!'."<br>\n";
			if(!$this->write_port('a001 FETCH '.$msgnum.' BODY[TEXT]'))
			{
				$this->error();
			}
			$response = $this->read_port();
			while(!ereg('^a001 OK FETCH completed',$response))
			{
				echo 'Response = '.$response."<br>\n";
				$response = $this->read_port();
			}
			return $this->header;
		}
		*/
		
		
		/**************************************************************************\
		*	Message Envelope (Header Info) Data
		\**************************************************************************/
		function header($stream_notused,$msg_num,$fromlength="",$tolength="",$defaulthost="")
		{
			if ($this->debug_dcom) { echo 'imap: header<br>'; }
			return False;
		}
		
		
		/**************************************************************************\
		*	More Data Communications (dcom) With IMAP Server
		\**************************************************************************/
	
		/**************************************************************************\
		*	DELETE a Message From the Server
		\**************************************************************************/
		function delete($stream_notused,$msg_num,$flags="")
		{
			if ($this->debug_dcom) { echo 'imap: delete<br>'; }
			return False;
		}
		
		
		/**************************************************************************\
		*	Get Message Headers From Server
		\**************************************************************************/
		function fetchheader($stream_notused,$msg_num,$flags='')
		{
			// NEEDED: code for flags: FT_UID; FT_INTERNAL; FT_PREFETCHTEXT
			if ($this->debug_dcom) { echo 'imap: fetchheader<br>'; }
			return False;
		}
		
		function fetch_header($start,$stop,$element)
		{
			if(!$this->write_port('a001 FETCH '.$start.':'.$stop.' RFC822.HEADER'))
			{
				$this->error();
			}
			for($i=$start;$i<=$stop;$i++)
			{
				$response = $this->read_port();
				//while(!ereg('FETCH completed',$response))
				while(chop($response)!='')
				{
					//echo 'Response = '.$response."<br>\n";
					if(ereg('^\*',$response))
					{
						$field = explode(' ',$response);
						$msg_num = $field[1];
					}
					if(ereg('^'.$element,$response))
					{
						$field_element[$msg_num] = $this->phpGW_quoted_printable_decode2(substr($response,strlen($element)+1));
						//echo '<b>Field:</b> '.$field_element[$msg_num]."\t = <b>Msg Num</b> ".$msg_num."<br>\n";
					}
					elseif(ereg('^'.strtoupper($element),$response))
					{
						$field_element[$msg_num] = $this->phpGW_quoted_printable_decode2(substr($response,strlen(strtoupper($element))+1));
						//echo '<b>Field:</b> '.$field_element[$msg_num]."\t = <b>Msg Num</b> ".$msg_num."<br>\n";
					}
					$response = $this->read_port();
				}
				$response = $this->read_port();
			}
			$response = $this->read_port();
			return $field_element;
		}
		
		
		function fetch_field($start,$stop,$element)
		{
			if(!$this->write_port('a001 FETCH '.$start.':'.$stop.' '.$element))
			{
				$this->error();
			}
			$response = $this->read_port();
			while(!ereg('FETCH completed',$response))
			{
				//echo 'Response = '.$response."<br>\n";
				$field = explode(' ',$response);
				$msg_num = intval($field[1]);
				$field_element[$msg_num] = substr($field[4],0,strpos($field[4],')'));
				//echo '<b>Field:</b> '.substr($field[4],0,strpos($field[4],')'))."\t = <b>Msg Num</b> ".$field_element[substr($field[4],0,strpos($field[4],')'))]."<br>\n";
				$response = $this->read_port();
			}
			return $field_element;
		}		
		
		
		/**************************************************************************\
		*	Get Message Body (Parts) From Server
		\**************************************************************************/
		function fetchbody($stream_notused,$msg_num,$part_num="",$flags="")
		{
			if ($this->debug_dcom) { echo 'imap: fetchbody<br>'; }
			return False;
		}
		
		/*!
		@function get_body
		@abstract implements IMAP_BODY
		*/
		function get_body($stream_notused,$msg_num,$flags='',$phpgw_include_header=True)
		{
			// NEEDED: code for flags: FT_UID; maybe FT_INTERNAL; FT_NOT; flag FT_PEEK has no effect on POP3
			if ($this->debug_dcom) { echo 'imap: get_body<br>'; }
			return False;
		}
		
	}

?>
