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


	function open ($server, $port, $user,$passwd)
	{
		global $phpgw;

		//$port = 110;
		if (!$this->open_port($server,$port,15))
		{
			echo "<p><center><b>" . lang("There was an error trying to connect to your POP3 server.<br>Please contact your admin to check the servername, username or password.")."</b></center>";
			$phpgw->common->phpgw_exit();
		}
		$this->read_port();
		if(!$this->msg2socket('USER '.$user,"^\+ok",&$response) || !$this->msg2socket('PASS '.$passwd,"^\+ok",&$response))
		{
			$this->error();
			return False;
		}
		else
		{
			echo "Successful POP3 Login.<br>\n";
			return $this->socket;
		}
	}

	// returns number of messages in the mailbox
	function num_msg($folder='')
	{
		if (!$this->msg2socket('STAT',"^\+ok",&$response))
		{
			$this->error();
		}
//		$response = $this->read_port();
		$num_msg = explode(' ',$response);
		return $num_msg[1];
	}

	function mailboxmsginfo($folder='')
	{
		$info = new msg_mb_info;
		if (!$this->msg2socket('STAT',"^\+ok",&$response))
		{
			$this->error();
		}
//		$response = $this->read_port();
		$num_msg = explode(' ',$response);
		
		$info->Nmsgs = $num_msg[0];
		$info->Size  = $num_msg[1];

		if ($info->Nmsgs)
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
		$info = new mailbox_status;
		$info->messages = $this->num_msg($folder);
		return $info;
	}

	function fetch_header_element($start,$stop,$element)
	{
		for($i=$start;$i<=$stop;$i++)
		{
//			echo "Reading msg: ".$i."<br>\n";
			if(!$this->write_port('TOP '.$i.' 0'))
			{
				$this->error();
			}
			$this->read_and_load('.');
			if($this->header[$element])
			{
				$field_element[$i] = $this->phpGW_quoted_printable_decode2($this->header[$element]);
//				echo $field_element[$i].' = '.$this->phpGW_quoted_printable_decode2($this->header[$element])."<br>\n";
			}
			else
			{
				$field_element[$i] = $this->phpGW_quoted_printable_decode2($this->header[strtoupper($element)]);
//				echo $field_element[$i].' = '.$this->phpGW_quoted_printable_decode2($this->header[strtoupper($element)])."<br>\n";
			}
			
		}
		return $field_element;
	}

	function sort($folder='',$criteria=SORTDATE,$reverse=False,$options='')
	{
		// nr_of_msgs on pop server
		$msg_num = $this->num_msg($folder);
		
		// no msgs - no sort.
		if (!$msg_num)
		{
			return false;
		}
		//echo "Number of Msgs = ".$msg_num."<br>\r\n";
		switch($criteria)
		{
			case SORTDATE:
				$old_list = $this->fetch_header_element(1,$msg_num,'Date');
				$field_list = $this->convert_date_array($old_list);
				break;
			case SORTARRIVAL:
				break;
			case SORTFROM:
				$field_list = $this->fetch_header_element(1,$msg_num,'From');
				break;
			case SORTSUBJECT:
				$field_list = $this->fetch_header_element(1,$msg_num,'Subject');
				break;
			case SORTTO:
				$field_list = $this->fetch_header_element(1,$msg_num,'To');
				break;
			case SORTCC:
				$field_list = $this->fetch_header_element(1,$msg_num,'cc');
				break;
			case SORTSIZE:
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
		return $return_array;
	}     
}
?>
