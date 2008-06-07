<?php
/**
 * iCal import and export via Horde iCalendar classes
 *
 * @link http://www.egroupware.org
 * @author Lars Kneschke <lkneschke@egroupware.org>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package calendar
 * @subpackage export
 * @version $Id$
 */

require_once EGW_SERVER_ROOT.'/phpgwapi/inc/horde/Horde/iCalendar.php';

/**
 * iCal import and export via Horde iCalendar classes
 */
class boical extends calendar_boupdate
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
	);
	/**
	 * @var array conversation of the participant status ical => egw
	 */
	var $status_ical2egw = array(
		'NEEDS-ACTION' => 'U',
		'ACCEPTED'     => 'A',
		'DECLINED'     => 'R',
		'TENTATIVE'    => 'T',
	);

	/**
	 * @var array $status_ical2egw conversation of the priority egw => ical
	 */
	var $priority_egw2ical = array(
		0 => 0,		// undefined
		1 => 9,		// low
		2 => 5,		// normal
		3 => 1,		// high
	);
	/**
	 * @var array $status_ical2egw conversation of the priority ical => egw
	 */
	var $priority_ical2egw = array(
		0 => 0,		// undefined
		9 => 1,	8 => 1, 7 => 1, 6 => 1,	// low
		5 => 2,		// normal
		4 => 3, 2 => 3, 3 => 3, 1 => 3,	// high
	);

	/**
	 * @var array $recur_egw2ical_2_0 converstaion of egw recur-type => ical FREQ
	 */
	var $recur_egw2ical_2_0 = array(
		MCAL_RECUR_DAILY        => 'DAILY',
		MCAL_RECUR_WEEKLY       => 'WEEKLY',
		MCAL_RECUR_MONTHLY_MDAY => 'MONTHLY',	// BYMONHTDAY={1..31}
		MCAL_RECUR_MONTHLY_WDAY => 'MONTHLY',	// BYDAY={1..5}{MO..SO}
		MCAL_RECUR_YEARLY       => 'YEARLY',
	);

	/**
	 * @var array $recur_egw2ical_1_0 converstaion of egw recur-type => ical FREQ
	 */
	var $recur_egw2ical_1_0 = array(
		MCAL_RECUR_DAILY        => 'D',
		MCAL_RECUR_WEEKLY       => 'W',
		MCAL_RECUR_MONTHLY_MDAY => 'MD',	// BYMONHTDAY={1..31}
		MCAL_RECUR_MONTHLY_WDAY => 'MP',	// BYDAY={1..5}{MO..SO}
		MCAL_RECUR_YEARLY       => 'YM',
	);

	/**
	 * manufacturer and name of the sync-client
	 *
	 * @var string
	 */
	var $productManufacturer = 'file';
	var $productName = '';

	/**
	 * Exports one calendar event to an iCalendar item
	 *
	 * @param int/array $events (array of) cal_id or array of the events
	 * @param string $version='1.0' could be '2.0' too
	 * @param string $method='PUBLISH'
	 * @param boolean $force_own_uid=true ignore the stored and maybe from the client transfered uid and generate a new one
	 * RalfBecker: GroupDAV/CalDAV requires to switch that non RFC conform behavior off, dont know if SyncML still needs it
	 * @return string/boolean string with vCal or false on error (eg. no permission to read the event)
	 */
	function &exportVCal($events,$version='1.0', $method='PUBLISH',$force_own_uid=true)
	{
		$egwSupportedFields = array(
			'CLASS'			=> array('dbName' => 'public'),
			'SUMMARY'		=> array('dbName' => 'title'),
			'DESCRIPTION'	=> array('dbName' => 'description'),
			'LOCATION'		=> array('dbName' => 'location'),
			'DTSTART'		=> array('dbName' => 'start'),
			'DTEND'			=> array('dbName' => 'end'),
			'ORGANIZER'		=> array('dbName' => 'owner'),
			'ATTENDEE'		=> array('dbName' => 'participants'),
			'RRULE'			=> array('dbName' => 'recur_type'),
			'EXDATE'		=> array('dbName' => 'recur_exception'),
				'PRIORITY'		=> array('dbName' => 'priority'),
				'TRANSP'		=> array('dbName' => 'non_blocking'),
			'CATEGORIES'	=> array('dbName' => 'category'),
		);
		if(!is_array($this->supportedFields))
		{
			$this->setSupportedFields();
		}

		if($this->productManufacturer == '' )
		{	// syncevolution is broken
			$version = "2.0";
		}

		$palm_enddate_workaround=False;
		if($this->productManufacturer == 'Synthesis AG'
			&& strpos($this->productName, "PalmOS") )
		{
			// This workaround adds 1 day to the recur_enddate if it exists, to fix a palm bug
			$palm_enddate_workaround=True;
		}

		$vcal = &new Horde_iCalendar;
		$vcal->setAttribute('PRODID','-//eGroupWare//NONSGML eGroupWare Calendar '.$GLOBALS['egw_info']['apps']['calendar']['version'].'//'.
			strtoupper($GLOBALS['egw_info']['user']['preferences']['common']['lang']));
		$vcal->setAttribute('VERSION',$version);
		$vcal->setAttribute('METHOD',$method);

		if (!is_array($events)) $events = array($events);

		foreach($events as $event)
		{
			if (!is_array($event) && !($event = $this->read($event,null,false,'server')))	// server = timestamp in server-time(!)
			{
				return false;	// no permission to read $cal_id
			}
			//_debug_array($event);

			// correct daylight saving time
			/* causes times wrong by one hour, if exporting events with DST different from the current date,
			which this fix is suppost to fix. Maybe the problem has been fixed in the horde code too.
			$currentDST = date('I', mktime());
			$eventDST = date('I', $event['start']);
			$DSTCorrection = ($currentDST - $eventDST) * 3600;
			$event['start']	= $event['start'] + $DSTCorrection;
			$event['end']	= $event['end'] + $DSTCorrection;
			*/
			$eventGUID = $GLOBALS['egw']->common->generate_uid('calendar',$event['id']);

			$vevent = Horde_iCalendar::newComponent('VEVENT',$vcal);
			$parameters = $attributes = array();

			foreach($egwSupportedFields as $icalFieldName => $egwFieldInfo)
			{
				if($this->supportedFields[$egwFieldInfo['dbName']])
				{
					switch($icalFieldName)
					{
						case 'ATTENDEE':
							//if (count($event['participants']) == 1 && isset($event['participants'][$this->user])) break;
							foreach((array)$event['participants'] as $uid => $status)
							{
								if (!($info = $this->resource_info($uid))) continue;
								// RB: MAILTO href contains only the email-address, NO cn!
								$attributes['ATTENDEE'][]	= $info['email'] ? 'MAILTO:'.$info['email'] : '';
								// ROLE={CHAIR|REQ-PARTICIPANT|OPT-PARTICIPANT|NON-PARTICIPANT} NOT used by eGW atm.
								$role = $uid == $event['owner'] ? 'CHAIR' : 'REQ-PARTICIPANT';
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
								$parameters['ATTENDEE'][] = array(
									'CN'       => $info['cn'] ? $info['cn'] : $info['name'],
									'ROLE'     => $role,
									'PARTSTAT' => $status,
									'CUTYPE'   => $cutype,
									'RSVP'     => $rsvp,
								)+($info['type'] != 'e' ? array('X-EGROUPWARE-UID' => $uid) : array());
							}
							break;

            			case 'CLASS':
            				$attributes['CLASS'] = $event['public'] ? 'PUBLIC' : 'PRIVATE';
    	    				break;

        				case 'ORGANIZER':	// according to iCalendar standard, ORGANIZER not used for events in the own calendar
        					if ($event['owner'] != $this->user)
        					//if (!isset($event['participants'][$event['owner']]) || count($event['participants']) > 1)
        					{
								$mailtoOrganizer = $GLOBALS['egw']->accounts->id2name($event['owner'],'account_email');
								$attributes['ORGANIZER'] = $mailtoOrganizer ? 'MAILTO:'.$mailtoOrganizer : '';
								$parameters['ORGANIZER']['CN'] = trim($GLOBALS['egw']->accounts->id2name($event['owner'],'account_firstname').' '.
									$GLOBALS['egw']->accounts->id2name($event['owner'],'account_lastname'));
        					}
							break;

						case 'DTEND':
							if(date('H:i:s',$event['end']) == '23:59:59') $event['end']++;
							if(date('H:i:s',$event['end']) == '23:59:00') $event['end']+=60; // needed by old eGW whole-day events
							$attributes[$icalFieldName]	= $event['end'];
							break;

						case 'RRULE':
							if ($event['recur_type'] == MCAL_RECUR_NONE) break;		// no recuring event
							if ($version == '1.0') {
								$interval = ($event['recur_interval'] > 1) ? $event['recur_interval'] : 1;
								$rrule = array('FREQ' => $this->recur_egw2ical_1_0[$event['recur_type']].$interval);
								switch ($event['recur_type'])
								{
            								case MCAL_RECUR_WEEKLY:
            									$days = array();
            									foreach($this->recur_days_1_0 as $id => $day)
            									{
            										if ($event['recur_data'] & $id) $days[] = strtoupper(substr($day,0,2));
												}
	            								$rrule['BYDAY'] = implode(' ',$days);
	            								$rrule['FREQ'] = $rrule['FREQ'].' '.$rrule['BYDAY'];
	            								break;

        	    							case MCAL_RECUR_MONTHLY_MDAY:	// date of the month: BYMONTDAY={1..31}
            									break;

             								case MCAL_RECUR_MONTHLY_WDAY:	// weekday of the month: BDAY={1..5}{MO..SO}
             									$rrule['BYDAY'] = (1 + (int) ((date('d',$event['start'])-1) / 7)).'+ '.
	             									strtoupper(substr(date('l',$event['start']),0,2));
	            								$rrule['FREQ'] = $rrule['FREQ'].' '.$rrule['BYDAY'];
										break;
								}

								if ($event['recur_enddate'])
								{
									$recur_enddate = (int)$event['recur_enddate'];
									if ($palm_enddate_workaround)
									{
										$recur_enddate += 86400;
									}
									# append T and the Endtime, since the RRULE seems not to be understood by the client without it
									$rrule['UNTIL'] = date('Ymd',$recur_enddate).'T'.date('His',($event['end']?$event['end']:$event['start'])) ;
								}
								else
								{
									$rrule['UNTIL'] = '#0';
								}

								$attributes['RRULE'] = $rrule['FREQ'].' '.$rrule['UNTIL'];
							} else {
								$rrule = array('FREQ' => $this->recur_egw2ical_2_0[$event['recur_type']]);
								switch ($event['recur_type'])
								{
            								case MCAL_RECUR_WEEKLY:
            									$days = array();
            									foreach($this->recur_days as $id => $day)
            									{
            										if ($event['recur_data'] & $id) $days[] = strtoupper(substr($day,0,2));
										}
	            								$rrule['BYDAY'] = implode(',',$days);
	            								break;

        	    							case MCAL_RECUR_MONTHLY_MDAY:	// date of the month: BYMONTDAY={1..31}
            									$rrule['BYMONTHDAY'] = (int) date('d',$event['start']);
            									break;

             								case MCAL_RECUR_MONTHLY_WDAY:	// weekday of the month: BDAY={1..5}{MO..SO}
             									$rrule['BYDAY'] = (1 + (int) ((date('d',$event['start'])-1) / 7)).
	             									strtoupper(substr(date('l',$event['start']),0,2));
										break;
								}
								if ($event['recur_interval'] > 1) $rrule['INTERVAL'] = $event['recur_interval'];
								if ($event['recur_enddate']) $rrule['UNTIL'] = date('Ymd',$event['recur_enddate']);	// only day is set in eGW

								// no idea how to get the Horde parser to produce a standard conformant
								// RRULE:FREQ=... (note the double colon after RRULE, we cant use the $parameter array)
								// so we create one value manual ;-)
								foreach($rrule as $name => $value)
								{
									$attributes['RRULE'][] = $name . '=' . $value;
								}
								$attributes['RRULE'] = implode(';',$attributes['RRULE']);
							}
							break;

						case 'EXDATE':
							if ($event['recur_exception'])
							{
								$days = array();
								foreach($event['recur_exception'] as $day)
								{
									$days[] = date('Ymd',$day);
								}
								$attributes['EXDATE'] = implode(',',$days);
								$parameters['EXDATE']['VALUE'] = 'DATE';
							}
							break;

						case 'PRIORITY':
 							$attributes['PRIORITY'] = (int) $this->priority_egw2ical[$event['priority']];
 							break;

 						case 'TRANSP':
							if ($version == '1.0') {
								$attributes['TRANSP'] = $event['non_blocking'] ? 1 : 0;
							} else {
								$attributes['TRANSP'] = $event['non_blocking'] ? 'TRANSPARENT' : 'OPAQUE';
							}
							break;

						case 'CATEGORIES':
							if ($event['category'])
							{
								$attributes['CATEGORIES'] = implode(',',$this->get_categories($event['category']));
							}
							break;

						default:
							if ($event[$egwFieldInfo['dbName']])	// dont write empty fields
							{
								$attributes[$icalFieldName]	= $event[$egwFieldInfo['dbName']];
							}
							break;
					}
				}
			}

			if(strtolower($this->productManufacturer) == 'nokia') {
				if($event['special'] == '1') {
					$attributes['X-EPOCAGENDAENTRYTYPE'] = 'ANNIVERSARY';
					$attributes['DTEND'] = $attributes['DTSTART'];
				} else {
					$attributes['X-EPOCAGENDAENTRYTYPE'] = 'APPOINTMENT';
				}
			}

			$modified = $GLOBALS['egw']->contenthistory->getTSforAction($eventGUID,'modify');
			$created = $GLOBALS['egw']->contenthistory->getTSforAction($eventGUID,'add');
			if (!$created && !$modified) $created = $event['modified'];
			if ($created) $attributes['CREATED'] = $created;
			if (!$modified) $modified = $event['modified'];
			if ($modified) $attributes['LAST-MODIFIED'] = $modified;

			foreach($event['alarm'] as $alarmID => $alarmData)
			{
				if ($version == '1.0')
				{
					$attributes['DALARM'] = $vcal->_exportDateTime($alarmData['time']);
					$attributes['AALARM'] = $vcal->_exportDateTime($alarmData['time']);
					// lets take only the first alarm
					break;
				}
				else
				{
					// VCalendar 2.0 / RFC 2445

					// skip over alarms that don't have the minimum required info
					if (!$alarmData['offset'] && !$alarmData['time'])
					{
						error_log("Couldn't add VALARM (no alarm time info)");
						continue;
					}

					// RFC requires DESCRIPTION for DISPLAY
					if (!$event['title'] && !$event['description'])
					{
						error_log("Couldn't add VALARM (no description)");
						continue;
					}

					$valarm = Horde_iCalendar::newComponent('VALARM',$vevent);
					if ($alarmData['offset'])
					{
						$valarm->setAttribute('TRIGGER', -$alarmData['offset'],
								array('VALUE' => 'DURATION', 'RELATED' => 'START'));
					}
					else
					{
						$valarm->setAttribute('TRIGGER', $alarmData['time'],
								array('VALUE' => 'DATE-TIME'));
					}

					$valarm->setAttribute('ACTION','DISPLAY');
					$valarm->setAttribute('DESCRIPTION',$event['title'] ? $event['title'] : $event['description']);
					$vevent->addComponent($valarm);
				}
			}

			$attributes['UID'] = $force_own_uid ? $eventGUID : $event['uid'];

			foreach($attributes as $key => $value)
			{
				foreach(is_array($value) ? $value : array($value) as $valueID => $valueData)
				{
					$valueData = $GLOBALS['egw']->translation->convert($valueData,$GLOBALS['egw']->translation->charset(),'UTF-8');
					$paramData = (array) $GLOBALS['egw']->translation->convert(is_array($value) ? $parameters[$key][$valueID] : $parameters[$key],
						$GLOBALS['egw']->translation->charset(),'UTF-8');
					//echo "$key:$valueID: value=$valueData, param=".print_r($paramDate,true)."\n";
					$vevent->setAttribute($key, $valueData, $paramData);
					$options = array();
					if($key != 'RRULE' && preg_match('/([\000-\012\015\016\020-\037\075])/',$valueData))
					{
						$options['ENCODING'] = 'QUOTED-PRINTABLE';
					}
					if(preg_match('/([\177-\377])/',$valueData))
					{
						$options['CHARSET'] = 'UTF-8';
					}
					$vevent->setParameter($key, $options);
				}
			}
			$vcal->addComponent($vevent);
		}
		//_debug_array($vcal->exportvCalendar());

		return $vcal->exportvCalendar();
	}

	/**
	 * Import an iCal
	 *
	 * @param string $_vcalData
	 * @param int $cal_id=-1 must be -1 for new entrys!
	 * @param string $etag=null if an etag is given, it has to match the current etag or the import will fail
	 * @return int|boolean cal_id > 0 on success, false on failure or 0 for a failed etag
	 */
	function importVCal($_vcalData, $cal_id=-1,$etag=null)
	{
		// our (patched) horde classes, do NOT unfold folded lines, which causes a lot trouble in the import
		$_vcalData = preg_replace("/[\r\n]+ /",'',$_vcalData);

		$vcal = &new Horde_iCalendar;
		if(!$vcal->parsevCalendar($_vcalData))
		{
			return FALSE;
		}

		$version = $vcal->getAttribute('VERSION');

		if(!is_array($this->supportedFields))
		{
			$this->setSupportedFields();
		}
		//echo "supportedFields="; _debug_array($this->supportedFields);

		$syncevo_enddate_fix = False;
		if( $this->productManufacturer == '' && $this->productName == '' )
		{
			// syncevolution needs an adjusted recur_enddate
			$syncevo_enddate_fix = True;
		}

		$Ok = false;	// returning false, if file contains no components
		foreach($vcal->getComponents() as $component)
		{
			if(is_a($component, 'Horde_iCalendar_vevent'))
			{
				$supportedFields = $this->supportedFields;
				#$event = array('participants' => array());
				$event		= array();
				$alarms		= array();
				$vcardData	= array(
					'recur_type'		=> MCAL_RECUR_NONE,
					'recur_exception'	=> array(),
				);

				// lets see what we can get from the vcard
				foreach($component->_attributes as $attributes)
				{
					switch($attributes['name'])
					{
						case 'AALARM':
						case 'DALARM':
							if (preg_match('/.*Z$/',$attributes['value'],$matches)) {
								$alarmTime = $vcal->_parseDateTime($attributes['value']);
								$alarms[$alarmTime] = array(
									'time' => $alarmTime
								);
							} elseif (preg_match('/(........T......);;(\d*);$/',$attributes['value'],$matches)) {
								//error_log(print_r($matches,true));
								$alarmTime = $vcal->_parseDateTime($matches[1]);
								$alarms[$alarmTime] = array(
									'time' => $alarmTime
								);
							} elseif (preg_match('/(........T......Z);;(\d*);$/',$attributes['value'],$matches)) {
								//error_log(print_r($matches,true));
								$alarmTime = $vcal->_parseDateTime($matches[1]);
								$alarms[$alarmTime] = array(
									'time' => $alarmTime
								);
							} elseif (preg_match('/(........T......)$/',$attributes['value'],$matches)) {
								$alarmTime = $vcal->_parseDateTime($attributes['value']);
								$alarms[$alarmTime] = array(
									'time' => $alarmTime
								);
							}
							break;
						case 'CLASS':
							$vcardData['public']		= (int)(strtolower($attributes['value']) == 'public');
							break;
						case 'DESCRIPTION':
							$vcardData['description']	= $attributes['value'];
							break;
						case 'DTEND':
							$dtend_ts = is_numeric($attributes['value']) ? $attributes['value'] : $this->date2ts($attributes['value']);
							if(date('H:i:s',$dtend_ts) == '00:00:00') {
								$dtend_ts -= 60;
							}
							$vcardData['end']		= $dtend_ts;
							break;
						case 'DTSTART':
							$vcardData['start']		= $attributes['value'];
							break;
						case 'LOCATION':
							$vcardData['location']	= $attributes['value'];
							break;
						case 'RRULE':
							$recurence = $attributes['value'];
							$type = preg_match('/FREQ=([^;: ]+)/i',$recurence,$matches) ? $matches[1] : $recurence[0];
							// vCard 2.0 values for all types
							if (preg_match('/UNTIL=([0-9T]+)/',$recurence,$matches))
							{
								$vcardData['recur_enddate'] = $vcal->_parseDateTime($matches[1]);
							}
							elseif (preg_match('/COUNT=([0-9]+)/',$recurence,$matches))
							{
								$vcardData['recur_count'] = (int)$matches[1];
							}
							if (preg_match('/INTERVAL=([0-9]+)/',$recurence,$matches))
							{
								// 1 is invalid,, egw uses 0 for interval
								$vcardData['recur_interval'] = (int) $matches[1] != 0 ? (int) $matches[1] : 0;
							}
							if (!isset($vcardData['start']))	// it might not yet be set, because the RRULE is before it
							{
								$vcardData['start'] = self::_get_attribute($component->_attributes,'DTSTART');
								$vcardData['end'] = self::_get_attribute($component->_attributes,'DTEND');
							}
							$vcardData['recur_data'] = 0;
							switch($type)
							{
								case 'W':
								case 'WEEKLY':
									$days = array();
									if(preg_match('/W(\d+) (.*) (.*)/',$recurence, $recurenceMatches))		// 1.0
									{
										$vcardData['recur_interval'] = $recurenceMatches[1];
										$days = explode(' ',trim($recurenceMatches[2]));
										if($recurenceMatches[3] != '#0')
											$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[3]);
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
										foreach($recur_days as $id => $day)
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
										$vcardData['recur_enddate'] = mktime(0,0,0,
											date('m',$vcardData['start']),
											date('d',$vcardData['start']) + ($vcardData['recur_interval']*($vcardData['recur_count']-1)*7),
											date('Y',$vcardData['start']));
									}
									break;

								case 'D':	// 1.0
									if(preg_match('/D(\d+) #(.\d)/', $recurence, $recurenceMatches)) {
										$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[2] > 0 && $vcardData['end']) {
											$vcardData['recur_enddate'] = mktime(
												date('H', $vcardData['end']),
												date('i', $vcardData['end']),
												date('s', $vcardData['end']),
												date('m', $vcardData['end']),
												date('d', $vcardData['end']) + ($recurenceMatches[2] * $vcardData['recur_interval']),
												date('Y', $vcardData['end'])
											);
										}
									} elseif(preg_match('/D(\d+) (.*)/', $recurence, $recurenceMatches)) {
										$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[2] != '#0') {
											$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[2]);
										}
									} else {
										break;
									}
									// fall-through
								case 'DAILY':	// 2.0
									$vcardData['recur_type'] = MCAL_RECUR_DAILY;

									if (!empty($vcardData['recur_count']))
									{
										$vcardData['recur_enddate'] = mktime(0,0,0,
											date('m',$vcardData['start']),
											date('d',$vcardData['start']) + ($vcardData['recur_interval']*($vcardData['recur_count']-1)),
											date('Y',$vcardData['start']));
									}
									break;

								case 'M':
									if(preg_match('/MD(\d+) #(.\d)/', $recurence, $recurenceMatches)) {
										$vcardData['recur_type'] = MCAL_RECUR_MONTHLY_MDAY;
										$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[2] > 0 && $vcardData['end']) {
											$vcardData['recur_enddate'] = mktime(
												date('H', $vcardData['end']),
												date('i', $vcardData['end']),
												date('s', $vcardData['end']),
												date('m', $vcardData['end']) + ($recurenceMatches[2] * $vcardData['recur_interval']),
												date('d', $vcardData['end']),
												date('Y', $vcardData['end'])
											);
										}
									} elseif(preg_match('/MD(\d+) (.*)/',$recurence, $recurenceMatches)) {
										$vcardData['recur_type'] = MCAL_RECUR_MONTHLY_MDAY;
										if($recurenceMatches[1] > 1)
											$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[2] != '#0')
											$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[2]);
									} elseif(preg_match('/MP(\d+) (.*) (.*) (.*)/',$recurence, $recurenceMatches)) {
										$vcardData['recur_type'] = MCAL_RECUR_MONTHLY_WDAY;
										if($recurenceMatches[1] > 1)
											$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[4] != '#0')
											$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[4]);
									}
									break;
								case 'MONTHLY':
									$vcardData['recur_type'] = strpos($recurence,'BYDAY') !== false ?
										MCAL_RECUR_MONTHLY_WDAY : MCAL_RECUR_MONTHLY_MDAY;

									if (!empty($vcardData['recur_count']))
									{
										$vcardData['recur_enddate'] = mktime(0,0,0,
											date('m',$vcardData['start']) + ($vcardData['recur_interval']*($vcardData['recur_count']-1)),
											date('d',$vcardData['start']),
											date('Y',$vcardData['start']));
									}
									break;

								case 'Y':		// 1.0
									if(preg_match('/YM(\d+) #(.\d)/', $recurence, $recurenceMatches)) {
										$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[2] > 0 && $vcardData['end']) {
											$vcardData['recur_enddate'] = mktime(
												date('H', $vcardData['end']),
												date('i', $vcardData['end']),
												date('s', $vcardData['end']),
												date('m', $vcardData['end']),
												date('d', $vcardData['end']),
												date('Y', $vcardData['end']) + ($recurenceMatches[2] * $vcardData['recur_interval'])
											);
										}
									} elseif(preg_match('/YM(\d+) (.*)/',$recurence, $recurenceMatches)) {
										$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[2] != '#0') {
											$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[2]);
										}
									} else {
										break;
									}
									// fall-through
								case 'YEARLY':	// 2.0
									$vcardData['recur_type'] = MCAL_RECUR_YEARLY;

									if (!empty($vcardData['recur_count']))
									{
										$vcardData['recur_enddate'] = mktime(0,0,0,
											date('m',$vcardData['start']),
											date('d',$vcardData['start']),
											date('Y',$vcardData['start']) + ($vcardData['recur_interval']*($vcardData['recur_count']-1)));
									}
									break;
							}
							if( $syncevo_enddate_fix && $vcardData['recur_enddate'] )
							{
								// Does syncevolution need to adjust recur_enddate
								$vcardData['recur_enddate'] = (int)$vcardData['recur_enddate'] + 86400;
							}
							break;
						case 'EXDATE':
							$vcardData['recur_exception'] = array_merge($vcardData['recur_exception'],$attributes['value']);
							break;
						case 'SUMMARY':
							$vcardData['title']		= $attributes['value'];
							break;
						case 'UID':
							$event['uid'] = $vcardData['uid'] = $attributes['value'];
							if ($cal_id <= 0 && !empty($vcardData['uid']) && ($uid_event = $this->read($vcardData['uid'])))
							{
								$cal_id = $event['id'] = $uid_event['id'];
								unset($uid_event);
							}
							break;
 						case 'TRANSP':
 							if($version == '1.0') {
 								$vcardData['non_blocking'] = $attributes['value'] == 1;
 							} else {
								$vcardData['non_blocking'] = $attributes['value'] == 'TRANSPARENT';
							}
							break;
						case 'PRIORITY':
 							$vcardData['priority'] = (int) $this->priority_ical2egw[$attributes['value']];
 							break;
 						case 'CATEGORIES':
 							if ($attributes['value'])
 							{
 								$vcardData['category'] = $this->find_or_add_categories(explode(',',$attributes['value']));
 							}
							else
							{
 								$vcardData['category'] = array();
							}
 							break;
 						case 'ATTENDEE':
 							if (preg_match('/MAILTO:([@.a-z0-9_-]+)/i',$attributes['value'],$matches) ||
 								preg_match('/<([@.a-z0-9_-]+)>/i',$attributes['value'],$matches))
								{
									$email = $matches[1];
								}
								elseif(strpos($attributes['value'],'@') !== false)
								{
									$email = $attributes['value'];
								}
 							if (($uid = $attributes['params']['X-EGROUPWARE-UID']) &&
 								($info = $this->resource_info($uid)) && $info['email'] == $email)
 							{
 								// we use the (checked) X-EGROUPWARE-UID
 							}
 							/*elseif($attributes['params']['CUTYPE'] == 'RESOURCE')
 							{

 							}*/
	 						elseif($attributes['value'] == 'Unknown')
 							{
 								$uid = $GLOBALS['egw_info']['user']['account_id'];
 							}
 							elseif (($uid = $GLOBALS['egw']->accounts->name2id($email,'account_email')))
 							{
 								// we use the account we found
 							}
							elseif ((list($data) = ExecMethod2('addressbook.addressbook_bo.search',array(
								'email' => $email,
								'email_home' => $email,
							),true,'','','',false,'OR')))
							{
								$uid = 'c'.$data['id'];
							}
 							else
 							{
 								$uid = 'e'.($attributes['params']['CN'] ? $attributes['params']['CN'].' <'.$email.'>' : $email);
 							}
 							$event['participants'][$uid] = isset($attributes['params']['PARTSTAT']) ?
 									$this->status_ical2egw[strtoupper($attributes['params']['PARTSTAT'])] :
 									($uid == $event['owner'] ? 'A' : 'U');
 							break;
 						case 'ORGANIZER':	// will be written direct to the event
 							if (preg_match('/MAILTO:([@.a-z0-9_-]+)/i',$attributes['value'],$matches) &&
 								($uid = $GLOBALS['egw']->accounts->name2id($matches[1],'account_email')))
 							{
 								$event['owner'] = $uid;
 							}
 							break;
 						case 'CREATED':		// will be written direct to the event
 							if ($event['modified']) break;
 							// fall through
 						case 'LAST-MODIFIED':	// will be written direct to the event
							$event['modified'] = $attributes['value'];
							break;
					}
				}

				// check if the entry is a birthday
				// this field is only set from NOKIA clients
				$agendaEntryType = $component->getAttribute('X-EPOCAGENDAENTRYTYPE');
				if (!is_a($agendaEntryType, 'PEAR_Error')) {
					if(strtolower($agendaEntryType) == 'anniversary') {
						$event['special'] = '1';
						// make it a whole day event for eGW
						$vcardData['end'] = $vcardData['start'] + 86399;
					}
				}

				if(!empty($vcardData['recur_enddate']))
				{
					// reset recure_enddate to 00:00:00 on the last day
					$vcardData['recur_enddate'] = mktime(0, 0, 0,
						date('m',$vcardData['recur_enddate']),
						date('d',$vcardData['recur_enddate']),
						date('Y',$vcardData['recur_enddate'])
					);
				}
				//echo "event=";_debug_array($vcardData);

				// now that we know what the vard provides, we merge that data with the information we have about the device
				$event['priority']		= 2;
				if($cal_id > 0)
				{
					$event['id'] = $cal_id;
				}
				while(($fieldName = array_shift($supportedFields)))
				{
					switch($fieldName)
					{
						case 'alarms':
							// not handled here
							break;
						case 'recur_type':
							$event['recur_type'] = $vcardData['recur_type'];
							if ($event['recur_type'] != MCAL_RECUR_NONE)
							{
								foreach(array('recur_interval','recur_enddate','recur_data','recur_exception') as $r)
								{
									if(isset($vcardData[$r]))
									{
										$event[$r] = $vcardData[$r];
									}
								}
							}
							unset($supportedFields['recur_type']);
							unset($supportedFields['recur_interval']);
							unset($supportedFields['recur_enddate']);
							unset($supportedFields['recur_data']);
							break;
						default:
							if (isset($vcardData[$fieldName]))
							{
								$event[$fieldName] = $vcardData[$fieldName];
							}
							unset($supportedFields[$fieldName]);
							break;
					}
				}

				// add ourself to new events as participant
 				if($cal_id == -1 && !isset($this->supportedFields['participants']))
 				{
					$event['participants'] = array($GLOBALS['egw_info']['user']['account_id'] => 'A');
 				}

				// If this is an updated meeting, and the client doesn't support
				// participants, add them back
				if( $cal_id > 0 && !isset($this->supportedFields['participants']))
				{
					if (($egw_event = $this->read($cal_id)))
					{
						$event['participants'] = $egw_event['participants'];
						$event['participant_types'] = $egw_event['participant_types'];
					}
				}

				// Check for resources, and don't remove them
				if( $cal_id > 0 )
				{
					// for each existing participant:
					if (($egw_event = $this->read($cal_id)))
					{
						foreach( $egw_event['participants'] as $uid => $status )
						{
							// Is it a resource and not longer present in the event?
							if ( $uid[0] == 'r' && !isset($event['participants'][$uid]) )
							{
								// Add it back in
								$event['participants'][$uid] = $event['participant_types']['r'][substr($uid,1)] = $status;
							}
						}
					}
				}

				// check if iCal changes the organizer, which is not allowed
				if ($cal_id > 0 && ($egw_event = $this->read($cal_id)) && $event['owner'] != $egw_event['owner'])
				{
					$event['owner'] = $egw_event['owner'];	// set it back to the original owner
				}

				#error_log('ALARMS');
				#error_log(print_r($event, true));

				// if an etag is given, include it in the update
				if (!is_null($etag))
				{
					$event['etag'] = $etag;
				}
				if (!($Ok = $this->update($event, TRUE)))
				{
					// check if current user is an attendee and tried to change his status
					if ($Ok === false && $cal_id && ($egw_event = $this->read($cal_id)) && isset($egw_event['participants'][$this->user]) &&
						$egw_event['participants'][$this->user] !== $event['participants'][$this->user])
					{
						$this->set_status($egw_event,$this->user,
							$status = $event['participants'][$this->user] ? $event['participants'][$this->user] : 'R');

						$Ok = $cal_id;
						continue;
					}
					break;	// stop with the first error
				}
				else
				{
					$eventID =& $Ok;

					// handle the alarms
					foreach ($component->getComponents() as $valarm)
					{
						if (is_a($valarm, 'Horde_iCalendar_valarm'))
						{
							$this->valarm2egw($alarms,$valarm);
						}
					}

					if(count($alarms) > 0 || (isset($this->supportedFields['alarms'])  && count($alarms) == 0))
					{
						// delete the old alarms
						$updatedEvent = $this->read($eventID);
						foreach($updatedEvent['alarm'] as $alarmID => $alarmData)
						{
							$this->delete_alarm($alarmID);
						}
					}

					foreach($alarms as $alarm)
					{
						$alarm['offset'] = $event['start'] - $alarm['time'];
						$alarm['owner'] = $GLOBALS['egw_info']['user']['account_id'];
						$this->save_alarm($eventID, $alarm);
					}
				}
			}
		}
		return $Ok;
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
		foreach($components as $attribute)
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
		foreach($valarm->_attributes as $vattr)
		{
			switch($vattr['name'])
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
				// case 'ACTION':
				// 	break;
				// case 'DISPLAY':
				// 	break;

				default:
					error_log('VALARM field:' .$vattr['name'] .':' . print_r($vattrval,true) . ' HAS NO CONVERSION YET');
			}
		}
		return $count;
	}

	function setSupportedFields($_productManufacturer='file', $_productName='')
	{
		// save them vor later use
		$this->productManufacturer = $_productManufacturer;
		$this->productName = $_productName;

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
			'title'				=> 'title',
			'alarms'			=> 'alarms',
		);

		$defaultFields['basic'] = $defaultFields['minimal'] + array(
			'recur_exception'	=> 'recur_exception',
			'priority'			=> 'priority',
		);

		$defaultFields['nexthaus'] = $defaultFields['basic'] + array(
			'participants'		=> 'participants',
		);

		$defaultFields['synthesis'] = $defaultFields['basic'] + array(
			'non_blocking'		=> 'non_blocking',
			'category'			=> 'category',
		);

		$defaultFields['evolution'] = $defaultFields['basic'] + array(
			'participants'		=> 'participants',
			'owner'				=> 'owner',
			'category'			=> 'category',
		);

		$defaultFields['full'] = $defaultFields['basic'] + array(
			'participants'		=> 'participants',
			'owner'				=> 'owner',
			'category'			=> 'category',
			'non_blocking'		=> 'non_blocking',
		);


		switch(strtolower($_productManufacturer))
		{
			case 'nexthaus corporation':
			case 'nexthaus corp':
				switch(strtolower($_productName))
				{
					default:
						$this->supportedFields = $defaultFields['nexthaus'];
						break;
				}
				break;

			// multisync does not provide anymore information then the manufacturer
			// we suppose multisync with evolution
			case 'the multisync project':
				switch(strtolower($_productName))
				{
					default:
						$this->supportedFields = $defaultFields['basic'];
						break;
				}
				break;

			case 'nokia':
				switch(strtolower($_productName))
				{
					case 'e61':
						$this->supportedFields = $defaultFields['minimal'];
						break;
					default:
						error_log("Unknown Nokia phone '$_productName', assuming E61");
						$this->supportedFields = $defaultFields['minimal'];
						break;
				}
				break;

			case 'sonyericsson':
			case 'sony ericsson':
				switch(strtolower($_productName))
				{
					case 'd750i':
					case 'p910i':
						$this->supportedFields = $defaultFields['basic'];
						break;
					default:
						error_log("Unknown Sony Ericsson phone '$_productName' assuming d750i");
						$this->supportedFields = $defaultFields['basic'];
						break;
				}
				break;

			case 'synthesis ag':
				switch(strtolower($_productName))
				{
					case 'sysync client pocketpc std':
					case 'sysync client pocketpc pro':
						$this->supportedFields = $defaultFields['full'];
						break;
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
				$this->supportedFields = $defaultFields['full'];
				break;

			// the fallback for SyncML
			default:
				error_log("Unknown calendar SyncML client: manufacturer='$_productManufacturer'  product='$_productName'");
				$this->supportedFields = $defaultFields['full'];
				break;
		}
	}

	function icaltoegw($_vcalData)
	{
		// our (patched) horde classes, do NOT unfold folded lines, which causes a lot trouble in the import
		$_vcalData = preg_replace("/[\r\n]+ /",'',$_vcalData);

		$vcal = &new Horde_iCalendar;
		if(!$vcal->parsevCalendar($_vcalData))
		{
			return FALSE;
		}

		if(!is_array($this->supportedFields))
		{
			$this->setSupportedFields();
		}
		//echo "supportedFields="; _debug_array($this->supportedFields);

		$Ok = false;	// returning false, if file contains no components
		foreach($vcal->getComponents() as $component)
		{
			if(is_a($component, 'Horde_iCalendar_vevent'))
			{
				$supportedFields = $this->supportedFields;
				#$event = array('participants' => array());
				$event		= array();
				$alarms		= array();
				$vcardData	= array('recur_type' => 0);

				// lets see what we can get from the vcard
				foreach($component->_attributes as $attributes)
				{
					switch($attributes['name'])
					{
						case 'AALARM':
						case 'DALARM':
							if (preg_match('/.*Z$/',$attributes['value'],$matches))
							{
								$alarmTime = $vcal->_parseDateTime($attributes['value']);
								$alarms[$alarmTime] = array(
									'time' => $alarmTime
								);
							}
							break;
						case 'CLASS':
							$vcardData['public']		= (int)(strtolower($attributes['value']) == 'public');
							break;
						case 'DESCRIPTION':
							$vcardData['description']	= $attributes['value'];
							break;
						case 'DTEND':
							if(date('H:i:s',$attributes['value']) == '00:00:00')
								$attributes['value']--;
							$vcardData['end']		= $attributes['value'];
							break;
						case 'DTSTART':
							$vcardData['start']		= $attributes['value'];
							break;
						case 'LOCATION':
							$vcardData['location']	= $attributes['value'];
							break;
						case 'RRULE':
							$recurence = $attributes['value'];
							$type = preg_match('/FREQ=([^;: ]+)/i',$recurence,$matches) ? $matches[1] : $recurence[0];
							// vCard 2.0 values for all types
							if (preg_match('/UNTIL=([0-9T]+)/',$recurence,$matches))
							{
								$vcardData['recur_enddate'] = $vcal->_parseDateTime($matches[1]);
							}
							if (preg_match('/INTERVAL=([0-9]+)/',$recurence,$matches))
							{
								$vcardData['recur_interval'] = (int) $matches[1];
							}
							$vcardData['recur_data'] = 0;
							switch($type)
							{
								case 'W':
								case 'WEEKLY':
									$days = array();
									if(preg_match('/W(\d+) (.*) (.*)/',$recurence, $recurenceMatches))		// 1.0
									{
										$vcardData['recur_interval'] = $recurenceMatches[1];
										$days = explode(' ',trim($recurenceMatches[2]));
										if($recurenceMatches[3] != '#0')
											$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[3]);
										$recur_days = $this->recur_days_1_0;
									}
									elseif (preg_match('/BYDAY=([^;: ]+)/',$recurence,$recurenceMatches))	// 2.0
									{
										$days = explode(',',$recurenceMatches[1]);
										$recur_days = $this->recur_days;
									}
									if ($days)
									{
										foreach($recur_days as $id => $day)
			            						{
			            							if (in_array(strtoupper(substr($day,0,2)),$days))
	            									{
	            										$vcardData['recur_data'] |= $id;
	            									}
	            								}
										$vcardData['recur_type'] = MCAL_RECUR_WEEKLY;
									}
									break;

								case 'D':		// 1.0
									if(!preg_match('/D(\d+) (.*)/',$recurence, $recurenceMatches)) break;
									$vcardData['recur_interval'] = $recurenceMatches[1];
									if($recurenceMatches[2] != '#0')
										$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[2]);
									// fall-through
								case 'DAILY':	// 2.0
									$vcardData['recur_type'] = MCAL_RECUR_DAILY;
									break;

								case 'M':
									if(preg_match('/MD(\d+) (.*)/',$recurence, $recurenceMatches))
									{
										$vcardData['recur_type'] = MCAL_RECUR_MONTHLY_MDAY;
										if($recurenceMatches[1] > 1)
											$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[2] != '#0')
											$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[2]);
									}
									elseif(preg_match('/MP(\d+) (.*) (.*) (.*)/',$recurence, $recurenceMatches))
									{
										$vcardData['recur_type'] = MCAL_RECUR_MONTHLY_WDAY;
										if($recurenceMatches[1] > 1)
											$vcardData['recur_interval'] = $recurenceMatches[1];
										if($recurenceMatches[4] != '#0')
											$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[4]);
									}
									break;
								case 'MONTHLY':
									$vcardData['recur_type'] = strpos($recurence,'BYDAY') !== false ?
										MCAL_RECUR_MONTHLY_WDAY : MCAL_RECUR_MONTHLY_MDAY;
									break;

								case 'Y':		// 1.0
									if(!preg_match('/YM(\d+) (.*)/',$recurence, $recurenceMatches)) break;
									$vcardData['recur_interval'] = $recurenceMatches[1];
									if($recurenceMatches[2] != '#0')
										$vcardData['recur_enddate'] = $vcal->_parseDateTime($recurenceMatches[2]);
									// fall-through
								case 'YEARLY':	// 2.0
									$vcardData['recur_type'] = MCAL_RECUR_YEARLY;
									break;
							}
							break;
						case 'EXDATE':
							$vcardData['recur_exception'] = $attributes['value'];
							break;
						case 'SUMMARY':
							$vcardData['title']		= $attributes['value'];
							break;
						case 'UID':
							$event['uid'] = $vcardData['uid'] = $attributes['value'];
							if ($cal_id <= 0 && !empty($vcardData['uid']) && ($uid_event = $this->read($vcardData['uid'])))
							{
								$event['id'] = $uid_event['id'];
								unset($uid_event);
							}
							break;
 						case 'TRANSP':
							$vcardData['non_blocking'] = $attributes['value'] == 'TRANSPARENT';
							break;
						case 'PRIORITY':
							if ($this->productManufacturer == 'nexthaus corporation'
								|| $this->productManufacturer == 'nexthaus corp')
							{
								$vcardData['priority'] = $attributes['value'] == 1 ? 3 : 2; // 1=high, 2=normal
							}
							else
							{
 								$vcardData['priority'] = (int) $this->priority_ical2egw[$attributes['value']];
							}
 							break;
 						case 'CATEGORIES':
 							if ($attributes['value'])
 							{
								$vcardData['category'] = $this->find_or_add_categories(explode(',',$attributes['value']));
							}
							else
							{
 								$vcardData['category'] = array();
							}
 							break;
 						case 'ATTENDEE':
 							if (preg_match('/MAILTO:([@.a-z0-9_-]+)/i',$attributes['value'],$matches) &&
 								($uid = $GLOBALS['egw']->accounts->name2id($matches[1],'account_email')))
 							{
 								$event['participants'][$uid] = isset($attributes['params']['PARTSTAT']) ?
 									$this->status_ical2egw[strtoupper($attributes['params']['PARTSTAT'])] :
 									($uid == $event['owner'] ? 'A' : 'U');
 							}
 							break;
 						case 'ORGANIZER':	// will be written direct to the event
 							if (preg_match('/MAILTO:([@.a-z0-9_-]+)/i',$attributes['value'],$matches) &&
 								($uid = $GLOBALS['egw']->accounts->name2id($matches[1],'account_email')))
 							{
 								$event['owner'] = $uid;
 							}
 							break;
 						case 'CREATED':		// will be written direct to the event
 							if ($event['modified']) break;
 							// fall through
 						case 'LAST-MODIFIED':	// will be written direct to the event
							$event['modified'] = $attributes['value'];
							break;
					}
				}

				// check if the entry is a birthday
				// this field is only set from NOKIA clients
				$agendaEntryType = $component->getAttribute('X-EPOCAGENDAENTRYTYPE');
				if (!is_a($agendaEntryType, 'PEAR_Error')) {
					if(strtolower($agendaEntryType) == 'anniversary') {
						$event['special'] = '1';
						$vcardData['end'] = $vcardData['start'] + 86399;
					}
				}

				if(!empty($vcardData['recur_enddate']))
				{
					// reset recure_enddate to 00:00:00 on the last day
					$vcardData['recur_enddate'] = mktime(0, 0, 0,
						date('m',$vcardData['recur_enddate']),
						date('d',$vcardData['recur_enddate']),
						date('Y',$vcardData['recur_enddate'])
					);
				}
				//echo "event=";_debug_array($vcardData);

				while(($fieldName = array_shift($supportedFields)))
				{
					switch($fieldName)
					{
						case 'recur_interval':
						case 'recur_enddate':
						case 'recur_data':
						case 'recur_exception':
						case 'alarms':
							// not handled here
							break;
						case 'recur_type':
							$event['recur_type'] = $vcardData['recur_type'];
							if ($event['recur_type'] != MCAL_RECUR_NONE)
							{
								foreach(array('recur_interval','recur_enddate','recur_data','recur_exception') as $r)
								{
									if(isset($vcardData[$r]))
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

				return $event;
			}
		}

		return false;
	}

	function search($_vcalData)
	{
		if(!$event = $this->icaltoegw($_vcalData)) {
			return false;
		}

		$query = array(
			'cal_start='.$this->date2ts($event['start'],true),	// true = Server-time
			'cal_end='.$this->date2ts($event['end'],true),
		);

		#foreach(array('title','location','priority','public','non_blocking') as $name) {
		foreach(array('title','location','public','non_blocking') as $name) {
			if (isset($event[$name])) $query['cal_'.$name] = $event[$name];
		}

		if($foundEvents = parent::search(array(
			'user'  => $this->user,
			'query' => $query,
		))) {
			if(is_array($foundEvents)) {
				$event = array_shift($foundEvents);
				return $event['id'];
			}
		}
		return false;
	}

	/**
	 * Create a freebusy vCal for the given user(s)
	 *
	 * @param int $user account_id
	 * @param mixed $end=null end-date, default now+1 month
	 * @return string
	 */
	function freebusy($user,$end=null)
	{
		if (!$end) $end = $this->now_su + 100*DAY_s;	// default next 100 days

		$vcal = &new Horde_iCalendar;
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
		foreach(array(
			'URL' => $this->freebusy_url($user),
			'DTSTART' => $this->date2ts($this->now_su,true),	// true = server-time
			'DTEND' => $this->date2ts($end,true),	// true = server-time
		  	'ORGANIZER' => $GLOBALS['egw']->accounts->id2name($user,'account_email'),
			'DTSTAMP' => time(),
		) as $attr => $value)
		{
			$vfreebusy->setAttribute($attr, $value, $parameters[$name]);
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

				$vfreebusy->setAttribute('FREEBUSY',array(array(
					'start' => $event['start'],
					'end' => $event['end'],
				)));
			}
		}
		$vcal->addComponent($vfreebusy);

		return $vcal->exportvCalendar();
	}
}
