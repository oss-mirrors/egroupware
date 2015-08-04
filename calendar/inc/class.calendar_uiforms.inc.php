<?php
/**
 * EGroupware - Calendar's forms of the UserInterface
 *
 * @link http://www.egroupware.org
 * @package calendar
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2004-15 by RalfBecker-At-outdoor-training.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * calendar UserInterface forms: view and edit events, freetime search
 *
 * The new UI, BO and SO classes have a strikt definition, in which time-zone they operate:
 *  UI only operates in user-time, so there have to be no conversation at all !!!
 *  BO's functions take and return user-time only (!), they convert internaly everything to servertime, because
 *  SO operates only on server-time
 *
 * The state of the UI elements is managed in the uical class, which all UI classes extend.
 *
 * All permanent debug messages of the calendar-code should done via the debug-message method of the bocal class !!!
 */
class calendar_uiforms extends calendar_ui
{
	var $public_functions = array(
		'freetimesearch'  => True,
		'edit' => true,
		'process_edit' => true,
		'export' => true,
		'import' => true,
		'cat_acl' => true,
		'meeting' => true,
		'mail_import' => true,
	);

	/**
	 * Standard durations used in edit and freetime search
	 *
	 * @var array
	 */
	var $durations = array();

	/**
	 * default locking time for entries, that are opened by another user
	 *
	 * @var locktime in seconds
	 */
	var $locktime_default=1;

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct(true);	// call the parent's constructor

		for ($n=15; $n <= 16*60; $n+=($n < 60 ? 15 : ($n < 240 ? 30 : ($n < 600 ? 60 : 120))))
		{
			$this->durations[$n*60] = sprintf('%d:%02d',$n/60,$n%60);
		}
	}

	/**
	 * Create a default event (adding a new event) by evaluating certain _GET vars
	 *
	 * @return array event-array
	 */
	function &default_add_event()
	{
		$extra_participants = $_GET['participants'] ? explode(',',$_GET['participants']) : array();

		// if participant is a contact, add its link title as title
		foreach($extra_participants as $uid)
		{
			if ($uid[0] == 'c')
			{
				$title = egw_link::title('addressbook', substr($uid, 1));
				break;
			}
		}

		if (isset($_GET['owner']))
		{
			$owner = $_GET['owner'];
		}
		// dont set the planner start group as owner/participants if called from planner
		elseif ($this->view != 'planner' || $this->owner != $this->cal_prefs['planner_start_with_group'])
		{
			$owner = $this->owner;
		}

		if (!$owner || !is_numeric($owner) || $GLOBALS['egw']->accounts->get_type($owner) != 'u' ||
			!$this->bo->check_perms(EGW_ACL_ADD,0,$owner))
		{
			if ($owner)	// make an owner who is no user or we have no add-rights a participant
			{
				// if we come from ressources we don't need any users selected in calendar
				if (!isset($_GET['participants']) || $_GET['participants'][0] != 'r')
				{
					foreach(explode(',',$owner) as $uid)
					{
						// only add users or a single ressource, not all ressources displayed by a category
						if (is_numeric($uid) || $owner == $uid)
						{
							$extra_participants[] = $uid;
						}
					}
				}
			}
			$owner = $this->user;
		}
		//echo "<p>this->owner=$this->owner, _GET[owner]=$_GET[owner], user=$this->user => owner=$owner, extra_participants=".implode(',',$extra_participants)."</p>\n";

		// by default include the owner as participant (the user can remove him)
		$extra_participants[] = $owner;

		$start = $this->bo->date2ts(array(
			'full' => isset($_GET['date']) && (int) $_GET['date'] ? (int) $_GET['date'] : $this->date,
			'hour' => (int) (isset($_GET['hour']) && (int) $_GET['hour'] ? $_GET['hour'] : $this->bo->cal_prefs['workdaystarts']),
			'minute' => (int) $_GET['minute'],
		));
		//echo "<p>_GET[date]=$_GET[date], _GET[hour]=$_GET[hour], _GET[minute]=$_GET[minute], this->date=$this->date ==> start=$start=".date('Y-m-d H:i',$start)."</p>\n";

		$participant_types['u'] = $participant_types = $participants = array();
		foreach($extra_participants as $uid)
		{
			if (isset($participants[$uid])) continue;	// already included

			if (!$this->bo->check_acl_invite($uid)) continue;	// no right to invite --> ignored

			if (is_numeric($uid))
			{
				$participants[$uid] = $participant_types['u'][$uid] =
					calendar_so::combine_status($uid == $this->user ? 'A' : 'U',1,
					($uid == $this->user || ($uid == $owner && $this->bo->check_perms(EGW_ACL_ADD,0,$owner))) ? 'CHAIR' : 'REQ-PARTICIPANT');
			}
			elseif (is_array($this->bo->resources[$uid[0]]))
			{
				// if contact is a user, use the user instead (as the GUI)
				if ($uid[0] == 'c' && ($account_id = $GLOBALS['egw']->accounts->name2id(substr($uid,1),'person_id')))
				{
					$uid = $account_id;
					$participants[$uid] = $participant_types['u'][$uid] =
						calendar_so::combine_status($uid == $this->user ? 'A' : 'U',1,
						($uid == $this->user || ($uid == $owner && $this->bo->check_perms(EGW_ACL_ADD,0,$owner))) ? 'CHAIR' : 'REQ-PARTICIPANT');
					continue;
				}
				$res_data = $this->bo->resources[$uid[0]];
				list($id,$quantity) = explode(':',substr($uid,1));
				if (($status = $res_data['new_status'] ? ExecMethod($res_data['new_status'],$id) : 'U'))
				{
					$participants[$uid] = $participant_types[$uid[0]][$id] =
						calendar_so::combine_status($status,$quantity,'REQ-PARTICIPANT');
				}
			}
		}
		if (!$participants)	// if all participants got removed, include current user
		{
			$participants[$this->user] = $participant_types['u'][$this->user] = calendar_so::combine_status('A',1,'CHAIR');
		}
		$alarms = array();
		// if default alarm set in prefs --> add it
		// we assume here that user does NOT have a whole-day but no regular default-alarm, no whole-day!
		if ((string)$this->cal_prefs['default-alarm'] !== '')
		{
			$offset = 60 * $this->cal_prefs['default-alarm'];
			$alarms[1] =  array(
				'default' => 1,
				'offset' => $offset ,
				'time'   => $start - $offset,
				'all'    => false,
				'owner'  => $owner,
				'id'	=> 1,
			);
		}
		return array(
			'participant_types' => $participant_types,
			'participants' => $participants,
			'owner' => $owner,
			'start' => $start,
			'end'   => $start + (int) $this->bo->cal_prefs['defaultlength']*60,
			'tzid'  => $this->bo->common_prefs['tz'],
			'priority' => 2,	// normal
			'public'=> $this->cal_prefs['default_private'] ? 0 : 1,
			'alarm' => $alarms,
			'recur_exception' => array(),
			'title' => $title ? $title : '',
		);
	}

	/**
	 * Process the edited event and evtl. call edit to redisplay it
	 *
	 * @param array $content posted eTemplate content
	 * @ToDo add conflict check / available quantity of resources when adding participants
	 */
	function process_edit($content)
	{
		if (!is_array($content))	// redirect from etemplate, if POST empty
		{
			return $this->edit(null,null,strip_tags($_GET['msg']));
		}
		// clear notification errors
		notifications::errors(true);
		$messages = null;
		$msg_permission_denied_added = false;
		list($button) = @each($content['button']);
		if (!$button && $content['action']) $button = $content['action'];	// action selectbox
		unset($content['button']); unset($content['action']);

		$view = $content['view'];
		if ($button == 'ical')
		{
			$msg = $this->export($content['id'],true);
		}
		// delete a recur-exception
		if ($content['recur_exception']['delete_exception'])
		{
			list($date) = each($content['recur_exception']['delete_exception']);
			// eT2 converts time to
			if (!is_numeric($date)) $date = egw_time::to (str_replace('Z','', $date), 'ts');
			unset($content['recur_exception']['delete_exception']);
			if (($key = array_search($date,$content['recur_exception'])) !== false)
			{
				// propagate the exception to a single event
				$recur_exceptions = $this->bo->so->get_related($content['uid']);
				foreach ($recur_exceptions as $id)
				{
					if (!($exception = $this->bo->read($id)) ||
							$exception['recurrence'] != $content['recur_exception'][$key]) continue;
					$exception['uid'] = common::generate_uid('calendar', $id);
					$exception['reference'] = $exception['recurrence'] = 0;
					$this->bo->update($exception, true, true,false,true,$messages,$content['no_notifications']);
					break;
				}
				unset($content['recur_exception'][$key]);
				$content['recur_exception'] = array_values($content['recur_exception']);
			}
		}
		// delete an alarm
		if ($content['alarm']['delete_alarm'])
		{
			list($id) = each($content['alarm']['delete_alarm']);
			//echo "delete alarm $id"; _debug_array($content['alarm']['delete_alarm']);

			if ($content['id'])
			{
				if ($this->bo->delete_alarm($id))
				{
					$msg = lang('Alarm deleted');
					unset($content['alarm'][$id]);
				}
				else
				{
					$msg = lang('Permission denied');
				}
			}
			else
			{
				unset($content['alarm'][$id]);
			}
		}
		if ($content['duration'])
		{
			$content['end'] = $content['start'] + $content['duration'];
		}
		// fix default alarm for a new (whole day) event, to be according to default-alarm(-wholeday) pref
		if ($content['alarm'][1]['default'])
		{
			$def_alarm = $this->cal_prefs['default-alarm'.($content['whole_day'] ? '-wholeday' : '')];
			if ((string)$def_alarm === '')
			{
				unset($content['alarm'][1]);	// '' = no alarm on whole day --> delete it
			}
			else
			{
				$content['alarm'][1]['offset'] = $offset = 60 * $def_alarm;
				$content['start'][1]['offset'] = $this->bo->date2ts($content['start']) - $offset;
			}
		}

		$event = $content;
		unset($event['new_alarm']);
		unset($event['alarm']['delete_alarm']);
		unset($event['duration']);

		if (in_array($button,array('ignore','freetime','reedit','confirm_edit_series')))
		{
			// no conversation necessary, event is already in the right format
		}
		else
		{
			// convert content => event
			if ($content['whole_day'])
			{
				$event['start'] = $this->bo->date2array($event['start']);
				$event['start']['hour'] = $event['start']['minute'] = 0; unset($event['start']['raw']);
				$event['start'] = $this->bo->date2ts($event['start']);
				$event['end'] = $this->bo->date2array($event['end']);
				$event['end']['hour'] = 23; $event['end']['minute'] = $event['end']['second'] = 59; unset($event['end']['raw']);
				$event['end'] = $this->bo->date2ts($event['end']);
			}
			// some checks for recurrences, if you give a date, make it a weekly repeating event and visa versa
			if ($event['recur_type'] == MCAL_RECUR_NONE && $event['recur_data']) $event['recur_type'] = MCAL_RECUR_WEEKLY;
			if ($event['recur_type'] == MCAL_RECUR_WEEKLY && !$event['recur_data'])
			{
				$event['recur_data'] = 1 << (int)date('w',$event['start']);
			}
			if (isset($content['participants']))
			{

				$event['participants'] = $event['participant_types'] = array();

				foreach($content['participants'] as $key => $data)
				{
					switch($key)
					{
						case 'delete':		// handled in default
						case 'quantity':	// handled in new_resource
						case 'role':		// handled in add, account or resource
						case 'cal_resources':
						case 'status_date':
							break;

						case 'add':
							// email or rfc822 addresse (eg. "Ralf Becker <ralf@domain.com>") in the search field
							$matches = array();
							if (($email = $content['participants']['resource']['search']) &&
									(preg_match('/^(.*<)?([a-z0-9_.-]+@[a-z0-9_.-]{5,})>?$/i',$email,$matches)))
							{
								$status = calendar_so::combine_status('U',$content['participants']['quantity'],$content['participants']['role']);
								// check if email belongs to account or contact --> prefer them over just emails (if we are allowed to invite him)
								if (($data = $GLOBALS['egw']->accounts->name2id($matches[2],'account_email')) && $this->bo->check_acl_invite($data))
								{
									$event['participants'][$data] = $event['participant_types']['u'][$data] = $status;
								}
								elseif ((list($data) = ExecMethod2('addressbook.addressbook_bo.search',array(
									'email' => $matches[2],
									'email_home' => $matches[2],
								),true,'','','',false,'OR')))
								{
									$event['participants']['c'.$data['id']] = $event['participant_types']['c'][$data['id']] = $status;
								}
								else
								{
									$event['participants']['e'.$email] = $event['participant_types']['e'][$email] = $status;
								}
							}
							elseif (!$content['participants']['account'] && !$content['participants']['resource'])
							{
								$msg = lang('You need to select an account, contact or resource first!');
							}
							break;

						case 'resource':
							if (is_array($data))	// if $data['current'] is NOT set --> $app==''
							{
								list($app,$id) = explode(':',$data['current']);
								if(!$app && !$id)
								{
									$app = $data['app'];
									$id = $data['id'];
								}
							}
							else
							{
								list($app,$id) = explode(':',$data);
							}
							foreach($this->bo->resources as $type => $data)
							{
								if ($data['app'] == $app) break;
							}
							$uid = $this->bo->resources[$type]['app'] == $app ? $type.$id : false;
							if ($app == 'home-accounts')
							{
								$data = $id;
							}
							// check if new entry is no account (or contact entry of an account)
							elseif ($app != 'addressbook' || !($data = $GLOBALS['egw']->accounts->name2id($id,'person_id')) || !$this->bo->check_acl_invite($data))
							{
								if ($uid && $id)
								{
									$status = isset($this->bo->resources[$type]['new_status']) ? ExecMethod($this->bo->resources[$type]['new_status'],$id) : 'U';
									if ($status)
									{
										$res_info = $this->bo->resource_info($uid);
										// todo check real availability = maximum - already booked quantity
										if (isset($res_info['useable']) && $content['participants']['quantity'] > $res_info['useable'])
										{
											$msg .= lang('Maximum available quantity of %1 exceeded!',$res_info['useable']);
											foreach(array('quantity','resource','role') as $n)
											{
												$event['participants'][$n] = $content['participants'][$n];
											}
										}
										else
										{
											$event['participants'][$uid] = $event['participant_types'][$type][$id] =
												calendar_so::combine_status($status,$content['participants']['quantity'],$content['participants']['role']);
										}
									}
									elseif(!$msg_permission_denied_added)
									{
										$msg .= lang('Permission denied!');
										$msg_permission_denied_added = true;
									}
								}
								// if participant is a contact and no title yet, add its link title as title
								if ($app == 'addressbook' && empty($event['title']))
								{
									$event['title'] = egw_link::title($app, substr($uid, 1));
								}
								break;
							}
							// fall-through for accounts entered as contact
						case 'account':
							foreach(is_array($data) ? $data : explode(',',$data) as $uid)
							{
								if ($uid && $this->bo->check_acl_invite($uid))
								{
									$event['participants'][$uid] = $event['participant_types']['u'][$uid] =
										calendar_so::combine_status($uid == $this->bo->user ? 'A' : 'U',1,$content['participants']['role']);
								}
								elseif($uid && !$msg_permission_denied_added)
								{
									$msg .= lang('Permission denied!');
									$msg_permission_denied_added = true;
								}
							}
							break;

						default:		// existing participant row
							if (!is_array($data)) continue;	// widgets in participant tab, above participant list
							foreach(array('uid','status','quantity','role') as $name)
							{
								$$name = $data[$name];
							}
							if ($content['participants']['delete'][$uid] || $content['participants']['delete'][md5($uid)])
							{
								$uid = false;	// entry has been deleted
							}
							elseif ($uid)
							{
								if (is_numeric($uid))
								{
									$id = $uid;
									$type = 'u';
								}
								else
								{
									$id = substr($uid,1);
									$type = $uid[0];
								}
								if ($data['old_status'] != $status && !(!$data['old_status'] && $status == 'G'))
								{
									//echo "<p>$uid: status changed '$data[old_status]' --> '$status<'/p>\n";
									$quantity = $role = null;
									$new_status = calendar_so::combine_status($status, $quantity, $role);
									if ($this->bo->set_status($event['id'],$uid,$new_status,isset($content['edit_single']) ? $content['participants']['status_date'] : 0, false, true, $content['no_notifications']))
									{
										// refreshing the calendar-view with the changed participant-status
										if($event['recur_type'] != MCAL_RECUR_NONE)
										{
											$msg = lang('Status for all future scheduled days changed');
										}
										else
										{
											if(isset($content['edit_single']))
											{
												$msg = lang('Status for this particular day changed');
												// prevent accidentally creating a real exception afterwards
												$view = true;
												$hide_delete = true;
											}
											else
											{
												$msg = lang('Status changed');
												//Refresh the event in the main window after changing status
												egw_framework::refresh_opener($msg, 'calendar', $event['id']);
											}
										}
										if (!$content['no_popup'])
										{
											//we are handling refreshing for status changes on client side
										}
										if ($status == 'R' && $event['alarm'])
										{
											// remove from bo->set_status deleted alarms of rejected users from UI too
											foreach($event['alarm'] as $alarm_id => $alarm)
											{
												if ((string)$alarm['owner'] === (string)$uid)
												{
													unset($event['alarm'][$alarm_id]);
												}
											}
										}
									}
								}
								if ($uid && $status != 'G')
								{
									$event['participants'][$uid] = $event['participant_types'][$type][$id] =
										calendar_so::combine_status($status,$quantity,$role);
								}
							}
							break;
					}
				}
			}
		}
		$preserv = array(
			'view'			=> $view,
			'hide_delete'	=> $hide_delete,
			'edit_single'	=> $content['edit_single'],
			'reference'		=> $content['reference'],
			'recurrence'	=> $content['recurrence'],
			'actual_date'	=> $content['actual_date'],
			'no_popup'		=> $content['no_popup'],
			'tabs'			=> $content['tabs'],
			'template'      => $content['template'],
		);
		$noerror=true;

		//error_log(__METHOD__.$button.'#'.array2string($content['edit_single']).'#');

		$ignore_conflicts = $status_reset_to_unknown = false;

		switch((string)$button)
		{
			case 'ignore':
				$ignore_conflicts = true;
				$button = $event['button_was'];	// save or apply
				unset($event['button_was']);
				break;

		}

		switch((string)$button)
		{
		case 'exception':	// create an exception in a recuring event
			$msg = $this->_create_exception($event,$preserv);
			break;

		case 'copy':	// create new event with copied content, some content need to be unset to make a "new" event
			unset($event['id']);
			unset($event['uid']);
			unset($event['reference']);
			unset($preserv['reference']);
			unset($event['recurrence']);
			unset($preserv['recurrence']);
			unset($event['recur_exception']);
			unset($event['edit_single']);	// in case it has been set
			unset($event['modified']);
			unset($event['modifier']);
			unset($event['caldav_name']);
			$event['owner'] = !(int)$this->owner || !$this->bo->check_perms(EGW_ACL_ADD,0,$this->owner) ? $this->user : $this->owner;

			// Clear participant stati
			foreach($event['participant_types'] as $type => &$participants)
			{
				foreach($participants as $id => &$response)
				{
					if($type == 'u' && $id == $event['owner']) continue;
					calendar_so::split_status($response, $quantity, $role);
					// if resource defines callback for status of new status (eg. Resources app acknowledges direct booking acl), call it
					$status = isset($this->bo->resources[$type]['new_status']) ? ExecMethod($this->bo->resources[$type]['new_status'],$id) : 'U';
					$response = calendar_so::combine_status($status,$quantity,$role);
				}
			}

			// Copy alarms
			if (is_array($event['alarm']))
			{
				foreach($event['alarm'] as $n => &$alarm)
				{
					unset($alarm['id']);
					unset($alarm['cal_id']);
				}
			}

			// Get links to be copied
			// With no ID, $content['link_to']['to_id'] is used
			$content['link_to']['to_id'] = array('to_app' => 'calendar', 'to_id' => 0);
			foreach(egw_link::get_links('calendar', $content['id']) as $link)
			{
				if ($link['app'] != egw_link::VFS_APPNAME)
				{
					egw_link::link('calendar', $content['link_to']['to_id'], $link['app'], $link['id'], $link['remark']);
				}
				elseif ($link['app'] == egw_link::VFS_APPNAME)
				{
					egw_link::link('calendar', $content['link_to']['to_id'], egw_link::VFS_APPNAME, array(
						'tmp_name' => egw_link::vfs_path($link['app2'], $link['id2']).'/'.$link['id'],
						'name' => $link['id'],
					), $link['remark']);
				}
			}
			unset($link);
			$preserv['view'] = $preserv['edit_single'] = false;
			$msg = lang('Event copied - the copy can now be edited');
			$event['title'] = lang('Copy of:').' '.$event['title'];
			break;

		case 'mail':
		case 'sendrequest':
		case 'save':
		case 'print':
		case 'apply':
		case 'infolog':
			if ($event['id'] && !$this->bo->check_perms(EGW_ACL_EDIT,$event))
			{
				$msg = lang('Permission denied');
				$button = '';
				break;
			}
			if ($event['start'] > $event['end'])
			{
				$msg = lang('Error: Starttime has to be before the endtime !!!');
				$button = '';
				break;
			}
			if ($event['recur_type'] != MCAL_RECUR_NONE && $event['recur_enddate'] && $event['start'] > $event['recur_enddate'])
			{
				$msg = lang('repetition').': '.lang('Error: Starttime has to be before the endtime !!!');
				$button = '';
				break;
			}
			if ($event['recur_type'] != MCAL_RECUR_NONE && $event['end']-$event['start'] > calendar_rrule::recurrence_interval($event['recur_type'], $event['recur_interval']))
			{
				$msg = lang('Error: Duration of event longer then recurrence interval!');
				$button = '';
				break;
			}
			if (!$event['participants'])
			{
				$msg = lang('Error: no participants selected !!!');
				$button = '';
				break;
			}
			// if private event with ressource reservation is forbidden
			if (!$event['public'] && $GLOBALS['egw_info']['server']['no_ressources_private'])
			{
				foreach (array_keys($event['participants']) as $uid)
				{
					if ($uid[0] == 'r') //ressource detection
					{
						$msg = lang('Error: ressources reservation in private events is not allowed!!!');
						$button = '';
						break 2; //break foreach and case
					}
				}
			}
			if ($content['edit_single'])	// we edited a single event from a series
			{
				$event['reference'] = $event['id'];
				$event['recurrence'] = $content['edit_single'];
				unset($event['id']);
				$conflicts = $this->bo->update($event,$ignore_conflicts,true,false,true,$messages,$content['no_notifications']);
				if (!is_array($conflicts) && $conflicts)
				{
					// now we need to add the original start as recur-execption to the series
					$recur_event = $this->bo->read($event['reference']);
					$recur_event['recur_exception'][] = $content['edit_single'];
					// check if we need to move the alarms, because they are next on that exception
					foreach($recur_event['alarm'] as $id => $alarm)
					{
						if ($alarm['time'] == $content['edit_single'] - $alarm['offset'])
						{
							$rrule = calendar_rrule::event2rrule($recur_event, true);
							foreach ($rrule as $time)
							{
								if ($content['edit_single'] < $time->format('ts'))
								{
									$alarm['time'] = $time->format('ts') - $alarm['offset'];
									$this->bo->save_alarm($event['reference'], $alarm);
									break;
								}
							}
						}
					}
					unset($recur_event['start']); unset($recur_event['end']);	// no update necessary
					unset($recur_event['alarm']);	// unsetting alarms too, as they cant be updated without start!
					$this->bo->update($recur_event,true);	// no conflict check here
					unset($recur_event);
					unset($event['edit_single']);			// if we further edit it, it's just a single event
					unset($preserv['edit_single']);
				}
				else	// conflict or error, we need to reset everything to the state befor we tried to save it
				{
					$event['id'] = $event['reference'];
					$event['reference'] = $event['recurrence'] = 0;
					$event['uid'] = $content['uid'];
				}
			}
			else	// we edited a non-reccuring event or the whole series
			{
				if (($old_event = $this->bo->read($event['id'])))
				{
					if ($event['recur_type'] != MCAL_RECUR_NONE)
					{
						// we edit a existing series event
						if ($event['start'] != $old_event['start'] ||
							$event['whole_day'] != $old_event['whole_day'])
						{
							if(!($next_occurrence = $this->bo->read($event['id'], $this->bo->now_su + 1, true)))
							{
								$msg = lang("Error: You can't shift a series from the past!");
								$noerror = false;
								break;
							}
							// splitting of series confirmed or first event clicked (no confirmation necessary)
							$orig_event = $event;

							// calculate offset against old series start or clicked recurrance,
							// depending on which is smaller
							$offset = $event['start'] - $old_event['start'];
							if (abs($offset) > abs($off2 = $event['start'] - $event['actual_date']))
							{
								$offset = $off2;
							}
							// base start-date of new series on actual / clicked date
							$actual_date = $event['actual_date'];
							$event['start'] = $actual_date + $offset;
							if ($content['duration'])
							{
								$event['end'] = $event['start'] + $content['duration'];
							}
							elseif($event['end'] < $event['start'])
							{
								$event['end'] = $event['start'] + $event['end'] - $actual_date;
							}
							//echo "<p>".__LINE__.": event[start]=$event[start]=".egw_time::to($event['start']).", duration=$content[duration], event[end]=$event[end]=".egw_time::to($event['end']).", offset=$offset</p>\n";
							$event['participants'] = $old_event['participants'];
							foreach ($old_event['recur_exception'] as $key => $exdate)
							{
								if ($exdate > $actual_date)
								{
									unset($old_event['recur_exception'][$key]);
									$event['recur_exception'][$key] += $offset;
								}
								else
								{
									unset($event['recur_exception'][$key]);
								}
							}
							$old_alarms = $old_event['alarm'];
							if ($old_event['start'] < $actual_date)
							{
								unset($orig_event);
								// copy event by unsetting the id(s)
								unset($event['id']);
								unset($event['uid']);
								unset($event['caldav_name']);

								// set enddate of existing event
								$rriter = calendar_rrule::event2rrule($old_event, true);
								$rriter->rewind();
								$last = $rriter->current();
								do
								{
									$rriter->next_no_exception();
									$occurrence = $rriter->current();
								}
								while ($rriter->valid() &&
										egw_time::to($occurrence, 'ts') < $actual_date &&
										($last = $occurrence));
								$last->setTime(0, 0, 0);
								$old_event['recur_enddate'] = egw_time::to($last, 'ts');
								if (!$this->bo->update($old_event,true,true,false,true,$dummy=null,$content['no_notifications']))
								{
									$msg .= ($msg ? ', ' : '') .lang('Error: the entry has been updated since you opened it for editing!').'<br />'.
										lang('Copy your changes to the clipboard, %1reload the entry%2 and merge them.','<a href="'.
											htmlspecialchars(egw::link('/index.php',array(
												'menuaction' => 'calendar.calendar_uiforms.edit',
												'cal_id'    => $content['id'],
											))).'">','</a>');
									$noerror = false;
									$event = $orig_event;
									break;
								}
								$event['alarm'] = array();
							}
						}
					}
					else
					{
						if ($old_event['start'] != $event['start'] ||
							$old_event['end'] != $event['end'] ||
							$event['whole_day'] != $old_event['whole_day'])
						{
							$sameday = (date('Ymd', $old_event['start']) == date('Ymd', $event['start']));
							foreach((array)$event['participants'] as $uid => $status)
							{
								$q = $r = null;
								calendar_so::split_status($status,$q,$r);
								if ($uid[0] != 'c' && $uid[0] != 'e' && $uid != $this->bo->user && $status != 'U')
								{
									$preferences = CreateObject('phpgwapi.preferences',$uid);
									$part_prefs = $preferences->read_repository();
									switch ($part_prefs['calendar']['reset_stati'])
									{
										case 'no':
											break;
										case 'startday':
											if ($sameday) break;
										default:
											$status_reset_to_unknown = true;
											$event['participants'][$uid] = calendar_so::combine_status('U',$q,$r);
											// todo: report reset status to user
									}
								}
							}
						}
					}
				}
				$conflicts = $this->bo->update($event,$ignore_conflicts,true,false,true,$messages,$content['no_notifications']);
				unset($event['ignore']);
			}
			if (is_array($conflicts))
			{
				$event['button_was'] = $button;	// remember for ignore
				return $this->conflicts($event,$conflicts,$preserv);
			}
			// check if there are messages from update, eg. removed participants or categories because of missing rights
			if ($messages)
			{
				$msg  .= ($msg ? ', ' : '').implode(', ',$messages);
			}
			if ($conflicts === 0)
			{
				$msg .= ($msg ? ', ' : '') .lang('Error: the entry has been updated since you opened it for editing!').'<br />'.
							lang('Copy your changes to the clipboard, %1reload the entry%2 and merge them.','<a href="'.
								htmlspecialchars(egw::link('/index.php',array(
								'menuaction' => 'calendar.calendar_uiforms.edit',
								'cal_id'    => $content['id'],
							))).'">','</a>');
				$noerror = false;
			}
			elseif ($conflicts > 0)
			{
				// series moved by splitting in two --> move alarms and exceptions
				if ($old_event && $old_event['id'] != $event['id'])
				{
					foreach ((array)$old_alarms as $alarm)
					{
						// check if alarms still needed in old event, if not delete it
						$event_time = $alarm['time'] + $alarm['offset'];
						if ($event_time >= $actual_date)
						{
							$this->bo->delete_alarm($alarm['id']);
						}
						$alarm['time'] += $offset;
						unset($alarm['id']);
						// if alarm would be in the past (eg. event moved back) --> move to next possible recurrence
						if ($alarm['time'] < $this->bo->now_su)
						{
							if (($next_occurrence = $this->bo->read($event['id'], $this->bo->now_su+$alarm['offset'], true)))
							{
								$alarm['time'] =  $next_occurrence['start'] - $alarm['offset'];
							}
							else
							{
								$alarm = false;	// no (further) recurence found --> ignore alarm
							}
						}
						// alarm is currently on a previous recurrence --> set for first recurrence of new series
						elseif ($event_time < $event['start'])
						{
							$alarm['time'] =  $event['start'] - $alarm['offset'];
						}
						if ($alarm)
						{
							$alarm['id'] = $this->bo->save_alarm($event['id'], $alarm);
							$event['alarm'][$alarm['id']] = $alarm;
						}
					}
					// attach all future exceptions to the new series
					$events =& $this->bo->search(array(
						'query' => array('cal_uid' => $old_event['uid']),
						'filter' => 'owner',  // return all possible entries
						'daywise' => false,
						'date_format' => 'ts',
					));
					foreach ((array)$events as $exception)
					{
						if ($exception['recurrence'] > $actual_date)
						{
							$exception['recurrence'] += $offset;
							$exception['reference'] = $event['id'];
							$exception['uid'] = $event['uid'];
							$this->bo->update($exception, true, true, true, true, $msg=null, $content['no_notifications']);
						}
					}
				}

				$message = lang('Event saved');
				if ($status_reset_to_unknown)
				{
					foreach((array)$event['participants'] as $uid => $status)
					{
						if ($uid[0] != 'c' && $uid[0] != 'e' && $uid != $this->bo->user)
						{
							calendar_so::split_status($status,$q,$r);
							$status = calendar_so::combine_status('U',$q,$r);
							$this->bo->set_status($event['id'], $uid, $status, 0, true);
						}
					}
					$message .= lang(', stati of participants reset');
				}

				$response = egw_json_response::get();
				if($response)
				{
					// Directly update stored data.  If event is still visible, it will
					// be notified & update itself.
					if(!$old_event)
					{
						// For new events, make sure we have the whole event, not just form data
						$event = $this->bo->read($event['id']);
					}
					$this->to_client($event);
					$response->call('egw.dataStoreUID','calendar::'.$event['id'],$event);
				}

				$msg = $message . ($msg ? ', ' . $msg : '');
				egw_framework::refresh_opener($msg, 'calendar', $event['id']);
				// writing links for new entry, existing ones are handled by the widget itself
				if (!$content['id'] && is_array($content['link_to']['to_id']))
				{
					egw_link::link('calendar',$event['id'],$content['link_to']['to_id']);
				}
			}
			else
			{
				$msg = lang('Error: saving the event !!!');
			}
			break;

		case 'cancel':
			if($content['cancel_needs_refresh'])
			{
				egw_framework::refresh_opener($msg, 'calendar');
			}
			break;

		case 'delete':					// delete of regular event
		case 'delete_keep_exceptions':	// series and user selected to keep the exceptions
		case 'delete_exceptions':		// series and user selected to delete the exceptions too
			$exceptions_kept = null;
			if ($this->bo->delete($event['id'], (int)$content['edit_single'], false, $event['no_notifications'],
				$button == 'delete_exceptions', $exceptions_kept))
			{
				if ($event['recur_type'] != MCAL_RECUR_NONE && $content['reference'] == 0 && !$content['edit_single'])
				{
					$msg = lang('Series deleted');
					if ($exceptions_kept) $msg .= lang(', exceptions preserved');
				}
				else
				{
					$msg = lang('Event deleted');
				}

			}
			break;

		case 'freetime':
			// the "click" has to be in onload, to make sure the button is already created
			$event['button_was'] = $button;
			break;

		case 'add_alarm':
			$time = $content['start'];
			$offset = $time - $content['new_alarm']['date'];
			if ($event['recur_type'] != MCAL_RECUR_NONE &&
				($next_occurrence = $this->bo->read($event['id'], $this->bo->now_su + $offset, true)) &&
				$time < $next_occurrence['start'])
			{
				$content['new_alarm']['date'] = $next_occurrence['start'] - $offset;
			}
			if ($this->bo->check_perms(EGW_ACL_EDIT,!$content['new_alarm']['owner'] ? $event : 0,$content['new_alarm']['owner']))
			{
				$alarm = array(
					'offset' => $offset,
					'time'   => $content['new_alarm']['date'],
					'all'    => !$content['new_alarm']['owner'],
					'owner'  => $content['new_alarm']['owner'] ? $content['new_alarm']['owner'] : $this->user,
				);
				if ($alarm['time'] < $this->bo->now_su)
				{
					$msg = lang("Can't add alarms in the past !!!");
				}
				elseif ($event['id'])	// save the alarm immediatly
				{
					if (($alarm_id = $this->bo->save_alarm($event['id'],$alarm)))
					{
						$alarm['id'] = $alarm_id;
						$event['alarm'][$alarm_id] = $alarm;

						$msg = lang('Alarm added');
						egw_framework::refresh_opener($msg,'calendar', $event['id'], 'update');
					}
					else
					{
						$msg = lang('Error adding the alarm');
					}
				}
				else
				{
					for($alarm['id']=1; isset($event['alarm'][$alarm['id']]); $alarm['id']++) {}	// get a temporary non-conflicting, numeric id
					$event['alarm'][$alarm['id']] = $alarm;
				}
			}
			else
			{
				$msg = lang('Permission denied');
			}
			break;
		}
		// add notification-errors, if we have some
		if (($notification_errors = notifications::errors(true)))
		{
			$msg .= ($msg ? "\n" : '').implode("\n", $notification_errors);
		}
		if (in_array($button,array('cancel','save','delete','delete_exceptions','delete_keep_exceptions')) && $noerror)
		{
			if ($content['lock_token'])	// remove an existing lock
			{
				egw_vfs::unlock(egw_vfs::app_entry_lock_path('calendar',$content['id']),$content['lock_token'],false);
			}
			if ($content['no_popup'])
			{
				egw::redirect_link('/index.php',array(
					'menuaction' => 'calendar.calendar_uiviews.index',
					'msg'        => $msg,
				));
			}
			if (in_array($button,array('delete_exceptions','delete_keep_exceptions')) || $content['recur_type'] && $button == 'delete')
			{
				egw_framework::refresh_opener($msg,'calendar');
			}
			else
			{
				egw_framework::refresh_opener($msg, 'calendar', $event['id'], $button == 'save' ? ($content['id'] ? 'update' : 'add') : 'delete');
			}
			egw_framework::window_close();
			common::egw_exit();
		}
		unset($event['no_notifications']);
		return $this->edit($event,$preserv,$msg,$event['id'] ? $event['id'] : $content['link_to']['to_id']);
	}

	/**
	 * Create an exception from the clicked event
	 *
	 * It's not stored to the DB unless the user saves it!
	 *
	 * @param array &$event
	 * @param array &$preserv
	 * @return string message that exception was created
	 */
	function _create_exception(&$event,&$preserv)
	{
		// In some cases where the user makes the first day an exception, actual_date may be missing
		$preserv['actual_date'] = $preserv['actual_date'] ? $preserv['actual_date'] : $event['start'];

		$event['end'] += $preserv['actual_date'] - $event['start'];
		$event['reference'] = $preserv['reference'] = $event['id'];
		$event['recurrence'] = $preserv['recurrence'] = $preserv['actual_date'];
		$event['start'] = $preserv['edit_single'] = $preserv['actual_date'];
		$event['recur_type'] = MCAL_RECUR_NONE;
		foreach(array('recur_enddate','recur_interval','recur_exception','recur_data') as $name)
		{
			unset($event[$name]);
		}
		// add all alarms as new alarms to execption
		$event['alarm'] = array_values((array)$event['alarm']);
		foreach($event['alarm'] as &$alarm)
		{
			unset($alarm['uid'], $alarm['id'], $alarm['time']);
		}
		if($this->bo->check_perms(EGW_ACL_EDIT,$event))
		{
			return lang('Save event as exception - Delete single occurrence - Edit status or alarms for this particular day');
		}
		return lang('Edit status or alarms for this particular day');
	}

	/**
	 * return javascript to open mail compose window with preset content to mail all participants
	 *
	 * @param array $event
	 * @param boolean $added
	 * @return string javascript window.open command
	 */
	function ajax_custom_mail($event,$added,$asrequest=false)
	{
		$to = array();

		foreach($event['participants'] as $uid => $status)
		{
			//error_log(__METHOD__.__LINE__.' '.$uid.':'.array2string($status));
			if (empty($status)) continue;
			$toadd = '';
			if ((isset($status['status']) && $status['status'] == 'R') || (isset($status['uid']) && $status['uid'] == $this->user)) continue;

			if (isset($status['uid']) && is_numeric($status['uid']) && $GLOBALS['egw']->accounts->get_type($status['uid']) == 'u')
			{
				if (!($email = $GLOBALS['egw']->accounts->id2name($status['uid'],'account_email'))) continue;

				$lid = $firstname = $lastname = null;
				$GLOBALS['egw']->accounts->get_account_name($status['uid'],$lid,$firstname,$lastname);

				$toadd = $firstname.' '.$lastname.' <'.$email.'>';
				if (!in_array($toadd,$to)) $to[] = $toadd;
				//error_log(__METHOD__.__LINE__.array2string($to));
			}
			elseif ($uid < 0)
			{
				foreach($GLOBALS['egw']->accounts->members($uid,true) as $uid)
				{
					if (!($email = $GLOBALS['egw']->accounts->id2name($uid,'account_email'))) continue;

					$GLOBALS['egw']->accounts->get_account_name($uid,$lid,$firstname,$lastname);

					$toadd = $firstname.' '.$lastname.' <'.$email.'>';
					// dont add groupmembers if they already rejected the event, or are the current user
					if (!in_array($toadd,$to) && ($event['participants'][$uid] !== 'R' && $uid != $this->user)) $to[] = $toadd;
					//error_log(__METHOD__.__LINE__.array2string($to));
				}
			}
			elseif(!empty($status['uid'])&& !is_numeric(substr($status['uid'],0,1)) && ($info = $this->bo->resource_info($status['uid'])))
			{
				$to[] = $info['email'];
				//error_log(__METHOD__.__LINE__.array2string($to));
			}
			elseif(!is_numeric(substr($uid,0,1)) && ($info = $this->bo->resource_info($uid)))
			{
				$to[] = $info['email'];
				//error_log(__METHOD__.__LINE__.array2string($to));
			}
		}
		list($subject,$body) = $this->bo->get_update_message($event,$added ? MSG_ADDED : MSG_MODIFIED);	// update-message is in TZ of the user
		//error_log(__METHOD__.print_r($event,true));
		$boical = new calendar_ical();
		// we need to pass $event[id] so iCal class reads event again,
		// as event is in user TZ, but iCal class expects server TZ!
		$ics = $boical->exportVCal(array($event['id']),'2.0','REQUEST',false);

		$ics_file = tempnam($GLOBALS['egw_info']['server']['temp_dir'],'ics');
		if(($f = fopen($ics_file,'w')))
		{
			fwrite($f,$ics);
			fclose($f);
		}
		//error_log(__METHOD__.__LINE__.array2string($to));
		$vars = array(
			'menuaction'      => 'mail.mail_compose.compose',
			'mimeType'		  => 'plain', // force type to plain as thunderbird seems to try to be smart while parsing html messages with ics attachments
			'preset[to]'      => $to,
			'preset[subject]' => $subject,
			'preset[body]'    => $body,
			'preset[name]'    => 'event.ics',
			'preset[file]'    => $ics_file,
			'preset[type]'    => 'text/calendar'.($asrequest?'; method=REQUEST':''),
			'preset[size]'    => filesize($ics_file),
		);
		if ($asrequest) $vars['preset[msg]'] = lang('You attempt to mail a meetingrequest to the recipients above. Depending on the client this mail is opened with, the recipient may or may not see the mailbody below, but only see the meeting request attached.');
		$response = egw_json_response::get();
		$response->call('app.calendar.custom_mail', $vars);
	}

	/**
	 * Get title of a uid / calendar participant
	 *
	 * @param int|string $uid
	 * @return string
	 */
	public function get_title($uid)
	{
		if (is_numeric($uid))
		{
			return common::grab_owner_name($uid);
		}
		elseif (($info = $this->bo->resource_info($uid)))
		{
			if ($uid[0] == 'e' && $info['name'] && $info['name'] != $info['email'])
			{
				return $info['name'].' <'.$info['email'].'>';
			}
			return $info['name'] ? $info['name'] : $info['email'];
		}
		return '#'.$uid;
	}

	/**
	 * Compare two uid by there title
	 *
	 * @param int|string $uid1
	 * @param int|string $uid2
	 * @return int see strnatcasecmp
	 */
	public function uid_title_cmp($uid1, $uid2)
	{
		return strnatcasecmp($this->get_title($uid1), $this->get_title($uid2));
	}

	/**
	 * Edit a calendar event
	 *
	 * @param array $event Event to edit, if not $_GET['cal_id'] contains the event-id
	 * @param array $preserv following keys:
	 *	view boolean view-mode, if no edit-access we automatic fallback to view-mode
	 *	hide_delete boolean hide delete button
	 *	no_popup boolean use a popup or not
	 *	edit_single int timestamp of single event edited, unset/null otherwise
	 * @param string $msg ='' msg to display
	 * @param mixed $link_to_id ='' from or for the link-widget
	 */
	function edit($event=null,$preserv=null,$msg='',$link_to_id='')
	{
		$sel_options = array(
			'recur_type' => &$this->bo->recur_types,
			'status'     => $this->bo->verbose_status,
			'duration'   => $this->durations,
			'role'       => $this->bo->roles,
			'new_alarm[options]' => $this->bo->alarms + array(0 => lang('Custom')),
			'action'     => array(
				'copy' => array('label' => 'Copy', 'title' => 'Copy this event'),
				'ical' => array('label' => 'Export', 'title' => 'Download this event as iCal'),
				'print' => array('label' => 'Print', 'title' => 'Print this event'),
				'infolog' => array('label' => 'InfoLog', 'title' => 'Create an InfoLog from this event'),
				'mail' => array('label' => 'Mail all participants', 'title' => 'Compose a mail to all participants after the event is saved'),
				'sendrequest' => array('label' => 'Meetingrequest to all participants', 'title' => 'Send meetingrequest to all participants after the event is saved'),
			),
		);
		unset($sel_options['status']['G']);
		if (!is_array($event))
		{
			$preserv = array(
				'no_popup' => isset($_GET['no_popup']),
				'template' => isset($_GET['template']) ? $_GET['template'] : (isset($_REQUEST['print']) ? 'calendar.print' : 'calendar.edit'),
			);
			$cal_id = (int) $_GET['cal_id'];
			if($_GET['action'])
			{
				$event = $this->bo->read($cal_id);
				$event['action'] = $_GET['action'];
				unset($event['participants']);
				return $this->process_edit($event);
			}
			// vfs url
			if (!empty($_GET['ical_url']) && parse_url($_GET['ical_url'], PHP_URL_SCHEME) == 'vfs')
			{
				$_GET['ical_vfs'] = parse_url($_GET['ical_url'], PHP_URL_PATH);
			}
			// vfs path
			if (!empty($_GET['ical_vfs']) &&
				(!egw_vfs::file_exists($_GET['ical_vfs']) || !($_GET['ical'] = file_get_contents(egw_vfs::PREFIX.$_GET['ical_vfs']))))
			{
				//error_log(__METHOD__."() Error: importing the iCal: vfs file not found '$_GET[ical_vfs]'!");
				$msg = lang('Error: importing the iCal').': '.lang('VFS file not found').': '.$_GET['ical_vfs'];
				$event =& $this->default_add_event();
			}
			if (!empty($_GET['ical_data']) &&
				!($_GET['ical'] = egw_link::get_data($_GET['ical_data'])))
			{
				//error_log(__METHOD__."() Error: importing the iCal: data not found '$_GET[ical_data]'!");
				$msg = lang('Error: importing the iCal').': '.lang('Data not found').': '.$_GET['ical_data'];
				$event =& $this->default_add_event();
			}
			if (!empty($_GET['ical']))
			{
				$ical = new calendar_ical();
				if (!($events = $ical->icaltoegw($_GET['ical'], '', 'utf-8')) || count($events) != 1)
				{
					error_log(__METHOD__."('$_GET[ical]') error parsing iCal!");
					$msg = lang('Error: importing the iCal');
					$event =& $this->default_add_event();
				}
				else
				{
					// as icaltoegw returns timestamps in server-time, we have to convert them here to user-time
					$this->bo->db2data($events, 'ts');

					$event = array_shift($events);
					if (($existing_event = $this->bo->read($event['uid'])))
					{
						$event = $existing_event;
					}
					else
					{
						$event['participant_types'] = array();
						foreach($event['participants'] as $uid => $status)
						{
							$user_type = $user_id = null;
							calendar_so::split_user($uid, $user_type, $user_id);
							$event['participant_types'][$user_type][$user_id] = $status;
						}
					}
					//error_log(__METHOD__."(...) parsed as ".array2string($event));
				}
				unset($ical);
			}
			elseif (!$cal_id || $cal_id && !($event = $this->bo->read($cal_id)))
			{
				if ($cal_id)
				{
					if (!$preserv['no_popup'])
					{
						egw_framework::window_close(lang('Permission denied'));
					}
					else
					{
						$GLOBALS['egw']->framework->render('<p class="message" align="center">'.lang('Permission denied')."</p>\n",null,true);
						common::egw_exit();
					}
				}
				$event =& $this->default_add_event();
			}
			else
			{
				$preserv['actual_date'] = $event['start'];		// remember the date clicked
				if ($event['recur_type'] != MCAL_RECUR_NONE)
				{
					if (empty($event['whole_day']))
					{
						$date = $_GET['date'];
					}
					else
					{
						$date = $this->bo->so->startOfDay(new egw_time($_GET['date'], egw_time::$user_timezone));
						$date->setUser();
					}
					$event = $this->bo->read($cal_id, $date, true);
					$preserv['actual_date'] = $event['start'];		// remember the date clicked
					if ($_GET['exception'])
					{
						$msg = $this->_create_exception($event,$preserv);
					}
					else
					{
						$event = $this->bo->read($cal_id, null, true);
					}
				}
			}
			// set new start and end if given by $_GET
			if(isset($_GET['start'])) { $event['start'] = $_GET['start']; }
			if(isset($_GET['end'])) { $event['end'] = $_GET['end']; }
			// check if the event is the whole day
			$start = $this->bo->date2array($event['start']);
			$end = $this->bo->date2array($event['end']);
			$event['whole_day'] = !$start['hour'] && !$start['minute'] && $end['hour'] == 23 && $end['minute'] == 59;

			$link_to_id = $event['id'];
			if (!$event['id'] && isset($_REQUEST['link_app']) && isset($_REQUEST['link_id']))
			{
				$link_ids = is_array($_REQUEST['link_id']) ? $_REQUEST['link_id'] : array($_REQUEST['link_id']);
				foreach(is_array($_REQUEST['link_app']) ? $_REQUEST['link_app'] : array($_REQUEST['link_app']) as $n => $link_app)
				{
					$link_id = $link_ids[$n];
					if(!preg_match('/^[a-z_0-9-]+:[:a-z_0-9-]+$/i',$link_app.':'.$link_id))	// guard against XSS
					{
						continue;
					}
					if(!$n)
					{
						$event['title'] = egw_link::title($link_app,$link_id);
						// ask first linked app via "calendar_set" hook, for further data to set, incl. links
						if (($set = $GLOBALS['egw']->hooks->single($event+array('location'=>'calendar_set','entry_id'=>$link_id),$link_app)))
						{
							foreach((array)$set['link_app'] as $i => $l_app)
							{
								if (($l_id=$set['link_id'][$i])) egw_link::link('calendar',$event['link_to']['to_id'],$l_app,$l_id);
							}
							unset($set['link_app']);
							unset($set['link_id']);

							$event = array_merge($event,$set);
						}
					}
					egw_link::link('calendar',$link_to_id,$link_app,$link_id);
				}
			}
		}

		$etpl = new etemplate_new();
		if (!$etpl->read($preserv['template']))
		{
			$etpl->read($preserv['template'] = 'calendar.edit');
		}
		$view = $preserv['view'] = $preserv['view'] || $event['id'] && !$this->bo->check_perms(EGW_ACL_EDIT,$event);
		//echo "view=$view, event="; _debug_array($event);
		// shared locking of entries to edit
		if (!$view && ($locktime = $GLOBALS['egw_info']['server']['Lock_Time_Calender']) && $event['id'])
		{
			$lock_path = egw_vfs::app_entry_lock_path('calendar',$event['id']);
			$lock_owner = 'mailto:'.$GLOBALS['egw_info']['user']['account_email'];

			if (($preserv['lock_token'] = $event['lock_token']))		// already locked --> refresh the lock
			{
				egw_vfs::lock($lock_path,$preserv['lock_token'],$locktime,$lock_owner,$scope='shared',$type='write',true,false);
			}
			if (($lock = egw_vfs::checkLock($lock_path)) && $lock['owner'] != $lock_owner)
			{
				$msg .= ' '.lang('This entry is currently opened by %1!',
					(($lock_uid = $GLOBALS['egw']->accounts->name2id(substr($lock['owner'],7),'account_email')) ?
					common::grab_owner_name($lock_uid) : $lock['owner']));
			}
			elseif($lock)
			{
				$preserv['lock_token'] = $lock['token'];
			}
			elseif(egw_vfs::lock($lock_path,$preserv['lock_token'],$locktime,$lock_owner,$scope='shared',$type='write',false,false))
			{
				//We handle AJAX_REQUEST in client-side for unlocking the locked entry, in case of closing the entry by X button or close button
			}
			else
			{
				$msg .= ' '.lang("Can't aquire lock!");		// eg. an exclusive lock via CalDAV ...
				$view = true;
			}
		}
		$content = array_merge($event,array(
			'link_to' => array(
				'to_id'  => $link_to_id,
				'to_app' => 'calendar',
			),
			'edit_single' => $preserv['edit_single'],	// need to be in content too, as it is used in the template
			'tabs'   => $preserv['tabs'],
			'view' => $view,
			'msg' => $msg,
			'query_delete_exceptions' => (int)($event['recur_type'] && $event['recur_exception']),
		));
		$content['duration'] = $content['end'] - $content['start'];
		if (isset($this->durations[$content['duration']])) $content['end'] = '';

		$row = 3;
		$readonlys = $content['participants'] = $preserv['participants'] = array();
		// preserve some ui elements, if set eg. under error-conditions
		foreach(array('quantity','resource','role') as $n)
		{
			if (isset($event['participants'][$n])) $content['participants'][$n] = $event['participants'][$n];
		}
		foreach($event['participant_types'] as $type => $participants)
		{
			$name = 'accounts';
			if (isset($this->bo->resources[$type]))
			{
				$name = $this->bo->resources[$type]['app'];
			}
			// sort participants (in there group/app) by title
			uksort($participants, array($this, 'uid_title_cmp'));
			foreach($participants as $id => $status)
			{
				$uid = $type == 'u' ? $id : $type.$id;
				$quantity = $role = null;
				calendar_so::split_status($status,$quantity,$role);
				$preserv['participants'][$row] = $content['participants'][$row] = array(
					'app'      => $name == 'accounts' ? ($GLOBALS['egw']->accounts->get_type($id) == 'g' ? 'Group' : 'User') : $name,
					'uid'      => $uid,
					'status'   => $status,
					'old_status' => $status,
					'quantity' => $quantity > 1 || $uid[0] == 'r' ? $quantity : '',	// only display quantity for resources or if > 1
					'role'     => $role,
				);
				// replace iCal roles with a nicer label and remove regular REQ-PARTICIPANT
				if (isset($this->bo->roles[$role]))
				{
					$content['participants'][$row]['role_label'] = lang($this->bo->roles[$role]);
				}
				// allow third party apps to use categories for roles
				elseif(substr($role,0,6) == 'X-CAT-')
				{
					$content['participants'][$row]['role_label'] = $GLOBALS['egw']->categories->id2name(substr($role,6));
				}
				else
				{
					$content['participants'][$row]['role_label'] = lang(str_replace('X-','',$role));
				}
				$content['participants'][$row]['delete_id'] = strpbrk($uid,'"\'<>') !== false ? md5($uid) : $uid;
				//echo "<p>$uid ($quantity): $role --> {$content['participants'][$row]['role']}</p>\n";

				if (($no_status = !$this->bo->check_status_perms($uid,$event)) || $view)
					$readonlys['participants'][$row]['status'] = $no_status;
				if ($preserv['hide_delete'] || !$this->bo->check_perms(EGW_ACL_EDIT,$event))
					$readonlys['participants']['delete'][$uid] = true;
				// todo: make the participants available as links with email as title
				$content['participants'][$row++]['title'] = $this->get_title($uid);
				// enumerate group-invitations, so people can accept/reject them
				if ($name == 'accounts' && $GLOBALS['egw']->accounts->get_type($id) == 'g' &&
					($members = $GLOBALS['egw']->accounts->members($id,true)))
				{
					$sel_options['status']['G'] = lang('Select one');
					// sort members by title
					usort($members, array($this, 'uid_title_cmp'));
					foreach($members as $member)
					{
						if (!isset($participants[$member]) && $this->bo->check_perms(EGW_ACL_READ,0,$member))
						{
							$preserv['participants'][$row] = $content['participants'][$row] = array(
								'app'      => 'Group invitation',
								'uid'      => $member,
								'status'   => 'G',
							);
							$readonlys['participants'][$row]['quantity'] = $readonlys['participants']['delete'][$member] = true;
							// read access is enough to invite participants, but you need edit rights to change status
							$readonlys['participants'][$row]['status'] = !$this->bo->check_perms(EGW_ACL_EDIT,0,$member);
							$content['participants'][$row++]['title'] = common::grab_owner_name($member);
						}
					}
				}
			}
			// resouces / apps we shedule, atm. resources and addressbook
			$content['participants']['cal_resources'] = '';
			foreach($this->bo->resources as $data)
			{
				if ($data['app'] == 'email') continue;	// make no sense, as we cant search for email
				$content['participants']['cal_resources'] .= ','.$data['app'];
			}
			// adding extra content for the resource link-entry widget to
			// * select resources or addressbook as a default selection on the app selectbox based on prefs
			$content['participants']['resource']['app'] = $this->cal_prefs['defaultresource_sel'];
			// * get informations from the event on the ajax callback
			if (in_array($content['participants']['resource']['app'],array('resources_conflict','resources_without_conflict')))
			{
				// fix real app string
				$content['participants']['resource']['app'] = 'resources';
			}
			// check if current pref. is an allowed application for the user
			if (!isset($GLOBALS['egw_info']['user']['apps'][$content['participants']['resource']['app']]))
			{
				$content['participants']['resource']['app'] = 'home-accounts';
			}
		}
		$content['participants']['status_date'] = $preserv['actual_date'];
		$preserved = array_merge($preserv,$content);
		$event['new_alarm']['options'] = $content['new_alarm']['options'];
		if ($event['alarm'])
		{
			// makes keys of the alarm-array starting with 1
			$content['alarm'] = array(false);
			foreach(array_values($event['alarm']) as $id => $alarm)
			{
				if (!$alarm['all'] && !$this->bo->check_perms(EGW_ACL_READ,0,$alarm['owner']))
				{
					continue;	// no read rights to the calendar of the alarm-owner, dont show the alarm
				}
				$alarm['all'] = (int) $alarm['all'];
				$after = false;
				if($alarm['offset'] < 0)
				{
					$after = true;
					$alarm['offset'] = -1 * $alarm['offset'];
				}
				$days = (int) ($alarm['offset'] / DAY_s);
				$hours = (int) (($alarm['offset'] % DAY_s) / HOUR_s);
				$minutes = (int) (($alarm['offset'] % HOUR_s) / 60);
				$label = array();
				if ($days) $label[] = $days.' '.lang('days');
				if ($hours) $label[] = $hours.' '.lang('hours');
				if ($minutes) $label[] = $minutes.' '.lang('Minutes');
				$alarm['offset'] = implode(', ',$label) . ' ' . ($after ? lang('after') : lang('before'));
				$content['alarm'][] = $alarm;

				$readonlys['alarm[delete_alarm]['.$alarm['id'].']'] = !$this->bo->check_perms(EGW_ACL_EDIT,$alarm['all'] ? $event : 0,$alarm['owner']);
			}
			if (count($content['alarm']) == 1)
			{
				$content['alarm'] = false; // no alarms added to content array
			}
		}
		else
		{
			$content['alarm'] = false;
		}
		$content['msg'] = $msg;

		if ($view)
		{
			$readonlys['__ALL__'] = true;	// making everything readonly, but widgets set explicitly to false
			$readonlys['button[cancel]'] = $readonlys['action'] =
				$readonlys['before_after'] = $readonlys['button[add_alarm]'] = $readonlys['new_alarm[owner]'] =
				$readonlys['new_alarm[options]'] = $readonlys['new_alarm[date]'] = false;

			$content['participants']['no_add'] = true;

			if(!$event['whole_day'])
			{
				$etpl->setElementAttribute('whole_day', 'disabled', true);
			}

			// respect category permissions
			if(!empty($event['category']))
			{
				$content['category'] = $this->categories->check_list(EGW_ACL_READ, $event['category']);
			}
		}
		else
		{
			$readonlys['recur_exception'] = true;

			if ($event['recur_type'] != MCAL_RECUR_NONE)
			{
				$readonlys['recur_exception'] = !count($content['recur_exception']);	// otherwise we get a delete button
				//$onclick =& $etpl->get_cell_attribute('button[delete]','onclick');
				//$onclick = str_replace('Delete this event','Delete this series of recuring events',$onclick);
			}
			elseif ($event['reference'] != 0)
			{
				$readonlys['recur_type'] = $readonlys['recur_enddate'] = true;
				$readonlys['recur_interval'] = $readonlys['recur_data'] = true;
			}
		}
		// disabling the custom fields tab, if there are none
		$readonlys['tabs'] = array(
			'custom' => !count($this->bo->customfields),
			'participants' => $this->accountsel->account_selection == 'none',
			'history' => !$event['id'],
		);
		if (!isset($GLOBALS['egw_info']['user']['apps']['mail']))	// no mail without mail-app
		{
			unset($sel_options['action']['mail']);
			unset($sel_options['action']['sendmeetingrequest']);
		}
		if (!$event['id'])	// no ical export for new (not saved) events
		{
			$readonlys['action'] = true;
		}
		if (!($readonlys['button[exception]'] = !$this->bo->check_perms(EGW_ACL_EDIT,$event) || $event['recur_type'] == MCAL_RECUR_NONE || ($event['recur_enddate'] &&$event['start'] > $event['recur_enddate'])))
		{
			$content['exception_label'] = $this->bo->long_date(max($preserved['actual_date'], $event['start']));
		}
		$readonlys['button[delete]'] = !$event['id'] || $preserved['hide_delete'] || !$this->bo->check_perms(EGW_ACL_DELETE,$event);

		if (!$event['id'] || $this->bo->check_perms(EGW_ACL_EDIT,$event))	// new event or edit rights to the event ==> allow to add alarm for all users
		{
			$sel_options['owner'][0] = lang('All participants');
		}
		if (isset($event['participant_types']['u'][$this->user]))
		{
			$sel_options['owner'][$this->user] = $this->bo->participant_name($this->user);
		}
		foreach((array) $event['participant_types']['u'] as $uid => $status)
		{
			if ($uid != $this->user && $status != 'R' && $this->bo->check_perms(EGW_ACL_EDIT,0,$uid))
			{
				$sel_options['owner'][$uid] = $this->bo->participant_name($uid);
			}
		}
		$content['no_add_alarm'] = !count($sel_options['owner']);	// no rights to set any alarm
		if (!$event['id'])
		{
			$etpl->set_cell_attribute('button[new_alarm]','type','checkbox');
		}
		if ($preserved['no_popup'])
		{
			$etpl->set_cell_attribute('button[cancel]','onclick','');
		}

		// Allow admins to restore deleted events
		if($GLOBALS['egw_info']['server']['calendar_delete_history'] && $event['deleted'] )
		{
			$content['deleted'] = $preserved['deleted'] = null;
			$etpl->set_cell_attribute('button[save]', 'label', 'Recover');
			$etpl->set_cell_attribute('button[apply]', 'disabled', true);
		}
		// Allow users to prevent notifications?
		$etpl->set_cell_attribute('no_notifications', 'disabled', !$GLOBALS['egw_info']['server']['calendar_allow_no_notification']);

		// Setup history tab
		$this->setup_history($content, $sel_options);

		//echo "content="; _debug_array($content);
		//echo "preserv="; _debug_array($preserved);
 		//echo "readonlys="; _debug_array($readonlys);
 		//echo "sel_options="; _debug_array($sel_options);
		$GLOBALS['egw_info']['flags']['app_header'] = lang('calendar') . ' - '
			. (!$event['id'] ? lang('Add')
				: ($view ? ($content['edit_single'] ? lang('View exception') : ($content['recur_type'] ? lang('View series') : lang('View')))
					: ($content['edit_single'] ? lang('Create exception') : ($content['recur_type'] ? lang('Edit series') : lang('Edit')))));

		$content['cancel_needs_refresh'] = (bool)$_GET['cancel_needs_refresh'];

		if (!empty($preserved['lock_token'])) $content['lock_token'] = $preserved['lock_token'];

		// non_interactive==true from $_GET calls immediate save action without displaying the edit form
		if(isset($_GET['non_interactive']) && (bool)$_GET['non_interactive'] === true)
		{
			unset($_GET['non_interactive']);	// prevent process_exec <--> edit loops
			$content['button']['save'] = true;
			$this->process_edit(array_merge($content,$preserved));
		}
		else
		{
			$etpl->exec('calendar.calendar_uiforms.process_edit',$content,$sel_options,$readonlys,$preserved,$preserved['no_popup'] ? 0 : 2);
		}
	}

	/**
	 * Remove (shared) lock via ajax, when edit popup get's closed
	 *
	 * @param int $id
	 * @param string $token
	 */
	function ajax_unlock($id,$token)
	{
		$lock_path = egw_vfs::app_entry_lock_path('calendar',$id);
		$lock_owner = 'mailto:'.$GLOBALS['egw_info']['user']['account_email'];

		if (($lock = egw_vfs::checkLock($lock_path)) && $lock['owner'] == $lock_owner || $lock['token'] == $token)
		{
			egw_vfs::unlock($lock_path,$token,false);
		}
	}

	/**
	 * Display for FMail an iCal meeting request and allow to accept, tentative or reject it or a reply and allow to apply it
	 *
	 * @todo Handle situation when user is NOT invited, but eg. can view that mail ...
	 * @param array $event = null; special usage if $event is array('event'=>null,'msg'=>'','useSession'=>true) we
	 * 		are called by new mail-app; and we intend to use the stuff passed on by session
	 * @param string $msg = null
	 */
	function meeting(array $event=null, $msg=null)
	{
		$user = $GLOBALS['egw_info']['user']['account_id'];
		$readonlys['button[apply]'] = true;
		$_usesession=!is_array($event);
		//special usage if $event is array('event'=>null,'msg'=>'','useSession'=>true) we
		//are called by new mail-app; and we intend to use the stuff passed on by session
		if ($event == array('event'=>null,'msg'=>'','useSession'=>true))
		{
			$event=null; // set to null
			$_usesession=true; // trigger session read
		}
		if (!is_array($event))
		{
			$ical_charset = 'utf-8';
			$ical_string = $_GET['ical'];
			if ($ical_string == 'session' || $_usesession)
			{
				$session_data = egw_cache::getSession('calendar', 'ical');
				$ical_string = $session_data['attachment'];
				$ical_charset = $session_data['charset'];
				$ical_method = $session_data['method'];
				$ical_sender = $session_data['sender'];
				unset($session_data);
			}
			$ical = new calendar_ical();
			if (!($events = $ical->icaltoegw($ical_string, '', $ical_charset)) || count($events) != 1)
			{
				error_log(__METHOD__."('$_GET[ical]') error parsing iCal!");
				$GLOBALS['egw']->framework->render(html::fieldset('<pre>'.htmlspecialchars($ical_string).'</pre>',
					lang('Error: importing the iCal')));
				return;
			}
			$event = array_shift($events);

			// convert event from servertime returned by calendar_ical to user-time
			$this->bo->server2usertime($event);

			if (($existing_event = $this->bo->read($event['uid'])) && !$existing_event['deleted'])
			{
				switch(strtolower($ical_method))
				{
					case 'reply':
						if ($ical_sender && ($event['ical_sender_uid'] = groupdav_principals::url2uid('mailto:'.$ical_sender)) &&
							isset($existing_event['participants'][$event['ical_sender_uid']]) &&
							$this->bo->check_status_perms($event['ical_sender_uid'], $existing_event))
						{
							$event['ical_sender_status'] = $event['participants'][$event['ical_sender_uid']];
							$quantity = $role = null;
							calendar_so::split_status($event['ical_sender_status'], $quantity, $role);
							$existing_status = $existing_event['participants'][$event['ical_sender_uid']];
							calendar_so::split_status($existing_status, $quantity, $role);
							if ($existing_status != $event['ical_sender_status'])
							{
								$readonlys['button[apply]'] = false;
							}
							else
							{
								$msg = lang('Status already applied');
							}
						}
						break;

					case 'request':
						$status = $existing_event['participants'][$user];
						calendar_so::split_status($status, $quantity, $role);
						if (strtolower($ical_method) == 'response' && isset($existing_event['participants'][$user]) &&
							$status != 'U' && isset($this->bo->verbose_status[$status]))
						{
							$msg = lang('You already replied to this invitation with').': '.lang($this->bo->verbose_status[$status]);
						}
						else
						{
							$msg = lang('Using already existing event on server.');
						}
						break;
				}
				$event['id'] = $existing_event['id'];
			}
			else	// event not in calendar
			{
				$readonlys['button[cancel]'] = true;	// no way to remove a canceled event not in calendar
			}
			$event['participant_types'] = array();
			foreach($event['participants'] as $uid => $status)
			{
				$user_type = $user_id = null;
				calendar_so::split_user($uid, $user_type, $user_id);
				$event['participants'][$uid] = $event['participant_types'][$user_type][$user_id] =
					$status && $status !== 'X' ? $status : 'U';	// X --> no status given --> U = unknown
			}
			//error_log(__METHOD__."(...) parsed as ".array2string($event));
			$event['recure'] = $this->bo->recure2string($event);
			$event['all_participants'] = implode(",\n",$this->bo->participants($event, true));

			$user_and_memberships = $GLOBALS['egw']->accounts->memberships($user, true);
			$user_and_memberships[] = $user;
			if (!array_intersect(array_keys($event['participants']), $user_and_memberships))
			{
				$msg .= ($msg ? "\n" : '').lang('You are not invited to that event!');
				if ($event['id'])
				{
					$readonlys['button[accept]'] = $readonlys['button[tentativ]'] =
						$readonlys['button[reject]'] = $readonlys['button[cancel]'] = true;
				}
			}
			// ignore events in the past (for recurring events check enddate!)
			if ($this->bo->date2ts($event['start']) < $this->bo->now_su &&
				(!$event['recur_type'] || $event['recur_enddate'] && $event['recur_enddate'] < $this->bo->now_su))
			{
				$msg = lang('Requested meeting is in the past!');
				$readonlys['button[accept]'] = $readonlys['button[tentativ]'] =
					$readonlys['button[reject]'] = $readonlys['button[cancel]'] = true;
			}
		}
		else
		{
			//_debug_array($event);
			list($button) = each($event['button']);
			unset($event['button']);

			// clear notification errors
			notifications::errors(true);

			switch($button)
			{
				case 'reject':
					if (!$event['id'])
					{
						// send reply to organizer
						$this->bo->send_update(MSG_REJECTED,array('e'.$event['organizer'] => 'DCHAIR'),$event);
						break;	// no need to store rejected event
					}
					// fall-through
				case 'accept':
				case 'tentativ':
					$status = strtoupper($button[0]);	// A, R or T
					if (!$event['id'])
					{
						// if organizer is a EGroupware user, but we have no rights to organizers calendar
						if (isset($event['owner']) && !$this->bo->check_perms(EGW_ACL_ADD,0,$event['owner']))
						{
							// --> make organize a participant with role chair and current user the owner
							$event['participant_types']['u'] = $event['participants'][$event['owner']] =
								calendar_so::combine_status('A', 1, 'CHAIR');
							$event['owner'] = $this->user;
						}
						// store event without notifications!
						if (($event['id'] = $this->bo->update($event, $ignore_conflicts=true, true, false, true, $msg, true)))
						{
							$msg[] = lang('Event saved');
						}
						else
						{
							$msg[] = lang('Error saving the event!');
							break;
						}
					}
					// set status and send notification / meeting response
					if ($this->bo->set_status($event['id'], $user, $status))
					{
						if (!$msg) $msg = lang('Status changed');
					}
					break;

				case 'apply':
					// set status and send notification / meeting response
					if ($this->bo->set_status($event['id'], $event['ical_sender_uid'], $event['ical_sender_status']))
					{
						$msg = lang('Status changed');
					}
					break;

				case 'cancel':
					if ($event['id'] && $this->bo->set_status($event['id'], $user, 'R'))
					{
						$msg = lang('Status changed');
					}
					break;
			}
			// add notification-errors, if we have some
			$msg = array_merge((array)$msg, notifications::errors(true));
		}
		$event['msg'] = implode("\n",(array)$msg);
		$readonlys['button[edit]'] = !$event['id'];
		$event['ics_method'] = $readonlys['ics_method'] = strtolower($ical_method);
		switch(strtolower($ical_method))
		{
			case 'reply':
				$event['ics_method_label'] = lang('Reply to meeting request');
				break;
			case 'cancel':
				$event['ics_method_label'] = lang('Meeting canceled');
				break;
			case 'request':
			default:
				$event['ics_method_label'] = lang('Meeting request');
				break;
		}
		$tpl = new etemplate_new('calendar.meeting');
		$tpl->exec('calendar.calendar_uiforms.meeting', $event, array(), $readonlys, $event, 2);
	}

	/**
	 * displays a scheduling conflict
	 *
	 * @param array $event
	 * @param array $conflicts array with conflicting events, the events are not garantied to be readable by the user!
	 * @param array $preserv data to preserv
	 */
	function conflicts($event,$conflicts,$preserv)
	{
		$etpl = CreateObject('etemplate.etemplate_new','calendar.conflicts');
		$allConflicts = array();

		foreach($conflicts as $k => $conflict)
		{
			$is_readable = $this->bo->check_perms(EGW_ACL_READ,$conflict);

			$conflicts[$k] += array(
				'icon_participants' => $is_readable ? (count($conflict['participants']) > 1 ? 'users' : 'single') : 'private',
				'tooltip_participants' => $is_readable ? implode(', ',$this->bo->participants($conflict)) : '',
				'time' => $this->bo->long_date($conflict['start'],$conflict['end'],true),
				'conflicting_participants' => implode(",\n",$this->bo->participants(array(
					'participants' => array_intersect_key((array)$conflict['participants'],$event['participants']),
				),true,true)),	// show group invitations too
				'icon_recur' => $conflict['recur_type'] != MCAL_RECUR_NONE ? 'recur' : '',
				'text_recur' => $conflict['recur_type'] != MCAL_RECUR_NONE ? lang('Recurring event') : ' ',
			);
				$allConflicts += array_intersect_key((array)$conflict['participants'],$event['participants']);
			}
		$content = $event + array(
			'conflicts' => array_values($conflicts),	// conflicts have id-start as key
		);
		$GLOBALS['egw_info']['flags']['app_header'] = lang('calendar') . ' - ' . lang('Scheduling conflict');
		$resources_config = config::read('resources');
		$readonlys = array();

		foreach (array_keys($allConflicts) as $pId)
		{
			if(substr($pId,0,1) == 'r' && $resources_config ) // resources Allow ignore conflicts
			{

				switch ($resources_config['ignoreconflicts'])
				{
					case 'no':
						$readonlys['button[ignore]'] = true;
						break;
					case 'allusers':
						$readonlys['button[ignore]'] = false;
						break;
					default:
						if (!$this->bo->check_status_perms($pId, $event))
						{
							$readonlys['button[ignore]'] = true;
							break;
						}
				}
			}
		}
		$etpl->exec('calendar.calendar_uiforms.process_edit',$content,array(),$readonlys,array_merge($event,$preserv),$preserv['no_popup'] ? 0 : 2);
	}

	/**
	 * Callback for freetimesearch button in edit
	 *
	 * It stores the data of the submitted form in the session under 'freetimesearch_args_'.$edit_content['id'],
	 * for later retrival of the freetimesearch method, called by the returned window.open() command.
	 *
	 * @param array $edit_content
	 * @return string with xajaxResponse
	 */
	function ajax_freetimesearch(array $edit_content)
	{
		$response = egw_json_response::get();
		//$response->addAlert(__METHOD__.'('.array2string($edit_content).')');

		// convert start/end date-time values to timestamps
		foreach(array('start', 'end') as $name)
		{
			if (!empty($edit_content[$name]))
			{
				$date = new egw_time($edit_content[$name]);
				$edit_content[$name] = $date->format('ts');
			}
		}

		if ($edit_content['duration'])
		{
			$edit_content['end'] = $edit_content['start'] + $edit_content['duration'];
		}
		if ($edit_content['whole_day'])
		{
			$arr = $this->bo->date2array($edit_content['start']);
			$arr['hour'] = $arr['minute'] = $arr['second'] = 0; unset($arr['raw']);
			$edit_content['start'] = $this->bo->date2ts($arr);
			$earr = $this->bo->date2array($edit_content['end']);
			$earr['hour'] = 23; $earr['minute'] = $earr['second'] = 59; unset($earr['raw']);
			$edit_content['end'] = $this->bo->date2ts($earr);
		}
		$content = array(
			'start'    => $edit_content['start'],
			'duration' => $edit_content['end'] - $edit_content['start'],
			'end'      => $edit_content['end'],
			'cal_id'   => $edit_content['id'],
			'recur_type'   => $edit_content['recur_type'],
			'participants' => array(),
		);
		foreach($edit_content['participants'] as $key => $data)
		{
			if (is_numeric($key) && !$edit_content['participants']['delete'][$data['uid']] &&
				!$edit_content['participants']['delete'][md5($data['uid'])])
			{
				$content['participants'][] = $data['uid'];
			}
			elseif ($key == 'account' && !is_array($data) && $data)
			{
				$content['participants'][] = $data;
			}
		}
		// default search parameters
		$content['start_time'] = $edit_content['whole_day'] ? 0 : $this->cal_prefs['workdaystarts'];
		$content['end_time'] = $this->cal_prefs['workdayends'];
		if ($this->cal_prefs['workdayends']*HOUR_s < $this->cal_prefs['workdaystarts']*HOUR_s+$content['duration'])
		{
			$content['end_time'] = 0;	// no end-time limit, as duration would never fit
		}
		$content['weekdays'] = MCAL_M_WEEKDAYS;

		$content['search_window'] = 7 * DAY_s;

		// store content in session
		egw_cache::setSession('calendar','freetimesearch_args_'.(int)$edit_content['id'],$content);

		//menuaction=calendar.calendar_uiforms.freetimesearch&values2url('start,end,duration,participants,recur_type,whole_day'),ft_search,700,500
		$link = 'calendar.calendar_uiforms.freetimesearch&cal_id='. $edit_content['id'];

		$response->call('app.calendar.freetime_search_popup',$link);

		//$response->addScriptCall('egw_openWindowCentered2',$link,'ft_search',700,500);

	}

	/**
	 * Freetime search
	 *
	 * As the function is called in a popup via javascript, parametes get initialy transfered via the url
	 * @param array $content=null array with parameters or false (default) to use the get-params
	 * @param string start[str] start-date
	 * @param string start[hour] start-hour
	 * @param string start[min] start-minutes
	 * @param string end[str] end-date
	 * @param string end[hour] end-hour
	 * @param string end[min] end-minutes
	 * @param string participants ':' delimited string of user-id's
	 */
	function freetimesearch($content = null)
	{
		$etpl = new etemplate_new('calendar.freetimesearch');
		$sel_options['search_window'] = array(
			7*DAY_s		=> lang('one week'),
			14*DAY_s	=> lang('two weeks'),
			31*DAY_s	=> lang('one month'),
			92*DAY_s	=> lang('three month'),
			365*DAY_s	=> lang('one year'),
		);
		if (!is_array($content))
		{
			// get content from session (and delete it immediatly)
			$content = egw_cache::getSession('calendar','freetimesearch_args_'.(int)$_GET['cal_id']);
			egw_cache::unsetSession('calendar','freetimesearch_args_'.(int)$_GET['cal_id']);
			//Since the start_time and end_time from calendar_user_preferences are numbers, not timestamp, in order to show them on date-timeonly
			//widget we need to convert them from numbers to timestamps, only for the first time when we have template without content
			$sTime = $content['start_time'];
			$eTime = $content['end_time'];
			$content['start_time'] = strtotime(((strlen($content['start_time'])<2)?("0".$content['start_time']):$content['start_time']).":00");
			$content['end_time'] = strtotime(((strlen($content['end_time'])<2)?("0".$content['end_time']):$content['end_time']).":00");

			// pick a searchwindow fitting the duration (search for a 10 day slot in a one week window never succeeds)
			foreach(array_keys($sel_options['search_window']) as $window)
			{
				if ($window > $content['duration'])
				{
					$content['search_window'] = $window;
					break;
				}
			}
		}
		else
		{
			if (!$content['duration']) $content['duration'] = $content['end'] - $content['start'];
			$weekds = 0;
			foreach ($content['weekdays'] as &$wdays)
			{
				$weekds = $weekds + $wdays;
			}
			//split_freetime_daywise function expects to get start_time and end_time values as string numbers, only "hour", therefore, since the date-timeonly widget returns
			//always timestamp, we need to convert them to only "hour" string numbers.
			$sTime = date('H', $content['start_time']);
			$eTime = date('H', $content['end_time']);
		}

		if ($content['recur_type'])
		{
			$content['msg'] .= lang('Only the initial date of that recuring event is checked!');
		}
		$content['freetime'] = $this->freetime($content['participants'],$content['start'],$content['start']+$content['search_window'],$content['duration'],$content['cal_id']);
		$content['freetime'] = $this->split_freetime_daywise($content['freetime'],$content['duration'],(is_array($content['weekdays'])?$weekds:$content['weekdays']),$sTime,$eTime,$sel_options);

		$GLOBALS['egw_info']['flags']['app_header'] = lang('calendar') . ' - ' . lang('freetime search');

		$sel_options['duration'] = $this->durations;
		if ($content['duration'] && isset($sel_options['duration'][$content['duration']])) $content['end'] = '';

		$etpl->exec('calendar.calendar_uiforms.freetimesearch',$content,$sel_options,NULL,array(
				'participants'	=> $content['participants'],
				'cal_id'		=> $content['cal_id'],
				'recur_type'	=> $content['recur_type'],
			),2);
	}

	/**
	 * calculate the freetime of given $participants in a certain time-span
	 *
	 * @param array $participants user-id's
	 * @param int $start start-time timestamp in user-time
	 * @param int $end end-time timestamp in user-time
	 * @param int $duration min. duration in sec, default 1
	 * @param int $cal_id own id for existing events, to exclude them from being busy-time, default 0
	 * @return array of free time-slots: array with start and end values
	 */
	function freetime($participants,$start,$end,$duration=1,$cal_id=0)
	{
		if ($this->debug > 2) $this->bo->debug_message(__METHOD__.'(participants=%1, start=%2, end=%3, duration=%4, cal_id=%5)',true,$participants,$start,$end,$duration,$cal_id);

		$busy = $this->bo->search(array(
			'start' => $start,
			'end'	=> $end,
			'users'	=> $participants,
			'ignore_acl' => true,	// otherwise we get only events readable by the user
		));
		$busy[] = array(	// add end-of-search-date as event, to cope with empty search and get freetime til that date
			'start'	=> $end,
			'end'	=> $end,
		);
		$ft_start = $start;
		$freetime = array();
		$n = 0;
		foreach($busy as $event)
		{
			if ((int)$cal_id && $event['id'] == (int)$cal_id) continue;	// ignore our own event

 			if ($event['non_blocking']) continue; // ignore non_blocking events

			// check if from all wanted participants at least one has a not rejected status in found event
			$non_rejected_found = false;
			foreach($participants as $uid)
			{
				if ($event['participants'][$uid] == 'R') continue;

				if (isset($event['participants'][$uid]) ||
					$uid > 0 && array_intersect(array_keys((array)$event['participants']),
						$GLOBALS['egw']->accounts->memberships($uid, true)))
				{
					$non_rejected_found = true;
					break;
				}
			}
			if (!$non_rejected_found) continue;

			if ($this->debug)
			{
				echo "<p>ft_start=".date('D d.m.Y H:i',$ft_start)."<br>\n";
				echo "event[title]=$event[title]<br>\n";
				echo "event[start]=".date('D d.m.Y H:i',$event['start'])."<br>\n";
				echo "event[end]=".date('D d.m.Y H:i',$event['end'])."<br>\n";
			}
			// $events ends before our actual position ==> ignore it
			if ($event['end'] < $ft_start)
			{
				//echo "==> event ends before ft_start ==> continue<br>\n";
				continue;
			}
			// $events starts before our actual position ==> set start to it's end and go to next event
			if ($event['start'] < $ft_start)
			{
				//echo "==> event starts before ft_start ==> set ft_start to it's end & continue<br>\n";
				$ft_start = $event['end'];
				continue;
			}
			$ft_end = $event['start'];

			// only show slots equal or bigger to min_length
			if ($ft_end - $ft_start >= $duration)
			{
				$freetime[++$n] = array(
					'start'	=> $ft_start,
					'end'	=> $ft_end,
				);
				if ($this->debug > 1) echo "<p>freetime: ".date('D d.m.Y H:i',$ft_start)." - ".date('D d.m.Y H:i',$ft_end)."</p>\n";
			}
			$ft_start = $event['end'];
		}
		if ($this->debug > 0) $this->bo->debug_message('uiforms::freetime(participants=%1, start=%2, end=%3, duration=%4, cal_id=%5) freetime=%6',true,$participants,$start,$end,$duration,$cal_id,$freetime);

		return $freetime;
	}

	/**
	 * split the freetime in daywise slot, taking into account weekdays, start- and stop-times
	 *
	 * If the duration is bigger then the difference of start- and end_time, the end_time is ignored
	 *
	 * @param array $freetime free time-slots: array with start and end values
	 * @param int $duration min. duration in sec
	 * @param int $weekdays allowed weekdays, bitfield of MCAL_M_...
	 * @param int $_start_time minimum start-hour 0-23
	 * @param int $_end_time maximum end-hour 0-23, or 0 for none
	 * @param array $sel_options on return options for start-time selectbox
	 * @return array of free time-slots: array with start and end values
	 */
	function split_freetime_daywise($freetime, $duration, $weekdays, $_start_time, $_end_time, &$sel_options)
	{
		if ($this->debug > 1) $this->bo->debug_message('uiforms::split_freetime_daywise(freetime=%1, duration=%2, start_time=%3, end_time=%4)',true,$freetime,$duration,$_start_time,$_end_time);

		$freetime_daywise = array();
		if (!is_array($sel_options)) $sel_options = array();
		$time_format = $this->common_prefs['timeformat'] == 12 ? 'h:i a' : 'H:i';

		$start_time = (int) $_start_time;	// ignore leading zeros
		$end_time   = (int) $_end_time;

		// ignore the end_time, if duration would never fit
		if (($end_time - $start_time)*HOUR_s < $duration)
		{
			$end_time = 0;
			if ($this->debug > 1) $this->bo->debug_message('uiforms::split_freetime_daywise(, duration=%2, start_time=%3,..) end_time set to 0, it never fits durationn otherwise',true,$duration,$start_time);
		}
		$n = 0;
		foreach($freetime as $ft)
		{
			$adaybegin = $this->bo->date2array($ft['start']);
			$adaybegin['hour'] = $adaybegin['minute'] = $adaybegin['second'] = 0;
			unset($adaybegin['raw']);
			$daybegin = $this->bo->date2ts($adaybegin);

			for($t = $daybegin; $t < $ft['end']; $t += DAY_s,$daybegin += DAY_s)
			{
				$dow = date('w',$daybegin+DAY_s/2);	// 0=Sun, .., 6=Sat
				$mcal_dow = pow(2,$dow);
				if (!($weekdays & $mcal_dow))
				{
					//echo "wrong day of week $dow<br>\n";
					continue;	// wrong day of week
				}
				$start = $t < $ft['start'] ? $ft['start'] : $t;

				if ($start-$daybegin < $start_time*HOUR_s)	// start earlier then start_time
				{
					$start = $daybegin + $start_time*HOUR_s;
				}
				// if end_time given use it, else the original slot's end
				$end = $end_time ? $daybegin + $end_time*HOUR_s : $ft['end'];
				if ($end > $ft['end']) $end = $ft['end'];

				// slot to small for duration
				if ($end - $start < $duration)
				{
					//echo "slot to small for duration=$duration<br>\n";
					continue;
				}
				$freetime_daywise[++$n] = array(
					'start'	=> $start,
					'end'	=> $end,
				);
				$times = array();
				for ($s = $start; $s+$duration <= $end && $s < $daybegin+DAY_s; $s += 60*$this->cal_prefs['interval'])
				{
					$e = $s + $duration;
					$end_date = $e-$daybegin > DAY_s ? lang(date('l',$e)).' '.date($this->common_prefs['dateformat'],$e).' ' : '';
					$times[$s] = date($time_format,$s).' - '.$end_date.date($time_format,$e);
				}
				$sel_options[$n.'start'] = $times;
			}
		}
		return $freetime_daywise;
	}

	/**
	 * Export events as vCalendar version 2.0 files (iCal)
	 *
	 * @param int|array $content numeric cal_id or submitted content from etempalte::exec
	 * @param boolean $return_error should an error-msg be returned or a regular page with it generated (default)
	 * @return string error-msg if $return_error
	 */
	function export($content=0,$return_error=false)
	{
        $boical = new calendar_ical();
		#error_log(__METHOD__.print_r($content,true));
		if (is_numeric($cal_id = $content ? $content : $_REQUEST['cal_id']))
		{
			if (!($ical =& $boical->exportVCal(array($cal_id),'2.0','PUBLISH',false)))
			{
				$msg = lang('Permission denied');

				if ($return_error) return $msg;
			}
			else
			{
				html::content_header('event.ics','text/calendar',bytes($ical));
				echo $ical;
				common::egw_exit();
			}
		}
		if (is_array($content))
		{
			$events =& $this->bo->search(array(
				'start' => $content['start'],
				'end'   => $content['end'],
				'enum_recuring' => false,
				'daywise'       => false,
				'owner'         => $this->owner,
				'date_format'   => 'server',	// timestamp in server time for boical class
			));
			if (!$events)
			{
				$msg = lang('No events found');
			}
			else
			{
				$ical =& $boical->exportVCal($events,'2.0','PUBLISH',false);
				html::content_header($content['file'] ? $content['file'] : 'event.ics','text/calendar',bytes($ical));
				echo $ical;
				common::egw_exit();
			}
		}
		if (!is_array($content))
		{
			$content = array(
				'start' => $this->bo->date2ts($_REQUEST['start'] ? $_REQUEST['start'] : $this->date),
				'end'   => $this->bo->date2ts($_REQUEST['end'] ? $_REQUEST['end'] : $this->date),
				'file'  => 'event.ics',
				'version' => '2.0',
			);
		}
		$content['msg'] = $msg;

		$GLOBALS['egw_info']['flags']['app_header'] = lang('calendar') . ' - ' . lang('iCal Export');
		$etpl = new etemplate_new('calendar.export');
		$etpl->exec('calendar.calendar_uiforms.export',$content);
	}

	/**
	 * Import events as vCalendar version 2.0 files (iCal)
	 *
	 * @param array $content submitted content from etempalte::exec
	 */
	function import($content=null)
	{
		if (is_array($content))
		{
			if (is_array($content['ical_file']) && is_uploaded_file($content['ical_file']['tmp_name']))
			{
				@set_time_limit(0);	// try switching execution time limit off
				$start = microtime(true);

				$calendar_ical = new calendar_ical;
				$calendar_ical->setSupportedFields('file', '');
				if (!$calendar_ical->importVCal($f=fopen($content['ical_file']['tmp_name'],'r')))
				{
					$msg = lang('Error: importing the iCal');
				}
				else
				{
					$msg = lang('iCal successful imported').' '.lang('(%1 events in %2 seconds)',
						$calendar_ical->events_imported,number_format(microtime(true)-$start,1));
				}
				if ($f) fclose($f);
			}
			else
			{
				$msg = lang('You need to select an iCal file first');
			}
		}
		$GLOBALS['egw_info']['flags']['app_header'] = lang('calendar') . ' - ' . lang('iCal Import');
		$etpl = new etemplate_new('calendar.import');

		$etpl->exec('calendar.calendar_uiforms.import', array(
			'msg' => $msg,
		));
	}

	/**
	 * Edit category ACL (admin only)
	 *
	 * @param array $_content
	 */
	function cat_acl(array $_content=null)
	{
		if (!$GLOBALS['egw_info']['user']['apps']['admin'])
		{
			throw new egw_exception_no_permission_admin();
		}
		if ($_content)
		{
			list($button) = each($_content['button']);
			unset($_content['button']);
			if ($button != 'cancel')	// store changed acl
			{
				foreach($_content as $data)
				{
					if (!($cat_id = $data['cat_id'])) continue;
					foreach(array_merge((array)$data['add'],(array)$data['status'],array_keys((array)$data['old'])) as $account_id)
					{
						$rights = 0;
						if (in_array($account_id,(array)$data['add'])) $rights |= calendar_boupdate::CAT_ACL_ADD;
						if (in_array($account_id,(array)$data['status'])) $rights |= calendar_boupdate::CAT_ACL_STATUS;
						if ($account_id) $this->bo->set_cat_rights($cat_id,$account_id,$rights);
					}
				}
			}
			if ($button != 'apply')	// end dialog
			{
				egw::redirect_link('/index.php', array(
					'menuaction' => 'admin.admin_ui.index',
					'ajax' => 'true'
				), 'admin');
			}
		}
		$content= $preserv = array();
		$n = 1;
		foreach($this->bo->get_cat_rights() as $Lcat_id => $data)
		{
			$cat_id = substr($Lcat_id,1);
			$row = array(
				'cat_id' => $cat_id,
				'add' => array(),
				'status' => array(),
			);
			foreach($data as $account_id => $rights)
			{
				if ($rights & calendar_boupdate::CAT_ACL_ADD) $row['add'][] = $account_id;
				if ($rights & calendar_boupdate::CAT_ACL_STATUS) $row['status'][] = $account_id;
			}
			$content[$n] = $row;
			$preserv[$n] = array(
				'cat_id' => $cat_id,
				'old' => $data,
			);
			$readonlys[$n.'[cat_id]'] = true;
			++$n;
		}
		// add empty row for new entries
		$content[] = array('cat_id' => '');

		$GLOBALS['egw_info']['flags']['app_header'] = lang('Calendar').' - '.lang('Category ACL');
		$tmp = new etemplate_new('calendar.cat_acl');
		$tmp->exec('calendar.calendar_uiforms.cat_acl',$content,null,$readonlys,$preserv);
	}

	/**
	* Set up the required fields to get the history tab
	*/
	public function setup_history(&$content, &$sel_options)
	{
		$status = 'history_status';

		$content['history'] = array(
			'id'    =>      $content['id'],
			'app'   =>      'calendar',
			'status-widgets' => array(
				'owner'        => 'select-account',
				'creator'      => 'select-account',
				'category'     => 'select-cat',
				'non_blocking' => array(''=>lang('No'), 1=>lang('Yes')),
				'public'       => array(''=>lang('No'), 1=>lang('Yes')),

				'start'		   => 'date-time',
				'end'		   => 'date-time',
				'deleted'      => 'date-time',

				'tz_id'        => 'select-timezone',

				// Participants
				'participants'	=>	array(
					'select-account',
					$sel_options['status'],
					$sel_options['role']
				),
				'participants-c'	=>	array(
					'link:addressbook',
					$sel_options['status'],
					'label',
					$sel_options['role']
				),
				'participants-r'	=>	array(
					'link:resources',
					$sel_options['status'],
					'label',
					$sel_options['role']
				),
			),
		);


		// Get participants for only this one, if it's recurring.  The date is on the end of the value.
		if($content['recur_type'] || $content['recurrence'])
		{
			$content['history']['filter'] = array(
				'(history_status NOT LIKE \'participants%\' OR (history_status LIKE \'participants%\' AND (
					history_new_value LIKE \'%' . bo_tracking::ONE2N_SEPERATOR . $content['recurrence'] . '\' OR
					history_old_value LIKE \'%' . bo_tracking::ONE2N_SEPERATOR . $content['recurrence'] . '\')))'
			);
		}

		// Translate labels
		$tracking = new calendar_tracking();
		foreach($tracking->field2label as $field => $label)
		{
			$sel_options[$status][$field] = lang($label);
		}
		// custom fields are now "understood" directly by historylog widget
	}

	/**
	 * moves an event to another date/time
	 *
	 * @param string $_eventId id of the event which has to be moved
	 * @param string $calendarOwner the owner of the calendar the event is in
	 * @param string $targetDateTime the datetime where the event should be moved to, format: YYYYMMDD
	 * @param string $targetOwner the owner of the target calendar
	 * @param string $durationT the duration to support resizable calendar event
	 * @return string XML response if no error occurs
	 */
	function ajax_moveEvent($_eventId,$calendarOwner,$targetDateTime,$targetOwner,$durationT=null)
	{
		// we do not allow dragging into another users calendar ATM
		if($targetOwner < 0)
		{
			$targetOwner = array($targetOwner);
		}
		if($calendarOwner !== $targetOwner && !is_array($targetOwner))
		{
			return false;
		}
		// But you may be viewing multiple users, or a group calendar and
		// dragging your event
		if(is_array($targetOwner) && !in_array($calendarOwner, $targetOwner))
		{
			$return = true;
			foreach($targetOwner as $owner)
			{
				if($owner < 0 && in_array($calendarOwner, $GLOBALS['egw']->accounts->members($owner,true)))
				{
					$return = false;
					break;
				}
			}
			if($return) return;
		}
		list($eventId, $date) = explode(':', $_eventId);
		$old_event=$event=$this->bo->read($eventId);
		if (!$durationT)
		{
			$duration=$event['end']-$event['start'];
		}
		else
		{
			$duration = $durationT;
		}

		// If we have a recuring event for a particular day, make an exception
		if ($event['recur_type'] != MCAL_RECUR_NONE && $date)
		{
			$d = new egw_time($date, egw_time::$user_timezone);
			if (!empty($event['whole_day']))
			{
				$d =& $this->bo->so->startOfDay($d);
				$d->setUser();
			}
			$event = $this->bo->read($eventId, $d, true);
			$preserv['actual_date'] = $d;		// remember the date clicked

			// For DnD, always create an exception
			$this->_create_exception($event,$preserv);
			unset($event['id']);
			$date = $d->format('ts');
		}

		$event['start'] = $this->bo->date2ts($targetDateTime);
		$event['end'] = $event['start']+$duration;
		$status_reset_to_unknown = false;
		$sameday = (date('Ymd', $old_event['start']) == date('Ymd', $event['start']));
		foreach((array)$event['participants'] as $uid => $status)
		{
			$q = $r = null;
			calendar_so::split_status($status,$q,$r);
			if ($uid[0] != 'c' && $uid[0] != 'e' && $uid != $this->bo->user && $status != 'U')
			{
				$preferences = CreateObject('phpgwapi.preferences',$uid);
				$part_prefs = $preferences->read_repository();
				switch ($part_prefs['calendar']['reset_stati'])
				{
					case 'no':
						break;
					case 'startday':
						if ($sameday) break;
					default:
						$status_reset_to_unknown = true;
						$event['participants'][$uid] = calendar_so::combine_status('U',$q,$r);
						// todo: report reset status to user
				}
			}
		}

		$message = false;
		$conflicts=$this->bo->update($event,false, true, false, true, $message);

		$response = egw_json_response::get();
		if(!is_array($conflicts) && $conflicts)
		{
			// Directly update stored data.  If event is still visible, it will
			// be notified & update itself.
			$this->to_client($event);
			$response->call('egw.dataStoreUID','calendar::'.$event['id'].($date?':'.$date:''),$event);

			if(!$sameday )
			{
				$response->call('egw.refresh', '','calendar',$event['id'],'update');
			}
		}
		else if ($conflicts)
		{
			$response->call(
				'egw_openWindowCentered2',
				$GLOBALS['egw_info']['server']['webserver_url'].'/index.php?menuaction=calendar.calendar_uiforms.edit
					&cal_id='.$event['id']
					.'&start='.$event['start']
					.'&end='.$event['end']
					.'&non_interactive=true'
					.'&cancel_needs_refresh=true',
				'',750,410);
		}
		else if ($message)
		{
			$response->call('egw.message',  implode('<br />', $message));
		}
		if ($status_reset_to_unknown)
		{
			foreach((array)$event['participants'] as $uid => $status)
			{
				if ($uid[0] != 'c' && $uid[0] != 'e' && $uid != $this->bo->user)
				{
					calendar_so::split_status($status,$q,$r);
					$status = calendar_so::combine_status('U',$q,$r);
					$this->bo->set_status($event['id'], $uid, $status, 0, true);
				}
			}
		}
	}

	/**
	 * Change the status via ajax
	 * @param string $_eventId
	 * @param integer $uid
	 * @param string $status
	 */
	function ajax_status($_eventId, $uid, $status)
	{
		list($eventId, $date) = explode(':', $_eventId);
		$event = $this->bo->read($eventId);

		// If we have a recuring event for a particular day, make an exception
		if ($event['recur_type'] != MCAL_RECUR_NONE && $date)
		{
			$d = new egw_time($date, egw_time::$user_timezone);
			if (!empty($event['whole_day']))
			{
				$d =& $this->bo->so->startOfDay($date);
				$d->setUser();
			}
			$event = $this->bo->read($eventId, $d, true);
			$preserv['actual_date'] = $d;		// remember the date clicked

			// For DnD, always create an exception
			$this->_create_exception($event,$preserv);
			unset($event['id']);
			$date = $d->format('ts');
		}
		if($event['participants'][$uid])
		{
			$q = $r = null;
			calendar_so::split_status($event['participants'][$uid],$q,$r);
			$event['participants'][$uid] = $status = calendar_so::combine_status($status,$q,$r);
			$this->bo->set_status($event['id'],$uid,$status,0,true);
		}
		$conflicts=$this->bo->update($event);

		$response = egw_json_response::get();
		if(!is_array($conflicts))
		{
			// Directly update stored data.  If event is still visible, it will
			// be notified & update itself.
			$this->to_client($event);
			$response->call('egw.dataStoreUID','calendar::'.$event['id'].($date?':'.$date:''),$event);
		}
		else
		{
			$response->call(
				'egw_openWindowCentered2',
				$GLOBALS['egw_info']['server']['webserver_url'].'/index.php?menuaction=calendar.calendar_uiforms.edit
					&cal_id='.$event['id']
					.'&start='.$event['start']
					.'&end='.$event['end']
					.'&non_interactive=true'
					.'&cancel_needs_refresh=true',
				'',750,410);
		}
	}

	/**
	 * Deletes an event
	 */
	public function ajax_delete($eventId)
	{
		list($eventId, $date) = explode(':',$eventId);
		$event=$this->bo->read($eventId);
		$response = egw_json_response::get();

		if ($this->bo->delete($event['id'], (int)$date))
		{
			if ($event['recur_type'] != MCAL_RECUR_NONE && !$date)
			{
				$msg = lang('Series deleted');
			}
			else
			{
				$msg = lang('Event deleted');
			}
			$response->apply('egw.refresh', Array($msg,'calendar',$eventId,'delete'));
		}
		else
		{
			$response->apply('egw.message', lang('Error'),'error');
		}
	}

	/**
	 * imports a mail as Calendar
	 *
	 * @param array $mailContent = null mail content
	 * @return  array
	 */
	function mail_import(array $mailContent=null)
	{
		// It would get called from compose as a popup with egw_data
		if (!is_array($mailContent) && ($_GET['egw_data']))
		{
			// get raw mail data
			egw_link::get_data ($_GET['egw_data']);
			return false;
		}

		if (is_array($mailContent))
		{
			// Addressbook
			$AB = new addressbook_bo();
			$accounts = array(0 => $GLOBALS['egw_info']['user']['account_id']);

			$participants[0] = array (
				'uid' => $GLOBALS['egw_info']['user']['account_id'],
				'delete_id' => $GLOBALS['egw_info']['user']['account_id'],
				'status' => 'A',
				'old_status' => 'A',
				'app' => 'User',
				'role' => 'REQ-PARTICIPANT'
			);
			foreach($mailContent['addresses'] as $address)
			{
				// Get available contacts from the email
				$contacts = $AB->search(array(
						'email' => $address['email'],
						'email_home' => $address['email']
					),'contact_id,contact_email,contact_email_home,egw_addressbook.account_id as account_id','','','',false,'OR',false,array('owner' => 0),'',false);
				if (is_array($contacts))
				{
					foreach($contacts as $account)
					{
						$accounts[] = $account['account_id'];
					}
				}
				else
				{
					$participants []= array (
						'app' => 'email',
						'uid' => 'e'.$address['email'],
						'status' => 'U',
						'old_status' => 'U'
					);
				}
			}
			$participants = array_merge($participants , array(
				"account" => $accounts,
				"role" => "REQ-PARTICIPANT",
				"add" => "pressed"
			));

			// Prepare calendar event draft
			$event = array(
				'title' => $mailContent['subject'],
				'description' => $mailContent['message'],
				'participants' => $participants,
				'link_to' => array(
					'to_app' => 'calendar',
					'to_id' => 0,
				),
				'start' => $mailContent['date'],
				'duration' => 60 * $this->cal_prefs['interval']
			);

			if (is_array($mailContent['attachments']))
			{
				foreach ($mailContent['attachments'] as $attachment)
				{
					if($attachment['egw_data'])
					{
						egw_link::link('calendar',$event['link_to']['to_id'],egw_link::DATA_APPNAME,  $attachment);
					}
					else if(is_readable($attachment['tmp_name']))
					{
						egw_link::link('calendar',$event['link_to']['to_id'],'file',  $attachment);
					}
				}
			}
		}
		else
		{
			egw_framework::window_close(lang('No content found to show up as calendar entry.'));
		}

		return $this->process_edit($event);
	}
}
