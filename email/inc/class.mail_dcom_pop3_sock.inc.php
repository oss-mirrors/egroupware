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
	// = = = = = = = = = = = =
	//   Functions that DO NOTHING in POP3
	// = = = = = = = = = = = =	
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
		if ($this->debug_dcom) { echo 'pop3: call to unused function in POP3: listmailbox (probable namespace discovery attempt)<br>'; }
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

	// = = = = = = = = = = = =
	//  OPEN and CLOSE Server Connection
	// = = = = = = = = = = = =
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

	// = = = = = = = = = = = =
	//  Mailbox Status and Information
	// = = = = = = = = = = = =
	function mailboxmsginfo($stream_notused='')
	{
		if ($this->debug_dcom) { echo 'pop3: Entering mailboxmsginfo<br>'; }
		// initialize the structure
		$info = new msg_mb_info;
		$info->Date = '';
		$info->Driver ='';
		$info->Mailbox = '';
		$info->Nmsgs = '';
		$info->Recent = '';
		$info->Unread = '';
		$info->Size = '';
		// POP3 will only give 2 items:
		// 1)  number of messages
		// 2) total size of mailbox
		// imap_mailboxmsginfo is the only function to return both of these
		if (!$this->msg2socket('STAT',"^\+ok",&$response))
		{
			$this->error();
			return False;
		}
		$num_msg = explode(' ',$response);
		// fill the only 2 data items we have
		$info->Nmsgs = trim($num_msg[1]);
		$info->Size  = trim($num_msg[2]);
		if ($info->Nmsgs)
		{
			if ($this->debug_dcom_extra)
			{
				echo 'pop3: mailboxmsginfo: info->Nmsgs: '.$info->Nmsgs.'<br>';
				echo 'pop3: mailboxmsginfo: info->Size: '.$info->Size.'<br>';
			}
			if ($this->debug_dcom) { echo 'pop3: Leaving mailboxmsginfo<br>'; }
			return $info;
		}
		else
		{
			if ($this->debug_dcom) { echo 'pop3: mailboxmsginfo: returining False<br>'; }
			if ($this->debug_dcom) { echo 'pop3: Leaving mailboxmsginfo<br>'; }
			return False;
		}
	}

	function status($stream_notused='', $fq_folder='',$options=SA_ALL)
	{
		if ($this->debug_dcom) { echo 'pop3: Entering status<br>'; }
		// POP3 has only INBOX so ignore $fq_folder
		// assume option is SA_ALL for POP3 because POP3 returns so little info anyway
		// initialize structure
		$info = new mailbox_status;
		$info->messages = '';
		$info->recent = '';
		$info->unseen = '';
		$info->uidnext = '';
		$info->uidvalidity = '';
		// POP3 only knows:
		// 1) many messages are in the box, which is:
		//	a) returned by imap_ mailboxmsginfo as ->Nmsgs (in IMAP this is thefolder opened)
		//	b) returned by imap_status (THIS) as ->messages (in IMAP used for folders other than the opened one)
		// 2) total size of the box, which is:
		//	returned by imap_ mailboxmsginfo as ->Size		
		// Most Efficient Method:
		//	call mailboxmsginfo and fill THIS structurte from that
		$mb_msg_info = $this->mailboxmsginfo($stream_notused);
		// all POP3 can return from imap_status is messages
		$info->messages = $mb_msg_info->Nmsgs;
		if ($this->debug_dcom) { echo 'pop3: status: info->messages: '.$info->messages.'<br>'; }
		if ($this->debug_dcom) { echo 'pop3: Leaving status<br>'; }
		return $info;
	}

	// returns number of messages in the mailbox
	function num_msg($stream_notused='')
	{
		if ($this->debug_dcom) { echo 'pop3: Entering num_msg<br>'; }
		// Most Efficient Method:
		//	call mailboxmsginfo and fill THIS size data from that
		$mb_msg_info = $this->mailboxmsginfo($stream_notused);
		$return_num_msg = $mb_msg_info->Nmsgs;
		if ($this->debug_dcom) { echo 'pop3: num_msg: '.$return_num_msg.'<br>'; }
		if ($this->debug_dcom) { echo 'pop3: Leaving num_msg<br>'; }
		return $return_num_msg;
	}

	// = = = = = = = = = = = =
	//  Message Sorting
	// = = = = = = = = = = = =
	function sort($stream_notused='',$criteria=SORTDATE,$reverse=False,$options='')
	{
		if ($this->debug_dcom) { echo 'pop3: Entering sort<br>'; }
		
		// nr_of_msgs on pop server
		$msg_num = $this->num_msg($stream_notused);
		
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
				//if ($this->debug_dcom) { echo 'pop3: sort: field_list: '.serialize($field_list).'<br><br><br>'; }
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
			//echo '('.$i.') Field: <b>'.$value."</b>\t\tMsg Num: <b>".$key."</b><br>\n";
			$i++;
		}
		@reset($return_array);
		if ($this->debug_dcom) { echo 'pop3: Leaving sort<br>'; }
		return $return_array;
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

	// = = = = = = = = = = = =
	//  Message Structure and Information
	// = = = = = = = = = = = =
	function fetchstructure($stream_notused,$msg_num,$flags="")
	{
		if ($this->debug_dcom) { echo 'pop3: Entering fetchstructure<br>'; }
		// first get the raw glob header
		$headers_raw = $this->get_header_raw($stream_notused,$msg_num);
		// unwrap any wrapped headers - using CR_LF_TAB as rfc822 "whitespace"
		$headers_raw = str_replace("\r\n\t"," ",$headers_raw);
		// unwrap any wrapped headers - using CR_LF_SPACE as rfc822 "whitespace"
		$headers_raw = str_replace("\r\n "," ",$headers_raw);
		// make raw headers into an array, throw away blank lines and "Received:" lines
		$header_array = Array();
		$header_array = $this->glob_to_array($headers_raw, False, '', False);
		if ($this->debug_dcom_extra)
		{
			echo 'pop3: fetchstructure iteration:<br>';
			for($i=0;$i < count($header_array);$i++)
			{
				echo '+'.htmlspecialchars($header_array[$i]).'<br>';
			}
		}
		if (!$header_array)
		{
			if ($this->debug_dcom) { echo 'pop3: Leaving fetchstructure with error<br>'; }
			return False;
		}
		$info = $this->sub_get_structure($header_array,1);
		if ((string)$info->type == '')
		{
			// default type - RFC says is Text (unless you are dealing with an attachment)
			$info->type = $this->default_type(True);
			// if no type we should NOT have a subtype, or else something is wrong
			$info->subtype = $this->default_subtype($info->type);
			$info->ifsubtype = true;
		}
		if ($info->encoding == '')
		{
			$info->encoding = $this->default_encoding();
		}
		if ($info->bytes == '')
		{
			if (!$this->msg2socket('LIST '.$msg_num,"^\+ok",&$response))
			{
				$this->error();
				if ($this->debug_dcom) { echo 'pop3: Leaving fetchstructure with error<br>'; }
				return False;
			}
			$list_response = explode(' ',$response);
			$info->bytes = (int)trim($list_response[2]);
		}
		if ($this->debug_dcom_extra)
		{
			echo '<br>dumping fetchstructure return info: <br>';
			var_dump($info);
			echo '<br><br><br>';
		}
		if ($this->debug_dcom) { echo 'pop3: Leaving fetchstructure<br>'; }
		return $info;
	}

	function sub_get_structure($header_array,$line_nr,$is_multi=false)
	{
		$debug_mime = $this->debug_dcom_extra;
		//$debug_mime = True;
		//$debug_mime = False;
		
		if ($this->debug_dcom) { echo 'pop3: Entering sub_get_structure<br>'; }
		// initialize the structure
		$info = new msg_struct;
		$info->type = '';
		$info->encoding = '';
		$info->ifsubtype = False;
		$info->subtype = '';
		$info->ifdescription = False;
		$info->description = '';
		$info->ifid = False;
		$info->id = '';
		$info->lines = '';
		$info->bytes = '';
		$info->ifdisposition = False;
		$info->disposition = '';
		$info->ifdparameters = False;
		$info->dparameters = array();
		$info->ifparameters = False;
		$info->parameters = array();
		$info->parts = array();
		/*
		// FILL THE DATA
		if ($is_multi)
		{
			$info->type = 0;
			$info->encoding = 0;
		}
		*/
		for ($i=0; $i < count($header_array) ;$i++)
		{
			$pos = strpos($header_array[$i]," ");
			if ($debug_mime) { echo 'header_array['.$i.']: '.$header_array[$i].'<br>'; }
			if (is_int($pos) && ($pos==0))
			{
				continue;
			}
			$keyword = strtolower(substr($header_array[$i],0,$pos));
			$content = trim(substr($header_array[$i],$pos+1));
			if ($debug_mime) { echo 'pos: '.$pos.'<br>'; }
			if ($debug_mime) { echo 'keyword: ['.$keyword.']<br>'; }
			if ($debug_mime) { echo 'content: ['.$content.']<br>- - - -<br>'; }
			switch ($keyword)
			{
			  case "content-type:" :
				// this will fill type and (hopefully) subtype
				$this->parse_type_subtype(&$info,$content);
				// ALSO, typically Paramaters are on this line as well
				$pos_param = strpos($content,";");
				if ($pos_param > 0)
				{
					// feed the whole param line into this function
					$content = substr($content,$pos_param+1);
					$this->parse_msg_params(&$info,$content);
				}
				break;
			  case "content-transfer-encoding:" :
				$info->encoding = $this->encoding_str_to_int($content);
				break;
			  case "content-description:" :
				$info->description   = $content;
				//$i = $this->more_info($msg_part,$i,&$info,"description");
				$info->ifdescription = true;
				break;
			  case "content-disposition:" :
				// disposition MAY have Paramaters on this line as well
				$pos_param = strpos($content,";");
				if ($pos_param > 0)
				{
					$content = substr($content,0,$pos_param);
				}
				$info->disposition = $content;
				$info->ifdisposition = True;
				// parse paramaters if any
				if ($pos_param > 0)
				{
					// feed the whole param line into this function
					$content = substr($content,$pos_param+1);
					$this->parse_msg_params(&$info,$content,False);
				}
				break;
			  case "content-identifier:" :
			  case "content-id:" :
			  case "message-id:" :
				if ((strstr($content, '<'))
				&& (strstr($content, '>')))
				{
					$content = str_replace('<','',$content);
					$content = str_replace('>','',$content);
				}
				//$i = $this->more_info($msg_part,$i,&$info,"id");
				$info->id   = $content;
				$info->ifid = true;
				break;
			  case "content-length:" :
				$info->bytes = (int)$content;
				break;
			  case "content-disposition:" :
				$info->disposition   = $content;
				//$i = $this->more_info($msg_part,$i,&$info,"disposition");
				$info->ifdisposition = true;
				break;
			  case "lines:" :
				$info->lines = (int)$content;
				break;
			  case "mime-version:" :
				$new_idx = count($info->parameters);
				$info->parameters[$new_idx] = new msg_params("Mime-Version",$content);
				$info->ifparameters = true;
				break;
			  default : break;
			}
		}
		if ($this->debug_dcom) { echo 'pop3: Leaving sub_get_structure<br>'; }
		return $info;
	}

	// used to get type and subtype
	function parse_type_subtype($info,$content)
	{
		if ($this->debug_dcom) { echo 'pop3: Entering parse_type_subtype<br>'; }
		// used by pop_fetchstructure only
		// get rid of any other params that might be here
		$pos = strpos($content,";");
		if ($pos > 0)
		{
			$content = substr($content,0,$pos);
		}
		// split type from subtype
		$pos = strpos($content,"/");
		if ($pos > 0)
		{
			$prim_type = strtolower(substr($content,0,$pos));
			$info->subtype = strtolower(substr($content,$pos+1));
			$info->ifsubtype = True;
		}
		else
		{
			$prim_type = strtolower($content);
		}
		$info->type = $this->type_str_to_int($prim_type);
		if ($info->ifsubtype == False)
		{
			// use RFC default for subtype
			$info->subtype = $this->default_subtype($info->type);
			$info->ifsubtype = True;
		}
		if ($this->debug_dcom_extra)
		{
			echo 'pop3: info->type '.$info->type.'<br>';
			echo 'pop3: info->ifsubtype '.$info->ifsubtype.'<br>';
			echo 'pop3: info->subtype '.$info->subtype.'<br>';
		}

		if ($this->debug_dcom) { echo 'pop3: Leaving parse_type_subtype<br>'; }
	}

	function parse_msg_params($info,$content,$is_disposition_param=False)
	{
		// seperate param strings into an string list array
		$param_list = Array();
		if (strstr($content, ';'))
		{
			$param_list = explode(";",$content);
		}
		else
		{
			$param_list[0] = $content;
		}
		// process each param string
		for ($x=0; $x < count($param_list) ;$x++)
		{
			$pos_token = strpos($param_list[$x],"=");
			if ($pos_token == 0)
			{
				// error - not a regular param=value pair
				$param_attrib = trim($param_list[$x]);
				$param_value = 'UNKNOWN_PARAM_VALUE';
			}
			else
			{
				$param_attrib = trim(substr($param_list[$x],0,$pos_token));
				$param_value = trim(substr($param_list[$x],$pos_token+1));
				$param_value = str_replace("\"","",$param_value);
			}
			// are these typical message paramaters or the more rare "disposition" params
			if ($is_disposition_param == False)
			{
				// typical msg params
				$new_idx = count($info->parameters);
				$info->parameters[$new_idx] = new msg_params($param_attrib,$param_value);
				$info->ifparameters = true;
			}
			else
			{
				// content-disposition paramaters are pretty rare
				$new_idx = count($info->dparameters);
				$info->dparameters[$new_idx] = new msg_params($param_attrib,$param_value);
				$info->ifparameters = true;
			}
		}
	}

	function type_str_to_int($type_str)
	{
		switch ($prim_type)
		{
			case "text"	: $type_int = TYPETEXT; break;
			case "multipart"	: $type_int = TYPEMULTIPART; break;
			case "message"	: $type_int = TYPEMESSAGE; break;
			case "application" : $type_int = TYPEAPPLICATION; break;
			case "audio"	: $type_int = TYPEAUDIO; break;
			case "image"	: $type_int = TYPEIMAGE; break;
			case "video"	: $type_int = TYPEVIDEO; break;
			default		: $type_int = TYPEOTHER; break;
		}
		return $type_int;
	}

	function default_type($probably_text=True)
	{
		if ($probably_text)
		{
			return TYPETEXT;
		}
		else
		{
			return TYPEAPPLICATION;
		}
	}

	function default_subtype($type_int=TYPEAPPLICATION)
	{
		// APPLICATION/OCTET-STREAM is the default when NO info is available
		switch ($type_int)
		{
			case TYPETEXT		: return 'plain'; break;
			case TYPEMULTIPART	: return 'mixed'; break;
			case TYPEMESSAGE		: return 'rfc822'; break;
			case TYPEAPPLICATION	: return 'octet-stream'; break;
			case TYPEAUDIO		: return 'basic'; break;
			default			: return 'unknown'; break;
		}
	}

	function default_encoding()
	{
		return ENC7BIT;
	}

	// MAY BE OBSOLETED
	function more_info($header,$i,$info,$infokey)
	{
		// used by pop_fetchstructure only
		do
		{
			$pos = strpos($header[$i+1]," ");
			if (is_int($pos) && !$pos)
			{
				$i++;
				$info->$infokey .= ltrim($header[$i]);
			}
		}
		while (is_int($pos) && !$pos);
		return $i;
	}

	function encoding_str_to_int($encoding_str)
	{
		switch (strtolower($encoding_str))
		{
			case "7bit"		: $encoding_int = ENC7BIT; break;
			case "8bit"		: $encoding_int = ENC8BIT; break;
			case "binary"		: $encoding_int = ENCBINARY; break;
			case "base64"		: $encoding_int = ENCBASE64; break;
			case "quoted-printable" : $encoding_int = ENCQUOTEDPRINTABLE; break;
			case "other"		: $encoding_int = ENCOTHER; break;
			case "uu"		: $encoding_int = ENCUU; break;
			default			: $encoding_int = ENCOTHER; break;
		}
		return $encoding_int;
	}

	function size_msg($stream_notused,$msg_num)
	{
		if ($this->debug_dcom) { echo 'pop3: Entering size_msg<br>'; }
		if (!$this->msg2socket('LIST '.$msg_num,"^\+ok",&$response))
		{
			$this->error();
			return False;
		}
		$list_response = explode(' ',$response);
		$return_size = trim($list_response[2]);
		$return_size = (int)$return_size * 1;
		if ($this->debug_dcom) { echo 'pop3: size_msg: '.$return_size.'<br>'; }
		if ($this->debug_dcom) { echo 'pop3: Leaving size_msg<br>'; }
		return $return_size;
	}

	// = = = = = = = = = = = =
	//  Message Envelope Data
	// = = = = = = = = = = = =
	function header($stream_notused,$msg_num,$fromlength="",$tolength="",$defaulthost="")
	{
		if ($this->debug_dcom) { echo 'pop3: Entering header<br>'; }
		$info = new envelope;
		$info->Size = $this->size_msg($stream_notused,$msg_num);
		$header_array = $this->get_header($stream_notused,$msg_num);
		if (!$header_array)
		{
			if ($this->debug_dcom) { echo 'pop3: Leaving header with error<br>'; }
			return false;
		}
		for ($i=0; $i < count($header_array); $i++)
		{
			// POP3 ONLY !!! - POP3 considers ALL messages as "unseen" and/or "recent"
			// because POP3 does not retain such info as seen or unseen
			//$info->Unseen = 'U';
			// I *may* comment that out because I find this annoying
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
			//$addr_details = new msg_aka;
			$addr_details = new address;
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

	// ----  DataCommunications With POP3 Server  ------

	// = = = = = = = = = = = =
	//  DELETE a Message From the Server
	// = = = = = = = = = = = =
	function delete($stream_notused,$msg_num,$flags="")
	{
		if ($this->debug_dcom) { echo 'pop3: Entering delete<br>'; }
		// in PHP 4 msg_num can be
		// a) an integer referencing a single message
		// b1) a comma seperated list of message numbers "1,2,6"
		// b2) and/or a range of messages format [STARTRANGE][COLON][ENDRANGE] "1:5"  "6:*"
		// make an array of message numbers to delete
		$tmp_array = Array();
		$tmp_array = explode(",",(string)$msg_num);
		// process the array, and clean any empty elements (explode can suck like that sometimes)
		$msg_num_array = Array();
		for($i=0;$i < count($tmp_array);$i++)
		{
			$this_element = (string)$tmp_array[$i];
			if ($this->debug_dcom_extra) { echo 'pop3: delete prep: this_element: '.$this_element.'<br>'; }
			$this_element = trim($this_element);
			// do nothing if this is an empty array element
			if ($this_element != '')
			{
				// not empty - process it
				// do we have a range
				$cookie = strpos($this_element,':');
				if ($cookie > 0)
				{
					$start_num = substr($this_element,0,$cookie);
					$end_num = substr($this_element,$cookie+1);
					// wildcard * used?
					if ($end_num == '*')
					{
						$end_num = $this->num_msg($stream_notused);
					}
					// make sure we are dealing with integers now
					$start_num = (int)$start_num;
					$end_num = (int)$end_num;
					// add each number in this range to the msg_num_array
					for($z=$start_num; $z >= $end_num; $z++)
					{
						// add to the msg_num_array
						$new_idx = count($msg_num_array);
						$msg_num_array[$new_idx] = (int)$z;
						if ($this->debug_dcom_extra) { echo 'pop3: delete prep: range: msg_num_array['.$new_idx.'] = '.$z.'<br>'; }
					}
				}
				else
				{
					// not a range, should be a single msg_num
					// add to the msg_num_array
					$new_idx = count($msg_num_array);
					$msg_num_array[$new_idx] = (int)$this_element;
					if ($this->debug_dcom_extra) { echo 'pop3: delete prep: msg_num_array['.$new_idx.'] = '.$this_element.'<br>'; }
				}
			}
		}
		// we should now have a reliable array of msg_nums we need to delete from the server
		for($i=0;$i < count($msg_num_array);$i++)
		{
			$this_msg_num = $msg_num_array[$i];
			if ($this->debug_dcom_extra) { echo 'pop3: delete: deleting this_msg_num '.$this_msg_num.'<br>'; }
			if (!$this->msg2socket('DELE '.$this_msg_num,"^\+ok",&$response))
			{
				$this->error();
				if ($this->debug_dcom) { echo 'pop3: Leaving delete with error deleting msgnum '.$this_msg_num.'<br>'; }
				return False;
			}
		}
		// these messages are now marked for deletion by the POP3 server
		// they will be expunged when user sucessfully explicitly logs out
		// if we make it here I have to assume no errors
		if ($this->debug_dcom) { echo 'pop3: Leaving delete<br>'; }
		return True;
	}

	// = = = = = = = = = = = =
	//  Get Message Headers From Server
	// = = = = = = = = = = = =
	function fetchheader($stream_notused,$msg_num,$flags='')
	{
		// NEEDED: code for flags: FT_UID; FT_INTERNAL; FT_PREFETCHTEXT
		if ($this->debug_dcom) { echo 'pop3: Entering fetchheader<br>'; }
		
		$header_glob = $this->get_header_raw($stream_notused,$msg_num,$flags);
		
		// do we also need to get the text of the message?
		if ((int)$flags == FT_PREFETCHTEXT)
		{
			// what the user really wants here is the whole enchalada, i.e. the headers AND the message
			$header_glob = $header_glob
				."\r\n"
				.$this->get_body($stream_notused,$msg_num,$flags);
		}
		
		if ($this->debug_dcom) { echo 'pop3: Leaving fetchheader<br>'; }
		return $header_glob;
	}

	// returns headers exploded into a string list
	function get_header($stream_notused,$msg_num,$flags='')
	{
		if ($this->debug_dcom) { echo 'pop3: Entering get_header<br>'; }
		// get header glob
		$header_glob = $this->get_header_raw($stream_notused,$msg_num,$flags);
		// make the header blob into an array of strings, one array element per header line
		$header_array = $this->glob_to_array($header_glob, False, '', True);
		if ($this->debug_dcom) { echo 'pop3: Leaving get_header<br>'; }
		return $header_array;
	}

	// returns unprocessed glob header string
	function get_header_raw($stream_notused,$msg_num,$flags='')
	{
		if ($this->debug_dcom) { echo 'pop3: Entering get_header_raw<br>'; }
		if (!$this->msg2socket('TOP '.$msg_num.' 0',"^\+ok",&$response))
		{
			$this->error();
			if ($this->debug_dcom) { echo 'pop3: Leaving get_header_raw with error<br>'; }
			return False;
		}
		$glob = $this->read_port_glob('.');
		if ($this->debug_dcom) { echo 'pop3: Leaving get_header_raw<br>'; }
		return $glob;
	}


	// = = = = = = = = = = = =
	//  Get Message Body (Parts) From Server
	// = = = = = = = = = = = =

/*
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
*/

	function fetchbody($stream_notused,$msg_num,$part_num="",$flags="")
	{
		if ($this->debug_dcom) { echo 'pop3: Entering fetchbody (pass thru)<br>'; }

		// totally under construction
		$body = $this->get_body($stream_notused,$msg_num,$flags,False);
		// the false above is a temporary, custom option, says to NOT include the headers in the retuen
		if ($this->debug_dcom) { echo 'pop3: Leaving fetchbody (pass thru)<br>'; }
		return $body;
	}

	function get_body($stream_notused,$msg_num,$flags='',$phpgw_include_header=True)
	{
		// implements IMAP_BODY
		// NEEDED: code for flags: FT_UID; maybe FT_INTERNAL; FT_NOT; flag FT_PEEK has no effect on POP3
		if ($this->debug_dcom) { echo 'pop3: Entering get_body<br>'; }
		if (!$this->msg2socket('RETR '.$msg_num,"^\+ok",&$response))
		{
			$this->error();
			if ($this->debug_dcom) { echo 'pop3: Leaving get_body with error<br>'; }
			return False;
		}
		// grab header seperately
		// (will include if flag FT_NOT is present, means to also get the header)
		$glob_header = '';
		while ($line = $this->read_port())
		{
			if ((chop($line) == '.')
			|| (chop($line) == ''))
			{
				break;
			}
			$glob_header .= $line;
		}
		// should we include the headers in the return
		if (($flags == FT_NOT)
		|| ($phpgw_include_header == True))
		{
			// we need to include the header here
			$glob_body = $glob_header ."\r\n";
		}
		else
		{
			$glob_body = '';
		}
		// now get the body
		$glob_body = $this->read_port_glob('.');
		if ($this->debug_dcom_extra)
		{
			echo 'pop3: get_body DUMP<br>= = = First DUMP: glob_header<br>';
			echo htmlspecialchars($glob_header).'<br>';
			echo 'pop3: get_body DUMP<br>= = = Second DUMP: glob_body<br>';
			echo htmlspecialchars($glob_body).'<br><br>';
		}
		if ($this->debug_dcom) { echo 'pop3: Leaving get_body<br>'; }
		return $glob_body;
	}



}
?>
