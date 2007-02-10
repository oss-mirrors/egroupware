<?php
/**************************************************************************\
* eGroupWare - Calendar's buisness-object: access + update                 *
* http://www.egroupware.org                                                *
* Written and (c) 2005 by Ralf Becker <RalfBecker@outdoor-training.de>     *
* --------------------------------------------                             *
*  This program is free software; you can redistribute it and/or modify it *
*  under the terms of the GNU General Public License as published by the   *
*  Free Software Foundation; either version 2 of the License, or (at your  *
*  option) any later version.                                              *
\**************************************************************************/

/* $Id$ */

require_once(EGW_INCLUDE_ROOT.'/calendar/inc/class.bocal.inc.php');

// types of messsages send by bocalupdate::send_update
define('MSG_DELETED',0);
define('MSG_MODIFIED',1);
define('MSG_ADDED',2);
define('MSG_REJECTED',3);
define('MSG_TENTATIVE',4);
define('MSG_ACCEPTED',5);
define('MSG_ALARM',6);
define('MSG_DISINVITE',7);

/**
 * Class to access AND manipulate all calendar data (business object)
 *
 * The new UI, BO and SO classes have a strikt definition, in which time-zone they operate:
 *  UI only operates in user-time, so there have to be no conversation at all !!!
 *  BO's functions take and return user-time only (!), they convert internaly everything to servertime, because
 *  SO operates only on server-time
 *
 * As this BO class deals with dates/times of several types and timezone, each variable should have a postfix
 * appended, telling with type it is: _s = seconds, _su = secs in user-time, _ss = secs in server-time, _h = hours
 *
 * All new BO code (should be true for eGW in general) NEVER use any $_REQUEST ($_POST or $_GET) vars itself.
 * Nor does it store the state of any UI-elements (eg. cat-id selectbox). All this is the task of the UI class(es) !!!
 *
 * All permanent debug messages of the calendar-code should done via the debug-message method of the bocal class !!!
 *
 * @package calendar
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2005 by RalfBecker-At-outdoor-training.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 */

class bocalupdate extends bocal
{
	/**
	 * name of method to debug or level of debug-messages:
	 *	False=Off as higher as more messages you get ;-)
	 *	1 = function-calls incl. parameters to general functions like search, read, write, delete
	 *	2 = function-calls to exported helper-functions like check_perms
	 *	4 = function-calls to exported conversation-functions like date2ts, date2array, ...
	 *	5 = function-calls to private functions
	 * @var mixed
	 */
	var $debug;
	
	/**
	 * @var string/boolean $log_file filename to enable the login or false for no update-logging
	 */
	var $log_file = false;

	/**
	 * Constructor
	 */
	function bocalupdate()
	{
		if ($this->debug > 0) $this->debug_message('bocalupdate::bocalupdate() started',True);

		$this->bocal();	// calling the parent constructor
		
		if (!is_object($GLOBALS['egw']->link))
		{
			$GLOBALS['egw']->link =& CreateObject('phpgwapi.bolink');
		}
		$this->link =& $GLOBALS['egw']->link;

		if ($this->debug > 0) $this->debug_message('bocalupdate::bocalupdate() finished',True);
	}

	/**
	 * updates or creates an event, it (optionaly) checks for conflicts and sends the necessary notifications 
	 *
	 * @param array &$event event-array, on return some values might be changed due to set defaults
	 * @param boolean $ignore_conflicts=false just ignore conflicts or do a conflict check and return the conflicting events
	 * @param boolean $touch_modified=true touch modificatin time and set modifing user, default true=yes
	 * @param boolean $ignore_acl=flase should we ignore the acl
	 * @return mixed on success: int $cal_id > 0, on error false or array with conflicting events (only if $check_conflicts)
	 * 		Please note: the events are not garantied to be readable by the user (no read grant or private)!
	 */
	function update(&$event,$ignore_conflicts=false,$touch_modified=true,$ignore_acl=false)
	{
		if ($this->debug > 1 || $this->debug == 'update')
		{
			$this->debug_message('bocalupdate::update(%1,ignore_conflict=%2,touch_modified=%3,ignore_acl=%4)',
				false,$event,$ignore_conflicts,$touch_modified,$ignore_acl);
		}
		// check some minimum requirements:
		// - new events need start, end and title
		// - updated events cant set start, end or title to empty
		if (!$event['id'] && (!$event['start'] || !$event['end'] || !$event['title']) ||
			$event['id'] && (isset($event['start']) && !$event['start'] || isset($event['end']) && !$event['end'] ||  
			isset($event['title']) && !$event['title']))
		{
			return false;
		}
		if (!$event['id'])	// some defaults for new entries
		{
			// if no owner given, set user to owner
			if (!$event['owner']) $event['owner'] = $this->user;
			// set owner as participant if none is given
			if (!$event['id'] && (!is_array($event['participants']) || !count($event['participants'])))
			{
				$event['participants'][$event['owner']] = 'U';
			} 
			// set the status of the current user to 'A' = accepted
			if (isset($event['participants'][$this->user]) &&  $event['participants'][$this->user] != 'A')
			{
				$event['participants'][$this->user] = 'A';
			}
		}
		// check if user has the permission to update / create the event
		if (!$ignore_acl && ($event['id'] && !$this->check_perms(EGW_ACL_EDIT,$event['id']) ||
			!$event['id'] && !$this->check_perms(EGW_ACL_EDIT,0,$event['owner'])) && 
			!$this->check_perms(EGW_ACL_ADD,0,$event['owner']))
		{
			return false;
		}
		// check for conflicts only happens !$ignore_conflicts AND if start + end date are given
		if (!$ignore_conflicts && !$event['non_blocking'] && isset($event['start']) && isset($event['end']))
		{
			$types_with_quantity = array();
			foreach($this->resources as $type => $data)
			{
				if ($data['max_quantity']) $types_with_quantity[] = $type;
			}
			// get all NOT rejected participants and evtl. their quantity
			$quantity = $users = array();
			foreach($event['participants'] as $uid => $status)
			{
				if ($status[0] == 'R') continue;	// ignore rejected participants

				$users[] = $uid;
				if (in_array($uid{0},$types_with_quantity))
				{
					$quantity[$uid] = max(1,(int) substr($status,2));
				}
			}
			$overlapping_events =& $this->search(array(
				'start' => $event['start'],
				'end'   => $event['end'],
				'users' => $users,
				'ignore_acl' => true,	// otherwise we get only events readable by the user
				'enum_groups' => true,	// otherwise group-events would not block time
			));
			if ($this->debug > 2 || $this->debug == 'update')
			{
				$this->debug_message('bocalupdate::update() checking for potential overlapping events for users %1 from %2 to %3',false,$users,$event['start'],$event['end']);
			}
			$max_quantity = $possible_quantity_conflicts = $conflicts = array();
			foreach((array) $overlapping_events as $k => $overlap)
			{
				if ($overlap['id'] == $event['id'] ||	// that's the event itself
					$overlap['id'] == $event['reference'] ||	// event is an exception of overlap
					$overlap['non_blocking'])			// that's a non_blocking event
				{
					continue;
				}
				if ($this->debug > 3 || $this->debug == 'update')
				{
					$this->debug_message('bocalupdate::update() checking overlapping event %1',false,$overlap);
				}
				// check if the overlap is with a rejected participant or within the allowed quantity
				$common_parts = array_intersect($users,array_keys($overlap['participants']));
				foreach($common_parts as $n => $uid)
				{
					if ($overlap['participants'][$uid]{0} == 'R') 
					{
						unset($common_parts[$uid]);
						continue;
					}
					if (is_numeric($uid) || !in_array($uid{0},$types_with_quantity))
					{
						continue;	// no quantity check: quantity allways 1 ==> conflict
					}
					if (!isset($max_quantity[$uid]))
					{
						$res_info = $this->resource_info($uid);
						$max_quantity[$uid] = $res_info[$this->resources[$uid{0}]['max_quantity']];
					}
					$quantity[$uid] += max(1,(int) substr($overlap['participants'][$uid],2));
					if ($quantity[$uid] <= $max_quantity[$uid])
					{
						$possible_quantity_conflicts[$uid][] =& $overlapping_events[$k];	// an other event can give the conflict
						unset($common_parts[$n]);
						continue;
					}
					// now we have a quantity conflict for $uid
				}
				if (count($common_parts))
				{
					if ($this->debug > 3 || $this->debug == 'update')
					{
						$this->debug_message('bocalupdate::update() conflicts with the following participants found %1',false,$common_parts);
					}
					$conflicts[$overlap['id'].'-'.$this->date2ts($overlap['start'])] =& $overlapping_events[$k];
				}
			}
			// check if we are withing the allowed quantity and if not add all events using that resource
			foreach($max_quantity as $uid => $max)
			{
				if ($quantity[$uid] > $max)
				{
					foreach((array)$possible_quantity_conflicts[$uid] as $conflict)
					{
						$conflicts[$conflict['id'].'-'.$this->date2ts($conflict['start'])] =& $possible_quantity_conflicts[$k];
					}
				}
			}
			unset($possible_quantity_conflicts);
			
			if (count($conflicts))
			{
				foreach($conflicts as $key => $conflict)
				{
					if (!$this->check_perms(EGW_ACL_READ,$conflict))
					{
						$conflicts[$key] = array(
							'id'    => $conflict['id'],
							'title' => lang('busy'),
							'participants' => array_intersect_key($conflict['participants'],$event['participants']),
							'start' => $conflict['start'],
							'end'   => $conflict['end'],
						);
					}
				}
				if ($this->debug > 2 || $this->debug == 'update')
				{
					$this->debug_message('bocalupdate::update() %1 conflicts found %2',false,count($conflicts),$conflicts);
				}
				return $conflicts;
			}					
		}
		// save the event to the database
		if ($touch_modified)
		{
			$event['modified'] = $this->now_su;	// we are still in user-time
			$event['modifier'] = $GLOBALS['egw_info']['user']['account_id'];
		}
		if (!($new_event = !(int)$event['id']))
		{
			$old_event = $this->read((int)$event['id'],null,$ignore_acl);
			// if no participants are set, set them from the old event, as we might need them to update recuring events
			if (!isset($event['participants'])) $event['participants'] = $old_event['participants'];
			//echo "old $event[id]="; _debug_array($old_event);
		}
		//echo "saving $event[id]="; _debug_array($event);
		$event2save = $event;

		if (!($cal_id = $this->save($event)))
		{
			return $cal_id;
		}
		$event = $this->read($cal_id);	// we re-read the event, in case only partial information was update and we need the full info for the notifies
		//echo "new $cal_id="; _debug_array($event);

		if ($this->log_file)
		{
			$this->log2file($event2save,$event,$old_event);
		}
		// send notifications
		if ($new_event)
		{
			$this->send_update(MSG_ADDED,$event['participants'],'',$event);
		}
		else // update existing event
		{
			$this->check4update($event,$old_event);
		}
		// notify the link-class about the update, as other apps may be subscribt to it
		$this->link->notify_update('calendar',$cal_id,$event);

		return $cal_id;
	}

	/**
	 * Check for added, modified or deleted participants
	 *
	 * @param array $new_event the updated event
	 * @param array $old_event the event before the update
	 */ 
	function check4update($new_event,$old_event)
	{
		$modified = $added = $deleted = array();
		
		//echo "<p>bocalupdate::check4update() new participants = ".print_r($new_event['participants'],true).", old participants =".print_r($old_event['participants'],true)."</p>\n";

		// Find modified and deleted participants ...
		foreach($old_event['participants'] as $old_userid => $old_status)
		{
			if(isset($new_event['participants'][$old_userid]))
			{
				$modified[$old_userid] = $new_event['participants'][$old_userid];
			}
			else
			{
				$deleted[$old_userid] = $old_status;
			}
		}
		// Find new participatns ...
		foreach($new_event['participants'] as $new_userid => $new_status)
		{
			if(!isset($old_event['participants'][$new_userid]))
			{
				$added[$new_userid] = 'U';
			}
		}
		//echo "<p>bocalupdate::check4update() added=".print_r($added,true).", modified=".print_r($modified,true).", deleted=".print_r($deleted,true)."</p>\n";
		if(count($added) || count($modified) || count($deleted))
		{
			if(count($added))
			{
				$this->send_update(MSG_ADDED,$added,$old_event,$new_event);
			}
			if(count($modified))
			{
				$this->send_update(MSG_MODIFIED,$modified,$old_event,$new_event);
			}
			if(count($deleted))
			{
				$this->send_update(MSG_DISINVITE,$deleted,$new_event);
			}
		}
	}

	/**
	 * checks if $userid has requested (in $part_prefs) updates for $msg_type
	 *
	 * @param int $userid numerical user-id
	 * @param array $part_prefs preferces of the user $userid
	 * @param int $msg_type type of the notification: MSG_ADDED, MSG_MODIFIED, MSG_ACCEPTED, ...
	 * @param array $old_event Event before the change
	 * @param array $new_event Event after the change
	 * @return boolean true = update requested, flase otherwise
	 */
	function update_requested($userid,$part_prefs,$msg_type,$old_event,$new_event)
	{
		if ($msg_type == MSG_ALARM)
		{
			return True;	// always True for now
		}
		$want_update = 0;

		// the following switch falls through all cases, as each included the following too
		//
		$msg_is_response = $msg_type == MSG_REJECTED || $msg_type == MSG_ACCEPTED || $msg_type == MSG_TENTATIVE;

		switch($ru = $part_prefs['calendar']['receive_updates'])
		{
			case 'responses':
				if ($msg_is_response)
				{
					++$want_update;
				}
			case 'modifications':
				if ($msg_type == MSG_MODIFIED)
				{
					++$want_update;
				}
			case 'time_change_4h':
			case 'time_change':
				$diff = max(abs($this->date2ts($old_event['start'])-$this->date2ts($new_event['start'])),
					abs($this->date2ts($old_event['end'])-$this->date2ts($new_event['end'])));
				$check = $ru == 'time_change_4h' ? 4 * 60 * 60 - 1 : 0;
				if ($msg_type == MSG_MODIFIED && $diff > $check)
				{
					++$want_update;
				}
			case 'add_cancel':
				if ($old_event['owner'] == $userid && $msg_is_response ||
					$msg_type == MSG_DELETED || $msg_type == MSG_ADDED || $msg_type == MSG_DISINVITE)
				{
					++$want_update;
				}
				break;
			case 'no':
				break;
		}
		//echo "<p>bocalupdate::update_requested(user=$userid,pref=".$part_prefs['calendar']['receive_updates'] .",msg_type=$msg_type,".($old_event?$old_event['title']:'False').",".($old_event?$old_event['title']:'False').") = $want_update</p>\n";
		return $want_update > 0;
	}

	/**
	 * sends update-messages to certain participants of an event
	 *
	 * @param int $msg_type type of the notification: MSG_ADDED, MSG_MODIFIED, MSG_ACCEPTED, ...
	 * @param array $to_notify numerical user-ids as keys (!) (value is not used)
	 * @param array $old_event Event before the change
	 * @param array $new_event=null Event after the change
	 * @param int $user=0 User who started the notify, default current user
	 * @return  mixed returncode from send-class or false on error
	 */
	function send_update($msg_type,$to_notify,$old_event,$new_event=null,$user=0)
	{
		if (!is_array($to_notify))
		{
			$to_notify = array();
		}
		$disinvited = $msg_type == MSG_DISINVITE ? array_keys($to_notify) : array();

		$owner = $old_event ? $old_event['owner'] : $new_event['owner'];
		if ($owner && !isset($to_notify[$owner]) && $msg_type != MSG_ALARM)
		{
			$to_notify[$owner] = 'owner';	// always include the event-owner
		}
		$version = $GLOBALS['egw_info']['apps']['calendar']['version'];

		// ignore events in the past (give a tolerance of 10 seconds for the script)
		if($old_event != False && $this->date2ts($old_event['start']) < ($this->now_su - 10))
		{
			return False;
		}
		$temp_user = $GLOBALS['egw_info']['user'];	// save user-date of the enviroment to restore it after

		if (!$user)
		{
			$user = $temp_user['account_id'];
		}
		if ($GLOBALS['egw']->preferences->account_id != $user)
		{
			$GLOBALS['egw']->preferences->preferences($user);
			$GLOBALS['egw_info']['user']['preferences'] = $GLOBALS['egw']->preferences->read_repository();
		}
		$sender = $GLOBALS['egw_info']['user']['email'];
		$sender_fullname = $GLOBALS['egw_info']['user']['fullname'];

		$event = $msg_type == MSG_ADDED || $msg_type == MSG_MODIFIED ? $new_event : $old_event;

		switch($msg_type)
		{
			case MSG_DELETED:
				$action = lang('Canceled');
				$msg = 'Canceled';
				$msgtype = '"calendar";';
				$method = 'cancel';
				break;
			case MSG_MODIFIED:
				$action = lang('Modified');
				$msg = 'Modified';
				$msgtype = '"calendar"; Version="'.$version.'"; Id="'.$new_event['id'].'"';
				$method = 'request';
				break;
			case MSG_DISINVITE:
				$action = lang('Disinvited');
				$msg = 'Disinvited';
				$msgtype = '"calendar";';
				$method = 'cancel';
				break;
			case MSG_ADDED:
				$action = lang('Added');
				$msg = 'Added';
				$msgtype = '"calendar"; Version="'.$version.'"; Id="'.$new_event['id'].'"';
				$method = 'request';
				break;
			case MSG_REJECTED:
				$action = lang('Rejected');
				$msg = 'Response';
				$msgtype = '"calendar";';
				$method = 'reply';
				break;
			case MSG_TENTATIVE:
				$action = lang('Tentative');
				$msg = 'Response';
				$msgtype = '"calendar";';
				$method = 'reply';
				break;
			case MSG_ACCEPTED:
				$action = lang('Accepted');
				$msg = 'Response';
				$msgtype = '"calendar";';
				$method = 'reply';
				break;
			case MSG_ALARM:
				$action = lang('Alarm');
				$msg = 'Alarm';
				$msgtype = '"calendar";';
				$method = 'publish';	// duno if thats right
				break;
			default:
				$method = 'publish';
		}
		$notify_msg = $this->cal_prefs['notify'.$msg];
		if (empty($notify_msg))
		{
			$notify_msg = $this->cal_prefs['notifyAdded'];	// use a default
		}
		$details = $this->_get_event_details($event,$action,$event_arr,$disinvited);

		if(!is_object($GLOBALS['egw']->send))
		{
			$GLOBALS['egw']->send =& CreateObject('phpgwapi.send');
		}
		$send = &$GLOBALS['egw']->send;

		// add all group-members to the notification, unless they are already participants
		foreach($to_notify as $userid => $statusid)
		{
			if (is_numeric($userid) && $GLOBALS['egw']->accounts->get_type($userid) == 'g' &&
				($members = $GLOBALS['egw']->accounts->member($userid)))
			{
				foreach($members as $member)
				{
					$member = $member['account_id'];
					if (!isset($to_notify[$member]))
					{
						$to_notify[$member] = 'G';	// Group-invitation
					}
				}
			}
		}
		foreach($to_notify as $userid => $statusid)
		{
			if (!is_numeric($userid))
			{
				$res_info = $this->resource_info($userid);
				$userid = $res_info['responsible'];
				if (!isset($userid)) continue;	
			}

			if ($statusid == 'R' || $GLOBALS['egw']->accounts->get_type($userid) == 'g')
			{
				continue;	// dont notify rejected participants or groups
			}
			if($userid != $GLOBALS['egw_info']['user']['account_id'] ||  $msg_type == MSG_ALARM)
			{
				$preferences =& CreateObject('phpgwapi.preferences',$userid);
				$part_prefs = $preferences->read_repository();

				if (!$this->update_requested($userid,$part_prefs,$msg_type,$old_event,$new_event))
				{
					continue;
				}
				$GLOBALS['egw']->accounts->get_account_name($userid,$lid,$details['to-firstname'],$details['to-lastname']);
				$details['to-fullname'] = $GLOBALS['egw']->common->display_fullname('',$details['to-firstname'],$details['to-lastname']);

				$to = $GLOBALS['egw']->accounts->id2name($userid,'account_email');
				if (!$to || !strstr($to,'@'))
				{
					// ToDo: give an error-message
					echo '<p>'.lang('Invalid email-address "%1" for user %2',$to,$GLOBALS['egw']->common->grab_owner_name($userid))."</p>\n";
					continue;
				}
				$GLOBALS['egw_info']['user']['preferences']['common']['tz_offset'] = $part_prefs['common']['tz_offset'];
				$GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] = $part_prefs['common']['timeformat'];
				$GLOBALS['egw_info']['user']['preferences']['common']['dateformat'] = $part_prefs['common']['dateformat'];
					
				$GLOBALS['egw']->datetime->tz_offset = 3600 * (int) $GLOBALS['egw_info']['user']['preferences']['common']['tz_offset'];

				// event is in user-time of current user, now we need to calculate the tz-difference to the notified user and take it into account
				$tz_diff = $GLOBALS['egw_info']['user']['preferences']['common']['tz_offset'] - $this->common_prefs['tz_offset'];
				if($old_event != False) $details['olddate'] = $this->format_date($old_event['start']+$tz_diff);
				$details['startdate'] = $this->format_date($event['start']+$tz_diff);
				$details['enddate']   = $this->format_date($event['end']+$tz_diff);

				list($subject,$body) = explode("\n",$GLOBALS['egw']->preferences->parse_notify($notify_msg,$details),2);

				$send->ClearAddresses();
				$send->ClearAttachments();
				$send->IsHTML(False);
				$send->AddAddress($to);
				$send->AddCustomHeader('X-eGroupWare-type: calendarupdate');

				switch($part_prefs['calendar']['update_format'])
				{
					case  'extended':
						$body .= "\n\n".lang('Event Details follow').":\n";
						foreach($event_arr as $key => $val)
						{
							if ($key != 'access' && $key != 'priority' && strlen($details[$key]))
							{
								$body .= sprintf("%-20s %s\n",$val['field'].':',$details[$key]);
							}
						}
						break;

					case  'ical':
						$ics = ExecMethod2('calendar.boical.exportVCal',$event['id'],'2.0',$method);
						if ($method == "request") 
						{
							$send->AddStringAttachment($ics, "cal.ics", "8bit", "text/calendar; method=$method");
						}
						break;
				}
				$send->From = $sender;
				$send->FromName = $sender_fullname;
				$send->Subject = $send->encode_subject($subject);
				$send->Body = $body;
				if ($this->debug)
				{
					echo "<hr /><p>to: $to<br>from: $sender_fullname &lt;$sender&gt;<br>Subject: $subject<br>".nl2br($body)."</p><hr />\n";
				}
				$returncode = $send->Send();
				
				// ToDo: give error-messages for all all failed sendings
				
				// notification via notification app.
				if (version_compare("5.1.0", phpversion()) && array_key_exists('notifications',$GLOBALS['egw_info']['apps'])) {
					require_once(EGW_INCLUDE_ROOT. '/notifications/inc/class.notification.inc.php');
					include(EGW_INCLUDE_ROOT. '/calendar/inc/class.php5_notification.inc.php');
				}

			}
		}
		// restore the enviroment
		$GLOBALS['egw_info']['user'] = $temp_user;
		$GLOBALS['egw']->datetime->tz_offset = 3600 * $GLOBALS['egw_info']['user']['preferences']['common']['tz_offset'];

		return $returncode;
	}

	function get_update_message($event,$added)
	{
		$details = $this->_get_event_details($event,$added ? lang('Added') : lang('Modified'),$nul);

		$notify_msg = $this->cal_prefs[$added || empty($this->cal_prefs['notifyModified']) ? 'notifyAdded' : 'notifyModified'];

		return explode("\n",$GLOBALS['egw']->preferences->parse_notify($notify_msg,$details),2);
	}

	/**
	 * Function called via async service, when an alarm is to be send
	 *
	 * @param array $alarm array with keys owner, cal_id, all
	 * @return boolean 
	 */
	function send_alarm($alarm)
	{
		//echo "<p>bocalendar::send_alarm("; print_r($alarm); echo ")</p>\n";
		$GLOBALS['egw_info']['user']['account_id'] = $this->owner = $alarm['owner'];

		$event_time_user = $alarm['time'] + $alarm['offset'] + $this->tz_offset_s;	// alarm[time] is in server-time, read requires user-time
		if (!$alarm['owner'] || !$alarm['cal_id'] || !($event = $this->read($alarm['cal_id'],$event_time_user)))
		{
			return False;	// event not found
		}
		if ($alarm['all'])
		{
			$to_notify = $event['participants'];
		}
		elseif ($this->check_perms(EGW_ACL_READ,$event))	// checks agains $this->owner set to $alarm[owner]
		{
			$to_notify[$alarm['owner']] = 'A';
		}
		else
		{
			return False;	// no rights
		}
		$ret = $this->send_update(MSG_ALARM,$to_notify,$event,False,$alarm['owner']);

		// create a new alarm for recuring events for the next event, if one exists
		if ($event['recur_type'] && ($event = $this->read($alarm['cal_id'],$event_time_user+1)))
		{
			$alarm['time'] = $this->date2ts($event['start']) - $alarm['offset'];

			$this->save_alarm($alarm['cal_id'],$alarm);
		}
		return $ret;
	}

	/**
	 * saves an event to the database, does NOT do any notifications, see bocalupdate::update for that
	 *
	 * This methode converts from user to server time and handles the insertion of users and dates of repeating events
	 *
	 * @param array $event
	 * @return int/boolean $cal_id > 0 or false on error (eg. permission denied)
	 */
	function save($event)
	{
		// check if user has the permission to update / create the event
		if ($event['id'] && !$this->check_perms(EGW_ACL_EDIT,$event['id']) ||
			!$event['id'] && !$this->check_perms(EGW_ACL_EDIT,0,$event['owner']) &&
			!$this->check_perms(EGW_ACL_ADD,0,$event['owner']))
		{
			return false;
		}
		// invalidate the read-cache if it contains the event we store now
		if ($event['id'] && $event['id'] == $this->cached_event['id']) $this->cached_event = array();

		$save_event = $event;
		// we run all dates through date2ts, to adjust to server-time and the possible date-formats
		foreach(array('start','end','modified','recur_enddate') as $ts)
		{
			// we convert here from user-time to timestamps in server-time!
			if (isset($event[$ts])) $event[$ts] = $event[$ts] ? $this->date2ts($event[$ts],true) : 0;
		}
		// same with the recur exceptions
		if (isset($event['recur_exception']) && is_array($event['recur_exception']))
		{
			foreach($event['recur_exception'] as $n => $date)
			{
				$event['recur_exception'][$n] = $this->date2ts($date,true);
			}
		}
		// same with the alarms
		if (isset($event['alarm']) && is_array($event['alarm']))
		{
			foreach($event['alarm'] as $id => $alarm)
			{
				$event['alarm'][$id]['time'] = $this->date2ts($alarm['time'],true);
			}
		}
		if (($cal_id = $this->so->save($event,$set_recurrences)) && $set_recurrences && $event['recur_type'] != MCAL_RECUR_NONE)
		{
			$save_event['id'] = $cal_id;
			$this->set_recurrences($save_event);
		}
		$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar',$cal_id,$event['id'] ? 'modify' : 'add',time());

		return $cal_id;
	}
	
	/**
	 * Check if the current user has the necessary ACL rights to change the status of $uid
	 * 
	 * For contacts we use edit rights of the owner of the event (aka. edit rights of the event).
	 *
	 * @param int/string $uid account_id or 1-char type-identifer plus id (eg. c15 for addressbook entry #15)
	 * @param array/int $event event array or id of the event
	 * @return boolean
	 */
	function check_status_perms($uid,$event)
	{
		if ($uid{0} == 'c')	// for contact we use the owner of the event
		{
			if (!is_array($event) && !($event = $this->read($event))) return false;

			return $this->check_perms(EGW_ACL_EDIT,0,$event['owner']);
		}
		if (!is_numeric($uid))	// this is eg. for resources (r123)
		{
			$resource = $this->resource_info($uid);

			return EGW_ACL_EDIT & $resource['rights'];
		}
		// regular user and groups
		return $this->check_perms(EGW_ACL_EDIT,0,$uid);
	}

	/**
	 * set the status of one participant for a given recurrence or for all recurrences since now (includes recur_date=0)
	 *
	 * @param int/array $event event-array or id of the event
	 * @param string/int $uid account_id or 1-char type-identifer plus id (eg. c15 for addressbook entry #15)
	 * @param int/char $status numeric status (defines) or 1-char code: 'R', 'U', 'T' or 'A'
	 * @param int $recur_date=0 date to change, or 0 = all since now
	 * @return int number of changed recurrences
	 */
	function set_status($event,$uid,$status,$recur_date=0)
	{
		$cal_id = is_array($event) ? $event['id'] : $event;
		//echo "<p>bocalupdate::set_status($cal_id,$uid,$status,$recur_date)</p>\n";
		if (!$cal_id || !$this->check_status_perms($uid,$event))
		{
			return false;
		}
		if (($Ok = $this->so->set_status($cal_id,is_numeric($uid)?'u':$uid{0},is_numeric($uid)?$uid:substr($uid,1),$status,$recur_date ? $this->date2ts($recur_date,true) : 0)))
		{
			$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar',$cal_id,'modify',time());

			static $status2msg = array(
				'R' => MSG_REJECTED,
				'T' => MSG_TENTATIVE,
				'A' => MSG_ACCEPTED,
			);
			if (isset($status2msg[$status]))
			{
				if (!is_array($event)) $event = $this->read($cal_id);
				if (isset($recur_date)) $event = $this->read($event['id'],$recur_date); //re-read the actually edited recurring event
				$this->send_update($status2msg[$status],$event['participants'],$event);
			}
		}
		return $Ok;
	}

	/**
	 * deletes an event
	 *
	 * @param int $cal_id id of the event to delete
	 * @param int $recur_date=0 if a single event from a series should be deleted, its date
	 * @return boolean true on success, false on error (usually permission denied)
	 */
	function delete($cal_id,$recur_date=0)
	{
		$event = $this->read($cal_id,$recur_date);
		
		if (!($event = $this->read($cal_id,$recur_date)) ||
			!$this->check_perms(EGW_ACL_DELETE,$event))
		{
			return false;
		}
		$this->send_update(MSG_DELETED,$event['participants'],$event);
		
		if (!$recur_date || $event['recur_type'] == MCAL_RECUR_NONE)
		{
			$this->so->delete($cal_id);
			$GLOBALS['egw']->contenthistory->updateTimeStamp('calendar',$cal_id,'delete',time());
						
			// delete all links to the event
			$this->link->unlink(0,'calendar',$cal_id);
		}
		else
		{
			$event['recur_exception'][] = $recur_date = $this->date2ts($event['start']);
			unset($event['start']);
			unset($event['end']);
			$this->save($event);	// updates the content-history
		}
		return true;
	}

	/**
	 * helper for send_update and get_update_message
	 * @internal
	 */
	function _get_event_details($event,$action,&$event_arr,$disinvited=array())
	{
		$details = array(			// event-details for the notify-msg
			'id'          => $event['id'],
			'action'      => $action,
		);
		$event_arr = $this->event2array($event);
		foreach($event_arr as $key => $val)
		{
			$details[$key] = $val['data'];
		}
		$details['participants'] = $details['participants'] ? implode("\n",$details['participants']) : '';

		$event_arr['link']['field'] = lang('URL');
		$eventStart_arr = $this->date2array($event['start']); // give this as 'date' to the link to pick the right recurrence for the participants state
		$link = $GLOBALS['egw_info']['server']['webserver_url'].'/index.php?menuaction=calendar.uiforms.edit&cal_id='.$event['id'].'&date='.$eventStart_arr['full'].'&no_popup=1';
		// if url is only a path, try guessing the rest ;-)
		if ($link{0} == '/')
		{
			$link = ($GLOBALS['egw_info']['server']['enforce_ssl'] || $_SERVER['HTTPS'] ? 'https://' : 'http://').
				($GLOBALS['egw_info']['server']['hostname'] ? $GLOBALS['egw_info']['server']['hostname'] : $_SERVER['HTTP_HOST']).
				$link;
		}
		$event_arr['link']['data'] = $details['link'] = $link;
		$dis = array();
		foreach($disinvited as $uid)
		{
			$dis[] = $this->participant_name($uid);
		}
		$details['disinvited'] = implode(', ',$dis);

		return $details;
	}

	/**
	 * create array with name, translated name and readable content of each attributes of an event
	 *
	 * old function, so far only used by send_update (therefor it's in bocalupdate and not bocal)
	 *
	 * @param array $event event to use
	 * @returns array of attributes with fieldname as key and array with the 'field'=translated name 'data' = readable content (for participants this is an array !)
	 */
	function event2array($event)
	{
		$var['title'] = Array(
			'field'		=> lang('Title'),
			'data'		=> $event['title']
		);

		$var['description'] = Array(
			'field'	=> lang('Description'),
			'data'	=> $event['description']
		);

		if (!is_object($GLOBALS['egw']->categories))
		{
			$GLOBALS['egw']->categories =& CreateObject('phpgwapi.categories');
		}
		foreach(explode(',',$event['category']) as $cat_id)
		{
			list($cat) = $GLOBALS['egw']->categories->return_single($cat_id);
			$cat_string[] = stripslashes($cat['name']);
		}
		$var['category'] = Array(
			'field'	=> lang('Category'),
			'data'	=> implode(', ',$cat_string)
		);

		$var['location'] = Array(
			'field'	=> lang('Location'),
			'data'	=> $event['location']
		);

		$var['startdate'] = Array(
			'field'	=> lang('Start Date/Time'),
			'data'	=> $this->format_date($event['start']),
		);

		$var['enddate'] = Array(
			'field'	=> lang('End Date/Time'),
			'data'	=> $this->format_date($event['end']),
		);

		$pri = Array(
			0   => '',
			1	=> lang('Low'),
			2	=> lang('Normal'),
			3	=> lang('High')
		);
		$var['priority'] = Array(
			'field'	=> lang('Priority'),
			'data'	=> $pri[$event['priority']]
		);

		$var['owner'] = Array(
			'field'	=> lang('Owner'),
			'data'	=> $GLOBALS['egw']->common->grab_owner_name($event['owner'])
		);

		$var['updated'] = Array(
			'field'	=> lang('Updated'),
			'data'	=> $this->format_date($event['modtime']).', '.$GLOBALS['egw']->common->grab_owner_name($event['modifier'])
		);

		$var['access'] = Array(
			'field'	=> lang('Access'),
			'data'	=> $event['public'] ? lang('Public') : lang('Private')
		);

		if (isset($event['participants']) && is_array($event['participants']))
		{
			$participants = $this->participants($event,true);
		}
		$var['participants'] = Array(
			'field'	=> lang('Participants'),
			'data'	=> $participants
		);

		// Repeated Events
		if($event['recur_type'] != MCAL_RECUR_NONE)
		{
			$var['recur_type'] = Array(
				'field'	=> lang('Repetition'),
				'data'	=> $this->recure2string($event),
			);
		}
		return $var;
	}

	/**
	 * log all updates to a file
	 *
	 * @param array $event2save event-data before calling save
	 * @param array $event_saved event-data read back from the DB
	 * @param array $old_event=null event-data in the DB before calling save
	 * @param string $type='update'
	 */
	function log2file($event2save,$event_saved,$old_event=null,$type='update')
	{
		if (!($f = fopen($this->log_file,'a')))
		{
			echo "<p>error opening '$this->log_file' !!!</p>\n";
			return false;
		}
		fwrite($f,$type.': '.$GLOBALS['egw']->common->grab_owner_name($this->user).': '.date('r')."\n");
		fwrite($f,"Time: time to save / saved time read back / old time before save\n");
		foreach(array('start','end') as $name)
		{
			fwrite($f,$name.': '.(isset($event2save[$name]) ? $this->format_date($event2save[$name]) : 'not set').' / '.
				$this->format_date($event_saved[$name]) .' / '.
				(is_null($old_event) ? 'no old event' : $this->format_date($old_event[$name]))."\n");
		}
		foreach(array('event2save','event_saved','old_event') as $name)
		{
			fwrite($f,$name.' = '.print_r($$name,true));
		}
		fwrite($f,"\n");
		fclose($f);

		return true;
	}

	/**
	 * saves a new or updated alarm
	 *
	 * @param int $cal_id Id of the calendar-entry
	 * @param array $alarm array with fields: text, owner, enabled, ..
	 * @return string id of the alarm, or false on error (eg. no perms)
	 */
	function save_alarm($cal_id,$alarm)
	{
		if (!$cal_id || !$this->check_perms(EGW_ACL_EDIT,$alarm['all'] ? $cal_id : 0,!$alarm['all'] ? $alarm['owner'] : 0))
		{
			//echo "<p>no rights to save the alarm=".print_r($alarm,true)." to event($cal_id)</p>";
			return false;	// no rights to add the alarm
		}
		$alarm['time'] = $this->date2ts($alarm['time'],true);	// user to server-time

		return $this->so->save_alarm($cal_id,$alarm);
	}

	/**
	 * delete one alarms identified by its id
	 *
	 * @param string $id alarm-id is a string of 'cal:'.$cal_id.':'.$alarm_nr, it is used as the job-id too
	 * @return int number of alarms deleted, false on error (eg. no perms)
	 */
	function delete_alarm($id)
	{
		list(,$cal_id) = explode(':',$id);

		if (!($alarm = $this->so->read_alarm($id)) || !$cal_id || !$this->check_perms(EGW_ACL_EDIT,$alarm['all'] ? $cal_id : 0,!$alarm['all'] ? $alarm['owner'] : 0))
		{
			return false;	// no rights to delete the alarm
		}
		return $this->so->delete_alarm($id);
	}
}
