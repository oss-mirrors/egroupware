<?php
  /**************************************************************************\
  * phpGroupWare API - smtp mailer                                           *
  * This file written by Itzchak Rehberg <izzysoft@qumran.org>               *
  * and Joseph Engo <jengo@phpgroupware.org>                                 *
  * This module should replace php's mail() function. It is fully syntax     *
  * compatible. In addition, when an error occures, a detailed error info    *
  * is stored in the array $send->err (see ../inc/email/global.inc.php for   *
  * details on this variable).                                               *
  * Copyright (C) 2000, 2001 Itzchak Rehberg                                 *
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

  /* $Id$ */

  class send_2822
  {
	var $err    = array("code","msg","desc");
	var $to_res = array();

	function send()
	{
	    $this->err["code"] = " ";
	    $this->err["msg"]  = " ";
	    $this->err["desc"] = " ";
	}
    
// ===  some sub-functions  ===

	function socket2msg($socket)
	{
		//$debug_send = True;
		$debug_send = False;
		if ($debug_send)
		{
			return True;
		}
		
		$followme = "-";
		$this->err["msg"] = "";
		do
		{
			$rmsg = fgets($socket,255);
			// echo "< $rmsg<BR>\n";
			$this->err["code"] = substr($rmsg,0,3);
			$followme = substr($rmsg,3,1);
			$this->err["msg"] = substr($rmsg,4);
			if (substr($this->err["code"],0,1) != 2 && substr($this->err["code"],0,1) != 3)
			{
				$rc  = fclose($socket);
				return false;
			}
			if ($followme = " ")
			{
				break;
			}
		}
		while ($followme = "-");
		return true;
	}

	function msg2socket($socket,$message)
	{
		global $phpgw;
		
		//$debug_send = True;
		$debug_send = False;
		if ($debug_send)
		{
			// send single line\n
			echo 'raw ' .$phpgw->msg->htmlspecialchars_encode($message);
			//echo "hex> ".bin2hex($message)."<BR>\r\n";
			return True;
		}

		$rc = fputs($socket,"$message");
		if (!$rc)
		{
			$this->err["code"] = "420";
			$this->err["msg"]  = "lost connection";
			$this->err["desc"] = "Lost connection to smtp server.";
			$rc  = fclose($socket);
			return false;
		}
		return true;
	}

	// ===== [ main function: smail_2822() ] =======

	function smail_2822($mail_out)
	{
		global $phpgw, $phpgw_info;
		
		//$debug_send = True;
		$debug_send = False;

		// error code and message of failed connection
		$errcode = "";
		$errmsg = "";
		// timeout in secs
		$timeout = 5;

		if ($debug_send)
		{
			$socket = 41; // arbitrary number, no significance
		}
		else
		{
			// now we try to open the socket and check, if any smtp server responds
			$socket = fsockopen($phpgw_info["server"]["smtp_server"],$phpgw_info["server"]["smtp_port"],$errcode,$errmsg,$timeout);
		}
		if (!$socket)
		{
			$this->err["code"] = "420";
			$this->err["msg"]  = "$errcode:$errmsg";
			$this->err["desc"] = "Connection to ".$phpgw_info["server"]["smtp_server"].":".$phpgw_info["server"]["smtp_port"]." failed - could not open socket.";
			return false;
		}
		else
		{
			$rrc = $this->socket2msg($socket);
		}
		
		$mymachine = $mail_out['mymachine'];
		$fromuser = $phpgw->msg->addy_array_to_str($mail_out['from']);
		// now we can send our message. 1st we identify ourselves and the sender
		$cmds = array (
			"\$src = \$this->msg2socket(\$socket,\"HELO \$mymachine\r\n\");",
			"\$rrc = \$this->socket2msg(\$socket);",
			"\$src = \$this->msg2socket(\$socket,\"MAIL FROM:\$fromuser\r\n\");",
			"\$rrc = \$this->socket2msg(\$socket);"
		);
		if ($debug_send)
		{
			echo '<pre>';
		}
		for ($src=true,$rrc=true,$i=0; $i<count($cmds);$i++)
		{
			eval ($cmds[$i]);
			if (!$src || !$rrc)
			{
				return false;
			}
		}

		// now we've got to feed the to's and cc's
		for ($i=0; $i<count($mail_out['mta_to']); $i++)
		{
			$src = $this->msg2socket($socket,'RCPT TO: '.$mail_out['mta_to'][$i]."\r\n");
			$rrc = $this->socket2msg($socket);
			// for lateron validation
			$this->to_res[$i][addr] = $mail_out['mta_to'][$i]['plain'];
			$this->to_res[$i][code] = $this->err["code"];
			$this->to_res[$i][msg]  = $this->err["msg"];
			$this->to_res[$i][desc] = $this->err["desc"];
		}

		if (!$debug_send)
		{
			//now we have to make sure that at least one $to-address was accepted
			$stop = 1;
			for ($i=0;$i<count($this->to_res);$i++)
			{
				$rc = substr($this->to_res[$i][code],0,1);
				if ($rc == 2)
				{
					// at least to this address we can deliver
					$stop = 0;
				}
			}
			if ($stop)
			{
				// no address found we can deliver to
				return false;
			}
		}

		// now we can go to deliver the headers!
		if (!$this->msg2socket($socket,"DATA\r\n"))
		{
			return false;
		}
		if (!$this->socket2msg($socket))
		{
			return false;
		}
		for ($i=0; $i<count($mail_out['main_headers']); $i++)
		{
			if (!$this->msg2socket($socket,$mail_out['main_headers'][$i]."\r\n"))
			{
				return false;
			}
		}
		// this CRLF terminates the header, signals the body will follow next (ONE CRLF ONLY)
		if (!$this->msg2socket($socket,"\r\n"))
		{
			return false;
		}
		// now we can go to deliver the body!
		for ($part_num=0; $part_num<count($mail_out['body']); $part_num++)
		{
			// mime headers for this mime part (if any)
			if (($mail_out['is_multipart'] == True)
			|| ($mail_out['is_forward'] == True))
			{
				for ($i=0; $i<count($mail_out['body'][$part_num]['mime_headers']); $i++)
				{
					$this_line = rtrim($this_line = $mail_out['body'][$part_num]['mime_headers'][$i])."\r\n";
					if (!$this->msg2socket($socket,$this_line))
					{
						return false;
					}
				}
				// a space needs to seperate the mime part headers from the mime part content
				if (!$this->msg2socket($socket,"\r\n"))
				{
					return false;
				}
			}
			// the part itself
			for ($i=0; $i<count($mail_out['body'][$part_num]['mime_body']); $i++)
			{
				$this_line = rtrim($mail_out['body'][$part_num]['mime_body'][$i])."\r\n";
				if (trim($this_line) == ".")
				{
					// rfc2822 escape the "special" single dot line into a double dot line
					$this_line = "." .$this_line;
				}
				if (!$this->msg2socket($socket,$this_line))
				{
					return false;
				}
			}
			// this space will seperate this part from any following parts that may be coming
			if (!$this->msg2socket($socket,"\r\n"))
			{
				return false;
			}
		}
		// at the end of a multipart email, we need to add the "final" boundary
		if (($mail_out['is_multipart'] == True)
		|| ($mail_out['is_forward'] == True))
		{
			// attachments / parts have their own boundary preceeding them in their mime headers
			// this is: "--"boundary
			// all boundary strings are have 2 dashes "--" added to their begining
			// and the FINAL boundary string (after all other parts) ALSO has 
			// 2 dashes "--" tacked on tho the end of it, very important !! 
			//   the first or last \r\n is *probably* not necessary
			$final_boundary = '--' .$mail_out['boundary'].'--'."\r\n";
			if (!$this->msg2socket($socket,$final_boundary))
			{
				return false;
			}
			// another blank line
			if (!$this->msg2socket($socket,"\r\n"))
			{
				return false;
			}
		}

		// special string "DOTCRLF" signals the end of the body
		if (!$this->msg2socket($socket,".\r\n"))
		{
			return false;
		}
		if (!$this->socket2msg($socket))
		{
			return false;
		}
		if (!$this->msg2socket($socket,"QUIT\r\n"))
		{
			return false;
		}
		
		if ($debug_send)
		{
			echo '</pre>';
		}
		
		if (!$debug_send)
		{
			do
			{
				$closing = $this->socket2msg($socket);
			}
			while ($closing);
		}
		return true;
	}

  // end of class
  }
