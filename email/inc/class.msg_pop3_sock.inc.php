<?php
  /**************************************************************************\
  * phpGroupWare Email - POP3 Mail emulator                                  *
  * http://www.phpgroupware.org/api                                          *
  * This file written by Itzchak Rehberg <izzy@phpgroupware.org>             *
  * and Joseph Engo <jengo@phpgroupware.org>                                 *
  * Mail function abstraction for POP3 servers                               *
  * Copyright (C) 2000, 2001 Itzchak Rehberg                                 *
  * -------------------------------------------------------------------------*
  * This library is part of phpGroupWare (http://www.phpgroupware.org)       *
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

  /**************************************************************************\
  \**************************************************************************/


  // Uncomment this to deal with huge pop3 mailboxes
  @set_time_limit(0);

  class msg extends msg_common
  {
    /**************************************************************************\
    * Common functions used by several pieces of this class                    *
    \**************************************************************************/
  
    function msg2socket($socket,$message) // send single line\n
    { 
	//  echo "> $message<BR>\n";
	if (!$socket)
	{
		$this->err[code] = "521";
		$this->err[msg]  = "socket does not exist";
		$this->err[desc] = "The required socket does not exist. The settings for your mail server may be wrong.";
		return 1;
	}
	$rc = fputs($socket,"$message");
	if (!$rc) 
	{
		$this->err[code] = "420";
		$this->err[msg]  = "lost connection";
		$this->err[desc] = "Lost connection to smtp server.";
		$rc  = fclose($socket);
		return 1;
	}
	return 0;
    }

    function close($stream,$flags="")
    {
	if ($this->msg2socket($stream,"QUIT\n")): return false; endif;
	if ($this->pop_socket2msg($stream)): return false; endif;
	return fclose($stream);
    }

    function delete($stream,$msg_num,$flags="")
    {
	if ($this->msg2socket($stream,"DELE $msg_num\n")): return false; endif;
	if ($this->pop_socket2msg($stream)): return false; endif;
	$this->force_check = true;
	return true;
    }
     
    function expunge($stream)
    {
	// no other mailboxes on pop
	return true;
    }
     
    function fetchbody($stream,$msgnr,$partnr="",$flags="")
    {
	if ($this->msg2socket($stream,"RETR $msgnr\n")): return false; endif;
	$message = "";
	$retr = fgets($stream,100); $i=1;
	if (strtolower(substr($retr,0,3)) != "+ok") return false;
	$bodystart = false;
	if (!$this->got_structure) $struct = $this->fetchstructure($stream,$msgnr);
	if ($this->boundary)
	{
		$thispart  = 0; $partstart = false; $partstop = false;
		$multipart = true; $boundary = "--".$this->boundary;
	}
	do
	{
		$retr = fgets($stream,4096);
		if (trim($retr) == "")
		{
			if ($multipart && ($thispart == $partnr))
			{
				$partstart = true;
			} 
			else
			{
				$bodystart = true;
			}
		}
		if ($multipart && is_int(strpos($retr,$boundary)) && !strpos($retr,$boundary))
		{
			if ($thispart == $partnr) $partstop = true;
			$thispart++;
		}
		if (chop($retr) == ".")
		{
			$retr = "";
		}
		else
		{
			$pos = strpos($retr,".");
			if (is_int($pos) && !$pos):
			$retr = substr($retr,1);
			endif;
		}
		if (!$multipart)
		{
			if (is_string($retr) && $retr && $bodystart) $message .= $retr;
		}
		else
		{
			if (is_string($retr) && $retr && $partstart && !$partstop) $message .= $retr;
		}
	}
	while (is_string($retr) && $retr);
	return $message;
    }

    function listmailbox($stream,$ref,$pattern)
    {
	// no other folders on pop
	return false;
    }

    function logout()
    {
	unlink($this->tempfile);
    }
     
    function num_msg($stream)
    {
	// returns number of messages in the mailbox
	if ($this->msg2socket($stream,"STAT\n")): return false; endif;
	if ($this->pop_socket2msg($stream)): return false; endif;
	$pos = strpos($this->err[msg]," ");
	return substr($this->err[msg],0,$pos);
    }
     
    function mailboxmsginfo($stream)
    {
	$info = new msg_mb_info;
	if ($this->msg2socket($stream,"STAT\n")): return false; endif;
	if ($this->pop_socket2msg($stream)): return false; endif;

	$this->err[msg] = chop($this->err[msg]);

	$pos = strpos($this->err[msg]," ");
	$info->Nmsgs = substr($this->err[msg],0,$pos);
	$info->Size  = substr($this->err[msg],$pos+1);

	if ($info->Nmsgs)
	{
		return $info;
	}
	else
	{
		return False;
	}
    }

    function mailcopy($stream,$msg_list,$mailbox,$flags)
    {
	// no other mbox on pop
	return false;
    }

    function mail_move($stream,$msg_list,$mailbox)
    {
	// no other mbox on pop
	return false;
    }

    function open($mail_server, $mail_port, $username, $password, $flags="")
    {
	global $phpgw_info;
	$timeout = 5;

	$socket = fsockopen(
		$mail_server,
		$mail_port,
		$errcode,
		$errmsg,
		$timeout);
	if (!$socket)
	{
		$this->err[code] = "420";
		$this->err[msg]  = "$errcode:$errmsg";
		$this->err[desc] = "Connection to ".$mail_server.":".$mail_port." failed - could not open socket.";
		return false;
	}
	if ($this->pop_socket2msg($socket)) { return false; }
	if ($this->msg2socket($socket,"USER $username\n")) { return false; }
	if ($this->pop_socket2msg($socket)) { return false; }
	if ($this->msg2socket($socket,"PASS $password\n")) { return false; }
	if ($this->pop_socket2msg($socket)) { return false; }
	return $socket;
    }
     
    function reopen($stream,$mailbox,$flags)
    {
	return false;
    }
     
    function size_msg($stream,$msg_nr)
    {
	if ($this->msg2socket($stream,"LIST $msg_nr\n")): return false; endif;
	if ($this->pop_socket2msg($stream)): return false; endif;
	$pos = strrpos($this->err[msg]," ");
	return substr($this->err[msg],$pos+1);
    }

    function sort($stream,$criteria,$reverse="",$options="")
    {
	$msg_num = $this->num_msg($stream); // nr_of_msgs on pop server
	if (!$msg_num) return false;     // no msgs - no sort.
	for ($i=1;$i<=$msg_num;$i++)
	{
		$sorted[$i] = $i;
	}

	$this->read_header();
     
	if ( count($sorted) != count($this->msg_info) -1 ) $this->force_check = true;
	if ($this->force_check)
	{
		$uid_list = $this->get_uid($stream);

		if (count($this->msg_info)>1)
		{
			$this->msg_sort(&$sorted,0);
		}

		if (count($this->msg_info)>1)
		{
			$this->update_msg_info($stream,$uid_list);
		}
		else
		{
			for ($i=1;$i<=@count($uid_list[id]);$i++)
			{
				$h_info = $this->header($stream,$uid_list[id][$i]);
				$f_info = $this->fetchstructure($stream,$uid_list[id][$i]);
				$this->msg_info[$i][0] = $uid_list[id][$i];
				$this->msg_info[$i][1] = $uid_list[uid][$i];
				$this->msg_info[$i][2] = $h_info->udate;
				$this->msg_info[$i][3] = $h_info->udate;
				$this->msg_info[$i][4] = $h_info->fromaddress;
				$this->msg_info[$i][5] = $h_info->toaddress;
				$this->msg_info[$i][6] = $h_info->ccaddress;
				$this->msg_info[$i][7] = $h_info->subject;
				$this->msg_info[$i][8] = $f_info->bytes;
			}
		}
		$this->force_check = false;
	}
	$criteria = strtolower($criteria);
	switch ($criteria)
	{
		case 0    : $this->msg_sort(&$sorted,2); break;
		case 2    : $this->msg_sort(&$sorted,4); break;
		case 3    : $this->msg_sort(&$sorted,7); break;
		case 6    : $this->msg_sort(&$sorted,8); break;
		case "sortdate"    : $this->msg_sort(&$sorted,2); break;
		case "sortarrival" : $this->msg_sort(&$sorted,3); break;
		case "sortfrom"    : $this->msg_sort(&$sorted,4); break;
		case "sortto"      : $this->msg_sort(&$sorted,5); break;
		case "sortcc"      : $this->msg_sort(&$sorted,6); break;
		case "sortsubject" : $this->msg_sort(&$sorted,7); break;
		case "sortsize"    : $this->msg_sort(&$sorted,8); break;
		default            : break;
	}

	for ($i=0;$i<count($sorted);$i++)
	{
		$tsorted[$i] = $sorted[$i+1];
	}
	$this->write_header();
	return $tsorted;
    }
     
    function status($stream,$mailbox,$options)
    {
	$status = (object) "0";
	return $status;
    }

    function append($stream, $folder = "Sent", $header, $body, $flags = "")
    {
	return false;
    }

    function login( $folder = "INBOX")
    {
	global $phpgw, $phpgw_info;

	error_reporting(error_reporting() - 2);

	if ($folder != "INBOX")
	{
		$folder = $this->construct_folder_str($folder);
	}

	// === ISSET CHECK ==
	if ( (isset($phpgw_info['user']['preferences']['email']['userid']))
	&& ($phpgw_info['user']['preferences']['email']['userid'] != '')
	&& (isset($phpgw_info['user']['preferences']['email']['passwd']))
	&& ($phpgw_info['user']['preferences']['email']['passwd'] != '') )
	{
		$user = $phpgw_info['user']['preferences']['email']['userid'];
		$pass = $phpgw_info['user']['preferences']['email']['passwd'];
	}
	else
	{
		// problem - invalid or nonexistant info for userid and/or passwd
		//echo "POP3 - LOGIN - NO USER AND OR NO PASSWD";
		return False;
	}

	$mail_server = $phpgw_info['user']['preferences']['email']['mail_server'];
	$mail_port = get_mailsvr_port();
	
	$mbox = $this->open($mail_server, $mail_port, $user , $pass);

	error_reporting(error_reporting() + 2);
	return $mbox;
    }


  /**************************************************************************\
  * Sub-Functions only used by the pop3() code                               *
  \**************************************************************************/

    function pop_socket2msg($socket)
    {
	// used by all functions
	if (!$socket)
	{
		$this->err[code] = "521";
		$this->err[msg]  = "socket does not exist";
		$this->err[desc] = "The required socket does not exist. The settings for your mail server may be wrong.";
		return 1;
	}
	$rmsg = fgets($socket,255);
	//  echo "< $rmsg<BR>";
	$errcode = substr($rmsg,0,3);
	$this->err[msg] = substr($rmsg,4);
	if (strtolower($errcode) != "+ok")
	{
		$this->err[code] = "550";
		return 1;
	}
	$this->err[code] = "250";
	return 0;
    }

   function more_info($header,$i,$info,$infokey)
   {
	// used by pop_fetchstructure only
	do
	{
		$pos = strpos($header[$i+1]," ");
		if (is_int($pos) && !$pos):
		$i++;
		$info->$infokey .= ltrim($header[$i]);
		endif;
	}
	while (is_int($pos) && !$pos);
	return $i;
   }

   function get_mime_param($header,$info,$i)
   {
	// used by pop_fetchstructure only
	$pos = strpos($header[$i],";");
	$content = trim(substr($header[$i],$pos+1));
	$done = false;
	do
	{
		$more = strpos($header[$i+1]," ");
		if (strlen($content)==0 && (is_int($more) && !$more)):
			$i++;
			$content = trim($header[$i]);
		endif;
		if (strlen($content)==0) break;
		$pos = strpos($content,"=");
		if (!(is_int($pos) && $pos)): return $i; endif;
		$val = str_replace("\"","",substr($content,$pos+1));
		$info->parameters[] = new msg_params(substr($content,0,$pos),$val);
		$info->ifparameters = true;
		$content="";
		if (!is_int($more) || $more) $done = true;
	}
	while (!$done);
	return $i;
   }

   function get_ctype($header,$info,$i,$content)
   {
	// used by pop_fetchstructure only
	$pos = strpos($content,"/");
	if (is_int($pos) && $pos)
	{
		$prim_type = strtolower(substr($content,0,$pos));
	}
	else
	{
		$prim_type = strtolower($content);
	}
	$pos = strpos($prim_type,";");
	if (is_int($pos) && $pos): $prim_type = substr($prim_type,0,$pos); endif;
	switch ($prim_type)
	{
		case "text"        : $info->type = 0; break;
		case "multipart"   : $info->type = 1; break;
		case "message"     : $info->type = 2; break;
		case "application" : $info->type = 3; break;
		case "audio"       : $info->type = 4; break;
		case "image"       : $info->type = 5; break;
		case "video"       : $info->type = 6; break;
		default            : $info->type = 7; break;
	}
	$pos = strpos($content,"/");
	if (is_int($pos))
	{
		$pos_para = strpos($content,";");
		if (is_int($pos_para) && $pos_para)
		{
			$info->subtype = substr($content,$pos+1,$pos_para - $pos -1);
		}
		else
		{
			$info->subtype = substr($content,$pos+1);
		}
		$info->ifsubtype = true;
	}
	if (is_int($pos_para)): $i = $this->get_mime_param($header,&$info,$i); endif;
   }

   function get_structure($msg_part,$line_nr,$is_multi=false)
   {
	// called from pop_fetchstructure()
	//$debug_mime = True;
	$info = new msg_struct;
	if ($is_multi)
	{
		$info->type = 0;
		$info->encoding = 0;
	}
	for ($i=$line_nr;$i<=$msg_part[0],trim($msg_part[$i]);$i++)
	{
		$pos = strpos($msg_part[$i]," ");
		if ($debug_mime) { echo 'msg_part['.$i.']: '.$msg_part[$i].'<br>'; }
		// need to capture "boundry=" keywords too
		if ((!is_int($pos) || ($pos==0))
		&& (stristr($msg_part[$i], 'boundary=')))
		{
			$msg_part[$i] = trim($msg_part[$i]);
			$msg_part[$i] = eregi_replace('boundary="', 'boundary ', $msg_part[$i]);
			$msg_part[$i] = eregi_replace('".*$', '', $msg_part[$i]);
			$pos = strpos($msg_part[$i]," ");
			if ($debug_mime) { echo 'msg_part['.$i.']: '.$msg_part[$i].'<br>'; }
		}
		if (is_int($pos) && ($pos==0))
		{
			continue;
		}
		$keyword = strtolower(substr($msg_part[$i],0,$pos));
		$content = trim(substr($msg_part[$i],$pos+1));
		if ($debug_mime) { echo 'pos: '.$pos.'<br>'; }
		if ($debug_mime) { echo 'keyword: ['.$keyword.']<br>'; }
		if ($debug_mime) { echo 'content: ['.$content.']<br>'.'<br>'; }
		switch ($keyword)
		{
		  case "content-type:" :
			$this->get_ctype($msg_part,&$info,&$i,$content);
			break;
		  case "content-transfer-encoding:" :
			switch (strtolower($content))
			{
			  case "7bit"             : $info->encoding = 0; break;
			  case "8bit"             : $info->encoding = 1; break;
			  case "binary"           : $info->encoding = 2; break;
			  case "base64"           : $info->encoding = 3; break;
			  case "quoted-printable" : $info->encoding = 4; break;
			  default                 : $info->encoding = 5; break;
			}
			break;
		  case "content-description:" :
			$info->description   = $content;
			$i = $this->more_info($msg_part,$i,&$info,"description");
			$info->ifdescription = true;
			break;
		  case "content-identifier:" :
			$info->id   = $content;
			$i = $this->more_info($msg_part,$i,&$info,"id");
			$info->ifid = true;
			break;
		  case "lines:" : $info->lines = $content; break;
		  case "content-length:" : $info->bytes = $content; break;
		  case "content-disposition:" :
			$info->disposition   = $content;
			$i = $this->more_info($msg_part,$i,&$info,"disposition");
			$info->ifdisposition = true;
			break;
		  case "mime-version:" :
			//$pos = strpos($content,"=");
			//$info->parameters[] = new msg_params("MIME-Version",substr($content,$pos+1));
			$info->parameters[] = new msg_params("MIME-Version",trim($content));
			$info->ifparameters = true;
			break;
		  case "boundary" :
			if ((isset($info->parameters)) && (count($info->parameters) > 0))
			{
				if ($debug_mime) { var_dump($info->parameters); }
				$new_idx = count($info->parameters);
				$add_params = new msg_params("boundary",trim($content));
				$info->parameters[$new_idx] = $add_params;
				if ($debug_mime) { var_dump($info->parameters); }
			}
			break;
		  default : break;
		}
	}
	return $info;
   }
 
   function get_boundary($info)
   {
	for ($i=0;$i<count($info->parameters);$i++)
	{
		$temp = $info->parameters[$i];
		if ($temp->attribute == "boundary")
		{
			$boundary = $temp->value;
		}
		return trim($boundary);
	}
   }

   function get_header($stream,$msg_num)
   {
	if ($this->msg2socket($stream,"TOP $msg_num 0\n")): return false; endif;
	$retr = fgets($stream,100); $i=1;
	// left it out for some test with get_attach - somehow the server response is
	// not as expected *!*
	//     if (strtolower(substr($retr,0,3)) != "+ok"): return false; endif;
	$i = 0;
	do
	{
		// retrieve complete header into array $header
		$retr = fgets($stream,4096);
		if (chop($retr) == "."): break; endif;
		if (is_string($retr) && $retr)
		{
			$i++;
			$header[$i] = $retr;
		}
	}
	while (is_string($retr) && $retr);
	$header[0] = $i;
	return $header;
   }

   function get_body($stream,$msg_num)
   {
	if ($this->msg2socket($stream,"RETR $msg_num\n")): return false; endif;
	$retr = fgets($stream,100); $i=1;
	// left it out for some test with get_attach - somehow the server response is
	// not as expected *!*
	//     if (strtolower(substr($retr,0,3)) != "+ok"): return false; endif;
	$i = 0;
	do
	{
		// skip header
		$retr = fgets($stream,4096);
		if (chop($retr) == "."): break; endif;
		if (chop($retr) == ""): break; endif;
	}
	while (is_string($retr) && $retr);
	do
	{
		// retrieve complete body into array $body
		$retr = fgets($stream,4096);
		if (chop($retr) == "."): break; endif;
		if (is_string($retr) && $retr)
		{
			$i++;
			$body[$i] = $retr;
		}
	}
	while (is_string($retr));
	$body[0] = $i;
	return $body;
   }


    // used only by pop_header
   function get_addr_details($people,$address,$header,$count)
   {
	global $phpgw_info;

	if (!trim($address)) return false;

	// check wether this header info is split to multiple lines
	$done = false;

	do
	{
		$pos = strpos($header[$count+1]," ");

		if (is_int($pos) && !$pos)
		{
			$count++;
			$address .= chop($header[$count]);
		}
		else
		{
			$done = true;
		}
	}
	while (!$done);

	$temp = $people . "address";

	if ($people == "return_path")
	{
		$this->$people = htmlspecialchars($address);
	}
	else
	{
		$this->$temp = htmlspecialchars($address);
	}

	for ($i=0,$pos=1;$pos;$i++)
	{
		$addr_details = new msg_aka;
		$pos = strpos($address,"<");
		$pos3 = strpos($address,"(");

		if (is_int($pos))
		{
			$pos2 = strpos($address,">");

			if ($pos2 == $pos+1)
			{
				$addr_details->adl = "nobody@nowhere";
			}
			else
			{
				$addr_details->adl = substr($address,$pos+1,$pos2 - $pos -1);
			}

			if ($pos)
			{
				$addr_details->personal = substr($address,0,$pos - 1);
			}
		}
		elseif (is_int($pos3))
		{
			$pos2 = strpos($address,")");

			if ($pos2 == $pos3+1)
			{
				$addr_details->personal = "nobody";
			}
			else
			{
				$addr_details->personal = substr($address, $pos3+1, $pos2-$pos3 - 1);
			}

			if ($pos3)
			{
				$addr_details->adl = substr($address,0,$pos3 - 1);
			}
		}
		else
		{
			$addr_details->adl = $address;
			$addr_details->personal = $address;
		}
		
		$pos3 = strpos($addr_details->adl,"@");
		if (!$pos3)
		{
			if (!$pos)
			{
				$addr_details->mailbox = $addr_details->adl;
			}

			$addr_details->host = $phpgw_info["server"]["imap_suffix"];
			$details[$i] = $addr_details;
			return $details;
		}

		$addr_details->mailbox = substr($addr_details->adl,0,$pos3);
		$addr_details->host    = substr($addr_details->adl,$pos3+1);
		$pos = ereg("\"",$addr_details->personal);

		if ($pos)
		{
			$addr_details->personal = substr($addr_details->personal,1,strlen($addr_details->personal)-2);
		}

		$pos = strpos($address,",");

		if ($pos): $address = trim(substr($address,$pos+1)); endif;
		$details[$i] = $addr_details;
	}
	return $details;
   }

   function make_udate($msg_date)
   {
	// used only by pop_header
	$pos = strpos($msg_date,",");
	if ($pos)
	{
		$msg_date = trim(substr($msg_date,$pos+1));
	}
	$pos = strpos($msg_date," ");
	$day = substr($msg_date,0,$pos);
	$msg_date = trim(substr($msg_date,$pos));
	$month = substr($msg_date,0,3);
	switch (strtolower($month))
	{
		case "jan" : $month =  1; break;
		case "feb" : $month =  2; break;
		case "mar" : $month =  3; break;
		case "apr" : $month =  4; break;
		case "may" : $month =  5; break;
		case "jun" : $month =  6; break;
		case "jul" : $month =  7; break;
		case "aug" : $month =  8; break;
		case "sep" : $month =  9; break;
		case "oct" : $month = 10; break;
		case "nov" : $month = 11; break;
		default    : $month = 12; break;
	}
	$msg_date = trim(substr($msg_date,3));
	$pos  = strpos($msg_date," ");
	$year = trim(substr($msg_date,0,$pos));
	$msg_date = trim(substr($msg_date,$pos));
	$hour = substr($msg_date,0,2);
	$minute = substr($msg_date,3,2);
	$second = substr($msg_date,6,2);
	$pos = strrpos($msg_date," ");
	$tzoff = trim(substr($msg_date,$pos));
	if (strlen($tzoff)==5)
	{
		$diffh = substr($tzoff,1,2); $diffm = substr($tzoff,3);
		if ((substr($tzoff,0,1)=="+") && is_int($diffh))
		{
			$hour -= $diffh; $minute -= $diffm;
		}
		else
		{
			$hour += $diffh; $minute += $diffm;
		}
	}
	$utime = mktime($hour,$minute,$second,$month,$day,$year);
	return $utime;
   }

   function update_msg_info($stream,$uid_list) {
     $t_list = array();
     for ($i=1;$i<=count($uid_list[id]);$i++) {
       $found = false;
       for ($k=1;$k<=count($this->msg_info);$k++) {
         if ($this->msg_info[$k][1] == $uid_list[uid][$i]) {
           $t_list[$i]    = $this->msg_info[$k];
           $t_list[$i][0] = $i;
           $found = true;
         }
         if ($found) continue 2;
       }
       if ($found) break; // else rebuild with new info from server
       $h_info = $this->header($stream,$uid_list[id][$i]);
       $f_info = $this->fetchstructure($stream,$uid_list[id][$i]);
       $t_list[$i][0] = $uid_list[id][$i];
       $t_list[$i][1] = $uid_list[uid][$i];
       $t_list[$i][2] = $h_info->udate;
       $t_list[$i][3] = $h_info->udate;
       $t_list[$i][4] = $h_info->fromaddress;
       $t_list[$i][5] = $h_info->toaddress;
       $t_list[$i][6] = $h_info->ccaddress;
       $t_list[$i][7] = $h_info->subject;
       $t_list[$i][8] = $f_info->bytes;
     }
     $this->msg_info = $t_list;
     return true;
   }

   function msg_sort($sorted,$criteria)
   {
	for ($i=1;$i<=count($sorted);$i++)
	{
		$temp[$i] = strtolower($this->msg_info[$i][$criteria]);
		switch ($criteria)
		{
			
			case 8 :
				// size is a string here so we have to add
				// some leading zeros for sorting
				do
				{
					$temp[$i] = "0".$temp[$i];
				}
				while (strlen($temp[$i]) < 12);
				break;
			case 4 :
				$temp[$i] = str_replace("&quot;","",$temp[$i]);
				break;
			default     : break;
		}
	}
	asort($temp);
	for (reset ($temp),$i=1; $key = key($temp); next($temp), $i++)
	{
		$sorted[$i] = $key;
	}
	return $sorted;
   }

   function get_uid($stream)
   {
	if ($this->msg2socket($stream,"UIDL\n")): return false; endif;
	$retr = fgets($stream,100); $i=1;
	if (strtolower(substr($retr,0,3)) != "+ok"): return false; endif;
	$i = 0;
	do
	{
		// retrieve list "id uid"
		$retr = fgets($stream,512);
		if (chop($retr) == "."): break; endif;
		if (is_string($retr) && $retr)
		{
			$i++;
			$pos = strpos($retr," ");
			$id_list[id][$i] = substr($retr,0,$pos);
			$id_list[uid][$i] = chop(substr($retr,$pos+1));
		}
	}
	while (is_string($retr) && $retr);
	return $id_list;
   }

   function write_header()
   {
	global $phpgw_info;

	if (file_exists($this->tempfile)) unlink($this->tempfile);
	$fp = fopen($this->tempfile,"w");
	for ($i=1;$i<count($this->msg_info);$i++)
	{
		$string = implode("\"",$this->msg_info[$i]);
		$rc = fwrite($fp,$string);
	}
	$rc = fclose($fp);
   }

   function read_header()
   {
	global $phpgw_info;

	if (!file_exists($this->tempfile)) return;
	$fp = fopen($this->tempfile,"r");
	$i = 1;
	while ($string = fgets($fp,8196))
	{
		$this->msg_info[$i] = explode("\"",$string);
		$i++;
	}
	$rc = fclose($fp);
   }

 } // end of class msg_sock

