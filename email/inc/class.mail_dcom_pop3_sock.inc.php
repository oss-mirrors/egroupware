<?php
  /**************************************************************************\
  * phpGroupWare API - POP3                                                  *
  * This file written by Mark Peters <skeeter@phpgroupware.org>              *
  * Handles specific operations in dealing with POP3                       *
  * Copyright (C) 2001 Mark Peters and Angelo "Angles" Puglisi                       *
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

	function login( $folder = "INBOX")
	{
		global $phpgw, $phpgw_info;

		//error_reporting(error_reporting() - 2);

		if ($folder != "INBOX")
		{
			$folder = $phpgw->msg->get_folder_long($folder);
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
		$mail_port = $phpgw->msg->get_mailsvr_port();
	
		$mbox = $this->open($mail_server, $mail_port, $user , $pass);

		//error_reporting(error_reporting() + 2);
		return $mbox;
	}

	// = = = Functions that DO NOTHING in POP3 = = =
	function createmailbox($stream,$mailbox) 
	{
		// N/A for pop3
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: createmailbox<br>'; }
		return true;
	}
	function deletemailbox($stream,$mailbox)
	{
		// N/A for pop3
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: deletemailbox<br>'; }
		return true;
	}
	function expunge($stream)
	{
		// N/A for pop3
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: expunge<br>'; }
		return true;
	}
	function listmailbox($stream,$ref,$pattern)
	{
		// N/A for pop3
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: listmailbox<br>'; }
		return False;
	}
	function mailcopy($stream,$msg_list,$mailbox,$flags)
	{
		// N/A for pop3
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: mailcopy<br>'; }
		return False;
	}
	function mail_move($stream,$msg_list,$mailbox)
	{
		// N/A for pop3
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: mail_move<br>'; }
		return False;
	}
	function reopen($stream,$mailbox,$flags = "")
	{
		// N/A for pop3
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: reopen<br>'; }
		return False;
	}
	function append($stream, $folder = "Sent", $header, $body, $flags = "")
	{
		// N/A for pop3
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: append<br>'; }
		return False;
	}


	function open ($fq_folder, $user, $pass, $flags='')
	{
		global $phpgw;
		
		if ($this->debug_dcom) { echo 'pop3: Entering open<br>'; }
		
		// fq_folder is a "fully qualified folder", seperate the parts:
		$svr_data = array();
		$svr_data = $this->distill_fq_folder($fq_folder);
		$folder = $svr_data['folder'];
		$server = $svr_data['server'];
		$port = $svr_data['port'];
		if ($this->debug_dcom) { echo 'pop3: open: svr_data:<br>'.serialize($svr_data).'<br>'; }

		//$port = 110;
		if (!$this->open_port($server,$port,15))
		{
			echo "<p><center><b>" . lang("There was an error trying to connect to your POP3 server.<br>Please contact your admin to check the servername, username or password.")."</b></center>";
			$phpgw->common->phpgw_exit();
		}
		$this->read_port();
		if(!$this->msg2socket('USER '.$user,"^\+ok",&$response) || !$this->msg2socket('PASS '.$pass,"^\+ok",&$response))
		{
			$this->error();
			if ($this->debug_dcom) { echo 'pop3: Leaving open with Error<br>'; }
			return False;
		}
		else
		{
			//echo "Successful POP3 Login.<br>\n";
			if ($this->debug_dcom) { echo 'pop3: open: Successful POP3 Login<br>'; }
			if ($this->debug_dcom) { echo 'pop3: Leaving open<br>'; }
			return $this->socket;
		}
	}

	function close($flags="")
	{
		if (!$this->msg2socket('QUIT',"^\+ok",&$response))
		{
			$this->error();
			if ($this->debug_dcom) { echo 'pop3: close: Error<br>'; }
			return False;
		}
		else
		{
			if ($this->debug_dcom) { echo 'pop3: close: Successful POP3 Logout<br>'; }
			return True;
		}
	}

	// returns number of messages in the mailbox
	function num_msg($fq_folder='')
	{
		if ($this->debug_dcom) { echo 'pop3: Entering num_msg<br>'; }
		// fq_folder is a "fully qualified folder" , seperate the parts:
		$svr_data = array();
		$svr_data = $this->distill_fq_folder($fq_folder);
		$folder = $svr_data['folder'];
		
		if (!$this->msg2socket('STAT',"^\+ok",&$response))
		{
			$this->error();
		}
		$num_msg = explode(' ',$response);
		if ($this->debug_dcom) { echo 'pop3: num_msg: num_msg: '.$num_msg[1].'<br>'; }
		if ($this->debug_dcom) { echo 'pop3: Leaving num_msg<br>'; }
		return $num_msg[1];
	}

	function mailboxmsginfo($fq_folder='')
	{
		if ($this->debug_dcom) { echo 'pop3: Entering mailboxmsginfo<br>'; }
		// fq_folder is a "fully qualified folder" , seperate the parts:
		$svr_data = array();
		$svr_data = $this->distill_fq_folder($fq_folder);
		$folder = $svr_data['folder'];
		
		$info = new msg_mb_info;
		if (!$this->msg2socket('STAT',"^\+ok",&$response))
		{
			$this->error();
		}
		$num_msg = explode(' ',$response);
		
		$info->messages = $num_msg[1];
		$info->size  = $num_msg[2];
		if ($info->messages)
		{
			if ($this->debug_dcom) { echo 'pop3: num_msg: info->messages: '.$info->messages.'<br>'; }
			if ($this->debug_dcom) { echo 'pop3: num_msg: info->size: '.$info->size.'<br>'; }
			if ($this->debug_dcom) { echo 'pop3: Leaving mailboxmsginfo<br>'; }
			return $info;
		}
		else
		{
			if ($this->debug_dcom) { echo 'pop3: num_msg: returining False<br>'; }
			if ($this->debug_dcom) { echo 'pop3: Leaving mailboxmsginfo<br>'; }
			return False;
		}
	}

	function status($fq_folder='',$options=SA_ALL)
	{
		if ($this->debug_dcom) { echo 'pop3: Entering status<br>'; }
		// fq_folder is a "fully qualified folder" meaning this format:
		// seperate the parts:
		$svr_data = array();
		$svr_data = $this->distill_fq_folder($fq_folder);
		$folder = $svr_data['folder'];
		
		$info = new mailbox_status;
		$info->messages = $this->num_msg($folder);
		if ($this->debug_dcom) { echo 'pop3: status: info->messages: '.$info->messages.'<br>'; }
		if ($this->debug_dcom) { echo 'pop3: Leaving status<br>'; }
		return $info;
	}

	function fetch_header_element($start,$stop,$element)
	{
		if ($this->debug_dcom) { echo 'pop3: Entering fetch_header_element<br>'; }
		for($i=$start;$i<=$stop;$i++)
		{
			if ($this->debug_dcom) { echo 'pop3: fetch_header_element: issue "TOP '.$i.' 0"<br>'; }
			if(!$this->write_port('TOP '.$i.' 0'))
			{
				$this->error();
			}
			$this->read_and_load('.');
			if($this->header[$element])
			{
				$field_element[$i] = $this->phpGW_quoted_printable_decode2($this->header[$element]);
//				echo $field_element[$i].' = '.$this->phpGW_quoted_printable_decode2($this->header[$element])."<br>\n";
				if ($this->debug_dcom) { echo 'pop3: fetch_header_element: field_element['.$i.']: '.$field_element[$i].'<br>'; }
			}
			else
			{
				$field_element[$i] = $this->phpGW_quoted_printable_decode2($this->header[strtoupper($element)]);
//				echo $field_element[$i].' = '.$this->phpGW_quoted_printable_decode2($this->header[strtoupper($element)])."<br>\n";
				if ($this->debug_dcom) { echo 'pop3: fetch_header_element: field_element['.$i.']: '.$field_element[$i].'<br>'; }
			}
			
		}
		if ($this->debug_dcom) { echo 'pop3: fetch_header_element: field_element: '.serialize($field_element).'<br><br><br>'; }
		if ($this->debug_dcom) { echo 'pop3: Leaving fetch_header_element<br>'; }
		return $field_element;
	}

	function sort($stream_notused='',$criteria=SORTDATE,$reverse=False,$options='')
	{
		if ($this->debug_dcom) { echo 'pop3: Entering sort<br>'; }
		
		// this is POP3, there's only 1 folder
		$folder = "INBOX";
		
		// nr_of_msgs on pop server
		$msg_num = $this->num_msg($folder);
		
		// no msgs - no sort.
		if (!$msg_num)
		{
			if ($this->debug_dcom) { echo 'pop3: Leaving sort with Error<br>'; }
			return false;
		}
		if ($this->debug_dcom) { echo 'pop3: sort: Number of Msgs:'.$msg_num.'<br>'; }
		switch($criteria)
		{
			case SORTDATE:
				if ($this->debug_dcom) { echo 'pop3: sort: case SORTDATE<br>'; }
				$old_list = $this->fetch_header_element(1,$msg_num,'Date');
				$field_list = $this->convert_date_array($old_list);
				break;
			case SORTARRIVAL:
				if ($this->debug_dcom) { echo 'pop3: sort: case SORTARRIVAL<br>'; }
				// TEST
				if (!$this->msg2socket('LIST',"^\+ok",&$response))
				{
					$this->error();
				}
				$response = $this->read_port_glob('.');
				$field_list = $this->glob_to_array($response, False, ' ');
				if ($this->debug_dcom) { echo 'pop3: sort: field_list: '.serialize($field_list).'<br><br><br>'; }
				break;
			case SORTFROM:
				if ($this->debug_dcom) { echo 'pop3: sort: case SORTFROM<br>'; }
				$field_list = $this->fetch_header_element(1,$msg_num,'From');
				break;
			case SORTSUBJECT:
				if ($this->debug_dcom) { echo 'pop3: sort: case SORTSUBJECT<br>'; }
				$field_list = $this->fetch_header_element(1,$msg_num,'Subject');
				break;
			case SORTTO:
				if ($this->debug_dcom) { echo 'pop3: sort: case SORTTO<br>'; }
				$field_list = $this->fetch_header_element(1,$msg_num,'To');
				break;
			case SORTCC:
				if ($this->debug_dcom) { echo 'pop3: sort: case SORTCC<br>'; }
				$field_list = $this->fetch_header_element(1,$msg_num,'cc');
				break;
			case SORTSIZE:
				if ($this->debug_dcom) { echo 'pop3: sort: case SORTSIZE<br>'; }
				$field_list = $this->fetch_header_element(1,$msg_num,'Size');
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
//			echo '('.$i.') Field: <b>'.$value."</b>\t\tMsg Num: <b>".$key."</b><br>\n";
			$i++;
		}
		@reset($return_array);
		if ($this->debug_dcom) { echo 'pop3: Leaving sort<br>'; }
		return $return_array;
	}

	function get_header_raw($stream_notused,$msg_num)
	{
		if ($this->debug_dcom) { echo 'pop3: Entering get_header_raw<br>'; }

		if (!$this->msg2socket('TOP '.$msg_num.' 0',"^\+ok",&$response))
		{
			$this->error();
		}
		$response = $this->read_port_glob('.');
		$msg_header_raw = $this->glob_to_array($response, False, '');
		
		if ($this->debug_dcom) { echo 'pop3: Leaving get_header_raw<br>'; }
		return $msg_header_raw;
	}

	function get_structure($header_array,$line_nr,$is_multi=false)
	{
		if ($this->debug_dcom) { echo 'pop3: Entering get_structure<br>'; }
		$debug_mime = True;
		//$debug_mime = False;
		
		$info = new struct;
		$info->parameters = array();
		if ($is_multi)
		{
			$info->type = 0;
			$info->encoding = 0;
		}
		for ($i=0; $i < count($header_array) ;$i++)
		{
			$pos = strpos($header_array[$i]," ");
			if ($debug_mime) { echo 'header_array['.$i.']: '.$header_array[$i].'<br>'; }
			// need to capture "boundry=" keywords too
			if ((!is_int($pos) || ($pos==0))
			&& (stristr($header_array[$i], 'boundary=')))
			{
				$header_array[$i] = trim($header_array[$i]);
				$header_array[$i] = eregi_replace('boundary="', 'boundary ', $header_array[$i]);
				$header_array[$i] = eregi_replace('".*$', '', $header_array[$i]);
				$pos = strpos($header_array[$i]," ");
				if ($debug_mime) { echo 'header_array['.$i.']: '.$header_array[$i].'<br>'; }
			}
			if (is_int($pos) && ($pos==0))
			{
				continue;
			}
			$keyword = strtolower(substr($header_array[$i],0,$pos));
			$content = trim(substr($header_array[$i],$pos+1));
			if ($debug_mime) { echo 'pos: '.$pos.'<br>'; }
			if ($debug_mime) { echo 'keyword: ['.$keyword.']<br>'; }
			if ($debug_mime) { echo 'content: ['.$content.']<br>'.'<br>'; }
			switch ($keyword)
			{
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
			  case "content-type:" :
				//$this->get_ctype($header_array[$i],&$info,&$i,$content);
				$info->type = $content;
				$info->subtype = 'FIX_ME';
				$info->ifsubtype = True;
				break;
			  case "content-description:" :
				$info->description   = $content;
				//$i = $this->more_info($msg_part,$i,&$info,"description");
				$info->ifdescription = true;
				break;
			  case "content-identifier:" :
			  case "message-id:" :
				$info->id   = $content;
				//$i = $this->more_info($msg_part,$i,&$info,"id");
				$info->ifid = true;
				break;
			  case "lines:" :
				$info->lines = $content;
				break;
			  case "content-length:" :
				$info->bytes = $content;
				break;
			  case "content-disposition:" :
				$info->disposition   = $content;
				//$i = $this->more_info($msg_part,$i,&$info,"disposition");
				$info->ifdisposition = true;
				break;
			  case "mime-version:" :
				//$pos = strpos($content,"=");
				//$info->parameters[] = new msg_params("MIME-Version",substr($content,$pos+1));
				$new_idx = count($info->parameters);
				$info->parameters[$new_idx] = new att_parameter;
				$info->parameters[$new_idx]->attribute = 'Mime-Version';
				$info->parameters[$new_idx]->value = $content;
				$info->ifparameters = true;
				break;
			  case "boundary" :
				$new_idx = count($info->parameters);
				$info->parameters[$new_idx] = new att_parameter;
				$info->parameters[$new_idx]->attribute = 'boundary';
				$info->parameters[$new_idx]->value = trim($content);
				break;
			  default : break;
			}
		}
		if ($this->debug_dcom) { echo 'pop3: Leaving get_structure<br>'; }
		return $info;
	}

	function fetchstructure($stream_notused,$msg_num,$flags="")
	{
		if ($this->debug_dcom) { echo 'pop3: Entering fetchstructure<br>'; }
		$header_array = $this->get_header_raw($stream,$msg_num);
		if (!$header_array)
		{
			return false;
		}
		
		echo '<br>dumping header_array: <br>';
		var_dump($header_array);
		echo '<br><br><br>';
		
		$info = $this->get_structure($header_array,1);
		
		echo 'dumping info: <br>';
		var_dump($info);
		echo '<br><br><br>';
		
		/*
		if (!$info->bytes)
		{
			$rc = ($this->msg2socket($stream,"LIST $msg_num\n"));
			if (!($this->pop_socket2msg($stream)))
			{
				$pos = strpos($this->err[msg]," ");
				$info->bytes = substr($this->err[msg],$pos+1);
			}
		}
		if ($info->type == 1)
		{ 
			// multipart
			$body = $this->get_body($stream,$msg_num);
			$boundary = $this->get_boundary(&$info);
			$boundary = str_replace("\"","",$boundary);
			$this->boundary = $boundary;
			for ($i=1;$i<=$body[0];$i++)
			{
				$pos1 = strpos($body[$i],"--$boundary");
				$pos2 = strpos($body[$i],"--$boundary--");
				if (is_int($pos2) && !$pos2)
				{
					break;
				}
				if (is_int($pos1) && !$pos1)
				{
					$info->parts[] = $this->get_structure($body,&$i,true);
				}
			}
		}
		//$this->got_structure = true;
		*/
		
		if ($this->debug_dcom) { echo 'pop3: Leaving fetchstructure<br>'; }
		return $info;
	}

	function header($stream_notused,$msg_nr,$fromlength="",$tolength="",$defaulthost="")
	{
		if ($this->debug_dcom) { echo 'pop3: Entering header<br>'; }
		$info = new msg_headinfo;
		//$info->size = $this->size_msg($stream,$msg_nr);
		$header_array = $this->get_header_raw($stream,$msg_nr);
		if (!$header_array)
		{
			return false;
		}
		for ($i=1;$i<=$header_array[0];$i++)
		{
			$pos = strpos($header_array[$i]," ");
			if (is_int($pos) && !$pos)
			{
				continue;
			}
			$keyword = strtolower(substr($header_array[$i],0,$pos));
			$content = trim(substr($header_array[$i],$pos+1));
			switch ($keyword)
			{
				case "from"	:
				case "from:"	:
				  $info->from = $this->get_addr_details("from",$content,&$header_array,&$i);
				  break;
				case "to"	:
				case "to:"	: 
				  // following two lines need to be put into a loop!
				  $info->to   = $this->get_addr_details("to",$content,&$header_array,&$i);
				  break;
				case "cc"	:
				case "cc:"	:
				  $info->cc   = $this->get_addr_details("cc",$content,&$header_array,&$i);
				  break;
				case "bcc"	:
				case "bcc:"	:
				  $info->bcc  = $this->get_addr_details("bcc",$content,&$header_array,&$i);
				  break;
				case "reply-to"	:
				case "reply-to:"	:
				  $info->reply_to = $this->get_addr_details("reply_to",$content,&$header_array,&$i);
				  break;
				case "sender"	:
				case "sender:"	:
				  $info->sender = $this->get_addr_details("sender",$content,&$header_array,&$i);
				  break;
				case "return-path"	:
				case "return-path:"	:
				  $info->return_path = $this->get_addr_details("return_path",$content,&$header_array,&$i);
				  break;
				case "subject"	:
				case "subject:"	:
				case "Subject:"	:
				  $pos = strpos($header_array[$i+1]," "); if (is_int($pos) && !$pos)
				  {
					$i++; $content .= chop($header_array[$i]);
				  }
				  $info->subject = htmlspecialchars($content);
				  $info->Subject = htmlspecialchars($content);
				  break;
	
				// only temp
				case "message-id"  :
				case "message-id:" :
				  $info->message_id = htmlspecialchars($content);
				  break;
				case "newsgroups:" :
				  $info->newsgroups = htmlspecialchars($content);
				  break;
				case "references:" :
				  $info->references = htmlspecialchars($content);
				  break;
				case "in-reply-to:" :
				  $info->in_reply_to = htmlspecialchars($content);
				  break;
				case "followup-to:" :
				  $info->follow_up_to = htmlspecialchars($content);
				  break;
				case "date:"	:
				  $info->date  = $content;
				  $info->udate = $this->make_udate($content);
				  break;
				default	:
				  break;
			}
		}
		if ($this->debug_dcom) { echo 'pop3: Leaving header<br>'; }
		return $info;
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




}
?>
