<?php
	/***************************************************************************\
	* phpGroupWare - FeLaMiMail                                                 *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.phpgroupware.org                                               *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; either version 2 of the License, or (at your    *
	* option) any later version.                                                *
	\***************************************************************************/
	/* $Id$ */

	class socaching
	{
		var $public_functions = array
		(
			'addAtachment'	=> True,
			'action'	=> True
		);
		
		function socaching($_hostname, $_accountname, $_foldername, $_accountid)
		{
			$this->hostname		= $_hostname;
			$this->accountname	= $_accountname;
			$this->foldername	= $_foldername;
			$this->accountid	= $_accountid;
			
			$this->db		= $GLOBALS['phpgw']->db;
		}
		
		function addToCache($_data)
		{
			$query = sprintf("insert into phpgw_felamimail_cache ".
					 "(accountid, hostname, foldername, accountname, uid, date, subject, sender_name, sender_address, to_name, to_address, size, attachments) ".
					 "values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
					 $this->accountid, addslashes($this->hostname), 
					 addslashes($this->foldername), addslashes($this->accountname), 
					 $_data['uid'], $_data['date'], addslashes($_data['subject']),
					 addslashes($_data['sender_name']), addslashes($_data['sender_address']),
					 addslashes($_data['to_name']), addslashes($_data['to_address']),
					 $_data['size'],$_data['attachments']);
			$this->db->query($query);
			
			#print "$query<br>";
		}
		
		function getHeaders($_firstMessage='', $_numberOfMessages='', $_sort='')
		{
			switch($_sort)
			{
				case "0":
					$sort = "order by date desc";
					break;
				case "1":
					$sort = "order by date asc";
					break;
				case "2":
					$sort = "order by sender_address desc";
					break;
				case "3":
					$sort = "order by sender_address asc";
					break;
				case "4":
					$sort = "order by subject desc";
					break;
				case "5":
					$sort = "order by subject asc";
					break;
				default:
					$sort = "order by date desc";
			}
			
			$query = sprintf("select uid, date, subject, sender_name, sender_address, to_name, to_address, size, attachments from phpgw_felamimail_cache ".
					 "where accountid='%s' and hostname='%s' and foldername = '%s' and accountname='%s' $sort",
					 $this->accountid, addslashes($this->hostname),
					 addslashes($this->foldername), addslashes($this->accountname));
			#print "$query<br>";
			
			if($_firstMessage == '' && $_numberOfMessages == '')
			{
				$this->db->query("$query",__LINE__,__FILE__);
			}
			else
			{
				$this->db->limit_query("$query",$_firstMessage-1,__LINE__,__FILE__,$_numberOfMessages);
			}
			while($this->db->next_record())
			{
				$retValue[] = array(
						'uid'			=> $this->db->f('uid'),
						'sender_name'		=> $this->db->f('sender_name'), 
						'sender_address'	=> $this->db->f('sender_address'), 
						'to_name'		=> $this->db->f('to_name'), 
						'to_address'		=> $this->db->f('to_address'),
						'attachments'		=> $this->db->f('attachments')
						);
			}
			return $retValue;
		}
		
		//return the cached status numbers
		// 
		// return values
		// 0 : nothing cached for this folder so far
		// array with the currently cached infos
		function getImapStatus()
		{
			$query = sprintf("select messages,recent,unseen,uidnext,uidvalidity ".
					 "from phpgw_felamimail_folderstatus where ".
					 "hostname='%s' and ".
					 "accountname='%s' and ".
					 "foldername='%s' and ".
					 "accountid='%s'",
					 $this->hostname, 
					 $this->accountname,
					 $this->foldername,
					 $this->accountid);
			$this->db->query($query);
			if ($this->db->next_record())
			{
				$retValue = array
				(
					'messages'	=> $this->db->f("messages"),
					'recent'	=> $this->db->f("recent"),
					'unseen'	=> $this->db->f("unseen"),
					'uidnext'	=> $this->db->f("uidnext"),
					'uidvalidity'	=> $this->db->f("uidvalidity")
				);
				return $retValue;
			}
			else
			{
				return 0;
			}
		}
		
		function removeFromCache($_uid)
		{
			$query = sprintf("delete from phpgw_felamimail_cache ".
					 "where accountid='%s' and hostname='%s' and foldername = '%s' and accountname='%s' ".
					 "and uid='%s'",
					 $this->accountid, addslashes($this->hostname),
					 addslashes($this->foldername), addslashes($this->accountname),
					 $_uid);
			$this->db->query($query);
			
			#print "$query<br>";
		}
		
		function updateImapStatus($_status, $firstUpdate)
		{
			if ($firstUpdate == true)
			{
				$query = sprintf("insert into phpgw_felamimail_folderstatus ".
					 "(accountid,hostname,foldername,accountname,messages,recent,unseen,uidnext,uidvalidity) ".
					 "values('%s','%s','%s','%s','%s','%s','%s','%s','%s')",
					 $this->accountid, addslashes($this->hostname),
					 addslashes($this->foldername), addslashes($this->accountname),
					 $_status->messages, $_status->recent, $_status->unseen, $_status->uidnext,
					 $_status->uidvalidity);
			}
			else
			{
				$query = sprintf("update phpgw_felamimail_folderstatus ".
					 "set messages='%s', recent='%s', unseen='%s', uidnext='%s', uidvalidity='%s' ".
					 "where accountid='%s' and hostname='%s' and foldername = '%s' and accountname='%s'",
					 $_status->messages, $_status->recent, $_status->unseen, $_status->uidnext,
					 $_status->uidvalidity, $this->accountid, addslashes($this->hostname),
					 addslashes($this->foldername), addslashes($this->accountname));
			}
			$this->db->query($query);
		}
	}
?>