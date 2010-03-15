<?php
/**
 * iCal import and export via Horde iCalendar classes
 *
 * @link http://www.egroupware.org
 * @author Lars Kneschke <lkneschke@egroupware.org>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @author Joerg Lehrke <jlehrke@noc.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package calendar
 * @subpackage export
 * @version $Id$
 */

require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/lib/core.php';

/**
 * iCal import and export via Horde iCalendar classes
 */
class calendar_ical extends calendar_boupdate
{
	/**
	 * @var array $supportedFields array containing the supported fields of the importing device
	 */
	var $supportedFields;

	var $recur_days_1_0 = array(
		MCAL_M_MONDAY    => 'MO',
		MCAL_M_TUESDAY   => 'TU',
		MCAL_M_WEDNESDAY => 'WE',
		MCAL_M_THURSDAY  => 'TH',
		MCAL_M_FRIDAY    => 'FR',
		MCAL_M_SATURDAY  => 'SA',
		MCAL_M_SUNDAY    => 'SU',
	);
	/**
	 * @var array $status_egw2ical conversation of the participant status egw => ical
	 */
	var $status_egw2ical = array(
		'U' => 'NEEDS-ACTION',
		'A' => 'ACCEPTED',
		'R' => 'DECLINED',
		'T' => 'TENTATIVE',
		'D' => 'DELEGATED'
	);
	/**
	 * @var array conversation of the participant status ical => egw
	 */
	var $status_ical2egw = array(
		'NEEDS-ACTION' => 'U',
		'NEEDS ACTION' => 'U',
		'ACCEPTED'     => 'A',
		'DECLINED'     => 'R',
		'TENTATIVE'    => 'T',
		'DELEGATED'    => 'D',
		'X-UNINVITED'  => 'G', // removed
	);

	/**
	 * @var array $priority_egw2ical conversion of the priority egw => ical
	 */
	var $priority_egw2ical = array(
		0 => 0,		// undefined
		1 => 9,		// low
		2 => 5,		// normal
		3 => 1,		// high
	);

	/**
	 * @var array $priority_ical2egw conversion of the priority ical => egw
	 */
	var $priority_ical2egw = array(
		0 => 0,		// undefined
		9 => 1,	8 => 1, 7 => 1, 6 => 1,	// low
		5 => 2,		// normal
		4 => 3, 3 => 3, 2 => 3, 1 => 3,	// high
	);

	/**
	 * @var array $priority_egw2funambol conversion of the priority egw => funambol
	 */
	var $priority_egw2funambol = array(
		0 => 1,		// undefined (mapped to normal since undefined does not exist)
		1 => 0,		// low
		2 => 1,		// normal
		3 => 2,		// high
	);

	/**
	 * @var array $priority_funambol2egw conversion of the priority funambol => egw
	 */
	var $priority_funambol2egw = array(
		0 => 1,		// low
		1 => 2,		// normal
		2 => 3,		// high
	);

	/**
	 * manufacturer and name of the sync-client
	 *
	 * @var string
	 */
	var $productManufacturer = 'file';
	var $productName = '';

	/**
	 * user preference: import all-day events as non blocking
	 *
	 * @var boolean
	 */
	var $nonBlockingAllday = false;

	/**
	 * user preference: attach UID entries to the DESCRIPTION
	 *
	 * @var boolean
	 */
	var $uidExtension = false;

	/**
	 * user preference: calendar to synchronize with
	 *
	 * @var int
	 */
	var $calendarOwner = 0;

	/**
	 * user preference: Use this timezone for import from and export to device
	 *
	 * @var mixed
	 * === false => use event's TZ
	 * === null  => export in UTC
	 * string    => device TZ
	 *
	 */
	var $tzid = null;

	/**
	 * Device CTCap Properties
	 *
	 * @var array
	 */
	var $clientProperties;

	/**
	 * vCalendar Instance for parsing
	 *
	 * @var array
	 */
	var $vCalendar;

	/**
	 * Addressbook BO instance
	 *
	 * @var array
	 */
	var $addressbook;

	/**
	 * Set Logging
	 *
	 * @var boolean
	 */
	var $log = false;
	var $logfile="/tmp/log-vcal";


	/**
	 * Constructor
	 *
	 * @param array $_clientProperties		client properties
	 */
	function __construct(&$_clientProperties = array())
	{
		parent::__construct();
		if ($this->log) $this->logfile = $GLOBALS['egw_info']['server']['temp_dir']."/log-vcal";
		$this->clientProperties = $_clientProperties;
		$this->vCalendar = new Horde_iCalendar;
		$this->addressbook = new addressbook_bo;
	}


	/**
	 * Exports one calendar event to an iCalendar item
	 *
	 * @param int|array $events (array of) cal_id or array of the events
	 * @param string $version='1.0' could be '2.0' too
	 * @param string $method='PUBLISH'
	 * @param int $recur_date=0	if set export the next recurrence at or after the timestamp,
	 *                          default 0 => export whole series (or events, if not recurring)
	 * @param string $principalURL='' Used for CalDAV exports
	 * @return string|boolean string with iCal or false on error (eg. no permission to read the event)
	 */
	function &exportVCal($events, $version='1.0', $method='PUBLISH', $recur_date=0, $principalURL='')
	{
		if ($this->log)
		{
			error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
				"($version, $method, $recur_date, $principalURL)\n",
				3, $this->logfile);
		}
		$egwSupportedFields = array(
			'CLASS'			=> 'public',
			'SUMMARY'		=> 'title',
			'DESCRIPTION'	=> 'description',
			'LOCATION'		=> 'location',
			'DTSTART'		=> 'start',
			'DTEND'			=> 'end',
			'ATTENDEE'		=> 'participants',
			'ORGANIZER'		=> 'owner',
			'RRULE'			=> 'recur_type',
			'EXDATE'		=> 'recur_exception',
			'PRIORITY'		=> 'priority',
			'TRANSP'		=> 'non_blocking',
			'CATEGORIES'	=> 'category',
			'UID'			=> 'uid',
			'RECURRENCE-ID' => 'recurrence',
			'SEQUENCE'		=> 'etag',
			'STATUS'		=> 'status',
		);

		if (!is_array($this->supportedFields)) $this->setSupportedFields();

		if ($this->productManufacturer == '' )
		{	// syncevolution is broken
			$version = '2.0';
		}

		$vcal = new Horde_iCalendar;
		$vcal->setAttribute('PRODID','-//eGroupWare//NONSGML eGroupWare Calendar '.$GLOBALS['egw_info']['apps']['calendar']['version'].'//'.
			strtoupper($GLOBALS['egw_info']['user']['preferences']['common']['lang']));
		$vcal->setAttribute('VERSION', $version);
		$vcal->setAttribute('METHOD', $method);
		$events_exported = false;

		if (!is_array($events)) $events = array($events);

		$vtimezones_added = array();
		foreach ($events as $event)
		{
			$organizerURL = '';
			$organizerCN = false;
			$recurrence = $this->date2usertime($recur_date);
			$tzid = null;

			if (!is_array($event)
				&& !($event = $this->read($event, $recurrence, false, 'server')))
			{
				if ($this->read($event, $recurrence, true, 'server'))
				{
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							'() User does not have the permission to read event ' . $event['id']. "\n",
							3,$this->logfile);
					}
					return -1; // Permission denied
				}
				else
				{
					$retval = false;  // Entry does not exist
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							"() Event $event not found.\n",
							3, $this->logfile);
					}
				}
				continue;
			}

			if ($this->tzid)
			{
				// explicit device timezone
				$tzid = $this->tzid;
			}
			elseif ($this->tzid === false)
			{
				// use event's timezone
				$tzid = $event['tzid'];
			}

			if (!isset(self::$tz_cache[$event['tzid']]))
			{
				self::$tz_cache[$event['tzid']] = calendar_timezones::DateTimeZone($event['tzid']);
			}

			if ($this->so->isWholeDay($event)) $event['whole_day'] = true;

			if ($this->log)
			{
				error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
					'(' . $event['id']. ',' . $recurrence . ")\n" .
					array2string($event)."\n",3,$this->logfile);
			}

			if ($recurrence)
			{
				if (!($master = $this->read($event['id'], 0, true, 'server'))) continue;

				if (!isset($this->supportedFields['participants']))
				{
					$days = $this->so->get_recurrence_exceptions($master, $tzid, 0, 0, 'tz_rrule');
					if (isset($days[$recurrence]))
					{
						$recurrence = $days[$recurrence]; // use remote representation
					}
					else
					{
						// We don't need status only exceptions
						if ($this->log)
						{
							error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
								"($_id, $recurrence) Gratuitous pseudo exception, skipped ...\n",
								3,$this->logfile);
						}
						continue; // unsupported status only exception
					}
				}
				else
				{
					$days = $this->so->get_recurrence_exceptions($master, $tzid, 0, 0, 'rrule');
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__."()\n" .
							array2string($days)."\n",3,$this->logfile);
					}
					$recurrence = $days[$recurrence]; // use remote representation
				}
				// force single event
				foreach (array('recur_enddate','recur_interval','recur_exception','recur_data','recur_date','id','etag') as $name)
				{
					unset($event[$name]);
				}
				$event['recur_type'] = MCAL_RECUR_NONE;
			}
			elseif ($event['recur_enddate'])
			{
				$time = new egw_time($event['recur_enddate'],egw_time::$server_timezone);
				// all calculations in the event's timezone
				$time->setTimezone(self::$tz_cache[$event['tzid']]);
				$time->setTime(23, 59, 59);
				$event['recur_enddate'] = egw_time::to($time, 'server');
			}

			// check if tzid of event (not only recuring ones) is already added to export
			if ($tzid && $tzid != 'UTC' && !in_array($tzid,$vtimezones_added))
			{
				// check if we have vtimezone component data for tzid of event, if not default to user timezone (default to server tz)
				if (!($vtimezone = calendar_timezones::tz2id($tzid,'component')))
				{
					error_log(__METHOD__."() unknown TZID='$tzid', defaulting to user timezone '".egw_time::$user_timezone->getName()."'!");
					$vtimezone = calendar_timezones::tz2id($tzid=egw_time::$user_timezone->getName(),'component');
					$tzid = null;
				}
				if (!isset(self::$tz_cache[$tzid]))
				{
					self::$tz_cache[$tzid] = calendar_timezones::DateTimeZone($tzid);
				}
				//error_log("in_array('$tzid',\$vtimezones_added)=".array2string(in_array($tzid,$vtimezones_added)).", component=$vtimezone");;
				if (!in_array($tzid,$vtimezones_added))
				{
					// $vtimezone is a string with a single VTIMEZONE component, afaik Horde_iCalendar can not add it directly
					// --> we have to parse it and let Horde_iCalendar add it again
					$horde_vtimezone = Horde_iCalendar::newComponent('VTIMEZONE',$container=false);
					$horde_vtimezone->parsevCalendar($vtimezone,'VTIMEZONE');
					// DTSTART must be in local time!
					$standard = $horde_vtimezone->findComponent('STANDARD');
					if (is_a($standard, 'Horde_iCalendar'))
					{
						$dtstart = $standard->getAttribute('DTSTART');
						$dtstart = new egw_time($dtstart, egw_time::$server_timezone);
						$dtstart->setTimezone(self::$tz_cache[$tzid]);
						$standard->setAttribute('DTSTART', $dtstart->format('Ymd\THis'), array(), false);
					}
					$daylight = $horde_vtimezone->findComponent('DAYLIGHT');
					if (is_a($daylight, 'Horde_iCalendar'))
					{
						$dtstart = $daylight->getAttribute('DTSTART');
						$dtstart = new egw_time($dtstart, egw_time::$server_timezone);
						$dtstart->setTimezone(self::$tz_cache[$tzid]);
						$daylight->setAttribute('DTSTART', $dtstart->format('Ymd\THis'), array(), false);
					}
					$vcal->addComponent($horde_vtimezone);
					$vtimezones_added[] = $tzid;
				}
			}
			if ($this->productManufacturer != 'file' && $this->uidExtension)
			{
				// Append UID to DESCRIPTION
				if (!preg_match('/\[UID:.+\]/m', $event['description'])) {
					$event['description'] .= "\n[UID:" . $event['uid'] . "]";
				}
			}

			$vevent = Horde_iCalendar::newComponent('VEVENT', $vcal);
			$parameters = $attributes = $values = array();

			if ($this->productManufacturer == 'sonyericsson')
			{
				$eventDST = date('I', $event['start']);
				if ($eventDST)
				{
					$attributes['X-SONYERICSSON-DST'] = 4;
				}
			}

			if ($event['recur_type'] != MCAL_RECUR_NONE)
			{
				$exceptions = array();

				// dont use "virtual" exceptions created by participant status for GroupDAV or file export
				if (!in_array($this->productManufacturer,array('file','groupdav')))
				{
					$filter = isset($this->supportedFields['participants']) ? 'rrule' : 'tz_rrule';
					$exceptions = $this->so->get_recurrence_exceptions($event, $tzid, 0, 0, $filter);
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__."(EXCEPTIONS)\n" .
							array2string($exceptions)."\n",3,$this->logfile);
					}
				}
				elseif (is_array($event['recur_exception']))
				{
					$exceptions = array_unique($event['recur_exception']);
					sort($exceptions);
				}
				$event['recur_exception'] = $exceptions;
				/*
				// Adjust the event start -- must not be an exception
				$length = $event['end'] - $event['start'];
				$rriter = calendar_rrule::event2rrule($event, false, $tzid);
				$rriter->rewind();
				if ($rriter->valid())
				{
					$event['start'] = egw_time::to($rriter->current, 'server');
					$event['end'] = $event['start'] + $length;
					foreach($exceptions as $key => $day)
					{
						// remove leading exceptions
						if ($day <= $event['start']) unset($exceptions[$key]);
					}
					$event['recur_exception'] = $exceptions;
				}
				else
				{
					// the series dissolved completely into exceptions
					continue;
				}*/
			}

			foreach ($egwSupportedFields as $icalFieldName => $egwFieldName)
			{
				if (!isset($this->supportedFields[$egwFieldName]))
				{
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							'(' . $event['id'] . ") [$icalFieldName] not supported\n",
							3,$this->logfile);
					}
					continue;
				}
				$values[$icalFieldName] = array();
				switch ($icalFieldName)
				{
					case 'ATTENDEE':
						//if (count($event['participants']) == 1 && isset($event['participants'][$this->user])) break;
						foreach ((array)$event['participants'] as $uid => $status)
						{
							if (!($info = $this->resource_info($uid))) continue;
							$participantURL = $info['email'] ? 'MAILTO:'.$info['email'] : '';
							$participantCN = '"' . ($info['cn'] ? $info['cn'] : $info['name']) . '"';
							calendar_so::split_status($status, $quantity, $role);
							if ($role == 'CHAIR' && $uid != $this->user)
							{
								$organizerURL = $participantURL;
								$organizerCN = $participantCN;
								$organizerUID = $uid;
							}
							// RB: MAILTO href contains only the email-address, NO cn!
							$attributes['ATTENDEE'][]	= $participantURL;
							// RSVP={TRUE|FALSE}	// resonse expected, not set in eGW => status=U
							$rsvp = $status == 'U' ? 'TRUE' : 'FALSE';
							// PARTSTAT={NEEDS-ACTION|ACCEPTED|DECLINED|TENTATIVE|DELEGATED|COMPLETED|IN-PROGRESS} everything from delegated is NOT used by eGW atm.
							$status = $this->status_egw2ical[$status];
							// CUTYPE={INDIVIDUAL|GROUP|RESOURCE|ROOM|UNKNOWN}
							switch ($info['type'])
							{
								case 'g':
									$cutype = 'GROUP';
									break;
								case 'r':
									$cutype = 'RESOURCE';
									break;
								case 'u':	// account
								case 'c':	// contact
								case 'e':	// email address
									$cutype = 'INDIVIDUAL';
									break;
								default:
									$cutype = 'UNKNOWN';
									break;
							};
							// ROLE={CHAIR|REQ-PARTICIPANT|OPT-PARTICIPANT|NON-PARTICIPANT|X-*}
							$parameters['ATTENDEE'][] = array(
								'CN'       => $participantCN,
								'ROLE'     => $role,
								'PARTSTAT' => $status,
								'CUTYPE'   => $cutype,
								'RSVP'     => $rsvp,
							)+($info['type'] != 'e' ? array('X-EGROUPWARE-UID' => $uid) : array())+
							($quantity > 1 ? array('X-EGROUPWARE-QUANTITY' => $quantity) : array());
						}
						break;

					case 'CLASS':
						$attributes['CLASS'] = $event['public'] ? 'PUBLIC' : 'PRIVATE';
						break;

    				case 'ORGANIZER':
    					// according to iCalendar standard, ORGANIZER not used for events in the own calendar
	    				if (!$organizerCN)
	    				{
		    				$organizerURL = $GLOBALS['egw']->accounts->id2name($event['owner'],'account_email');
		    				$organizerURL = $organizerURL ? 'MAILTO:'.$organizerURL : '';
		    				$organizerCN = '"' . trim($GLOBALS['egw']->accounts->id2name($event['owner'],'account_firstname')
			    				. ' ' . $GLOBALS['egw']->accounts->id2name($event['owner'],'account_lastname')) . '"';
			    			$organizerUID = $event['owner'];
		    				if (!isset($event['participants'][$event['owner']]))
		    				{
			    				$attributes['ATTENDEE'][] = $organizerURL;
			    				$parameters['ATTENDEE'][] = array(
				    				'CN'       => $organizerCN,
									'ROLE'     => 'CHAIR',
									'PARTSTAT' => 'DELEGATED',
									'CUTYPE'   => 'INDIVIDUAL',
									'RSVP'     => 'FALSE',
									'X-EGROUPWARE-UID' => $event['owner'],
			    				);
		    				}
	    				}
		    			if ($this->productManufacturer != 'groupdav'
			    				|| !$this->check_perms(EGW_ACL_EDIT,$event['id']))
	    				{
		    				$attributes['ORGANIZER'] = $organizerURL;
		    				$parameters['ORGANIZER']['CN'] = $organizerCN;
		    				$parameters['ORGANIZER']['X-EGROUPWARE-UID'] = $organizerUID;
	    				}
	    				break;

					case 'DTSTART':
						if (!isset($event['whole_day']))
						{
							$attributes['DTSTART'] = self::getDateTime($event['start'],$tzid,$parameters['DTSTART']);
						}
						break;

					case 'DTEND':
						// write start + end of whole day events as dates
						if (isset($event['whole_day']))
						{
							$event['end-nextday'] = $event['end'] + 12*3600;	// we need the date of the next day, as DTEND is non-inclusive (= exclusive) in rfc2445
							foreach (array('start' => 'DTSTART','end-nextday' => 'DTEND') as $f => $t)
							{
								$time = new egw_time($event[$f],egw_time::$server_timezone);
								$arr = egw_time::to($time,'array');
								$vevent->setAttribute($t, array('year' => $arr['year'],'month' => $arr['month'],'mday' => $arr['day']),
									array('VALUE' => 'DATE'));
							}
							unset($attributes['DTSTART']);
						}
						else
						{
							$attributes['DTEND'] = self::getDateTime($event['end'],$tzid,$parameters['DTEND']);
						}
						break;

					case 'RRULE':
						if ($event['recur_type'] == MCAL_RECUR_NONE) break;		// no recuring event
						$rriter = calendar_rrule::event2rrule($event, false, $tzid);
						$rrule = $rriter->generate_rrule($version);
						if ($version == '1.0')
						{
							$attributes['RRULE'] = $rrule['FREQ'].' '.$rrule['UNTIL'];
						}
						else // $version == '2.0'
						{
							$attributes['RRULE'] = '';
							$parameters['RRULE'] = $rrule;
						}
						break;

					case 'EXDATE':
						if ($event['recur_type'] == MCAL_RECUR_NONE) break;
						if (!empty($event['recur_exception']))
						{
							// use 'DATE' instead of 'DATE-TIME' on whole day events
							if (isset($event['whole_day']))
							{
								$value_type = 'DATE';
								foreach ($event['recur_exception'] as $id => $timestamp)
								{
									$time = new egw_time($timestamp,egw_time::$server_timezone);
									$time->setTimezone(self::$tz_cache[$event['tzid']]);
									$arr = egw_time::to($time,'array');
									$days[$id] = array(
										'year'  => $arr['year'],
										'month' => $arr['month'],
										'mday'  => $arr['day'],
									);
								}
								$event['recur_exception'] = $days;
							}
							else
							{
								$value_type = 'DATE-TIME';
								foreach ($event['recur_exception'] as $key => $timestamp)
								{
									$event['recur_exception'][$key] = self::getDateTime($timestamp,$tzid,$parameters['EXDATE']);
								}
							}
							$attributes['EXDATE'] = '';
							$values['EXDATE'] = $event['recur_exception'];
							$parameters['EXDATE']['VALUE'] = $value_type;
						}
						break;

					case 'PRIORITY':
						if ($this->productManufacturer == 'funambol' &&
							(strpos($this->productName, 'outlook') !== false
								|| strpos($this->productName, 'pocket pc') !== false))
						{
							$attributes['PRIORITY'] = (int) $this->priority_egw2funambol[$event['priority']];
						}
						else
						{
							$attributes['PRIORITY'] = (int) $this->priority_egw2ical[$event['priority']];
						}
						break;

					case 'TRANSP':
						if ($version == '1.0')
						{
							$attributes['TRANSP'] = ($event['non_blocking'] ? 1 : 0);
						}
						else
						{
							$attributes['TRANSP'] = ($event['non_blocking'] ? 'TRANSPARENT' : 'OPAQUE');
						}
						break;

					case 'STATUS':
						$attributes['STATUS'] = 'CONFIRMED';
						break;

					case 'CATEGORIES':
						if ($event['category'] && ($values['CATEGORIES'] = $this->get_categories($event['category'])))
						{
							if (count($values['CATEGORIES']) == 1)
							{
								$attributes['CATEGORIES'] = array_shift($values['CATEGORIES']);
							}
							else
							{
								$attributes['CATEGORIES'] = '';
							}
						}
						break;

					case 'RECURRENCE-ID':
						if ($version == '1.0')
						{
								$icalFieldName = 'X-RECURRENCE-ID';
						}
						if ($recur_date)
						{
							// We handle a pseudo exception
							if (isset($event['whole_day']))
							{
								$time = new egw_time($recur_date,egw_time::$server_timezone);
								$time->setTimezone(self::$tz_cache[$event['tzid']]);
								$arr = egw_time::to($time,'array');
								$vevent->setAttribute($icalFieldName, array(
									'year' => $arr['year'],
									'month' => $arr['month'],
									'mday' => $arr['day']),
									array('VALUE' => 'DATE')
								);
							}
							else
							{
								$attributes[$icalFieldName] = self::getDateTime($recur_date,$tzid,$parameters[$icalFieldName]);
							}
						}
						elseif ($event['recurrence'] && $event['reference'])
						{
							// $event['reference'] is a calendar_id, not a timestamp
							if (!($revent = $this->read($event['reference']))) break;	// referenced event does not exist

							if (isset($revent['whole_day']))
							{
								$time = new egw_time($event['recurrence'],egw_time::$server_timezone);
								$time->setTimezone(self::$tz_cache[$event['tzid']]);
								$arr = egw_time::to($time,'array');
								$vevent->setAttribute($icalFieldName, array(
									'year' => $arr['year'],
									'month' => $arr['month'],
									'mday' => $arr['day']),
									array('VALUE' => 'DATE')
								);
							}
							else
							{
								$attributes[$icalFieldName] = self::getDateTime($event['recurrence'],$tzid,$parameters[$icalFieldName]);
							}
							unset($revent);
						}
						break;

					default:
						if (isset($this->clientProperties[$icalFieldName]['Size']))
						{
							$size = $this->clientProperties[$icalFieldName]['Size'];
							$noTruncate = $this->clientProperties[$icalFieldName]['NoTruncate'];
							if ($this->log && $size > 0)
							{
								error_log(__FILE__.'['.__LINE__.'] '.__METHOD__ .
									"() $icalFieldName Size: $size, NoTruncate: " .
									($noTruncate ? 'TRUE' : 'FALSE') . "\n",3,$this->logfile);
							}
							//Horde::logMessage("vCalendar $icalFieldName Size: $size, NoTruncate: " .
							//	($noTruncate ? 'TRUE' : 'FALSE'), __FILE__, __LINE__, PEAR_LOG_DEBUG);
						}
						else
						{
							$size = -1;
							$noTruncate = false;
						}
						$value = $event[$egwFieldName];
						$cursize = strlen($value);
						if ($size > 0 && $cursize > $size)
						{
							if ($noTruncate)
							{
								if ($this->log)
								{
									error_log(__FILE__.'['.__LINE__.'] '.__METHOD__ .
										"() $icalFieldName omitted due to maximum size $size\n",3,$this->logfile);
								}
								//Horde::logMessage("vCalendar $icalFieldName omitted due to maximum size $size",
								//	__FILE__, __LINE__, PEAR_LOG_WARNING);
								continue; // skip field
							}
							// truncate the value to size
							$value = substr($value, 0, $size - 1);
							if ($this->log)
							{
								error_log(__FILE__.'['.__LINE__.'] '.__METHOD__ .
									"() $icalFieldName truncated to maximum size $size\n",3,$this->logfile);
							}
							//Horde::logMessage("vCalendar $icalFieldName truncated to maximum size $size",
							//	__FILE__, __LINE__, PEAR_LOG_INFO);
						}
						if (!empty($value) || ($size >= 0 && !$noTruncate))
						{
							$attributes[$icalFieldName] = $value;
						}
				}
			}

			if ($this->productManufacturer == 'nokia')
			{
				if ($event['special'] == '1')
				{
					$attributes['X-EPOCAGENDAENTRYTYPE'] = 'ANNIVERSARY';
					$attributes['DTEND'] = $attributes['DTSTART'];
				}
				elseif ($event['special'] == '2')
				{
					$attributes['X-EPOCAGENDAENTRYTYPE'] = 'EVENT';
				}
				else
				{
					$attributes['X-EPOCAGENDAENTRYTYPE'] = 'APPOINTMENT';
				}
			}

			$modified = $GLOBALS['egw']->contenthistory->getTSforAction('calendar',$event['id'],'modify');
			$created = $GLOBALS['egw']->contenthistory->getTSforAction('calendar',$event['id'],'add');
			if (!$created && !$modified) $created = $event['modified'];
			if ($created)
			{
				$attributes['CREATED'] = $created;
			}
			if (!$modified) $modified = $event['modified'];
			if ($modified)
			{
				$attributes['LAST-MODIFIED'] = $modified;
			}
			$attributes['DTSTAMP'] = time();
			foreach ($event['alarm'] as $alarmID => $alarmData)
			{
				// skip over alarms that don't have the minimum required info
				if (!$alarmData['offset'] && !$alarmData['time']) continue;

				// skip alarms not being set for all users and alarms owned by other users
				if ($alarmData['all'] != true && $alarmData['owner'] != $this->user)
				{
					continue;
				}

				if ($alarmData['offset'])
				{
					$alarmData['time'] = $event['start'] - $alarmData['offset'];
				}

				$description = trim(preg_replace("/\r?\n?\\[[A-Z_]+:.*\\]/i", '', $event['description']));

				if ($version == '1.0')
				{
					if ($event['title']) $description = $event['title'];
					if ($description)
					{
						$values['DALARM']['snooze_time'] = '';
						$values['DALARM']['repeat count'] = '';
						$values['DALARM']['display text'] = $description;
						$values['AALARM']['snooze_time'] = '';
						$values['AALARM']['repeat count'] = '';
						$values['AALARM']['display text'] = $description;
					}
					$attributes['DALARM'] = self::getDateTime($alarmData['time'],$tzid,$parameters['DALARM']);
					$attributes['AALARM'] = self::getDateTime($alarmData['time'],$tzid,$parameters['AALARM']);
					// lets take only the first alarm
					break;
				}
				else
				{
					// VCalendar 2.0 / RFC 2445

					// RFC requires DESCRIPTION for DISPLAY
					if (!$event['title'] && !$description) continue;

					if (isset($event['whole_day']) && $alarmData['offset'])
					{
						$alarmData['time'] = $event['start'] - $alarmData['offset'];
						$alarmData['offset'] = false;
					}

					$valarm = Horde_iCalendar::newComponent('VALARM',$vevent);
					if ($alarmData['offset'])
					{
						$valarm->setAttribute('TRIGGER', -$alarmData['offset'],
								array('VALUE' => 'DURATION', 'RELATED' => 'START'));
					}
					else
					{
						$params = array('VALUE' => 'DATE-TIME');
						$value = self::getDateTime($alarmData['time'],$tzid,$params);
						$valarm->setAttribute('TRIGGER', $value, $params);
					}

					$valarm->setAttribute('ACTION','DISPLAY');
					$valarm->setAttribute('DESCRIPTION',$event['title'] ? $event['title'] : $description);
					$vevent->addComponent($valarm);
				}
			}

			foreach ($attributes as $key => $value)
			{
				foreach (is_array($value) && $parameters[$key]['VALUE']!='DATE' ? $value : array($value) as $valueID => $valueData)
				{
					$valueData = $GLOBALS['egw']->translation->convert($valueData,$GLOBALS['egw']->translation->charset(),'UTF-8');
                    $paramData = (array) $GLOBALS['egw']->translation->convert(is_array($value) ?
                    		$parameters[$key][$valueID] : $parameters[$key],
                            $GLOBALS['egw']->translation->charset(),'UTF-8');
                    $valuesData = (array) $GLOBALS['egw']->translation->convert($values[$key],
                    		$GLOBALS['egw']->translation->charset(),'UTF-8');
                    $content = $valueData . implode(';', $valuesData);

					if (preg_match('/[^\x20-\x7F]/', $content) ||
						($paramData['CN'] && preg_match('/[^\x20-\x7F]/', $paramData['CN'])))
					{
						$paramData['CHARSET'] = 'UTF-8';
						switch ($this->productManufacturer)
						{
							case 'groupdav':
								if ($this->productName == 'kde')
								{
									$paramData['ENCODING'] = 'QUOTED-PRINTABLE';
								}
								else
								{
									$paramData['CHARSET'] = '';
									if (preg_match('/([\000-\012\015\016\020-\037\075])/', $valueData))
									{
										$paramData['ENCODING'] = 'QUOTED-PRINTABLE';
									}
									else
									{
										$paramData['ENCODING'] = '';
									}
								}
								break;
							case 'funambol':
								$paramData['ENCODING'] = 'FUNAMBOL-QP';
						}
					}
					/*
					if (preg_match('/([\000-\012])/', $valueData))
					{
						if ($this->log)
						{
							error_log(__FILE__.'['.__LINE__.'] '.__METHOD__ .
								"() Has invalid XML data: $valueData",3,$this->logfile);
						}
					}
					*/
					$vevent->setAttribute($key, $valueData, $paramData, true, $valuesData);
				}
			}
			$vcal->addComponent($vevent);
			$events_exported = true;
		}

		$retval = $events_exported ? $vcal->exportvCalendar() : false;
 		if ($this->log)
 		{
 			error_log(__FILE__.'['.__LINE__.'] '.__METHOD__ .
				"() '$this->productManufacturer','$this->productName'\n",3,$this->logfile);
 			error_log(__FILE__.'['.__LINE__.'] '.__METHOD__ .
				"()\n".array2string($retval)."\n",3,$this->logfile);
 		}
		return $retval;
	}

	/**
	 * Get DateTime value for a given time and timezone
	 *
	 * @param int|string|DateTime $time in server-time as returned by calendar_bo for $data_format='server'
	 * @param string $tzid TZID of event or 'UTC' or NULL for palmos timestamps in usertime
	 * @param array &$params=null parameter array to set TZID
	 * @return mixed attribute value to set: integer timestamp if $tzid == 'UTC' otherwise Ymd\THis string IN $tzid
	 */
	static function getDateTime($time,$tzid,array &$params=null)
	{
		if (empty($tzid) || $tzid == 'UTC')
		{
			return egw_time::to($time,'ts');
		}
		if (!is_a($time,'DateTime'))
		{
			$time = new egw_time($time,egw_time::$server_timezone);
		}
		if (!isset(self::$tz_cache[$tzid]))
		{
			self::$tz_cache[$tzid] = calendar_timezones::DateTimeZone($tzid);
		}
		$time->setTimezone(self::$tz_cache[$tzid]);
		$params['TZID'] = $tzid;

		return $time->format('Ymd\THis');
	}

	/**
	 * Import an iCal
	 *
	 * @param string $_vcalData
	 * @param int $cal_id=-1 must be -1 for new entries!
	 * @param string $etag=null if an etag is given, it has to match the current etag or the import will fail
	 * @param boolean $merge=false	merge data with existing entry
	 * @param int $recur_date=0 if set, import the recurrence at this timestamp,
	 *                          default 0 => import whole series (or events, if not recurring)
	 * @param string $principalURL='' Used for CalDAV imports
	 * @param int $user=null account_id of owner, default null
	 * @return int|boolean cal_id > 0 on success, false on failure or 0 for a failed etag
	 */
	function importVCal($_vcalData, $cal_id=-1, $etag=null, $merge=false, $recur_date=0, $principalURL='', $user=null)
	{
		if (!is_array($this->supportedFields)) $this->setSupportedFields();

		if (!($events = $this->icaltoegw($_vcalData, $principalURL)))
		{
			return false;
		}

		if ($cal_id > 0)
		{
			if (count($events) == 1)
			{
				$events[0]['id'] = $cal_id;
				if (!is_null($etag)) $events[0]['etag'] = (int) $etag;
				if ($recur_date) $events[0]['recurrence'] = $recur_date;
			}
			elseif (($foundEvent = $this->find_event(array('id' => $cal_id), 'exact')) &&
					($eventId = array_shift($foundEvent)) &&
					($egwEvent = $this->read($eventId)))
			{
				foreach ($events as $k => $event)
				{
					if (!isset($event['uid'])) $events[$k]['uid'] = $egwEvent['uid'];
				}
			}
		}

		// check if we are importing an event series with exceptions in CalDAV
		// only first event / series master get's cal_id from URL
		// other events are exceptions and need to be checked if they are new
		// and for real (not status only) exceptions their recurrence-id need
		// to be included as recur_exception to the master
		if ($this->productManufacturer == 'groupdav' && $cal_id > 0 &&
			count($events) > 1 && !$events[1]['id'] &&
			$events[0]['recur_type'] != MCAL_RECUR_NONE)
		{
			calendar_groupdav::fix_series($events);
		}
		foreach ($events as $event)
		{
			if ($this->so->isWholeDay($event)) $event['whole_day'] = true;
			if (is_array($event['category']))
			{
				$event['category'] = $this->find_or_add_categories($event['category'],
					isset($event['id']) ? $event['id'] : -1);
			}
			if ($this->log)
			{
				error_log(__FILE__.'['.__LINE__.'] '.__METHOD__
					."($cal_id, $etag, $recur_date, $principalURL, $user)\n"
					. array2string($event)."\n",3,$this->logfile);
			}

			/*
			if ($event['recur_type'] != MCAL_RECUR_NONE)
			{
				// Adjust the event start -- no exceptions before and at the start
				$length = $event['end'] - $event['start'];
				$rriter = calendar_rrule::event2rrule($event, false);
				$rriter->rewind();
				if (!$rriter->valid()) continue; // completely disolved into exceptions

				$newstart = egw_time::to($rriter->current, 'server');
				if ($newstart != $event['start'])
				{
					// leading exceptions skiped
					$event['start'] = $newstart;
					$event['end'] = $newstart + $length;
				}

				$exceptions = $event['recur_exception'];
				foreach($exceptions as $key => $day)
				{
					// remove leading exceptions
					if ($day <= $event['start'])
					{
						if ($this->log)
						{
							error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
								'(): event SERIES-MASTER skip leading exception ' .
								$day . "\n",3,$this->logfile);
						}
						unset($exceptions[$key]);
					}
				}
				$event['recur_exception'] = $exceptions;
			}
			*/
			$updated_id = false;
			$event_info = $this->get_event_info($event);

			// common adjustments for existing events
			if (is_array($event_info['stored_event']))
			{
				if (empty($event['uid']))
				{
					$event['uid'] = $event_info['stored_event']['uid']; // restore the UID if it was not delivered
				}
				elseif (empty($event['id']))
				{
					$event['id'] = $event_info['stored_event']['id']; // CalDAV does only provide UIDs
				}
				if ($merge)
				{
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							"()[MERGE]\n",3,$this->logfile);
					}

					// overwrite with server data for merge
					foreach ($event_info['stored_event'] as $key => $value)
					{
						switch ($key)
						{
							case 'participants_types':
								continue;

							case 'participants':
								foreach ($event_info['stored_event']['participants'] as $uid => $status)
								{
									// Is a participant and no longer present in the event?
									if (!isset($event['participants'][$uid]))
									{
										// Add it back in
										$event['participants'][$uid] = $status;
									}
								}
								break;

							default:
								if (!empty($value)) $event[$key] = $value;
						}
					}
				}
				else
				{
					// no merge
					if(!isset($this->supportedFields['category']) || !isset($event['category']))
					{
						$event['category'] = $event_info['stored_event']['category'];
					}
					if (!isset($this->supportedFields['participants'])
						|| !$event['participants']
						|| !is_array($event['participants'])
						|| !count($event['participants']))
					{
						if ($this->log)
						{
							error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							"() No participants\n",3,$this->logfile);
						}

						// If this is an updated meeting, and the client doesn't support
						// participants OR the event no longer contains participants, add them back
						unset($event['participants']);
					}
					else
					{
						// if the client does not return a status, we restore the original one
						foreach ($event['participants'] as $uid => $status)
						{
							// Is it a resource and no longer present in the event?
							if ($status[0] == 'X')
							{
								if (isset($event_info['stored_event']['participants'][$uid]))
								{
									if ($this->log)
									{
										error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
											"() Restore status for $uid\n",3,$this->logfile);
									}
									$event['participants'][$uid] = $event_info['stored_event']['participants'][$uid];
								}
								else
								{
									$event['participants'][$uid] = calendar_so::combine_status('U');
								}
							}
						}
						foreach ($event_info['stored_event']['participants'] as $uid => $status)
						{
							// Is it a resource and no longer present in the event?
							if ($uid[0] == 'r' && !isset($event['participants'][$uid]))
							{
								if ($this->log)
								{
									error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
										"() Restore resource $uid to status $status\n",3,$this->logfile);
								}
								// Add it back in
								$event['participants'][$uid] = $status;
							}
						}
					}

					if ($event['whole_day'] && $event['tzid'] != $event_info['stored_event']['tzid'])
					{
						// Adjust dates to original TZ
						$time = new egw_time($event['start'],egw_time::$server_timezone);
						$time =& $this->so->startOfDay($time, $event_info['stored_event']['tzid']);
						$event['start'] = egw_time::to($time,'server');
						$time = new egw_time($event['end'],egw_time::$server_timezone);
						$time =& $this->so->startOfDay($time, $event_info['stored_event']['tzid']);
						$time->setTime(23, 59, 59);
						$event['end'] = egw_time::to($time,'server');
						if ($event['recur_type'] != MCAL_RECUR_NONE)
						{
							foreach ($event['recur_exception'] as $key => $day)
							{
								$time = new egw_time($day,egw_time::$server_timezone);
								$time =& $this->so->startOfDay($time, $event_info['stored_event']['tzid']);
								$event['recur_exception'][$key] = egw_time::to($time,'server');
							}
						}
						elseif($event['recurrence'])
						{
							$time = new egw_time($event['recurrence'],egw_time::$server_timezone);
							$time =& $this->so->startOfDay($time, $event_info['stored_event']['tzid']);
							$event['recurrence'] = egw_time::to($time,'server');
						}
					}

					calendar_rrule::rrule2tz($event, $event_info['stored_event']['start'],
						$event_info['stored_event']['tzid']);

					$event['tzid'] = $event_info['stored_event']['tzid'];
					// avoid that iCal changes the organizer, which is not allowed
					$event['owner'] = $event_info['stored_event']['owner'];
				}
			}
			else // common adjustments for new events
			{
				unset($event['id']);
				// set non blocking all day depending on the user setting
				if (isset($event['whole_day']) && $this->nonBlockingAllday)
				{
					$event['non_blocking'] = 1;
				}

				if (!is_null($user))
				{
					if ($this->check_perms(EGW_ACL_ADD, 0, $user))
					{
						$event['owner'] = $user;
					}
					else
					{
						return false; // no permission
					}
				}
				// check if an owner is set and the current user has add rights
				// for that owners calendar; if not set the current user
				elseif (!isset($event['owner'])
					|| !$this->check_perms(EGW_ACL_ADD, 0, $event['owner']))
				{
					$event['owner'] = $this->user;
				}

				if (!$event['participants']
					|| !is_array($event['participants'])
					|| !count($event['participants']))
				{
					$status = $event['owner'] == $this->user ? 'A' : 'U';
					$status = calendar_so::combine_status($status, 1, 'CHAIR');
					$event['participants'] = array($event['owner'] => $status);
				}
				else
				{
					foreach ($event['participants'] as $uid => $status)
					{
						// if the client did not give us a proper status => set default
						if ($status[0] == 'X')
						{
							if ($uid == $event['owner'])
							{
								$event['participants']['uid'] = calendar_so::combine_status('A', 1, 'CHAIR');
							}
							else
							{
								$event['participants']['uid'] = calendar_so::combine_status('U');
							}
						}
					}
				}
			}

			// update alarms depending on the given event type
			if (count($event['alarm']) > 0 || isset($this->supportedFields['alarm']))
			{
				switch ($event_info['type'])
				{
					case 'SINGLE':
					case 'SERIES-MASTER':
					case 'SERIES-EXCEPTION':
					case 'SERIES-EXCEPTION-PROPAGATE':
						if (count($event['alarm']) > 0)
						{
							foreach ($event['alarm'] as $newid => &$alarm)
							{
								if (!isset($alarm['offset']) && isset($alarm['time']))
								{
									$alarm['offset'] = $event['start'] - $alarm['time'];
								}
								if (!isset($alarm['time']) && isset($alarm['offset']))
								{
									$alarm['time'] = $event['start'] - $alarm['offset'];
								}
								$alarm['owner'] = $this->user;
								$alarm['all'] = false;

								if (is_array($event_info['stored_event'])
										&& count($event_info['stored_event']['alarm']) > 0)
								{
									foreach ($event_info['stored_event']['alarm'] as $alarm_id => $alarm_data)
									{
										if ($alarm['time'] == $alarm_data['time'] &&
											($alarm_data['all'] || $alarm_data['owner'] == $this->user))
										{
											unset($event['alarm'][$newid]);
											unset($event_info['stored_event']['alarm'][$alarm_id]);
											continue;
										}
									}
								}
							}
						}
						break;

					case 'SERIES-PSEUDO-EXCEPTION':
						// nothing to do here
						break;
				}
				if (is_array($event_info['stored_event'])
						&& count($event_info['stored_event']['alarm']) > 0)
				{
					foreach ($event_info['stored_event']['alarm'] as $alarm_id => $alarm_data)
					{
						// only touch own alarms
						if ($alarm_data['all'] == false && $alarm_data['owner'] == $this->user)
						{
							$this->delete_alarm($alarm_id);
						}
					}
				}
			}

			// save event depending on the given event type
			switch ($event_info['type'])
			{
				case 'SINGLE':
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							"(): event SINGLE\n",3,$this->logfile);
					}

					// update the event
					if ($event_info['acl_edit'])
					{
						// Force SINGLE
						$event['reference'] = 0;
						$event_to_store = $event; // prevent $event from being changed by the update method
						$this->server2usertime($event_to_store);
						$updated_id = $this->update($event_to_store, true);
						unset($event_to_store);
					}
					break;

				case 'SERIES-MASTER':
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							"(): event SERIES-MASTER\n",3,$this->logfile);
					}

					// remove all known pseudo exceptions and update the event
					if ($event_info['acl_edit'])
					{
						$filter = isset($this->supportedFields['participants']) ? 'map' : 'tz_map';
						$days = $this->so->get_recurrence_exceptions($event_info['stored_event'], $this->tzid, 0, 0, $filter);
						if ($this->log)
						{
							error_log(__FILE__.'['.__LINE__.'] '.__METHOD__."(EXCEPTIONS MAPPING):\n" .
								array2string($days)."\n",3,$this->logfile);
						}
						if (is_array($days))
						{
							$recur_exceptions = array();
							/*
							if (!isset($days[$event_info['stored_event']['start']]) &&
								$event_info['stored_event']['start'] < $event['start'])
							{
								// We started with a pseudo exception and moved the
								// event start therefore to the future; let's try to go back
								$exceptions = $this->so->get_recurrence_exceptions($event_info['stored_event'], $this->tzid, 0, 0, 'rrule');
								if ($this->log)
								{
									error_log(__FILE__.'['.__LINE__.'] '.__METHOD__."(START ADJUSTMENT):\n" .
										array2string($exceptions)."\n",3,$this->logfile);
								}
								$startdate = $event_info['stored_event']['start'];
								$length = $event['end'] - $event['start'];
								$rriter = calendar_rrule::event2rrule($event_info['stored_event'], false);
								$rriter->rewind();
								do
								{
									// start is a pseudo excpetion for sure
									$rriter->next_no_exception();
									$day = $this->date2ts($rriter->current());
									if ($this->log)
									{
										error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
											'(): event SERIES-MASTER try leading pseudo exception ' .
											$day . "\n",3,$this->logfile);
									}
									if ($day >= $event['start']) break;
									if (!isset($exceptions[$day]))
									{
										// all leading occurrences have to be exceptions;
										// if not -> no restore
										if ($this->log)
										{
											error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
												'(): event SERIES-MASTER pseudo exception series broken at ' .
												$rriter->current() . "!\n",3,$this->logfile);
										}
										$startdate = $event['start'];
										$recur_exceptions = array();
										break;
									}
									$recur_exceptions[] = $day;
								} while ($rriter->valid());

								$event['start'] = $startdate;
								$event['end'] = $startdate + $length;
							} */

							foreach ($event['recur_exception'] as $recur_exception)
							{
								if (isset($days[$recur_exception]))
								{
									$recur_exceptions[] = $days[$recur_exception];
								}
							}
							$event['recur_exception'] = $recur_exceptions;
						}

						$event_to_store = $event; // prevent $event from being changed by the update method
						$this->server2usertime($event_to_store);
						$updated_id = $this->update($event_to_store, true);
						unset($event_to_store);
					}
					break;

				case 'SERIES-EXCEPTION':
				case 'SERIES-EXCEPTION-PROPAGATE':
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							"(): event SERIES-EXCEPTION\n",3,$this->logfile);
					}

					// update event
					if ($event_info['acl_edit'])
					{
						if (isset($event_info['stored_event']['id']))
						{
							// We update an existing exception
							$event['id'] = $event_info['stored_event']['id'];
							$event['category'] = $event_info['stored_event']['category'];
						}
						else
						{
							// We create a new exception
							unset($event['id']);
							unset($event_info['stored_event']);
							$event['recur_type'] = MCAL_RECUR_NONE;
							$event_info['master_event']['recur_exception'] =
								array_unique(array_merge($event_info['master_event']['recur_exception'],
									array($event['recurrence'])));
							/*
							// Adjust the event start -- must not be an exception
							$length = $event_info['master_event']['end'] - $event_info['master_event']['start'];
							$rriter = calendar_rrule::event2rrule($event_info['master_event'], false);
							$rriter->rewind();
							if ($rriter->valid())
							{
								$newstart = egw_time::to($rriter->current, 'server');
								foreach($event_info['master_event']['recur_exception'] as $key => $day)
								{
									// remove leading exceptions
									if ($day < $newstart)
									{
										if (($foundEvents = $this->find_event(
											array('uid' => $event_info['master_event']['uid'],
												'recurrence' => $day), 'exact')) &&
												($eventId = array_shift($foundEvents)) &&
												($exception = read($eventId, 0, 'server')))
										{
											// Unlink this exception
											unset($exception['uid']);
											$this->update($exception, true);
										}
										if ($event['recurrence'] == $day)
										{
											// Unlink this exception
											unset($event['uid']);
										}
										unset($event_info['master_event']['recur_exception'][$key]);
									}
								}
							}
							if ($event_info['master_event']['start'] < $newstart)
							{
								$event_info['master_event']['start'] = $newstart;
								$event_info['master_event']['end'] = $newstart + $length;
							}*/
							$event['reference'] = $event_info['master_event']['id'];
							$event['category'] = $event_info['master_event']['category'];
							$event['owner'] = $event_info['master_event']['owner'];
							$event_to_store = $event_info['master_event']; // prevent the master_event from being changed by the update method
							$this->server2usertime($event_to_store);
							$this->update($event_to_store, true);
							unset($event_to_store);
						}

						$event_to_store = $event; // prevent $event from being changed by update method
						$this->server2usertime($event_to_store);
						$updated_id = $this->update($event_to_store, true);
						unset($event_to_store);
					}
					break;

				case 'SERIES-PSEUDO-EXCEPTION':
					if ($this->log)
					{
						error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
							"(): event SERIES-PSEUDO-EXCEPTION\n",3,$this->logfile);
					}
					//Horde::logMessage('importVCAL event SERIES-PSEUDO-EXCEPTION',
					//	__FILE__, __LINE__, PEAR_LOG_DEBUG);

					if ($event_info['acl_edit'])
					{
						// truncate the status only exception from the series master
						$recur_exceptions = array();
						foreach ($event_info['master_event']['recur_exception'] as $recur_exception)
						{
							if ($recur_exception != $event['recurrence'])
							{
								$recur_exceptions[] = $recur_exception;
							}
						}
						$event_info['master_event']['recur_exception'] = $recur_exceptions;

						// save the series master with the adjusted exceptions
						$event_to_store = $event_info['master_event']; // prevent the master_event from being changed by the update method
						$this->server2usertime($event_to_store);
						$updated_id = $this->update($event_to_store, true, true, false, false);
						unset($event_to_store);
					}

					break;
			}

			// read stored event into info array for fresh stored (new) events
			if (!is_array($event_info['stored_event']) && $updated_id > 0)
			{
				$event_info['stored_event'] = $this->read($updated_id, 0, false, 'server');
			}

			if (isset($event['participants']))
			{
				// update status depending on the given event type
				switch ($event_info['type'])
				{
					case 'SINGLE':
					case 'SERIES-MASTER':
					case 'SERIES-EXCEPTION':
					case 'SERIES-EXCEPTION-PROPAGATE':
						if (is_array($event_info['stored_event'])) // status update requires a stored event
						{
							if ($event_info['acl_edit'])
							{
								// update all participants if we have the right to do that
								$this->update_status($event, $event_info['stored_event']);
							}
							elseif (isset($event['participants'][$this->user]) || isset($event_info['stored_event']['participants'][$this->user]))
							{
								// update the users status only
								$this->set_status($event_info['stored_event']['id'], $this->user,
									($event['participants'][$this->user] ? $event['participants'][$this->user] : 'R'), 0, true);
							}
						}
						break;

					case 'SERIES-PSEUDO-EXCEPTION':
						if (is_array($event_info['master_event'])) // status update requires a stored master event
						{
							$recurrence = $this->date2usertime($event['recurrence']);
							if ($event_info['acl_edit'])
							{
								// update all participants if we have the right to do that
								$this->update_status($event, $event_info['stored_event'], $recurrence);
							}
							elseif (isset($event['participants'][$this->user]) || isset($event_info['master_event']['participants'][$this->user]))
							{
								// update the users status only
								$this->set_status($event_info['master_event']['id'], $this->user,
									($event['participants'][$this->user] ? $event['participants'][$this->user] : 'R'), $recurrence, true);
							}
						}
						break;
				}
			}

			// choose which id to return to the client
			switch ($event_info['type'])
			{
				case 'SINGLE':
				case 'SERIES-MASTER':
				case 'SERIES-EXCEPTION':
					$return_id = is_array($event_info['stored_event']) ? $event_info['stored_event']['id'] : false;
					break;

				case 'SERIES-PSEUDO-EXCEPTION':
					$return_id = is_array($event_info['master_event']) ? $event_info['master_event']['id'] . ':' . $event['recurrence'] : false;
					break;

				case 'SERIES-EXCEPTION-PROPAGATE':
					if ($event_info['acl_edit'] && is_array($event_info['stored_event']))
					{
						// we had sufficient rights to propagate the status only exception to a real one
						$return_id = $event_info['stored_event']['id'];
					}
					else
					{
						// we did not have sufficient rights to propagate the status only exception to a real one
						// we have to keep the SERIES-PSEUDO-EXCEPTION id and keep the event untouched
						$return_id = $event_info['master_event']['id'] . ':' . $event['recurrence'];
					}
					break;
			}

			if ($this->log)
			{
				$event_info['stored_event'] = $this->read($event_info['stored_event']['id']);
				error_log(__FILE__.'['.__LINE__.'] '.__METHOD__."()[$updated_id]\n" .
					array2string($event_info['stored_event'])."\n",3,$this->logfile);
			}
		}
		return $return_id;
	}

	/**
	 * get the value of an attribute by its name
	 *
	 * @param array $attributes
	 * @param string $name eg. 'DTSTART'
	 * @param string $what='value'
	 * @return mixed
	 */
	static function _get_attribute($components,$name,$what='value')
	{
		foreach ($components as $attribute)
		{
			if ($attribute['name'] == $name)
			{
				return !$what ? $attribute : $attribute[$what];
			}
		}
		return false;
	}

	static function valarm2egw(&$alarms, &$valarm)
	{
		$count = 0;
		foreach ($valarm->_attributes as $vattr)
		{
			switch ($vattr['name'])
			{
				case 'TRIGGER':
					$vtype = (isset($vattr['params']['VALUE']))
						? $vattr['params']['VALUE'] : 'DURATION'; //default type
					switch ($vtype)
					{
						case 'DURATION':
							if (isset($vattr['params']['RELATED'])
								&& $vattr['params']['RELATED'] != 'START')
							{
								error_log("Unsupported VALARM offset anchor ".$vattr['params']['RELATED']);
							}
							else
							{
								$alarms[] = array('offset' => -$vattr['value']);
								$count++;
							}
							break;
						case 'DATE-TIME':
							$alarms[] = array('time' => $vattr['value']);
							$count++;
							break;
						default:
							// we should also do ;RELATED=START|END
							error_log('VALARM/TRIGGER: unsupported value type:' . $vtype);
					}
					break;
				case 'ACTION':
				case 'DISPLAY':
				case 'DESCRIPTION':
				case 'SUMMARY':
				case 'ATTACH':
				case 'ATTENDEE':
					// we ignore these fields silently
				 	break;

				default:
					error_log('VALARM field ' .$vattr['name'] . ': ' . $vattr['value'] . ' HAS NO CONVERSION YET');
			}
		}
		return $count;
	}

	function setSupportedFields($_productManufacturer='', $_productName='')
	{
		$state =& $_SESSION['SyncML.state'];
		if (isset($state))
		{
			$deviceInfo = $state->getClientDeviceInfo();
		}

		// store product manufacturer and name for further usage
		if ($_productManufacturer)
		{
				$this->productManufacturer = strtolower($_productManufacturer);
				$this->productName = strtolower($_productName);
		}

		if (isset($deviceInfo) && is_array($deviceInfo))
		{
			/*
			if ($this->log)
			{
				error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
					'() ' . array2string($deviceInfo) . "\n",3,$this->logfile);
			}
			*/
			if (isset($deviceInfo['uidExtension']) &&
				$deviceInfo['uidExtension'])
			{
				$this->uidExtension = true;
			}
			if (isset($deviceInfo['nonBlockingAllday']) &&
				$deviceInfo['nonBlockingAllday'])
			{
				$this->nonBlockingAllday = true;
			}
			if (isset($deviceInfo['tzid']) &&
				$deviceInfo['tzid'])
			{
				switch ($deviceInfo['tzid'])
				{
					case 1:
						$this->tzid = false; // use event's TZ
						break;
					case 2:
						$this->tzid = null; // use UTC for export
						break;
					default:
						$this->tzid = $deviceInfo['tzid'];
				}
			}
			elseif (strpos($this->productName, 'palmos') !== false)
			{
				// for palmos we have to use user-time and NO timezone
				$this->tzid = false;
			}

			if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_owner']))
			{
				$owner = $GLOBALS['egw_info']['user']['preferences']['syncml']['calendar_owner'];
				if ($owner == 0)
				{
					$owner = $GLOBALS['egw_info']['user']['account_primary_group'];
				}
				if (0 < (int)$owner && $this->check_perms(EGW_ACL_EDIT,0,$owner))
				{
					$this->calendarOwner = $owner;
				}
			}
			if (!isset($this->productManufacturer) ||
				 $this->productManufacturer == '' ||
				 $this->productManufacturer == 'file')
			{
				$this->productManufacturer = strtolower($deviceInfo['manufacturer']);
			}
			if (!isset($this->productName) || $this->productName == '')
			{
				$this->productName = strtolower($deviceInfo['model']);
			}
		}

		$defaultFields['minimal'] = array(
			'public'			=> 'public',
			'description'		=> 'description',
			'end'				=> 'end',
			'start'				=> 'start',
			'location'			=> 'location',
			'recur_type'		=> 'recur_type',
			'recur_interval'	=> 'recur_interval',
			'recur_data'		=> 'recur_data',
			'recur_enddate'		=> 'recur_enddate',
			'recur_exception'	=> 'recur_exception',
			'title'				=> 'title',
			'alarm'				=> 'alarm',
		);

		$defaultFields['basic'] = $defaultFields['minimal'] + array(
			'priority'			=> 'priority',
		);

		$defaultFields['nexthaus'] = $defaultFields['basic'] + array(
			'participants'		=> 'participants',
			'uid'				=> 'uid',
		);

		$defaultFields['s60'] = $defaultFields['basic'] + array(
			'category'			=> 'category',
			'uid'				=> 'uid',
		);

		$defaultFields['synthesis'] = $defaultFields['basic'] + array(
			'participants'		=> 'participants',
			'owner'				=> 'owner',
			'category'			=> 'category',
			'non_blocking'		=> 'non_blocking',
			'uid'				=> 'uid',
			'recurrence'		=> 'recurrence',
			'etag'				=> 'etag',
		);

		$defaultFields['funambol'] = $defaultFields['basic'] + array(
			'participants'		=> 'participants',
			'owner'				=> 'owner',
			'category'			=> 'category',
			'non_blocking'		=> 'non_blocking',
		);

		$defaultFields['evolution'] = $defaultFields['basic'] + array(
			'participants'		=> 'participants',
			'owner'				=> 'owner',
			'category'			=> 'category',
			'uid'				=> 'uid',
		);

		$defaultFields['full'] = $defaultFields['basic'] + array(
			'participants'		=> 'participants',
			'owner'				=> 'owner',
			'category'			=> 'category',
			'non_blocking'		=> 'non_blocking',
			'uid'				=> 'uid',
			'recurrence'		=> 'recurrence',
			'etag'				=> 'etag',
			'status'			=> 'status',
		);


		switch ($this->productManufacturer)
		{
			case 'nexthaus corporation':
			case 'nexthaus corp':
				switch ($this->productName)
				{
					default:
						$this->supportedFields = $defaultFields['nexthaus'];
						break;
				}
				break;

			// multisync does not provide anymore information then the manufacturer
			// we suppose multisync with evolution
			case 'the multisync project':
				switch ($this->productName)
				{
					default:
						$this->supportedFields = $defaultFields['basic'];
						break;
				}
				break;

			case 'siemens':
				switch ($this->productName)
				{
					case 'sx1':
						$this->supportedFields = $defaultFields['minimal'];
						break;
					default:
						error_log("Unknown Siemens phone '$_productName', using minimal set");
						$this->supportedFields = $defaultFields['minimal'];
						break;
				}
				break;

			case 'nokia':
				switch ($this->productName)
				{
					case 'e61':
						$this->supportedFields = $defaultFields['minimal'];
						break;
					case 'e51':
					case 'e90':
					case 'e71':
					case 'e66':
					case '6120c':
					case 'nokia 6131':
					case 'n97 mini':
						$this->supportedFields = $defaultFields['s60'];
						break;
					default:
						error_log("Unknown Nokia phone '$_productName', assuming E61");
						$this->supportedFields = $defaultFields['minimal'];
						break;
				}
				break;

			case 'sonyericsson':
			case 'sony ericsson':
				switch ($this->productName)
				{
					case 'd750i':
					case 'p910i':
					case 'g705i':
					case 'w890i':
						$this->supportedFields = $defaultFields['basic'];
						break;
					default:
						error_log("Unknown Sony Ericsson phone '$this->productName' assuming d750i");
						$this->supportedFields = $defaultFields['basic'];
						break;
				}
				break;

			case 'synthesis ag':
				switch ($this->productName)
				{
					case 'sysync client pocketpc std':
					case 'sysync client pocketpc pro':
					case 'sysync client iphone contacts':
					case 'sysync client iphone contacts+todoz':
					default:
						$this->supportedFields = $defaultFields['synthesis'];
						break;
				}
				break;

			//Syncevolution compatibility
			case 'patrick ohly':
				$this->supportedFields = $defaultFields['evolution'];
				break;

			case '': // seems syncevolution 0.5 doesn't send a manufacturer
				error_log("No vendor name, assuming syncevolution 0.5");
				$this->supportedFields = $defaultFields['evolution'];
				break;

			case 'file':	// used outside of SyncML, eg. by the calendar itself ==> all possible fields
				$this->tzid = false; // use event's TZ
				$this->supportedFields = $defaultFields['full'];
				break;

			case 'groupdav':		// all GroupDAV access goes through here
				$this->tzid = false; // use event's TZ
				switch ($this->productName)
				{
					default:
						$this->supportedFields = $defaultFields['full'];
				}
				break;

			case 'funambol':
				$this->supportedFields = $defaultFields['funambol'];
				break;

			// the fallback for SyncML
			default:
				error_log("Unknown calendar SyncML client: manufacturer='$this->productManufacturer'  product='$this->productName'");
				$this->supportedFields = $defaultFields['synthesis'];
		}

		if ($this->log)
		{
			error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
				'(' . $this->productManufacturer .
				', '. $this->productName .', ' .
				($this->tzid ? $this->tzid : egw_time::$user_timezone->getName()) .
				")\n" , 3, $this->logfile);
		}

		//Horde::logMessage('setSupportedFields(' . $this->productManufacturer . ', '
		//	. $this->productName .', ' .
		//	($this->tzid ? $this->tzid : egw_time::$user_timezone->getName()) .')',
		//	__FILE__, __LINE__, PEAR_LOG_DEBUG);
	}

	/**
	 * Convert vCalendar data in EGw events
	 *
	 * @param string $_vcalData
	 * @param string $principalURL='' Used for CalDAV imports
	 * @return array|boolean events on success, false on failure
	 */
	function icaltoegw($_vcalData, $principalURL='')
	{
		if ($this->log)
		{
			error_log(__FILE__.'['.__LINE__.'] '.__METHOD__."($principalURL)\n" .
				array2string($_vcalData)."\n",3,$this->logfile);
		}

		$events = array();

		if ($this->tzid)
		{
			$tzid = $this->tzid;
		}
		else
		{
			$tzid = egw_time::$user_timezone->getName();
		}

		date_default_timezone_set($tzid);

		$vcal = new Horde_iCalendar;
		if (!$vcal->parsevCalendar($_vcalData))
		{
			if ($this->log)
			{
				error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.
					"(): No vCalendar Container found!\n",3,$this->logfile);
			}
			if ($this->tzid)
			{
				date_default_timezone_set($GLOBALS['egw_info']['server']['server_timezone']);
			}
			return false;
		}
		$version = $vcal->getAttribute('VERSION');
		if (!is_array($this->supportedFields)) $this->setSupportedFields();

		foreach ($vcal->getComponents() as $component)
		{
			if (is_a($component, 'Horde_iCalendar_vevent'))
			{
				if (($event = $this->vevent2egw($component, $version, $this->supportedFields, $principalURL)))
				{
					//common adjustments
					if ($this->productManufacturer == '' && $this->productName == ''
						&& !empty($event['recur_enddate']))
					{
						// syncevolution needs an adjusted recur_enddate
						$event['recur_enddate'] = (int)$event['recur_enddate'] + 86400;
					}
 					if ($event['recur_type'] != MCAL_RECUR_NONE)
 					{
 						// No reference or RECURRENCE-ID for the series master
 						$event['reference'] = $event['recurrence'] = 0;
 					}

 					// handle the alarms
 					$alarms = $event['alarm'];
					foreach ($component->getComponents() as $valarm)
					{
						if (is_a($valarm, 'Horde_iCalendar_valarm'))
						{
							$this->valarm2egw($alarms, $valarm);
						}
					}
					$event['alarm'] = $alarms;
					if ($this->tzid || empty($event['tzid']))
					{
						$event['tzid'] = $tzid;
					}

					$events[] = $event;
				}
			}
			else
			{
				if ($this->log)
				{
					error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.'()' .
						get_class($component)." found\n",3,$this->logfile);
				}
			}
		}

		date_default_timezone_set($GLOBALS['egw_info']['server']['server_timezone']);

		return $events;
	}

	/**
	 * Parse a VEVENT
	 *
	 * @param array $component			VEVENT
	 * @param string $version			vCal version (1.0/2.0)
	 * @param array $supportedFields	supported fields of the device
	 * @param string $principalURL=''	Used for CalDAV imports
	 *
	 * @return array|boolean			event on success, false on failure
	 */
	function vevent2egw(&$component, $version, $supportedFields, $principalURL='')
	{
		if (!is_a($component, 'Horde_iCalendar_vevent'))
		{
			if ($this->log)
			{
				error_log(__FILE__.'['.__LINE__.'] '.__METHOD__.'()' .
					get_class($component)." found\n",3,$this->logfile);
			}
			return false;
		}

		if (isset($GLOBALS['egw_info']['user']['preferences']['syncml']['minimum_uid_length'])) {
			$minimum_uid_length = $GLOBALS['egw_info']['user']['preferences']['syncml']['minimum_uid_length'];
		}
		else
		{
			$minimum_uid_length = 8;
		}

		$isDate = false;
		$event		= array();
		$alarms		= array();
		$vcardData	= array(
			'recur_type'		=> MCAL_RECUR_NONE,
			'recur_exception'	=> array(),
		);
		// we parse DTSTART and DTEND first
		foreach ($component->_attributes as $attributes)
		{
			switch ($attributes['name'])
			{
				case 'DTSTART':
					if (isset($attributes['params']['VALUE'])
							&& $attributes['params']['VALUE'] == 'DATE')
					{
						$isDate = true;
					}
					$dtstart_ts = is_numeric($attributes['value']) ? $attributes['value'] : $this->date2ts($attributes['value']);
					$vcardData['start']	= $dtstart_ts;

					if ($this->tzid)
					{
						$event['tzid'] = $this->tzid;
					}
					else
					{
						$event['tzid'] =  date_default_timezone_get();

						if (!empty($attributes['params']['TZID']))
						{
							// import TZID, if PHP understands it (we only care about TZID of starttime,
							// as we store only a TZID for the whole event)
							try
							{
								$tz = calendar_timezones::DateTimeZone($attributes['params']['TZID']);
								$event['tzid'] = $tz->getName();
							}
							catch(Exception $e)
							{
								error_log(__METHOD__ . '() unknown TZID='
									. $attributes['params']['TZID'] . ', defaulting to timezone "'
									. date_default_timezone_get() . '".');
								$event['tzid'] = date_default_timezone_get();	// default to current timezone
							}
						}
					}
					break;

				case 'DTEND':
					$dtend_ts = is_numeric($attributes['value']) ? $attributes['value'] : $this->date2ts($attributes['value']);
					if (date('H:i:s',$dtend_ts) == '00:00:00')
					{
						$dtend_ts -= 1;
					}
					$vcardData['end']	= $dtend_ts;
			}
		}
		if (!isset($vcardData['start']))
		{
			if ($this->log)
			{
				error_log(__FILE__.'['.__LINE__.'] '.__METHOD__
					. "() DTSTART missing!\n",3,$this->logfile);
			}
			return false; // not a valid entry
		}
		// lets see what we can get from the vcard
		foreach ($component->_attributes as $attributes)
		{
			switch ($attributes['name'])
			{
				case 'AALARM':
				case 'DALARM':
					$alarmTime = $attributes['value'];
					$alarms[$alarmTime] = array(
						'time' => $alarmTime
					);
					break;
				case 'CLASS':
					$vcardData['public'] = (int)(strtolower($attributes['value']) == 'public');
					break;
				case 'DESCRIPTION':
					$vcardData['description'] = str_replace("\r\n", "\n", $attributes['value']);
					if (preg_match('/\s*\[UID:(.+)?\]/Usm', $attributes['value'], $matches))
					{
						if (!isset($vCardData['uid'])
								&& strlen($matches[1]) >= $minimum_uid_length)
						{
							$vcardData['uid'] = $matches[1];
						}
					}
					break;
				case 'RECURRENCE-ID':
				case 'X-RECURRENCE-ID':
					$vcardData['recurrence'] = $attributes['value'];
					break;
				case 'LOCATION':
					$vcardData['location']	= str_replace("\r\n", "\n", $attributes['value']);
					break;
				case 'RRULE':
					$recurence = $attributes['value'];
					$vcardData['recur_interval'] = 1;
					$type = preg_match('/FREQ=([^;: ]+)/i',$recurence,$matches) ? $matches[1] : $recurence[0];
					// vCard 2.0 values for all types
					if (preg_match('/UNTIL=([0-9TZ]+)/',$recurence,$matches))
					{
						$vcardData['recur_enddate'] = $this->vCalendar->_parseDateTime($matches[1]);
					}
					elseif (preg_match('/COUNT=([0-9]+)/',$recurence,$matches))
					{
						$vcardData['recur_count'] = (int)$matches[1];
					}
					if (preg_match('/INTERVAL=([0-9]+)/',$recurence,$matches))
					{
						$vcardData['recur_interval'] = (int) $matches[1] ? (int) $matches[1] : 1;
					}
					$vcardData['recur_data'] = 0;
					switch($type)
					{
						case 'W':
						case 'WEEKLY':
							$days = array();
							if (preg_match('/W(\d+) *((?i: [AEFHMORSTUW]{2})+)?( +([^ ]*))$/',$recurence, $recurenceMatches))		// 1.0
							{
								$vcardData['recur_interval'] = $recurenceMatches[1];
								if (empty($recurenceMatches[2]))
								{
									$days[0] = strtoupper(substr(date('D', $vcardData['start']),0,2));
								}
								else
								{
									$days = explode(' ',trim($recurenceMatches[2]));
								}

								if (preg_match('/#(\d+)/',$recurenceMatches[4],$repeatMatches))
								{
									if ($repeatMatches[1]) $vcardData['recur_count'] = $repeatMatches[1];
								}
								else
								{
									$vcardData['recur_enddate'] = $this->vCalendar->_parseDateTime($recurenceMatches[4]);
								}

								$recur_days = $this->recur_days_1_0;
							}
							elseif (preg_match('/BYDAY=([^;: ]+)/',$recurence,$recurenceMatches))	// 2.0
							{
								$days = explode(',',$recurenceMatches[1]);
								$recur_days = $this->recur_days;
							}
							else	// no day given, use the day of dtstart
							{
								$vcardData['recur_data'] |= 1 << (int)date('w',$vcardData['start']);
								$vcardData['recur_type'] = MCAL_RECUR_WEEKLY;
							}
							if ($days)
							{
								foreach ($recur_days as $id => $day)
								{
									if (in_array(strtoupper(substr($day,0,2)),$days))
									{
										$vcardData['recur_data'] |= $id;
									}
								}
								$vcardData['recur_type'] = MCAL_RECUR_WEEKLY;
							}

							if (!empty($vcardData['recur_count']))
							{
								$vcardData['recur_enddate'] = mktime(
									date('H', $vcardData['end']),
									date('i', $vcardData['end']),
									date('s', $vcardData['end']),
									date('m', $vcardData['start']),
									date('d', $vcardData['start']) + ($vcardData['recur_interval']*($vcardData['recur_count']-1)*7),
									date('Y', $vcardData['start']));
							}
							break;

						case 'D':	// 1.0
							if (preg_match('/D(\d+) #(\d+)/', $recurence, $recurenceMatches))
							{
								$vcardData['recur_interval'] = $recurenceMatches[1];
								if ($recurenceMatches[2] > 0 && $vcardData['end'])
								{
									$vcardData['recur_enddate'] = mktime(
										date('H', $vcardData['end']),
										date('i', $vcardData['end']),
										date('s', $vcardData['end']),
										date('m', $vcardData['end']),
										date('d', $vcardData['end']) + ($vcardData['recur_interval']*($recurenceMatches[2]-1)),
										date('Y', $vcardData['end'])
									);
								}
							}
							elseif (preg_match('/D(\d+) (.*)/', $recurence, $recurenceMatches))
							{
								$vcardData['recur_interval'] = $recurenceMatches[1];
								$vcardData['recur_enddate'] = $this->vCalendar->_parseDateTime(trim($recurenceMatches[2]));
							}
							else break;

							// fall-through
						case 'DAILY':	// 2.0
							$vcardData['recur_type'] = MCAL_RECUR_DAILY;

							if (!empty($vcardData['recur_count']))
							{
								$vcardData['recur_enddate'] = mktime(
									date('H', $vcardData['end']),
									date('i', $vcardData['end']),
									date('s', $vcardData['end']),
									date('m', $vcardData['start']),
									date('d', $vcardData['start']) + ($vcardData['recur_interval']*($vcardData['recur_count']-1)),
									date('Y', $vcardData['start']));
							}
							break;

						case 'M':
							if (preg_match('/MD(\d+)(?: [^ ])? #(\d+)/', $recurence, $recurenceMatches))
							{
								$vcardData['recur_type'] = MCAL_RECUR_MONTHLY_MDAY;
								$vcardData['recur_interval'] = $recurenceMatches[1];
								if ($recurenceMatches[2] > 0 && $vcardData['end'])
								{
									$vcardData['recur_enddate'] = mktime(
										date('H', $vcardData['end']),
										date('i', $vcardData['end']),
										date('s', $vcardData['end']),
										date('m', $vcardData['end']) + ($vcardData['recur_interval']*($recurenceMatches[2]-1)),
										date('d', $vcardData['end']),
										date('Y', $vcardData['end'])
									);
								}
							}
							elseif (preg_match('/MD(\d+) (.*)/',$recurence, $recurenceMatches))
							{
								$vcardData['recur_type'] = MCAL_RECUR_MONTHLY_MDAY;
								if ($recurenceMatches[1] > 1)
								{
									$vcardData['recur_interval'] = $recurenceMatches[1];
								}
								$vcardData['recur_enddate'] = $this->vCalendar->_parseDateTime(trim($recurenceMatches[2]));
							}
							elseif (preg_match('/MP(\d+) (.*) (.*) (.*)/',$recurence, $recurenceMatches))
							{
								$vcardData['recur_type'] = MCAL_RECUR_MONTHLY_WDAY;
								$vcardData['recur_interval'] = $recurenceMatches[1];
								if (preg_match('/#(\d+)/',$recurenceMatches[4],$recurenceMatches))
								{
									if ($recurenceMatches[1])
									{
										$vcardData['recur_enddate'] = mktime(
											date('H', $vcardData['end']),
											date('i', $vcardData['end']),
											date('s', $vcardData['end']),
											date('m', $vcardData['start']) + ($vcardData['recur_interval']*($recurenceMatches[1]-1)),
											date('d', $vcardData['start']),
											date('Y', $vcardData['start']));
									}
								}
								else
								{
									$vcardData['recur_enddate'] = $this->vCalendar->_parseDateTime(trim($recurenceMatches[4]));
								}
							}
							break;
						case 'MONTHLY':
							$vcardData['recur_type'] = strpos($recurence,'BYDAY') !== false ?
									MCAL_RECUR_MONTHLY_WDAY : MCAL_RECUR_MONTHLY_MDAY;

							if (!empty($vcardData['recur_count']))
							{
								$vcardData['recur_enddate'] = mktime(
									date('H', $vcardData['end']),
									date('i', $vcardData['end']),
									date('s', $vcardData['end']),
									date('m', $vcardData['start']) + ($vcardData['recur_interval']*($vcardData['recur_count']-1)),
									date('d', $vcardData['start']),
									date('Y', $vcardData['start']));
							}
							break;

						case 'Y':		// 1.0
							if (preg_match('/YM(\d+)[^#]*#(\d+)/', $recurence, $recurenceMatches))
							{
								$vcardData['recur_interval'] = $recurenceMatches[1];
								if ($recurenceMatches[2] > 0 && $vcardData['end'])
								{
									$vcardData['recur_enddate'] = mktime(
										date('H', $vcardData['end']),
										date('i', $vcardData['end']),
										date('s', $vcardData['end']),
										date('m', $vcardData['end']),
										date('d', $vcardData['end']),
										date('Y', $vcardData['end']) + ($recurenceMatches[2] * $vcardData['recur_interval'])
									);
								}
							}
							elseif (preg_match('/YM(\d+) (.*)/',$recurence, $recurenceMatches))
							{
								$vcardData['recur_interval'] = $recurenceMatches[1];
								if ($recurenceMatches[2] != '#0')
								{
									$vcardData['recur_enddate'] = $this->vCalendar->_parseDateTime(trim($recurenceMatches[2]));
								}
							} else break;

							// fall-through
						case 'YEARLY':	// 2.0
							$vcardData['recur_type'] = MCAL_RECUR_YEARLY;

							if (!empty($vcardData['recur_count']))
							{
								$vcardData['recur_enddate'] = mktime(
									date('H', $vcardData['end']),
									date('i', $vcardData['end']),
									date('s', $vcardData['end']),
									date('m', $vcardData['start']),
									date('d', $vcardData['start']),
									date('Y', $vcardData['start']) + ($vcardData['recur_interval']*($vcardData['recur_count']-1)));
							}
							break;
					}
					break;
				case 'EXDATE':
					if (!$attributes['value']) break;
					if ((isset($attributes['params']['VALUE'])
							&& $attributes['params']['VALUE'] == 'DATE') ||
						(!isset($attributes['params']['VALUE']) && $isDate))
					{
						$days = array();
						$hour = date('H', $vcardData['start']);
						$minutes = date('i', $vcardData['start']);
						$seconds = date('s', $vcardData['start']);
						foreach ($attributes['values'] as $day)
						{
							$days[] = mktime(
								$hour,
								$minutes,
								$seconds,
								$day['month'],
								$day['mday'],
								$day['year']);
						}
						$vcardData['recur_exception'] = array_merge($vcardData['recur_exception'], $days);
					}
					else
					{
						$vcardData['recur_exception'] = array_merge($vcardData['recur_exception'], $attributes['values']);
					}
					break;
				case 'SUMMARY':
					$vcardData['title'] = str_replace("\r\n", "\n", $attributes['value']);
					break;
				case 'UID':
					if (strlen($attributes['value']) >= $minimum_uid_length)
					{
						$event['uid'] = $vcardData['uid'] = $attributes['value'];
					}
					break;
				case 'TRANSP':
					if ($version == '1.0')
					{
						$vcardData['non_blocking'] = ($attributes['value'] == 1);
					}
					else
					{
						$vcardData['non_blocking'] = ($attributes['value'] == 'TRANSPARENT');
					}
					break;
				case 'PRIORITY':
					if ($this->productManufacturer == 'funambol' &&
						(strpos($this->productName, 'outlook') !== false
							|| strpos($this->productName, 'pocket pc') !== false))
					{
						$vcardData['priority'] = (int) $this->priority_funambol2egw[$attributes['value']];
					}
					else
					{
						$vcardData['priority'] = (int) $this->priority_ical2egw[$attributes['value']];
					}
					break;
				case 'CATEGORIES':
					if ($attributes['value'])
					{
						$vcardData['category'] = explode(',', $attributes['value']);
					}
					else
					{
						$vcardData['category'] = array();
					}
					break;
				case 'ATTENDEE':
				case 'ORGANIZER':
					if (isset($attributes['params']['PARTSTAT']))
				    {
				    	$attributes['params']['STATUS'] = $attributes['params']['PARTSTAT'];
				    }
				    if (isset($attributes['params']['STATUS']))
					{
						$status = $this->status_ical2egw[strtoupper($attributes['params']['STATUS'])];
						if (empty($status)) $status = 'X';
					}
					else
					{
						$status = 'X'; // client did not return the status
					}
					$uid = $email = $cn = '';
					$quantity = 1;
					$role = 'REQ-PARTICIPANT';
					if (!empty($attributes['params']['ROLE']))
					{
						$role = $attributes['params']['ROLE'];
					}
					// try pricipal url from CalDAV
					if (strpos($attributes['value'], 'http') === 0)
					{
						if (!empty($principalURL) && strstr($attributes['value'], $principalURL) !== false)
						{
							$uid = $this->user;
							if ($this->log)
							{
								error_log(__FILE__.'['.__LINE__.'] '.__METHOD__
									. "(): Found myself: '$uid'\n",3,$this->logfile);
							}
						}
						else
						{
							if ($this->log)
							{
								error_log(__FILE__.'['.__LINE__.'] '.__METHOD__
									. '(): Unknown URI: ' . $attributes['value']
									. "\n",3,$this->logfile);
							}
							$attributes['value'] = '';
						}
					}
					// try X-EGROUPWARE-UID
					if (!$uid && !empty($attributes['params']['X-EGROUPWARE-UID']))
					{
						$uid = $attributes['params']['X-EGROUPWARE-UID'];
						if (!empty($attributes['params']['X-EGROUPWARE-QUANTITY']))
						{
							$quantity = $attributes['params']['X-EGROUPWARE-QUANTITY'];
						}
						if ($this->log)
						{
							error_log(__FILE__.'['.__LINE__.'] '.__METHOD__
								. "(): Found X-EGROUPWARE-UID: '$uid'\n",3,$this->logfile);
						}
					}
					elseif ($attributes['value'] == 'Unknown')
					{
							// we use the current user
							$uid = $this->user;
					}
					// try to find an email address
					elseif (preg_match('/MAILTO:([@.a-z0-9_-]+)|MAILTO:"?([.a-z0-9_ -]*)"?[ ]*<([@.a-z0-9_-]*)>/i',
						$attributes['value'],$matches))
					{
						$email = $matches[1] ? $matches[1] : $matches[3];
						$cn = isset($matches[2]) ? $matches[2]: '';
					}
					elseif (!empty($attributes['value']) &&
						preg_match('/"?([.a-z0-9_ -]*)"?[ ]*<([@.a-z0-9_-]*)>/i',
						$attributes['value'],$matches))
					{
						$cn = $matches[1];
						$email = $matches[2];
					}
					elseif (strpos($attributes['value'],'@') !== false)
					{
						$email = $attributes['value'];
					}
					if (!$uid && $email && ($uid = $GLOBALS['egw']->accounts->name2id($email, 'account_email')))
					{
						// we use the account we found
						if ($this->log)
						{
							error_log(__FILE__.'['.__LINE__.'] '.__METHOD__
								. "() Found account: '$uid', '$cn', '$email'\n",3,$this->logfile);
						}
					}
					if (!$uid)
					{
						$searcharray = array();
						// search for provided email address ...
						if ($email)
						{
							$searcharray = array('email' => $email, 'email_home' => $email);
						}
						// ... and for provided CN
						if (!empty($attributes['params']['CN']))
						{
							if ($attributes['params']['CN'][0] == '"'
								&& substr($attributes['params']['CN'],-1) == '"')
							{
								$cn = substr($attributes['params']['CN'],1,-1);
							}
							$searcharray['n_fn'] = $cn;
						}
						elseif ($cn)
						{
							$searcharray['n_fn'] = $cn;
						}

						//elseif (//$attributes['params']['CUTYPE'] == 'GROUP'
						if (preg_match('/(.*) Group/', $cn, $matches))
						{
							if (($uid =  $GLOBALS['egw']->accounts->name2id($matches[1], 'account_lid', 'g')))
							{
								//Horde::logMessage("vevent2egw: group participant $uid",
								//			__FILE__, __LINE__, PEAR_LOG_DEBUG);
								if (!isset($vcardData['participants'][$this->user]) &&
									$status != 'X' && $status != 'U')
								{
									// User tries to reply to the group invitiation
									$members = $GLOBALS['egw']->accounts->members($uid, true);
									if (in_array($this->user, $members))
									{
										//Horde::logMessage("vevent2egw: set status to " . $status,
										//		__FILE__, __LINE__, PEAR_LOG_DEBUG);
										$vcardData['participants'][$this->user] =
											calendar_so::combine_status($status,$quantity,$role);
									}
								}
								$status = 'U'; // keep the group
							}
							else continue; // can't find this group
						}
						elseif (empty($searcharray))
						{
							continue;	// participants without email AND CN --> ignore it
						}
						elseif ((list($data) = $this->addressbook->search($searcharray,
							array('id','egw_addressbook.account_id as account_id','n_fn'),
							'egw_addressbook.account_id IS NOT NULL DESC, n_fn IS NOT NULL DESC',
							'','',false,'OR')))
						{
							// found an addressbook entry
							$uid = $data['account_id'] ? (int)$data['account_id'] : 'c'.$data['id'];
							if ($this->log)
							{
								error_log(__FILE__.'['.__LINE__.'] '.__METHOD__
									. "() Found addressbook entry: '$uid', '$cn', '$email'\n",3,$this->logfile);
							}
						}
						else
						{
							if (!$email)
							{
								$email = 'no-email@egroupware.org';	// set dummy email to store the CN
							}
							$uid = 'e'. ($cn ? '"' . $cn . '" <' . $email . '>' : $email);
							if ($this->log)
							{
								error_log(__FILE__.'['.__LINE__.'] '.__METHOD__
									. "() Not Found, create dummy: '$uid', '$cn', '$email'\n",3,$this->logfile);
							}
						}
					}
					switch($attributes['name'])
					{
						case 'ATTENDEE':
							if (!isset($attributes['params']['ROLE']) &&
								isset($event['owner']) && $event['owner'] == $uid)
							{
								$attributes['params']['ROLE'] = 'CHAIR';
							}
							if (!isset($vcardData['participants'][$uid]) ||
									$vcardData['participants'][$uid][0] != 'A')
							{
								// for multiple entries the ACCEPT wins
								// add quantity and role
								$vcardData['participants'][$uid] =
									calendar_so::combine_status($status, $quantity, $role);

								if (!$this->calendarOwner && is_numeric($uid) &&
										$role == 'CHAIR' &&
										is_a($component->getAttribute('ORGANIZER'), 'PEAR_Error'))
								{
									// we can store the ORGANIZER as event owner
									$event['owner'] = $uid;
								}
							}
							break;

						case 'ORGANIZER':
							if (isset($vcardData['participants'][$uid]))
							{
								$status = $vcardData['participants'][$uid];
								calendar_so::split_status($status, $quantity, $role);
								$vcardData['participants'][$uid] =
									calendar_so::combine_status($status, $quantity, 'CHAIR');
							}
							if (!$this->calendarOwner && is_numeric($uid))
							{
								// we can store the ORGANIZER as event owner
								$event['owner'] = $uid;
							}
							else
							{
								// we must insert a CHAIR participant to keep the ORGANIZER
								$event['owner'] = $this->user;
								if (!isset($vcardData['participants'][$uid]))
								{
									// save the ORGANIZER as event CHAIR
									$vcardData['participants'][$uid] =
										calendar_so::combine_status('D', 1, 'CHAIR');
								}
							}
					}
					break;
				case 'CREATED':		// will be written direct to the event
					if ($event['modified']) break;
					// fall through
				case 'LAST-MODIFIED':	// will be written direct to the event
					$event['modified'] = $attributes['value'];
			}
		}

		// check if the entry is a birthday
		// this field is only set from NOKIA clients
		$agendaEntryType = $component->getAttribute('X-EPOCAGENDAENTRYTYPE');
		if (!is_a($agendaEntryType, 'PEAR_Error'))
		{
			if (strtolower($agendaEntryType) == 'anniversary')
			{
				$event['special'] = '1';
				$event['non_blocking'] = 1;
				// make it a whole day event for eGW
				$vcardData['end'] = $vcardData['start'] + 86399;
			}
			elseif (strtolower($agendaEntryType) == 'event')
			{
				$event['special'] = '2';
				$event['non_blocking'] = 1;
			}
		}

		$event['priority'] = 2; // default
		$event['alarm'] = $alarms;

		// now that we know what the vard provides,
		// we merge that data with the information we have about the device
		while (($fieldName = array_shift($supportedFields)))
		{
			switch ($fieldName)
			{
				case 'recur_interval':
				case 'recur_enddate':
				case 'recur_data':
				case 'recur_exception':
					// not handled here
					break;

				case 'recur_type':
					$event['recur_type'] = $vcardData['recur_type'];
					if ($event['recur_type'] != MCAL_RECUR_NONE)
					{
						$event['reference'] = 0;
						foreach (array('recur_interval','recur_enddate','recur_data','recur_exception') as $r)
						{
							if (isset($vcardData[$r]))
							{
								$event[$r] = $vcardData[$r];
							}
						}
					}
					break;

				default:
					if (isset($vcardData[$fieldName]))
					{
						$event[$fieldName] = $vcardData[$fieldName];
					}
				break;
			}
		}
		if (!empty($event['recur_enddate']))
		{
			// reset recure_enddate to 00:00:00 on the last day
			$rriter = calendar_rrule::event2rrule($event, false);
			$rriter->rewind();
			$last = clone $rriter->time;
			while ($rriter->current <= $rriter->enddate)
			{
				$last = clone $rriter->current;
				$rriter->next_no_exception();
			}
			$delta = $event['end'] - $event['start'];
			$last->modify('+' . $delta . ' seconds');
			$last->setTime(0, 0, 0);
			$event['recur_enddate'] = egw_time::to($last, 'server');
		}

		if ($this->calendarOwner) $event['owner'] = $this->calendarOwner;

		if ($this->log)
		{
			error_log(__FILE__.'['.__LINE__.'] '.__METHOD__."()\n" .
				array2string($event)."\n",3,$this->logfile);
		}
		//Horde::logMessage("vevent2egw:\n" . print_r($event, true),
        //    	__FILE__, __LINE__, PEAR_LOG_DEBUG);
		return $event;
	}

	function search($_vcalData, $contentID=null, $relax=false)
	{
		if (($events = $this->icaltoegw($_vcalData)))
		{
			// this function only supports searching a single event
			if (count($events) == 1)
			{
				$filter = $relax ? 'relax' : 'check';
				$event = array_shift($events);
				$eventId = -1;
				if ($this->so->isWholeDay($event)) $event['whole_day'] = true;
				if ($contentID)
				{
					$parts = preg_split('/:/', $contentID);
					$event['id'] = $eventId = $parts[0];
				}
				$event['category'] = $this->find_or_add_categories($event['category'], $eventId);
				return $this->find_event($event, $filter);
			}
			if ($this->log)
			{
				error_log(__FILE__.'['.__LINE__.'] '.__METHOD__."() found:\n" .
					array2string($events)."\n",3,$this->logfile);
			}
		}
		return array();
	}

	/**
	 * Create a freebusy vCal for the given user(s)
	 *
	 * @param int $user account_id
	 * @param mixed $end=null end-date, default now+1 month
	 * @param boolean $utc=true if false, use severtime for dates
	 * @return string
	 */
	function freebusy($user,$end=null,$utc=true)
	{
		if (!$end) $end = $this->now_su + 100*DAY_s;	// default next 100 days

		$vcal = new Horde_iCalendar;
		$vcal->setAttribute('PRODID','-//eGroupWare//NONSGML eGroupWare Calendar '.$GLOBALS['egw_info']['apps']['calendar']['version'].'//'.
			strtoupper($GLOBALS['egw_info']['user']['preferences']['common']['lang']));
		$vcal->setAttribute('VERSION','2.0');

		$vfreebusy = Horde_iCalendar::newComponent('VFREEBUSY',$vcal);
		$parameters = array(
			'ORGANIZER' => $GLOBALS['egw']->translation->convert(
				$GLOBALS['egw']->accounts->id2name($user,'account_firstname').' '.
				$GLOBALS['egw']->accounts->id2name($user,'account_lastname'),
				$GLOBALS['egw']->translation->charset(),'utf-8'),
		);
		if ($utc)
		{
			foreach (array(
				'URL' => $this->freebusy_url($user),
				'DTSTART' => $this->date2ts($this->now_su,true),	// true = server-time
				'DTEND' => $this->date2ts($end,true),	// true = server-time
		  		'ORGANIZER' => $GLOBALS['egw']->accounts->id2name($user,'account_email'),
				'DTSTAMP' => time(),
			) as $attr => $value)
			{
				$vfreebusy->setAttribute($attr, $value);
			}
		}
		else
		{
			foreach (array(
				'URL' => $this->freebusy_url($user),
				'DTSTART' => date('Ymd\THis',$this->date2ts($this->now_su,true)),	// true = server-time
				'DTEND' => date('Ymd\THis',$this->date2ts($end,true)),	// true = server-time
		  		'ORGANIZER' => $GLOBALS['egw']->accounts->id2name($user,'account_email'),
				'DTSTAMP' => date('Ymd\THis',time()),
			) as $attr => $value)
			{
				$vfreebusy->setAttribute($attr, $value);
			}
		}
		$fbdata = parent::search(array(
			'start' => $this->now_su,
			'end'   => $end,
			'users' => $user,
			'date_format' => 'server',
			'show_rejected' => false,
		));
		if (is_array($fbdata))
		{
			foreach ($fbdata as $event)
			{
				if ($event['non_blocking']) continue;

				if ($utc)
				{
					$vfreebusy->setAttribute('FREEBUSY',array(array(
						'start' => $event['start'],
						'end' => $event['end'],
					)));
				}
				else
				{
					$vfreebusy->setAttribute('FREEBUSY',array(array(
						'start' => date('Ymd\THis',$event['start']),
						'end' => date('Ymd\THis',$event['end']),
					)));
				}
			}
		}
		$vcal->addComponent($vfreebusy);

		return $vcal->exportvCalendar();
	}
}