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
		@abstract reads data from an IMAP server until the line that begins with the specified param "cmd_tag"
		@param $cmd_tag : string is the special string that indicates a server is done sending data
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
		function imap_read_port($cmd_tag='')
		{
			// the $cmd_tag OK, BAD, NO line that marks the completion of server data
			// is not actually considered data
			// to put this line in the return data array may confuse the calling function
			// so it will go in $this->server_last_ok_response
			// for inspection by the calling function if so desired
			// so clear it of any left over value from a previous request
			$this->server_last_ok_response = '';
			
			// we return an array of strings, so initialize an empty array
			$return_me = Array();
			// is we do not know what to look for as an end tag, then abort
			if ($cmd_tag == '')
			{
				return $return_me;
			}
			// read the data until a tagged command completion is encountered
			while ($line = $this->read_port())
			{
				if ($this->str_begins_with($line, $cmd_tag) == False)
				{
					// continue reading from this port
					// each line of data from the server goes into an array
					$next_pos = count($return_me);
					$return_me[$next_pos] = $line;
				}
				// so we have a cmd_tag, is it followed by OK ?
				elseif ($this->str_begins_with($line, $cmd_tag.' OK'))
				{
					// we got a tagged command response OK
					// but if we send an empty array under this test error scheme
					// calling function will think there was an error
					// DECISION: if array is count zero, put this OK line in it
					// otherwise array already had valid server data in it
					// FIXME: and we do not want to add this OK line which is NOT actually data
					// FIXME: OR we ALWAYS add the final OK line and expect calling function
					// to ignore it ????
					if (count($return_me) == 0)
					{
						// add this OK line just to return a NON empty array
						$return_me[0] = $line;
					}
					else
					{
						// valid server data ALREADY exists in the return array
						// to add this final OK line *MAY* confuse the calling function
						// because this final OK line is NOT actually server data
						// THEREFOR: put the OK line in $this->server_last_ok_response for inspection
						// by the calling function if so desired
						$this->server_last_ok_response = $line;
					}
					// END READING THE PORT
					// in any case, we reached the end of server data
					// so we must break out of this loop
					break;
				}
				// not an OK tag, was it an understandable error NO or BAD ?
				elseif (($this->str_begins_with($line, $cmd_tag.' NO'))
				|| ($this->str_begins_with($line, $cmd_tag.' BAD')))
				{
					// error analysis, we have a useful error response from the server
					// put that error string into $this->server_last_error_str
					$this->server_last_error_str = $line;
					// what should we return here IF there was a NO or BAD error ?
					// how about an empty array, how about FALSE ??
						
					// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
					// empty the array
					$return_me = Array();
					// END READING THE PORT
					// in any case (BAD or NO)
					// we reached the end of server data
					// so we must break out of this loop
					break;
				}
				else
				// so not OK and not a known error, log the unknown error
				{
					// error analysis, generic record of unknown error situation
					// put that error string into $this->server_last_error_str
					$this->server_last_error_str = 'imap unknown error in imap_read_port: "'.$line.'"';
					// what should we return here IF there was a NO or BAD error ?
					// how about an empty array, how about FALSE ??
						
					// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
					// empty the array
					$return_me = Array();
					// END READING THE PORT
					// in any case (unknown data after $cmd_tag completion)
					// we reached the end of server data
					// so we must break out of this loop
					break;
				}
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
		/*!
		@function open
		@abstract implements php function IMAP_OPEN
		@param $fq_folder : string : {SERVER_NAME:PORT/OPTIONS}FOLDERNAME
		@param $user :  string : account name to log into on the server
		@param $pass :  string : password for this account on the mail server
		@param $flags :  NOT YET IMPLEMENTED
		@discussion implements the functionality of php function IMAP_OPEN
		note that php's IMAP_OPEN applies to IMAP, POP3 and NNTP servers
		@syntax ?
		@author Angles, skeeter
		@access	public
		*/
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


			$cmd_tag = 'L001';
			$full_command = $cmd_tag.' LOGIN "'.quotemeta($user).'" "'.quotemeta($pass).'"';
			$expecting = $cmd_tag; // may be followed by OK, NO, or BAD
			
			if ($this->debug_dcom_extra) { echo 'imap: open: write_port: '. htmlspecialchars($full_command) .'<br>'; }
			if ($this->debug_dcom_extra) { echo 'imap: open: expecting: "'. htmlspecialchars($expecting) .'" followed by OK, NO, or BAD<br>'; }
			
			if(!$this->write_port($full_command))
			{
				if ($this->debug_dcom) { echo 'imap: open: could not write_port<br>'; }
				$this->error();
				// does $this->error() ever continue onto next line?
				return False;
			}
			// server can spew some b.s. hello messages before the official response
			// read the server data
			$response_array = $this->imap_read_port($expecting);
			
			// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
			if (count($response_array) == 0)
			{
				if ($this->debug_dcom_extra)
				{
					echo 'imap: open: error in Open<br>';
					echo 'imap: open: last recorded error:<br>';
					echo  $this->server_last_error().'<br>';
				}
				if ($this->debug_dcom) { echo 'imap: Leaving Open with error<br>'; }
				return False;
			}
			else
			{
				if ($this->debug_dcom_extra)
				{
					echo 'imap: open: response_array line by line:<br>';
					for ($i=0; $i<count($response_array); $i++)
					{
						echo '-ArrayPos['.$i.'] data: ' .htmlspecialchars($response_array[$i]) .'<br>';
					}
					echo 'imap: open: =ENDS= response_array line by line:<br>';
					echo 'imap: open: last server completion line: "'.htmlspecialchars($this->server_last_ok_response).'"<br>';
				}
				if ($this->debug_dcom) { echo 'imap: open: Successful IMAP Login<br>'; }
			}
			
			// now that we have logged in, php's IMAP_OPEN would now select the desired folder
			if ($this->debug_dcom_extra) { echo 'imap: open: php IMAP_OPEN would now select desired folder: "'. htmlspecialchars($folder) .'"<br>'; }
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
			
			$cmd_tag = 'c001';
			$full_command = $cmd_tag.' LOGOUT';
			$expecting = $cmd_tag; // may be followed by OK, NO, or BAD
			
			if ($this->debug_dcom_extra) { echo 'imap: close: write_port: "'. htmlspecialchars($full_command) .'"<br>'; }
			if ($this->debug_dcom_extra) { echo 'imap: close: expecting: "'. htmlspecialchars($expecting) .'" followed by OK, NO, or BAD<br>'; }
			
			if(!$this->write_port($full_command))
			{
				if ($this->debug_dcom) { echo 'imap: close: could not write_port<br>'; }
				$this->error();
			}
			
			// server can spew some b.s. goodbye message before the official response
			// read the server data
			$response_array = $this->imap_read_port($expecting);
			
			// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
			if (count($response_array) == 0)
			{
				if ($this->debug_dcom_extra)
				{
					echo 'imap: close: error in Close<br>';
					echo 'imap: close: last recorded error:<br>';
					echo  $this->server_last_error().'<br>';
				}
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
					echo 'imap: close: last server completion line: "'.htmlspecialchars($this->server_last_ok_response).'"<br>';
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
			
			$cmd_tag = 'r001';
			$full_command = $cmd_tag.' SELECT "'.$folder.'"';
			$expecting = $cmd_tag; // may be followed by OK, NO, or BAD
			
			if ($this->debug_dcom_extra) { echo 'imap: reopen: write_port: "'. htmlspecialchars($full_command) .'"<br>'; }
			if ($this->debug_dcom_extra) { echo 'imap: reopen: expecting: "'. htmlspecialchars($expecting) .'" followed by OK, NO, or BAD<br>'; }
			
			if(!$this->write_port($full_command))
			{
				if ($this->debug_dcom) { echo 'imap: reopen: could not write_port<br>'; }
				$this->error();
			}
			
			// read the server data
			$response_array = $this->imap_read_port($expecting);
			
			// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
			if (count($response_array) == 0)
			{
				if ($this->debug_dcom_extra)
				{
					echo 'imap: reopen: error in reopen<br>';
					echo 'imap: reopen: last recorded error:<br>';
					echo  $this->server_last_error().'<br>';
				}
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
					echo 'imap: reopen: last server completion line: "'.htmlspecialchars($this->server_last_ok_response).'"<br>';
				}
				if ($this->debug_dcom) { echo 'imap: Leaving reopen<br>'; }
				return True;
			}
		}

		/*!
		@function listmailbox
		@abstract implements IMAP_LISTMAILBOX
		@param $stream_notused : socket class handles stream reference internally
		@param $server_str : string : {SERVER_NAME:PORT/OPTIONS}
		@param $pattern : string : can be a namespace, or a mailbox name, or a namespace_delimiter, 
		or a namespace_delimiter_mailboxname, AND/OR including either "%" or "*" (see discussion below)
		@result an array containing the names of the mailboxes
		@discussion: if param $pattern includes some form of mailbox reference, that tells the server where in the
		mailbox hierarchy to start searching. If neither wildcard "%" nor "*" follows said mailbox reference, then the
		server returns the delimiter and the namespace for said mailbox reference. More typically, either one of the
		wildcards "*" or "%" follows said mailbox reference, in which case the server behaves as such:
		_begin_PHP_MANUAL_quote: There are two special characters you can pass as part of the pattern: '*' and '%'.
		'*' means to return all mailboxes. If you pass pattern as '*', you will get a list of the entire mailbox hierarchy. 
		'%' means to return the current level only. '%' as the pattern parameter will return only the top level mailboxes; 
		'~/mail/%' on UW_IMAPD will return every mailbox in the ~/mail directory, but none in subfolders of that directory.
		_end_quote_
		See RFC 2060 Section 6.3.8 (client specific) and Section 7.2.2 (server specific) for more details.
		The imap LIST command takes 2 params , the first is either blank or a mailbox reference, the second is either blank
		or one of the wildcard tokens "*" or "%". PHP's param $pattern is a combination of the imap LIST command's
		2 params, the difference between the imap and the php param(s) is that the php param $pattern will contain
		both mailbox reference AND/OR one of the wildcaed tokens in the same string, whereas the imap command
		seperates the wildcard token from the mailbox reference. I refer to IMAP_LISTMAILBOX's 2nd param as
		$server_str here while the php manual calls that same param "$ref", which is somewhat misnamed because the php
		manual states "ref should normally be just the server specification as described in imap_open()" which apparently
		means the server string {serverName:port/options} with no namespace, no delimiter, nor any mailbox name.
		@author Angles, skeeter
		@access	public
		*/
		function listmailbox($stream_notused,$server_str,$pattern)
		{
			if ($this->debug_dcom) { echo 'imap: Entering listmailbox<br>'; }
			$mailboxes_array = Array();
			
			// prepare params, seperate wildcards "*" or "%" from param $pattern
			// LIST param 1 is empty or is a mailbox reference string withOUT any wildcard
			// LIST param 2 is empty or is the wildcard either "%" or "*"
			if ((strstr($pattern, '*'))
			|| (strstr($pattern, '%')))
			{
				if (($pattern == '*')
				|| ($pattern == '%'))
				{
					// no mailbox reference string, so LIST param 1 is empty
					$list_params = '"" "' .$pattern .'"';
				}
				else
				{
					// just assume the * or % is at the end of the string
					// seperate it from the rest of the pattern
					$boxref = substr($pattern, 0, -1);
					$wildcard = substr($pattern, -1);
					$list_params = '"' .$boxref .'" "' .$wildcard .'"';
				}
			}
			elseif (strlen($pattern) == 0)
			{
				// empty $pattern equates to both LIST params being empty, which IS Valid
				$list_params = '"" ""';
			}
			else
			{
				// we have a string with no wildcard, so LIST param 2 is empty
				$list_params = '"' .$pattern .'" ""';
			}

			$cmd_tag = 'X001';
			$full_command = $cmd_tag.' LIST '.$list_params;
			$expecting = $cmd_tag; // may be followed by OK, NO, or BAD
			
			if ($this->debug_dcom_extra) { echo 'imap: listmailbox: write_port: ['. htmlspecialchars($full_command) .']<br>'; }
			if ($this->debug_dcom_extra) { echo 'imap: listmailbox: expecting: "'. htmlspecialchars($expecting) .'" followed by OK, NO, or BAD<br>'; }
			
			if(!$this->write_port($full_command))
			{
				if ($this->debug_dcom) { echo 'imap: listmailbox: could not write_port<br>'; }
				$this->error();
			}
			
			// read the server data
			$response_array = $this->imap_read_port($expecting);
			
			// TEST THIS ERROR DETECTION - empty array = error (BAD or NO)
			if (count($response_array) == 0)
			{
				if ($this->debug_dcom_extra)
				{
					echo 'imap: listmailbox: error in listmailbox<br>';
					echo 'imap: listmailbox: last recorded error:<br>';
					echo  $this->server_last_error().'<br>';
				}
				if ($this->debug_dcom) { echo 'imap: Leaving listmailbox with error<br>'; }
				return False;				
			}
			else
			{
				if ($this->debug_dcom_extra)
				{
					echo 'imap: listmailbox: response_array line by line:<br>';
					for ($i=0; $i<count($response_array); $i++)
					{
						echo '-ArrayPos['.$i.'] data: ' .htmlspecialchars($response_array[$i]) .'<br>';
					}
					echo 'imap: listmailbox: =ENDS= response_array line by line:<br>';
					echo 'imap: listmailbox: last server completion line: "'.htmlspecialchars($this->server_last_ok_response).'"<br>';
				}
			}
			
			if ($this->debug_dcom) { echo 'imap: Leaving listmailbox<br>'; }
			return $mailboxes_array;
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
		
		// OBSOLETED
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
