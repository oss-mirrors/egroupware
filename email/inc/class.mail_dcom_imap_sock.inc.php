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
		*	data analysis specific to IMAP data communications
		\**************************************************************************/

		/*!
		@function str_begins_with
		@abstract determine if string $haystack begins with string $needle
		@param $haystack : string : data to examine to determine if it starts with $needle
		@param $needle : string : $needle should or should not start at position 0 (zero) of $haystack
		@result  Boolean, True or False
		@discussion this is a NON-REGEX way to to so this, and is NOT case sensitive
		this *should* be faster then Regular expressions and *should* not be confused by
		regex special chars such as the period "." or the slashes "/" and "\" , etc...
		@syntax ?
		@author Angles
		@access	public or private
		*/
		function str_begins_with($haystack,$needle='')
		{
			if ((trim($haystack) == '')
			|| (trim($needle) == ''))
			{
				return False;
			}
			/*
			// now do a case insensitive search for needle as the beginning part of haystack
			if (stristr($haystack,$needle) == False)
			{
				// needle is not anywhere in haystack
				return False;
			}
			// so needle IS in haystack
			// now see if needle is the same as the begining of haystack (case insensitive)
			if (strpos(strtolower($haystack),strtolower($needle)) == 0)
			{
				// in this case we know 0 means "at position zero" (i.e. NOT "could not find")
				// because we already checked for the existance of needle above
				return True;
			}
			else
			{
				return False;
			}
			*/
			// now do a case insensitive search for needle as the beginning part of haystack
			// stristr returns everything in haystack from the 1st occurance of needle (including needle itself)
			//   to the end of haystack, OR returns FALSE if needle is not in haystack
			$stristr_found = stristr($haystack,$needle);
			if ($stristr_found == False)
			{
				// needle is not anywhere in haystack
				return False;
			}
			// so needle IS in haystack
			// if needle starts at the beginning of haystack then stristr will return the entire haystack string
			// thus strlen of $stristr_found and $haystack would be the same length
			if (strlen($haystack) == strlen($stristr_found))
			{
				// needle DOES begin at position zero of haystack
				return True;
			}
			else
			{
				// where ever needle is, it is NOT at the beginning of haystack
				return False;
			}
		}
		
		/*!
		@function imap_read_port
		@abstract reads data from an IMAP server until the line that begins with the specified param "tag"
		@param $end_begins_with : string is the special string that indicates a server is done sending data
		this is generally the same "tag" identifier that the client sent when initiate the command, ex. "A001"
		@result  array where each line of the server data exploded at every CRLF pair into an array
		@discussion IMAP servers send out data that is fairly well "typed", meaning RFC2060
		is pretty strict about what the server may send out, allowing the client (us) to more easily
		interpet this data. The important indicator is the string at the beginning of each line of data
		from the server, it can be:
		"*" (astrisk) = "untagged" =  means "this line contains server data and more data will follow"
		"+" (plus sign) means "you, the client, must now finish sending your data to the server"
		"tagged" is the command tag that the client used to initiate this command, such as "A001"
		IMAP server's final line of data for that command will contain that command's tag as sent from the client
		This tagged "command completion" signal is followed by either:
		"OK" = successful command completion
		"NO" = failure of some kind
		"BAD" = protocol error such as unrecognized command or syntax error, client should abort this command processing
		@syntax ?
		@author Angles, skeeter
		@access	private
		*/
		function imap_read_port($end_begins_with='')
		{
			$return_me = Array();
			// is we do not know what to look for as an end tag, then abort
			if ($end_begins_with == '')
			{
				return $return_me;
			}
			// read the data until a tagged command completion is encountered
			while ($line = $this->read_port())
			{
				if ($this->str_begins_with($line, $end_begins_with))
				{
					// error analysis if not OK
					// put that error string into $this->server_last_error_str
					if ((stristr($line, 'NO'))
					|| (stristr($line, 'BAD')))
					{
						$this->server_last_error_str = $line;
						// what should we return here IF there was a NO or BAD error ?
						// how about an empty array, how about FALSE ??
						
						// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
						// empty the array
						$return_me = Array();
					}
					else
					{
						// we got a tagged command response OK
						// but if we send an empty array under this test error scheme
						// calling function will think there was an error
						// DECISION: if array is count zero, put this OK line in it
						// otherwise array already had valid server data in it
						// and we do not want to add this OK line which is NOT actually data
						if (count($return_me) == 0)
						{
							// add this OK line just to return a NON empty array
							$return_me[0] = $line;
						}
						else
						{
							// do nothing, valid server data exists
						}
					}
					// in any case (OK, BAD or NO) we reached the end of server data
					// so we must break out of this loop
					break;
				}
				$next_pos = count($return_me);
				$return_me[$next_pos] = $line;
			}
			return $return_me;
		}
		
		/*!
		@function server_last_error
		@abstract implements IMAP_LAST_ERROR
		@result  string
		@discussion ?
		@syntax ?
		@author Angles
		@access	public
		*/
		function server_last_error()
		{
			if ($this->debug_dcom) { echo 'imap: call to server_last_error<br>'; }
			//return 'unimplemented error detection in class imap sock';
			return $this->server_last_error_str;
		}
		
		
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
			if ($this->debug_dcom) { echo 'imap: call to not-yet-implemented socket function: fetch_overview<br>'; }
			return False;
		}
	
		
		/**************************************************************************\
		*	OPEN and CLOSE Server Connection
		\**************************************************************************/
		function open ($fq_folder, $user, $pass, $flags='')
		{
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
				$GLOBALS['phpgw']->common->phpgw_exit();
			}
			else
			{
				$junk = $this->read_port();
				if ($this->debug_dcom_extra) { echo 'imap: open: open port server hello: "' .htmlspecialchars($this->show_crlf($junk)) .'"<br>'; }
			}
			
			if ($this->debug_dcom_extra) { echo 'imap: open: msg2socket: will issue: '. 'L001 LOGIN "'.quotemeta($user).'" "'.quotemeta($pass).'"' .'<br>'; }
			if ($this->debug_dcom_extra) { echo 'imap: open: msg2socket: will expect: '. '^L001 OK' .'<br>'; }
			
			if(!$this->msg2socket('L001 LOGIN "'.quotemeta($user).'" "'.quotemeta($pass).'"','^L001 OK',&$response))
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
			// php's IMAP_OPEN also selects the desired folder (mailbox) after the connection is established
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
			
			$issue_command = 'c001 LOGOUT';
			//$expecting = 'c001 OK';
			$expecting = 'c001 '; // may not be OK, could be BAD or NO
			
			if ($this->debug_dcom_extra) { echo 'imap: close: write_port: "'. htmlspecialchars($issue_command) .'"<br>'; }			
			if ($this->debug_dcom_extra) { echo 'imap: close: expecting: "'. htmlspecialchars($expecting) .'"<br>'; }

			if(!$this->write_port($issue_command))
			{
				if ($this->debug_dcom) { echo 'imap: close: could not write_port<br>'; }
				$this->error();
			}
			
			/*
			// server can spew some bs goodbye message before the official response
			// so TRY THIS 3 TIMES before failing
			for ($i=1; $i<4; $i++)
			{
				if ($this->debug_dcom_extra) { echo 'imap: close: reading port try # '.$i.'<br>'; }
				
				$response = $this->read_port();
				// do this for debugging, it will not effect this statement anyway
				$response = $this->show_crlf($response);
				if ($this->str_begins_with($response, $expecting) == False)
				{
					if ($this->debug_dcom_extra) { echo 'imap: close: NOT expecting: "'. htmlspecialchars($response) .'"<br>'; }
				}
				else
				{
					if ($this->debug_dcom_extra) { echo 'imap: close: got expecting: "'. htmlspecialchars($response) .'"<br>'; }
					if ($this->debug_dcom) { echo 'imap: Leaving Close<br>'; }
					return True;
					// return implicitly breaks us out of this loop and exits this function
				}
			}
			if ($this->debug_dcom_extra) { echo 'imap: Leaving Close with Error: could not logout<br>'; }
			return False;
			*/
			// read the server data
			$response_array = $this->imap_read_port($expecting);
			
			// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
			if (count($response_array) == 0)
			{
				if ($this->debug_dcom) { echo 'imap: Leaving Close with error<br>'; }
				return False;				
			}
			else
			{
				if ($this->debug_dcom_extra)
				{
					echo 'imap: close: response_array line by line:<br>';
					for ($i=0; $i<count($response_array); $i++)
					{
						echo '-ArrayPos['.$i.'] data: ' .htmlspecialchars($response_array[$i]) .'<br>';
					}
					echo 'imap: close: =ENDS= response_array line by line:<br>';
				}
				if ($this->debug_dcom) { echo 'imap: Leaving Close<br>'; }
				return True;
			}

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
			
			$issue_command = 'r001 SELECT "'.$folder.'"';
			//$expecting = 'r001 OK';
			$expecting = 'r001 '; // may not be OK, could be BAD or NO
			
			if ($this->debug_dcom_extra) { echo 'imap: reopen: write_port: "'. htmlspecialchars($issue_command) .'"<br>'; }			
			if ($this->debug_dcom_extra) { echo 'imap: reopen: expecting: "'. htmlspecialchars($expecting) .'"<br>'; }
			
			if(!$this->write_port($issue_command))
			{
				if ($this->debug_dcom) { echo 'imap: Leaving reopen with error, could not write to port<br>'; }
				$this->error();
			}
			// read the server data
			$response_array = $this->imap_read_port($expecting);
			
			// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
			if (count($response_array) == 0)
			{
				if ($this->debug_dcom) { echo 'imap: Leaving reopen with error<br>'; }
				return False;				
			}
			else
			{
				if ($this->debug_dcom_extra)
				{
					echo 'imap: reopen: response_array line by line:<br>';
					for ($i=0; $i<count($response_array); $i++)
					{
						echo '-ArrayPos['.$i.'] data: ' .htmlspecialchars($response_array[$i]) .'<br>';
					}
					echo 'imap: reopen: =ENDS= response_array line by line:<br>';
				}
				if ($this->debug_dcom) { echo 'imap: Leaving reopen<br>'; }
				return True;
			}
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
			switch($GLOBALS['phpgw_info']['user']['preferences']['email']['imap_server_type'])
			{
				case 'UW-Maildir':
					if (isset($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_folder']))
					{
						if (empty($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_folder']))
						{
							$folder = $folder;
						}
						else
						{
							$folder = $GLOBALS['phpgw_info']['user']['preferences']['email']['mail_folder'].$folder;
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
