<?php
	/**************************************************************************\
	* phpGroupWare - E-Mail                                                    *
	* http://www.phpgroupware.org                                              *
	* Written and maintained by Seek3r <dan@kuykendall.org                     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'  => 'email',
		'noheader'    => True,
		'nofooter'    => True,
		'nonavbar'    => True,
		'noappheader' => True,
		'noappfooter' => True
	);
	include('../header.inc.php');
	$GLOBALS['phpgw']->template->set_block('common','randomdata');
	$GLOBALS['phpgw']->template->set_var('data','just some lame text<br>');
	$GLOBALS['phpgw']->template->fp('main','randomdata', True);
	$GLOBALS['phpgw']->template->pfp('out', 'main');

	class msg
	{
		var $msgaccount;
		var $mailref;
		var $curfolder = 'INBOX';
		var $connection = 'notconnected';
		var $folders;
		var $timeout = 300;
		var $lastaction = '';
		var $msgstart = 0;
		var $limit = 999999;
		
		function start()
		{
echo 'start(): $this->folders <pre>';print_r($this->folders);echo '</pre>';
			$this->tree();
			$this->limit = 10;
			$this->cd('INBOX');
			$this->ls();
echo 'start(): $this->folders <pre>';print_r($this->folders);echo '</pre>';
			//$this->cd('INBOX.Business');
			//$this->ls();
			$this->cd('INBOX.ProjectsActive.reef');
			$this->ls();
			//$this->cd('INBOX');
//echo 'start(): $this->folders <pre>';print_r($this->folders);echo '</pre>';
		}
		function getmicrotime()
		{ 
    	list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
    } 

		function msg($msgaccount = 0)
		{
			$this->msgaccount = $msgaccount;
echo 'msg(): $this->msgaccount is '.$this->msgaccount.'<br>';
			/*
			if (isset($GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['port']))
			{
				$port = $GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['port'];
			}
			else
			{
			}
			if (isset($GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['protocol']))
			{
				$protocol = $GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['protocol'];
			}
			else
			{
			}
			if (isset($GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['server']))
			{
				$server = $GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['server'];
			}
			else
			{
			}
			*/
			$port = '143';
			$protocol = 'imap';
			$server = 'mail.kuykendall.org';
			//$server = 'localhost';
			$this->mailref = '{'.$server.':'.$port.'/'.$protocol.'}';

			$curfolder_session_data = $GLOBALS['phpgw']->session->appsession('curfolder_'.$this->msgaccount);
			if ($curfolder_session_data != '')
			{
				$this->cd($curfolder_session_data);
			}

			if ($this->sub_read_cache())
			{
echo 'msg(): using cached folders<br>';
			}
			else
			{
echo 'msg(): calling connect<br>';
				$this->connect('',True);
			}
		}

		function connect($folder = '', $refresh = False)
		{
//			if($this->
			$previous_folder = $this->curfolder;
			if ($folder != '' && $folder != $this->curfolder)
			{
				$this->curfolder = $folder;
				$folderchange = True;
			}
			else
			{
				$folderchange = False;
			}
			
			if ($this->connection == 'notconnected' || $folderchange || $needrefresh == True)
			{
				if (isset($GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['user']))
				{
					$user = $GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['user'];
				}
				else
				{
				}
				if (isset($GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['password']))
				{
					$password = $GLOBALS['phpgw_info']['user']['preferences']['email']['accounts'][$this->msgaccount]['password'];
				}
				else
				{
				}
				$user = 'test';
				$password = 'somepass';
				$havecache = $this->sub_read_cache($this->curfolder);


				if ($this->connection == 'notconnected')
				{
					echo 'no existing connection.<br>';
				}
				else
				{
					echo 'existing connection<br>';
				}	
				if ($folderchange)
				{
					echo 'folder change requested.<br>';
				}
				else
				{
					echo 'no folder change<br>';
				}	
				if ($refresh)
				{
					echo 'refresh requested.<br>';
				}
				else
				{
					echo 'no refresh<br>';
				}	
				if ($havecache)
				{
					echo 'I have the cache.<br>';
				}
				else
				{
					echo 'Im missing the cache<br>';
				}	







				if ($this->connection == 'notconnected' && $refresh == False && $havecache == True)
				{
echo 'connect(): no need to make connect<br>';
					$result = $this->connection;
					$readcache = False;
				}
				elseif ($this->connection == 'notconnected')
				{
echo 'connect(): establishing IMAP connection to '.$this->curfolder.'...';
					$result = imap_open ($this->mailref.$this->curfolder, $user, $password);
					$closeold = False;
					$readcache = True;
				}
				else
				{
echo 'connect(): changing IMAP connection to '.$this->curfolder.'...';
					$result = imap_open ($this->mailref.$this->curfolder, $user, $password);
					if (!$refresh)
					{
						$closeold = True;
						$readcache = True;
					}
					else
					{
						$readcache = True;
					}
				}

				if (!$result)
				{
echo ' failed<br>';
					$this->curfolder = $previous_folder;
					return False;				
				}
echo ' suceeded<br>';
				if ($closeold)
				{
					imap_close($this->connection, CL_EXPUNGE);
					$this->sub_write_cache($previous_folder);
				}

				if ($readcache)
				{
					$this->sub_read_cache($this->curfolder);
				}
				$this->connection = $result;
//				$this->sub_write_cache();
			}
			return True;
		}

		function cd($folder = '')
		{
			$previous_folder = $this->curfolder;
			if ($folder != '' && $folder != $this->curfolder)
			{
				$result = $this->connect($folder);
				if (!$result)
				{
echo 'cd(): to '.$folder.' failed<br>';
					return False;
				}
			}
echo 'cd(): to '.$folder.' suceeded<br>';
			$GLOBALS['phpgw']->session->appsession('curfolder_'.$this->msgaccount, '',$this->curfolder);
			return True;
		}

		function tree($needrefresh = False, $getdetails = True)
		{
$starttime = $this->getmicrotime();
			if (!is_array($this->folders) || $needrefresh == True)
			{
echo 'tree(): getting new folder list<br>';
				$this->connect();
				$folderlist = imap_listmailbox($this->connection, $this->mailref, '*');
				reset($folderlist);
				while (list($key,$value) = each ($folderlist))
				{
					$longname = str_replace ($this->mailref, '', $value);
					if (!is_array($this->folders[$longname]) && $longname != '')
					{
						$this->folders[$longname] = Array();
					}
					$validfolders[$longname] = True;
				}
				ksort($this->folders);
				while (list($key,$value) = each ($this->folders))
				{
					if($key != '')
					{
						if($getdetails && $key != '')
						{
							$uidnext_old = $this->folders[$key]['details']['uidnext'];
							$this->sub_read_folder($key);
							$uidnext_new = $this->folders[$key]['details']['uidnext'];
							if ($uidnext_old != $uidnext_new)
							{
								unset($this->folders[$key]['msg_list']);
							}
						}
						//$this->folders[$key]['longname'] = $key;
						if (strstr ($key,'INBOX.') == 0)
						{
							$this->folders[$key]['shortname'] = str_replace ('INBOX.', '', $key);
						}
						else
						{
							$this->folders[$key]['shortname'] = $key;
						}
					}
					else
					{
						unset($this->folders[$key]);
					}
				}

				while (list($key, $value) = each($this->folders))
				{
					if (!$validfolders[$key])
					{
echo 'tree(): dropping '.$key.'from cache<br>';
						unset($this->folders[$key]);	
						$updatecache = True;
					}
				}

				ksort($this->folders);
echo 'tree(): saving to cache<br>';
				$this->sub_write_cache();
			}
			else
			{
echo 'tree(): NOT getting new folder list. Using cache<br>';
			}
			
$endtime = $this->getmicrotime();
$timediff = $endtime - $starttime;
echo 'tree(): took '.$timediff.' seconds.<br>';
			return $this->folders;
		}

		function ls($refresh = False, $sortby = 'date', $assending = True, $folder = '')
		{
$starttime = $this->getmicrotime();
			$this->cd($folder);
echo 'ls(): $this->curfolder is '.$this->curfolder.'<br>';
echo 'ls(): $this->folders[ '.$this->curfolder.']<pre>';print_r($this->folders[$this->curfolder]);echo '</pre>';
			switch ($sortby)
			{
			    case 'arrival':
 							$sortcode = SORTARRIVAL;
			        break;
			    case 'from':
 							$sortcode = SORTFROM;
			        break;
 			    case 'subject':
 							$sortcode = SORTSUBJECT;
       			break;
 			    case 'to':
 							$sortcode = SORTTO;
       			break;
 			    case 'cc':
 							$sortcode = SORTCC;
       			break;
 			    case 'size':
 							$sortcode = SORTSIZE;
       			break;
					default:
							$sortcode = SORTDATE;
			}
						
			$updatecache = False;

			if (!is_array($this->folders[$this->curfolder]['msg_list']) || $refresh == True)
			{
				$this->connect('',True);
echo 'ls(): before sort $this->curfolder is '.$this->curfolder.'<br>';
				$this->folders[$this->curfolder]['msg_list'] = Array();
				$this->folders[$this->curfolder]['msg_list'] = imap_sort($this->connection,$sortcode,$assending,SE_UID);
				$updatecache = True;
			}
			if (!is_array($this->folders[$this->curfolder]['msgs']) || $refresh = True)
			{
				while (list($key, $value) = each($this->folders[$this->curfolder]['msg_list']))
				{
					$msg_list_reversed[$value] = True;
					if (!is_array($this->folders[$this->curfolder]['msgs'][$value]['header']))
					{
//echo 'ls(): adding '.$value.'<br>';
						$needed_msgs[$key] = $value;
					}
				}
//echo 'ls(): msg_list_reversed<pre>';print_r($msg_list_reversed);echo '</pre>';

				if(is_array($needed_msgs) && count($needed_msgs) >= 1)
				{
					$needed_msgs_list = implode(',',$needed_msgs);
echo 'ls(): $needed_msgs_list: '.$needed_msgs_list.'<br>';
					$this->connect('',True);
					$newmsgs = imap_fetch_overview ($this->connection, $needed_msgs_list, FT_UID);
					krsort($newmsgs);	
					while (list(, $value) = each($newmsgs))
					{
						/* FILTERS GO HERE */
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['msgno'] = $value->msgno;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['subject'] = $value->subject;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['date'] = $value->date;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['references'] = $value->references;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['message_id'] = $value->message_id;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['size'] = $value->size;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['recent'] = $value->recent;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['flagged'] = $value->flagged;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['answered'] = $value->answered;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['deleted'] = $value->deleted;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['seen'] = $value->seen;
						$this->folders[$this->curfolder]['msgs'][$value->uid]['header']['draft'] = $value->draft;
						$updatecache = True;
					}
				}
				while (list($key, $value) = each($this->folders[$this->curfolder]['msgs']))
				{
					if (!$msg_list_reversed[$key])
					{
echo 'ls(): dropping '.$key.'from cache<br>';
						unset($this->folders[$this->curfolder]['msgs'][$key]);	
						$updatecache = True;
					}
				}
			}

			if ($updatecache)
			{
echo 'ls(): saving to cache<br>';
				$this->sub_write_cache($this->curfolder);
			}
$endtime = $this->getmicrotime();
$timediff = $endtime - $starttime;
echo 'ls(): took '.$timediff.' seconds.<br>';

			return $this->folders[$this->curfolder]['msg_list'];
		}

		function sub_write_cache($folder = '')
		{
			if ($folder != '')
			{
echo 'sub_write_cache(): caching for $this->folders['.$folder.']<br>';
				$GLOBALS['phpgw']->session->appsession('folders_'.$this->msgaccount.'_'.$folder, '', $this->folders[$folder]['msgs']);
			}
			else
			{
				if (is_array($this->folders))
				{
					while (list($key) = each ($this->folders))
					{
						if (is_array($this->folders[$key]['msgs']))
						{
							if ($key != '')
							{
echo 'sub_write_cache(): seperatetly caching for $this->folders['.$key.']<br>';
								$tempdata[$key] = $this->folders[$key]['msgs'];
								unset($this->folders[$key]['msgs']);
								$GLOBALS['phpgw']->session->appsession('folders_'.$this->msgaccount.'_'.$key, '', $tempdata[$key]);
							}
						}
					}
echo 'sub_write_cache(): caching folders<br>';
					$GLOBALS['phpgw']->session->appsession('folders_'.$this->msgaccount, '', $this->folders);
					if (is_array($tempdata))
					{
						while (list($key) = each ($tempdata))
						{
							if ($key != '')
							{
echo 'sub_write_cache(): reattaching messages for $this->folders['.$key.']<br>';
								$this->folders[$key]['msgs'] = $tempdata[$key];
							}
						}
					}
				}
			}
		}

		function sub_read_cache($folder = '')
		{
			if ($folder != '')
			{
echo 'sub_read_cache(): getting cached messages for '.$folder.'...';
				$messages_session_data = $GLOBALS['phpgw']->session->appsession('folders_'.$this->msgaccount.'_'.$folder);
				if (is_array($messages_session_data))
				{
echo 'passed<br>';
					$this->folders[$this->curfolder]['msgs'] = $messages_session_data;
				}
				else
				{
echo 'failed<br>';
					return False;
				}
			}
			else
			{
echo 'sub_read_cache(): getting cached folders...';
				$folders_session_data = $GLOBALS['phpgw']->session->appsession('folders_'.$this->msgaccount);
				if (is_array($folders_session_data))
				{
echo 'passed<br>';
					$this->folders = $folders_session_data;
				}
				else
				{
echo 'failed<br>';
					return False;
				}
				$messages_session_data = $GLOBALS['phpgw']->session->appsession('folders_'.$this->msgaccount.'_'.$this->curfolder);
				if (is_array($messages_session_data))
				{
echo 'sub_read_cache(): adding cached messages for '.$this->curfolder.'<br>';
					$this->folders[$folder]['msgs'] = $messages_session_data;
				}
			}
			return True;
		}
		
		function sub_read_folder($folder = '', $refresh = False)
		{
			if ($folder == '')
			{
				$folder = $this->folder;
			}

			if (!is_array($this->folders[$folder]['details']) || $refresh = True)
			{
				//$this->folders[$key] = $folderdata;
				$this->connect('',True);
				$status = imap_status($this->connection, $this->mailref.$folder, SA_ALL);
				$this->folders[$folder]['details']['flags'] = $status->flags;
				$this->folders[$folder]['details']['messages'] = $status->messages;
				$this->folders[$folder]['details']['recent'] = $status->recent;
				$this->folders[$folder]['details']['unseen'] = $status->unseen;
				$this->folders[$folder]['details']['uidnext'] = $status->uidnext;
				$this->folders[$folder]['details']['uidvalidity'] = $status->uidvalidity;
				return True;
			}
			return False;
		}

		function sub_read_header($UID, $folder = '', $refresh = False)
		{
			if ($folder == '')
			{
				$folder = $this->folder;
			}

			if (!is_array($this->folders[$folder]['msgs'][$UID]['header']) || $refresh = True)
			{
				$previous_folder = $this->curfolder;
				$this->cd($folder);
				$this->connect('',True);
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['msgno'] = imap_msgno ($this->connection, $UID);
				$headervalues = imap_headerinfo($this->connection, $this->folders[$this->curfolder]['msgs'][$UID]['header']['msgno']);
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['subject'] = $headervalues->subject;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['date'] = $headervalues->date;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['udate'] = $headervalues->udate;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['references'] = $headervalues->references;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['in_reply_to'] = $headervalues->in_reply_to;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['followup_to'] = $headervalues->followup_to;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['remail'] = $headervalues->remail;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['newsgroups'] = $headervalues->newsgroups;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['message_id'] = $headervalues->message_id;
				$this->folders[$this->curfolder]['msgs'][$UID]['header']['size'] = $headervalues->Size;
				if ($headervalues->Recent == 'R')
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['recent'] = 1;
				}
				else
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['recent'] = 0;
				}
				if ($headervalues->Flagged == 'F')
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['flagged'] = 1;
				}
				else
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['flagged'] = 0;
				}
				if ($headervalues->Answered == 'A')
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['answered'] = 1;
				}
				else
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['answered'] = 0;
				}
				if ($headervalues->Deleted == 'D')
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['deleted'] = 1;
				}
				else
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['deleted'] = 0;
				}
				if ($headervalues->Unseen == 'U')
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['seen'] = 1;
				}
				else
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['seen'] = 0;
				}
				if ($headervalues->Draft == 'X')
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['draft'] = 1;
				}
				else
				{
					$this->folders[$this->curfolder]['msgs'][$UID]['header']['draft'] = 0;
				}
				
				$this->cd($previous_folder);
				return True;
			}
			return False;
		}
	} /* end of msg class */

	$msg = new msg;
	$msg->start();
