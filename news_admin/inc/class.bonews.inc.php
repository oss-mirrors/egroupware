<?php
	/**************************************************************************\
	* eGroupWare - News                                                        *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	* --------------------------------------------                             *
	* This program was sponsered by Golden Glair productions                   *
	* http://www.goldenglair.com                                               *
	\**************************************************************************/

	/* $Id$ */

	class bonews
	{
		var $sonews;
		var $acl;
		var $start = 0;
		var $query = '';
		var $sort  = '';
		var $cat_id;
		var $total = 0;
		var $debug;
		var $use_session = False;
		var $unixtimestampmax;
		var $dateformat;
		var $cats = array();

		function bonews($session=False)
		{
			$this->acl =& CreateObject('news_admin.boacl');
			$this->sonews =& CreateObject('news_admin.sonews');
			$this->accounts = $GLOBALS['egw']->accounts->get_list();
			$this->debug = False;
			if($session)
			{
				$this->read_sessiondata();
				$this->use_session = True;
				foreach(array('start','query','sort','order','cat_id') as $var)
				{
					$this->$var = get_var($var, array('POST', 'GET'), '');
				}

				$this->cat_id = $this->cat_id ? $this->cat_id : 'all';
				$this->save_sessiondata();
			}
			$this->catbo =& CreateObject('phpgwapi.categories','','news_admin');
			$this->cats = $this->catbo->return_array('all',0,False,'','','cat_name',True);
			settype($this->cats,'array');
			//change this around 19 Jan 2038 03:14:07 GMT
			$this->unixtimestampmax = 2147483647;
			$this->dateformat = $GLOBALS['egw_info']['user']['preferences']['common']['dateformat'];
			$c =& CreateObject('phpgwapi.config','news_admin');
			$c->read_repository();
			$this->config = $c->config_data;
		}

		function save_sessiondata()
		{
			$data = array(
				'start' => $this->start,
				'query' => $this->query,
				'sort'  => $this->sort,
				'order' => $this->order,
				'cat_id' => $this->cat_id,
			);
			if($this->debug) { echo '<br>Save:'; _debug_array($data); }
			$GLOBALS['egw']->session->appsession('session_data','news_admin',$data);
		}

		function read_sessiondata()
		{
			$data = $GLOBALS['egw']->session->appsession('session_data','news_admin');
			if($this->debug) { echo '<br>Read:'; _debug_array($data); }

			$this->start  = $data['start'];
			$this->query  = $data['query'];
			$this->sort   = $data['sort'];
			$this->order  = $data['order'];
			$this->cat_id = $data['cat_id'];
		}

		function get_newslist($cat_id, $start=0, $order='',$sort='',$limit=0,$activeonly=False)
		{
			$charset = $GLOBALS['egw']->translation->charset();
			
			$cats = False;
			if ($cat_id == 'all')
			{
				foreach($this->cats as $cat)
				{
					 if ($this->acl->is_readable($cat['id']))
					 {
						$cats[] = $cat['id'];
					 }
				 }
			}
			elseif($this->acl->is_readable($cat_id))
			{
				$cats = $cat_id;
			}
			
			if($cats)
			{
				$news = $this->sonews->get_newslist($cats, $start,$order,$sort,$limit,$activeonly,$this->total);
				foreach($news as $id => $item)
				{
					$news[$id]['content'] = ($item['is_html'] ? 
									$item['content'] : 
									nl2br(@htmlspecialchars($item['content'],ENT_COMPAT,$charset)
								));
				}
				return $news;
			}
			else
			{
				return array();
			}
		}

		function get_all_public_news($limit = 5)
		{
			$charset = $GLOBALS['egw']->translation->charset();
			
			$news = $this->sonews->get_all_public_news($limit);
			foreach($news as $id => $item)
			{
				$news[$id]['content'] = ($item['is_html'] ? 
								$item['content'] : 
								nl2br(@htmlspecialchars($item['content'],ENT_COMPAT,$charset)
							));
			}
			return $news;
		}

		function delete($news_id)
		{
			$this->sonews->delete($news_id);
		}

		function add($news)
		{
			return $this->acl->is_writeable($news['category']) ?
				$this->sonews->add($news) :
				false;
		}

		function edit($news)
		{
			$oldnews = $this->sonews->get_news($news['id']);
			return ($this->acl->is_writeable($oldnews['category']) && 
					$this->acl->is_writeable($news['category'])) ?
				$this->sonews->edit($news) :
				False;
		}

		function get_visibility(&$news)
		{
			$now = time();

			if ($news['end'] < $now)
			{
				return lang('Never');
			}
			else
			{
				if ($news['begin'] < $now)
				{
					if ($news['end'] == $this->unixtimestampmax)
					{
						return lang('Always');
					}
					else
					{
						return lang('until') . date($this->dateformat,$news['end']);
					}
				}
				else
				{
					if ($news['end'] == $this->unixtimestampmax)
					{
						return lang('from') . date($this->dateformat,$news['begin']);

					}
					else
					{
						return lang('from') . ' ' . date($this->dateformat,$news['begin']) . ' ' . 
							lang('until') . ' ' . date($this->dateformat,$news['end']);
					}
				}
			}
		}

		//return the selectboxes with calculated defaults, and change begin and end by sideaffect
		function get_options(&$news)
		{
			$now = time();
			//always is default
			if (!isset($news['begin']))
			{
				//these are only displayed values not necessarily the ones that will get stored
				$news['begin'] = $now;
				$news['end'] = $now;
				$from = 1;
				$until = 1;
			}
			//if enddate is in the past set option to never
			elseif ($news['end'] < $now)
			{
				$news['begin'] = $now;
				$news['end'] = $now;
				$from = 0;
				$until = 1;
			}
			else
			{
				if ($news['begin'] < $now)
				{
					$news['begin'] = $now;
					if ($news['end'] == $this->unixtimestampmax)
					{
						$news['end'] = $now;
						$from = 1;
						$until = 1;
					}
					else
					{
						$from = 0.5;
						$until = 0.5;
					}
				}
				else
				{
					if ($news['end'] == $this->unixtimestampmax)
					{
						$news['end'] = $now;
						$from = 0.5;
						$until = 1;
					}
					else
					{
						$from = 0.5;
						$until = 0.5;
					}
				}
			}
			$options['from'] = '<option value="1"' . (($from == 1) ? ' selected="selected"' : '') . '>' . lang('Always') . '</option>';
			$options['from'] .= '<option value="0"' . (($from == 0) ? ' selected="selected"' : '') . '>' . lang('Never') . '</option>';
			$options['from'] .= '<option value="0.5"' . (($from == 0.5) ? ' selected="selected"' : '') . '>' . lang('From') . '</option>';
			$options['until'] = '<option value="1"' . (($until == 1) ? ' selected="selected"' : '') . '>' . lang('Always') . '</option>';
			$options['until'] .= '<option value="0.5"' . (($until == 0.5) ? ' selected="selected"' : '') . '>' . lang('until') . '</option>';
			return $options;
		}

		//set the begin and end dates 
		function set_dates($from,$until,&$news)
		{
			switch($from)
			{
				//always
				case 1:
					$news['begin'] = $news['date'];
					$news['end'] = $this->unixtimestampmax;
					break;
				//never
				case 0:
					$news['begin'] = 0;
					$news['end'] = 0;
					break;
				default:
					$news['begin'] = mktime(0,0,0,(int)$news['begin_m'], (int)$news['begin_d'], (int)$news['begin_y']);
					switch($until)
					{
						case 1:
							$news['end'] = $this->unixtimestampmax;
							break;
						default:
							$news['end'] = mktime(0,0,0,(int)$news['end_m'], (int)$news['end_d'], (int)$news['end_y']);
					}
			}
		}

// 		function format_fields($fields)
// 		{
// 			$cat =& CreateObject('phpgwapi.categories','news_admin');

// 			$item = array(
// 				'id'          => $fields['id'],
// 				'date'        => $GLOBALS['egw']->common->show_date($fields['date']),
// 				'subject'     => $GLOBALS['egw']->strip_html($fields['subject']),
// 				'submittedby' => $fields['submittedby'],
// 				'content'     => $fields['content'],
// 				'status'      => lang($fields['status']),
// 				'cat'         => $cat->id2name($fields['cat'])
// 			);
// 			return $item;
// 		}

		function get_news($news_id)
		{
			$news = $this->sonews->get_news($news_id);
			
			if ($this->acl->is_readable($news['category']))
			{
				$this->total = 1;
				$news['content'] = ($news['is_html'] ? 
							$news['content']: 
							nl2br(htmlspecialchars($news['content'],ENT_COMPAT,$GLOBALS['egw']->translation->charset())
						));
				return $news;
			}
			else
			{
				 return False;
			}
		}

		// The following functions are added by wbshang @ realss, 2005-2-21

		// Get addressbook categories that have received mails
		function get_receiver_cats($news_id)
		{
			if(!$news_id)
			{
				return array();
			}
			return ($cat_id = $this->sonews->get_receiver_cats($news_id)) ? explode(",", $cat_id) : array();
		}

		// Send mail to $news['mailto'] with the content of $news
		function send_mail($news)
		{
			// first, get all members who are gonna to receive this mail
			if(!is_object($GLOBALS['egw']->contacts))
			{
				$GLOBALS['egw']->contacts =& CreateObject('phpgwapi.contacts');
			}
			$fields = array(
				'n_family' => True,
				'n_given'  => True,
				'email'    => True,
				'email_home' => True,
			//	'cat_id' => True
			);
			$members = array();
			foreach($news['mailto'] as $cat_id)
			{
				$filter = 'tid=n,cat_id=' . $cat_id;
						$members = array_merge($members,$GLOBALS['egw']->contacts->read('','',$fields,'',$filter));
					}

			// then, prepare to send mail
					$mail =& CreateObject('phpgwapi.send');

					// build subject
					$subject = lang('News') . ': ' . $news['subject'];

					// build body
					$body  = '';
					$body .= lang('Subject') . ': ' . $news['subject'] . "<br/>";
			$body .= lang('Submitted By') . $GLOBALS['egw']->common->grab_owner_name($news['submittedby']) . "<br/>";
					$body .= lang('Date') . ': ' . $GLOBALS['egw']->common->show_date($news['date']) . "<br/><br/>";
					$body .= lang('Content') . ":<br/>";
			$body .= $news['content'] . "<br/><br/>";

			$mail->Subject = $subject;
			$mail->Body = $body;
			$mail->From = $GLOBALS['egw_info']['user']['preferences']['news_admin']['EmailFrom'];
			$mail->FromName = $GLOBALS['egw']->common->grab_owner_name($news['submittedby']);
			$replyto = $GLOBALS['egw_info']['user']['preferences']['news_admin']['EmailReplyto'];
			$mail->AddReplyTo($replyto);
			$mail->Sender = $replyto;   // the Return-Path
			$mail->IsHTML(true);
		/*	attach files, no use now
			if(!empty($news['fileadded']))
			{
				foreach($news['fileadded'] as $file)
				{
					$p = $this->vfs->path_parts(array(
						'string' => '/news_admin/'.$news['id'].'/'.$file['name'],
						'relatives' => array(
							RELATIVE_NONE)
						));
					$mail->AddAttachment($p->real_full_path,$file['name'],'base64',$file['type']);
				}  // it seems that the sumsize of attachments is limited
			} */

				foreach($members as $member)
			{
				if($sent[$member['id']])
				{
					continue;
				}
				$sent[$member['id']] = True;
				if($GLOBALS['egw_info']['user']['preferences']['news_admin']['SendtohomeEmail'])  /* send to the home_email if business_email is empty */
				{
					$to = strpos($member['email'],'@') ? $member['email'] : $member['email_home'];
				}
				else
				{
					$to = $member['email'];
				}
				if(!strpos($to,'@'))
				{
					continue;     // no email address available, just skip it
				}
				$toname = $member['n_given'] . ' ' . $member['n_family'];

				$mail->ClearAddresses();
				$mail->AddAddress($to,$toname);

						if (!$mail->Send())
						{
					$errors[] = "Error sending mail to $toname &lt;$to&gt;";
				//	echo $mail->ErrorInfo;
				}
			}
			if(is_array($errors))
			{
				return $errors;
			}
		}
	}
?>
