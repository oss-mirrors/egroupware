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

	var $num_msgs;

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

	function status_query($folder,$field)
	{
		if(!$this->write_port('a001 STATUS '.$folder.' ('.$field.')'))
		{
			$this->error();
		}
		$response = $this->read_port();
//		echo 'Response = '.$response."<br>\n";
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
//			echo 'Response = '.$response."<br>\n";
		}
		return False;
	}

	function open_folder($folder)
	{
		if(!$this->msg2socket('a001 SELECT "'.$folder.'"',"EXISTS",&$response))
		{
			$this->error();
		}
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

	function fetch_field($start,$stop,$element)
	{
		if(!$this->write_port('a001 FETCH '.$start.':'.$stop.' '.$element))
		{
			$this->error();
		}
		$response = $this->read_port();
		while(!ereg('FETCH completed',$response))
		{
//			echo 'Response = '.$response."<br>\n";
			$field = explode(' ',$response);
			$msg_num = intval($field[1]);
			$field_element[$msg_num] = substr($field[4],0,strpos($field[4],')'));
//			echo '<b>Field:</b> '.substr($field[4],0,strpos($field[4],')'))."\t = <b>Msg Num</b> ".$field_element[substr($field[4],0,strpos($field[4],')'))]."<br>\n";
			$response = $this->read_port();
		}
		return $field_element;
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
//			while(!ereg('FETCH completed',$response))
			while(chop($response)!='')
			{
//				echo 'Response = '.$response."<br>\n";
				if(ereg('^\*',$response))
				{
					$field = explode(' ',$response);
					$msg_num = $field[1];
				}
				if(ereg('^'.$element,$response))
				{
					$field_element[$msg_num] = $this->phpGW_quoted_printable_decode2(substr($response,strlen($element)+1));
//					echo '<b>Field:</b> '.$field_element[$msg_num]."\t = <b>Msg Num</b> ".$msg_num."<br>\n";
				}
				elseif(ereg('^'.strtoupper($element),$response))
				{
					$field_element[$msg_num] = $this->phpGW_quoted_printable_decode2(substr($response,strlen(strtoupper($element))+1));
//					echo '<b>Field:</b> '.$field_element[$msg_num]."\t = <b>Msg Num</b> ".$msg_num."<br>\n";
				}
				$response = $this->read_port();
			}
			$response = $this->read_port();
		}
		$response = $this->read_port();
		return $field_element;
	}

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

	function sort($folder='',$criteria=SORTDATE,$reverse=False,$options='')
	{
		if($folder == '' || $folder == $this>mailbox)
		{
			$folder = $this->mailbox;
			$num_msgs = $this->num_msgs;
		}
		else
		{
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
//			echo '('.$i++.') Field: <b>'.$value."</b>\t\tMsg Num: <b>".$key."</b><br>\n";
		}
		@reset($return_array);
		return $return_array;
	}

	function fetchstructure($msgnum)
	{

		if(!$this->write_port('a001 FETCH '.$msgnum.' BODY[HEADER]'))
//		if(!$this->write_port('a001 FETCH '.$msgnum.' BODY.PEEK[HEADER.FIELDS (Date To From Cc Subject Message-Id X-Priority Content-Type)]'))
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
}
?>
