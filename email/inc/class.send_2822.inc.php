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
    
	function msg($service, $to, $subject, $body, $msgtype='', $cc='', $bcc='', $from='', $sender='')
	{
		global $phpgw_info, $phpgw;
		
		if ($from == '')
		{
			$from = '"'.$phpgw_info['user']['fullname'].'" <'.$phpgw_info['user']['preferences']['email']['address'].'>';
		}
		if ($sender == '')
		{
			$sender = '"'.$phpgw_info['user']['fullname'].'" <'.$phpgw_info['user']['preferences']['email']['address'].'>';
		}

		//$now = getdate();
		// RFC2822: date *should* be local time with the correct offset, but this is problematic on many machines
		$header  = 'Date: '.gmdate('D, d M Y H:i:s').' +0000'."\r\n";
		$header .= 'From: '.$from."\r\n";
		if($from != $sender)
		{
			$header .= 'Sender: '.$sender."\r\n";
		}
		$header .= 'Reply-To: "'.$phpgw_info['user']['fullname'].'" <'.$phpgw_info['user']['preferences']['email']['address'].'>'."\r\n";
		$header .= 'To: '.$to."\r\n";
		if (!empty($cc))
		{
			$header .= 'Cc: '.$cc."\r\n";
		}
		if (!empty($bcc))
		{
			$header .= 'Bcc: '.$bcc."\r\n";
		}
		if (!empty($msgtype))
		{
			$header .= 'X-phpGW-Type: '.$msgtype."\r\n";
		}
		$header .= 'X-Mailer: phpGroupWare (http://www.phpgroupware.org)'."\r\n";

		if (ereg('Message-Boundary', $body)) 
		{
			$header .= 'Subject: ' . stripslashes($subject) . "\r\n"
				. 'MIME-Version: 1.0'."\r\n"
				. 'Content-Type: multipart/mixed;'."\r\n"
				. ' boundary="Message-Boundary"'."\r\n";
				
			$body = '--Message-Boundary'."\r\n"
				. 'Content-type: text/plain; charset=US-ASCII'."\r\n"
				// if (!empty($msgtype))
				// {
				//	$header .= "Content-type: text/plain; phpgw-type=".$msgtype."\r\n";
				// }
				.'Content-Disposition: inline'."\r\n"
				.'Content-transfer-encoding: 7bit'."\r\n"
				. "\r\n"
				. ltrim($body);
		}
		else
		{
			$header .= 'Subject: '.stripslashes($subject)."\r\n"
				. 'MIME-version: 1.0'."\r\n"
				. 'Content-type: text/plain; charset="'.lang('charset').'"'."\r\n";
			if (!empty($msgtype))
			{
				$header .= 'Content-type: text/plain; phpgw-type='.$msgtype."\r\n";
			}
			$header .= 'Content-Disposition: inline'."\r\n"
				. 'Content-description: Mail message body'."\r\n";
		}
		if ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'imap' && $phpgw_info['user']['apps']['email'] && is_object($phpgw->msg))
		{
			$stream = $phpgw->msg->login('Sent');
			$phpgw->msg->append($stream, 'Sent', $header, $body, "\\Seen");
			$phpgw->msg->close($stream);
		}
		if (strlen($cc)>1)
		{
			$to .= ','.$cc;
		}

		if (strlen($bcc)>1)
		{
			$to .= ','.$bcc;
		}

		$returnccode = $this->smail_2822($to, $body, $header);

		return $returnccode;
	}

	// ==================================================[ some sub-functions ]===

	function socket2msg($socket)
	{
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
		// send single line\n
		// echo "raw> $message<BR>\n";
		// echo "hex> ".bin2hex($message)."<BR>\n";
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

	function put2socket($socket,$message)
	{	
		// multiple lines, we have to split it
		$message_array = Array();
		$message_array = explode("\r\n", $message);
		// send this line by line - DO NOT TRIM - we must retain the "whitespace" used in header folding
		for ($z=0;$z<count($message_array);$z++)
		{
			if (chop($message_array[$z]) != '')
			{
				$this_line = chop($message_array[$z])."\r\n";
				if (!$this->msg2socket($socket,$this_line))
				{
					return false;
				}
			}
		}
		return true;
	}

	function check_header($subject,$header)
	{
		// check if header contains subject and is correctly terminated
		$header = chop($header);
		$header .= "\r\n";
		if (is_string($subject) && !$subject)
		{
			// no subject specified
			return $header;
		}
		$theader = strtolower($header);
		$pos  = ereg($theader,"\r\nsubject:");
		if ($pos)
		{
			// found after a new line
			return $header;
		}
		$pos = strpos($theader,"subject:");
		if (is_int($pos) && !$pos)
		{
			// found at start
			return $header;
		}
		$pos = strstr($subject,"\r\n");
		if (!$pos)
		{
			$subject .= "\r\n";
		}
		$subject = "Subject: " .$subject;
		$header .= $subject;
		return $header;
	}

	// ===== [ main function: smail_2822() ] =======

	function smail_2822($to,$message,$header)
	{
		global $phpgw_info;

		$fromuser = $phpgw_info["user"]["preferences"]["email"]["address"];
		$mymachine = $phpgw_info["server"]["hostname"];
		// error code and message of failed connection
		$errcode = "";
		$errmsg = "";
		// timeout in secs
		$timeout = 5;

		// now we try to open the socket and check, if any smtp server responds
		$socket = fsockopen($phpgw_info["server"]["smtp_server"],$phpgw_info["server"]["smtp_port"],$errcode,$errmsg,$timeout);
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

		// now we can send our message. 1st we identify ourselves and the sender
		$cmds = array (
			"\$src = \$this->msg2socket(\$socket,\"HELO \$mymachine\r\n\");",
			"\$rrc = \$this->socket2msg(\$socket);",
			"\$src = \$this->msg2socket(\$socket,\"MAIL FROM:<\$fromuser>\r\n\");",
			"\$rrc = \$this->socket2msg(\$socket);"
		);
		for ($src=true,$rrc=true,$i=0; $i<count($cmds);$i++)
		{
			eval ($cmds[$i]);
			if (!$src || !$rrc)
			{
				return false;
			}
		}

		// now we've got to evaluate the $to's
		// remove any CRLF WSP because for now we just feed one at a time
		$toaddr = $to;
		$toaddr = ereg_replace("\r\n ", "", $toaddr);
		$toaddr = ereg_replace("\r\n", "", $toaddr);
		$toaddr = explode(",",$toaddr);
		$numaddr = count($toaddr);
		for ($i=0; $i<$numaddr; $i++)
		{
			$goes_to = trim($toaddr[$i])."\r\n";
			$src = $this->msg2socket($socket,'RCPT TO: '.$goes_to);
			$rrc = $this->socket2msg($socket);
			// for lateron validation
			$this->to_res[$i][addr] = $toaddr[$i];
			$this->to_res[$i][code] = $this->err["code"];
			$this->to_res[$i][msg]  = $this->err["msg"];
			$this->to_res[$i][desc] = $this->err["desc"];
		}

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

		// now we can go to deliver the message!
		if (!$this->msg2socket($socket,"DATA\r\n"))
		{
			return false;
		}
		if (!$this->socket2msg($socket))
		{
			return false;
		}
		if ($header != "")
		{
			//$header = $this->check_header($subject,$header);
			// chop the trailing whitespaces and CRLF's from the header
			$header = chop($header);
			if (!$this->put2socket($socket,$header))
			{
				return false;
			}
			// this CRLF terminates the header, signals the body will follow next
			if (!$this->put2socket($socket,"\r\n"))
			{
				return false;
			}
		}
		$message  = chop($message);
		$message .= "\r\n";
		if (!$this->put2socket($socket,$message))
		{
			return false;
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
		do
		{
			$closing = $this->socket2msg($socket);
		}
		while ($closing);
		return true;
	}

  // end of class
  }
