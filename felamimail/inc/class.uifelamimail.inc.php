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

	class uifelamimail
	{
		var $public_functions = array
		(
			'addVcard'		=> True,
			'deleteMessage'		=> True,
			'handleButtons'		=> True,
			'toggleFilter'		=> True,
			'viewMainScreen'	=> True,
			'compressFolder'	=> True
		);
		
		var $mailbox;		// the current folder in use
		var $startMessage;	// the first message to show
		var $sort;		// how to sort the messages
		var $moveNeeded;	// do we need to move some messages?

		function uifelamimail()
		{
			global $phpgw, $phpgw_info;
			
			if(isset($GLOBALS['HTTP_POST_VARS']["mark_unread_x"])) 
				$GLOBALS['HTTP_POST_VARS']["mark_unread"] = "true";
			if(isset($GLOBALS['HTTP_POST_VARS']["mark_read_x"])) 
				$GLOBALS['HTTP_POST_VARS']["mark_read"] = "true";
			if(isset($GLOBALS['HTTP_POST_VARS']["mark_unflagged_x"])) 
				$GLOBALS['HTTP_POST_VARS']["mark_unflagged"] = "true";
			if(isset($GLOBALS['HTTP_POST_VARS']["mark_flagged_x"])) 
				$GLOBALS['HTTP_POST_VARS']["mark_flagged"] = "true";
			if(isset($GLOBALS['HTTP_POST_VARS']["mark_deleted_x"])) 
				$GLOBALS['HTTP_POST_VARS']["mark_deleted"] = "true";

			$this->bofelamimail		= CreateObject('felamimail.bofelamimail');
			
			
			if($GLOBALS['HTTP_POST_VARS']["changeFilter"] == 'changeFilter' &&
				isset($GLOBALS['HTTP_POST_VARS']["filter"]))
			{
				// change filter
				$this->bofelamimail->sessionData['activeFilter'] = $GLOBALS['HTTP_POST_VARS']["filter"];
			}
			elseif(isset($GLOBALS['HTTP_GET_VARS']["filter"]))
			{
				// change filter
				$this->bofelamimail->sessionData['activeFilter'] = $GLOBALS['HTTP_GET_VARS']["filter"];
			}
			elseif(isset($GLOBALS['HTTP_POST_VARS']["mailbox"]) && 
				$GLOBALS['HTTP_GET_VARS']["menuaction"] == "felamimail.uifelamimail.handleButtons" &&
				empty($GLOBALS['HTTP_POST_VARS']["mark_unread"]) &&
				empty($GLOBALS['HTTP_POST_VARS']["mark_read"]) &&
				empty($GLOBALS['HTTP_POST_VARS']["mark_unflagged"]) &&
				empty($GLOBALS['HTTP_POST_VARS']["mark_flagged"]) &&
				empty($GLOBALS['HTTP_POST_VARS']["mark_deleted"]))
			{
				if ($GLOBALS['HTTP_POST_VARS']["folderAction"] == "changeFolder")
				{
					// change folder
					$this->bofelamimail->sessionData['mailbox']	= $GLOBALS['HTTP_POST_VARS']["mailbox"];
					$this->bofelamimail->sessionData['startMessage']= 1;
					$this->bofelamimail->sessionData['sort']	= 6;
					$this->bofelamimail->sessionData['activeFilter']= -1;
				}
				elseif($GLOBALS['HTTP_POST_VARS']["folderAction"] == "moveMessage")
				{
					//print "move messages<br>";
					$this->bofelamimail->sessionData['mailbox'] 	= urldecode($GLOBALS['HTTP_POST_VARS']["oldMailbox"]);
					$this->bofelamimail->sessionData['startMessage']= 1;
					if (is_array($GLOBALS['HTTP_POST_VARS']["msg"]))
					{
						// we need to initialize the classes first
						$this->moveNeeded = "1";
					}
				}
			}
			elseif(isset($GLOBALS['HTTP_POST_VARS']["mailbox"]) &&
				$GLOBALS['HTTP_GET_VARS']["menuaction"] == "felamimail.uifelamimail.handleButtons" &&
				!empty($GLOBALS['HTTP_POST_VARS']["mark_deleted"]))
			{
				// delete messages
				$this->bofelamimail->sessionData['startMessage']= 1;
			}
			elseif($GLOBALS['HTTP_GET_VARS']["menuaction"] == "felamimail.uifelamimail.deleteMessage")
			{
				// delete 1 message from the mail reading window
				$this->bofelamimail->sessionData['startMessage']= 1;
			}

			// navigate for and back
			if(isset($GLOBALS['HTTP_GET_VARS']["startMessage"]))
			{
				$this->bofelamimail->sessionData['startMessage'] = $GLOBALS['HTTP_GET_VARS']["startMessage"];
			}
			// change sorting
			if(isset($GLOBALS['HTTP_GET_VARS']["sort"]))
			{
				$this->bofelamimail->sessionData['sort'] = $GLOBALS['HTTP_GET_VARS']["sort"];
			}
			$this->bofelamimail->saveSessionData();
			
			$this->mailbox 		= $this->bofelamimail->sessionData['mailbox'];
			$this->startMessage 	= $this->bofelamimail->sessionData['startMessage'];
			$this->sort 		= $this->bofelamimail->sessionData['sort'];
			$this->filter 		= $this->bofelamimail->sessionData['activeFilter'];

			#$this->cats			= CreateObject('phpgwapi.categories');
			#$this->nextmatchs		= CreateObject('phpgwapi.nextmatchs');
			#$this->account			= $phpgw_info['user']['account_id'];
			$this->t			= CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
			#$this->grants			= $phpgw->acl->get_grants('notes');
			#$this->grants[$this->account]	= PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;
			$this->connectionStatus = $this->bofelamimail->openConnection();

			$this->rowColor[0] = $phpgw_info["theme"]["row_on"];
			$this->rowColor[1] = $phpgw_info["theme"]["row_off"];

			$this->dataRowColor[0] = $phpgw_info["theme"]["bg01"];
			$this->dataRowColor[1] = $phpgw_info["theme"]["bg02"];
			                 
		}

		function addVcard()
		{
			$messageID 	= $GLOBALS['HTTP_GET_VARS']['messageID'];
			$partID 	= $GLOBALS['HTTP_GET_VARS']['partID'];
			$attachment = $this->bofelamimail->getAttachment($messageID,$partID);
			
			$tmpfname = tempnam ($GLOBALS['phpgw_info']['server']['temp_dir'], "phpgw_");
			$fp = fopen($tmpfname, "w");
			fwrite($fp, $attachment['attachment']);
			fclose($fp);
			
			$vcard = CreateObject('phpgwapi.vcard');
			$entry = $vcard->in_file($tmpfname);
			$entry['owner'] = $GLOBALS['phpgw_info']['user']['account_id'];
			$entry['access'] = 'private';
			$entry['tid'] = 'n';
			
			_debug_array($entry);
			print "<br><br>";
			
			print quoted_printable_decode($entry['fn'])."<br>";
			
			#$boaddressbook = CreateObject('addressbook.boaddressbook');
			#$soaddressbook = CreateObject('addressbook.soaddressbook');
			#$soaddressbook->add_entry($entry);
			#$ab_id = $boaddressbook->get_lastid();
			
			unlink($tmpfname);
			
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function compressFolder()
		{
			$this->bofelamimail->compressFolder();
			$this->viewMainScreen();
		}

		function deleteMessage()
		{
			$message[] = $GLOBALS['HTTP_GET_VARS']["message"];

			$this->bofelamimail->deleteMessages($message);
			$this->viewMainScreen();
		}
		
		function display_app_header()
		{
			global $phpgw, $phpgw_info;
			
			$phpgw->common->phpgw_header();
			echo parse_navbar();
			
		}
	
		function handleButtons()
		{
			if($this->moveNeeded == "1")
			{
				$this->bofelamimail->moveMessages($GLOBALS['HTTP_POST_VARS']["mailbox"],
									$GLOBALS['HTTP_POST_VARS']["msg"]);
			}
			
			elseif(!empty($GLOBALS['HTTP_POST_VARS']["mark_deleted"]) &&
				is_array($GLOBALS['HTTP_POST_VARS']["msg"]))
			{
				$this->bofelamimail->deleteMessages($GLOBALS['HTTP_POST_VARS']["msg"]);
			}
			
			elseif(!empty($GLOBALS['HTTP_POST_VARS']["mark_unread"]) &&
				is_array($GLOBALS['HTTP_POST_VARS']["msg"]))
			{
				$this->bofelamimail->flagMessages("unread",$GLOBALS['HTTP_POST_VARS']["msg"]);
			}
			
			elseif(!empty($GLOBALS['HTTP_POST_VARS']["mark_read"]) &&
				is_array($GLOBALS['HTTP_POST_VARS']["msg"]))
			{
				$this->bofelamimail->flagMessages("read",$GLOBALS['HTTP_POST_VARS']["msg"]);
			}
			
			elseif(!empty($GLOBALS['HTTP_POST_VARS']["mark_unflagged"]) &&
				is_array($GLOBALS['HTTP_POST_VARS']["msg"]))
			{
				$this->bofelamimail->flagMessages("unflagged",$GLOBALS['HTTP_POST_VARS']["msg"]);
			}
			
			elseif(!empty($GLOBALS['HTTP_POST_VARS']["mark_flagged"]) &&
				is_array($GLOBALS['HTTP_POST_VARS']["msg"]))
			{
				$this->bofelamimail->flagMessages("flagged",$GLOBALS['HTTP_POST_VARS']["msg"]);
			}
			

			$this->viewMainScreen();
		}

		function viewMainScreen()
		{
			$bopreferences		= CreateObject('felamimail.bopreferences');
			$bofilter		= CreateObject('felamimail.bofilter');
			$mailPreferences	= $bopreferences->getPreferences();

			$urlMailbox = urlencode($this->mailbox);
			
			$maxMessages = $GLOBALS['phpgw_info']["user"]["preferences"]["common"]["maxmatchs"];
			
		
			$this->display_app_header();
			
			$this->t->set_file(array("body" => 'mainscreen.tpl'));
			$this->t->set_block('body','main');
			$this->t->set_block('body','status_row_tpl');
			$this->t->set_block('body','header_row_S');
			$this->t->set_block('body','header_row_');
			$this->t->set_block('body','header_row_AS');
			$this->t->set_block('body','header_row_RAS');
			$this->t->set_block('body','header_row_ADS');
			$this->t->set_block('body','header_row_F');
			$this->t->set_block('body','header_row_FA');
			$this->t->set_block('body','header_row_FS');
			$this->t->set_block('body','header_row_FAS');
			$this->t->set_block('body','header_row_R');
			$this->t->set_block('body','header_row_RS');
			$this->t->set_block('body','header_row_D');
			$this->t->set_block('body','header_row_DS');
			$this->t->set_block('body','header_row_A');
			$this->t->set_block('body','error_message');

			$this->translate();
			
			$this->t->set_var('oldMailbox',$urlMailbox);
			$this->t->set_var('image_path',PHPGW_IMAGES);
			$refreshTime = $GLOBALS['phpgw_info']['user']['preferences'][felamimail]['refreshTime'];
			if($refreshTime > 0)
			{
				$this->t->set_var('refreshTime',sprintf("setTimeout( \"refresh()\", %s );",$refreshTime*60*1000));
			}
			else
			{
				$this->t->set_var('refreshTime','');
			}
			// set the url to open when refreshing
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen'
			);
			$this->t->set_var('refresh_url',$GLOBALS['phpgw']->link('/index.php',$linkData));
			
			// set the default values for the sort links (sort by url)
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
				'startMessage'	=> 1,
				'sort'		=> "2"
			);
			$this->t->set_var('url_sort_from',$GLOBALS['phpgw']->link('/index.php',$linkData));
		
			// set the default values for the sort links (sort by date)
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
				'startMessage'	=> 1,
				'sort'		=> "0"
			);
			$this->t->set_var('url_sort_date',$GLOBALS['phpgw']->link('/index.php',$linkData));
		
			// set the default values for the sort links (sort by subject)
			$linkData = array
			(
				'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
				'startMessage'	=> 1,
				'sort'		=> "4"
			);
			$this->t->set_var('url_sort_subject',$GLOBALS['phpgw']->link('/index.php',$linkData));
			
			// create the filter ui
			$filterList = $bofilter->getFilterList();
			if($this->filter == -1)
				$filterUI .= "<option value=\"-1\">".lang('no filter')."</option>";
			else
				$filterUI .= "<option value=\"-1\" selected>".lang('no filter')."</option>";
			while(list($key,$value) = @each($filterList))
			{
				$selected="";
				if($this->filter == $key) $selected="selected";
				$filterUI .= "<option value=".$key." $selected>".$value['filterName']."</option>";
			}
			$this->t->set_var('filter_options',$filterUI);
			
			// create the urls for sorting
			switch($this->sort)
			{
				case "0":
					$linkData = array
					(
						'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
						'startMessage'	=> 1,
						'sort'		=> "1"
					);
					$this->t->set_var('url_sort_date',$GLOBALS['phpgw']->link('/index.php',$linkData));
					
					break;
				case "2":
					$linkData = array
					(
						'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
						'startMessage'	=> 1,
						'sort'		=> "3"
					);
					$this->t->set_var('url_sort_from',$GLOBALS['phpgw']->link('/index.php',$linkData));
					
					break;
				case "4":
					$linkData = array
					(
						'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
						'startMessage'	=> 1,
						'sort'		=> "5"
					);
					$this->t->set_var('url_sort_subject',$GLOBALS['phpgw']->link('/index.php',$linkData));
					
					break;
			}

			if($this->connectionStatus != 'True')
			{
				$this->t->set_var('message',$this->connectionStatus);
				$this->t->parse('header_rows','error_message',True);
			}
			else
			{
				$folders = $this->bofelamimail->getFolderList('true');
			
				$headers = $this->bofelamimail->getHeaders($this->startMessage, $maxMessages, $this->sort);
			
				// create the listing of subjects
				$maxSubjectLength = 80;
				$maxAddressLength = 30;
				for($i=0; $i<count($headers['header']); $i++)
				{
					if (!empty($headers['header'][$i]['subject']))
					{
						// make the subject shorter if it is to long
						if(strlen($headers['header'][$i]['subject']) > $maxSubjectLength)
						{
							$headers['header'][$i]['subject'] = substr($headers['header'][$i]['subject'],0,$maxSubjectLength)."...";
						}
						$headers['header'][$i]['subject'] = htmlentities($headers['header'][$i]['subject']);
						if($headers['header'][$i]['attachments'] == "true")
						{
							$image = '<img src="'.PHPGW_IMAGES.'/attach.gif" border="0">';
							$headers['header'][$i]['subject'] = "$image&nbsp;".$headers['header'][$i]['subject'];
						}
						$this->t->set_var('header_subject', $headers['header'][$i]['subject']);
					
					}
					else
					{
						$this->t->set_var('header_subject',htmlentities("(".lang('no subject').")"));
					}
				
					if ($mailPreferences['sent_folder'] == $this->mailbox)
					{
						if (!empty($headers['header'][$i]['to_name']))
						{
							$sender_name	= $headers['header'][$i]['to_name'];
							$full_address	=
								$headers['header'][$i]['to_name'].
								" <".
								$headers['header'][$i]['to_address'].
								">";
						}
						else
						{
							$sender_name	= $headers['header'][$i]['to_address'];
							$full_address	= $headers['header'][$i]['to_address'];
						}
						$this->t->set_var('lang_from',lang("to"));
					}
					else
					{
						if (!empty($headers['header'][$i]['sender_name']))
						{
							$sender_name	= $headers['header'][$i]['sender_name'];
							$full_address	= 
								$headers['header'][$i]['sender_name'].
								" <".
								$headers['header'][$i]['sender_address'].
								">";
						}
						else
						{
							$sender_name	= $headers['header'][$i]['sender_address'];
							$full_address	= $headers['header'][$i]['sender_address'];
						}
						$this->t->set_var('lang_from',lang("from"));
					}
					if(strlen($sender_name) > $maxAddressLength)
					{
						$sender_name = substr($sender_name,0,$maxAddressLength)."...";
					}
					$this->t->set_var('sender_name',$sender_name);
					$this->t->set_var('full_address',$full_address);
				
					if($GLOBALS['HTTP_GET_VARS']["select_all"] == "select_all")
					{
						$this->t->set_var('row_selected',"checked");
					}

					$this->t->set_var('message_counter',$i);
					$this->t->set_var('message_uid',$headers['header'][$i]['uid']);
					$this->t->set_var('date',$headers['header'][$i]['date']);
					$this->t->set_var('size',$this->show_readable_size($headers['header'][$i]['size']));
					$flags = "";
					if(!empty($headers['header'][$i]['recent'])) $flags .= "R";
					if(!empty($headers['header'][$i]['flagged'])) $flags .= "F";
					if(!empty($headers['header'][$i]['answered'])) $flags .= "A";
					if(!empty($headers['header'][$i]['deleted'])) $flags .= "D";
					if(!empty($headers['header'][$i]['seen'])) $flags .= "S";
					#$this->t->set_var('flags',$flags);	

#					$linkData = array
#					(
#						'mailbox'	=> $urlMailbox,
#						'passed_id'	=> $headers['header'][$i]['id'],
#						'uid'		=> $headers['header'][$i]['uid'],
#					);
#					$this->t->set_var('url_read_message',$GLOBALS['phpgw']->link('/felamimail/read_body.php',$linkData));
				
					$linkData = array
					(
						'menuaction'    => 'felamimail.uidisplay.display',
						'showHeader'	=> 'false',
						'uid'		=> $headers['header'][$i]['uid']
					);
					$this->t->set_var('url_read_message',$GLOBALS['phpgw']->link('/index.php',$linkData));
				
					$linkData = array
					(
						'menuaction'    => 'felamimail.uicompose.compose',
						'send_to'	=> urlencode($headers['header'][$i]['sender_address'])
					);
					$this->t->set_var('url_compose',$GLOBALS['phpgw']->link('/index.php',$linkData));
					
					$linkData = array
					(
						'menuaction'    => 'addressbook.uiaddressbook.add_email',
						'add_email'	=> urlencode($headers['header'][$i]['sender_address']),
						'name'		=> urlencode($headers['header'][$i]['sender_name']),
						'referer'	=> urlencode($GLOBALS['PHP_SELF'].'?'.$GLOBALS['QUERY_STRING'])
					);
					$this->t->set_var('url_add_to_addressbook',$GLOBALS['phpgw']->link('/index.php',$linkData));
					
					$this->t->set_var('phpgw_images',PHPGW_IMAGES);
			
					$this->t->parse('header_rows','header_row_'.$flags,True);
				}
				$firstMessage = $headers['info']['first'];
				$lastMessage = $headers['info']['last'];
				$totalMessage = $headers['info']['total'];
				$langTotal = lang("total");		
			}
			
			// set the select all/nothing link
			if($GLOBALS['HTTP_GET_VARS']["select_all"] == "select_all")
			{
				// link to unselect all messages
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen'
				);
				$selectLink = sprintf("<a class=\"body_link\" href=\"%s\">%s</a>",
							$GLOBALS['phpgw']->link('/index.php',$linkData),
							lang("Unselect All"));
				$this->t->set_var('change_folder_checked','');
				$this->t->set_var('move_message_checked','checked');
			}
			else
			{
				// link to select all messages
				$linkData = array
				(
					'select_all'	=> 'select_all',
					'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen'
				);
				$selectLink = sprintf("<a class=\"body_link\" href=\"%s\">%s</a>",
							$GLOBALS['phpgw']->link('/index.php',$linkData),
							lang("Select all"));
				$this->t->set_var('change_folder_checked','checked');
				$this->t->set_var('move_message_checked','');
			}
			$this->t->set_var('select_all_link',$selectLink);
			

			// create the links for the delete options
			// "delete all" in the trash folder
			// "compress folder" in normal folders
			if ($mailPreferences['trash_folder'] == $this->mailbox &&
			    $mailPreferences['deleteOptions'] == "move_to_trash")
			{
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uifelamimail.compressFolder'
				);
				$trashLink = sprintf("<a class=\"body_link\" href=\"%s\">%s</a>",
							$GLOBALS['phpgw']->link('/index.php',$linkData),
							lang("delete all"));
				
				$this->t->set_var('trash_link',$trashLink);
			}
			elseif($mailPreferences['deleteOptions'] == "mark_as_deleted")
			{
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uifelamimail.compressFolder'
				);
				$trashLink = sprintf("<a class=\"body_link\" href=\"%s\">%s</a>",
							$GLOBALS['phpgw']->link('/index.php',$linkData),
							lang("compress folder"));
				$this->t->set_var('trash_link',$trashLink);
			}
			
			
			$this->t->set_var('message',lang("Viewing messages")." <b>$firstMessage</b> - <b>$lastMessage</b> ($totalMessage $langTotal)");
			if($firstMessage > 1)
			{
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
					'startMessage'	=> $this->startMessage - $maxMessages
				);
				$link = $GLOBALS['phpgw']->link('/index.php',$linkData);
				$this->t->set_var('link_previous',"<a class=\"body_link\" href=\"$link\">".lang("previous")."</a>");
			}
			else
			{
				$this->t->set_var('link_previous',lang("previous"));
			}
			
			if($totalMessage > $lastMessage)
			{
				$linkData = array
				(
					'menuaction'	=> 'felamimail.uifelamimail.viewMainScreen',
					'startMessage'	=> $this->startMessage + $maxMessages
				);
				$link = $GLOBALS['phpgw']->link('/index.php',$linkData);
				$this->t->set_var('link_next',"<a class=\"body_link\" href=\"$link\">".lang("next")."</a>");
			}
			else
			{
				$this->t->set_var('link_next',lang("next"));
			}
			$this->t->parse('status_row','status_row_tpl',True);
			
			for($i=0; $i<count($folders); $i++)
			{
				$selected = '';
				if ($this->mailbox == $folders[$i]) 
				{
					$selected = ' selected';
				}
				$options_folder .= sprintf('<option value="%s"%s>%s</option>',
							htmlspecialchars($folders[$i]),
							$selected,
							htmlspecialchars($folders[$i]));
			}
			$this->t->set_var('options_folder',$options_folder);
			
			$linkData = array
			(
				'menuaction'    => 'felamimail.uicompose.compose'
			);
			$this->t->set_var('url_compose_empty',$GLOBALS['phpgw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'    => 'felamimail.uifilter.mainScreen'
			);
			$this->t->set_var('url_filter',$GLOBALS['phpgw']->link('/index.php',$linkData));

			$linkData = array
			(
				'menuaction'    => 'felamimail.uifelamimail.handleButtons'
			);
			$this->t->set_var('url_change_folder',$GLOBALS['phpgw']->link('/index.php',$linkData));
			$this->t->set_var('lang_mark_messages_as',lang('mark messages as'));
			$this->t->set_var('lang_delete_selected',lang('delete selected messages'));
			                                                                                                                                                                        
			$this->t->parse("out","main");
			print $this->t->get('out','main');
			
			if($this->connectionStatus == 'True')
			{
				$this->bofelamimail->closeConnection();
			}
			$GLOBALS['phpgw']->common->phpgw_footer();
		
		}

		/* Returns a string showing the size of the message/attachment */
		function show_readable_size($bytes)
		{
			$bytes /= 1024;
			$type = 'k';
			
			if ($bytes / 1024 > 1)
			{
				$bytes /= 1024;
				$type = 'm';
			}
			
			if ($bytes < 10)
			{
				$bytes *= 10;
				settype($bytes, 'integer');
				$bytes /= 10;
			}
			else
				settype($bytes, 'integer');
			
			return $bytes . '<small>&nbsp;' . $type . '</small>';
		}
		
		function toggleFilter()
		{
			$this->bofelamimail->toggleFilter();
			$this->viewMainScreen();
		}

		function translate()
		{
			global $phpgw_info;			

			$this->t->set_var('th_bg',$phpgw_info["theme"]["th_bg"]);
			$this->t->set_var('bg_01',$phpgw_info["theme"]["bg01"]);
			$this->t->set_var('bg_02',$phpgw_info["theme"]["bg02"]);

			$this->t->set_var('lang_compose',lang('compose'));
			$this->t->set_var('lang_edit_filter',lang('edit filter'));
			$this->t->set_var('lang_move_selected_to',lang('move selected to'));
			$this->t->set_var('lang_doit',lang('do it!'));
			$this->t->set_var('lang_change_folder',lang('change folder'));
			$this->t->set_var('lang_move_message',lang('move messages'));
			$this->t->set_var('desc_read',lang("mark selected as read"));
			$this->t->set_var('desc_unread',lang("mark selected as unread"));
			$this->t->set_var('desc_important',lang("mark selected as flagged"));
			$this->t->set_var('desc_unimportant',lang("mark selected as unflagged"));
			$this->t->set_var('desc_deleted',lang("delete selected"));
			$this->t->set_var('lang_date',lang("date"));
			$this->t->set_var('lang_size',lang("size"));
			$this->t->set_var('lang_replied',lang("replied"));
			$this->t->set_var('lang_read',lang("read"));
			$this->t->set_var('lang_unread',lang("unread"));
			$this->t->set_var('lang_deleted',lang("deleted"));
			$this->t->set_var('lang_recent',lang("recent"));
			$this->t->set_var('lang_flagged',lang("flagged"));
			$this->t->set_var('lang_unflagged',lang("unflagged"));
			$this->t->set_var('lang_subject',lang("subject"));
			$this->t->set_var('lang_add_to_addressbook',lang("add to addressbook"));
			$this->t->set_var('lang_no_filter',lang("no filter"));
			$this->t->set_var('lang_connection_failed',lang("The connection to the IMAP Server failed!!"));
		}
	}
?>
