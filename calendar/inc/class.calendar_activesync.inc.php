<?php
/**
 * EGroupware: ActiveSync access: Calendar plugin
 *
 * @link http://www.egroupware.org
 * @package calendar
 * @subpackage activesync
 * @author Ralf Becker <rb@stylite.de>
 * @author Klaus Leithoff <kl@stylite.de>
 * @author Philip Herbert <philip@knauber.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */


/**
 * Calendar activesync plugin
 *
 * Plugin creates a device specific file to map alphanumeric folder names to nummeric id's.
 */
class calendar_activesync implements activesync_plugin_read
{
	/**
	 * var BackendEGW
	 */
	private $backend;

	/**
	 * Instance of calendar_bo
	 *
	 * @var calendar_boupdate
	 */
	private $calendar;

	/**
	 * Integer id of current mail account / connection
	 *
	 * @var int
	 */
	private $account;

	/**
	 * Constructor
	 *
	 * @param BackendEGW $backend
	 */
	public function __construct(BackendEGW $backend)
	{
		$this->backend = $backend;
	}


	/**
	 *  This function is analogous to GetMessageList.
	 *
	 *  @ToDo implement preference, include own private calendar
	 */
	public function GetFolderList()
	{
		if (!isset($this->calendar)) $this->calendar = new calendar_boupdate();

		foreach ($this->calendar->list_cals() as $label => $entry)
		{
			$folderlist[] = $f = array(
				'id'	=>	$this->backend->createID('calendar',$entry['grantor']),
				'mod'	=>	$GLOBALS['egw']->accounts->id2name($entry['grantor'],'account_fullname'),
				'parent'=>	'0',
			);
		};
		//error_log(__METHOD__."() returning ".array2string($folderlist));
		return $folderlist;
	}

	/**
	 * Get Information about a folder
	 *
	 * @param string $id
	 * @return SyncFolder|boolean false on error
	 */
	public function GetFolder($id)
	{
		$this->backend->splitID($id, $type, $owner);

		$folderObj = new SyncFolder();
		$folderObj->serverid = $id;
		$folderObj->parentid = '0';
		$folderObj->displayname = $GLOBALS['egw']->accounts->id2name($owner,'account_fullname');
		if ($owner == $GLOBALS['egw_info']['user']['account_id'])
		{
			$folderObj->type = SYNC_FOLDER_TYPE_APPOINTMENT;
		}
		else
		{
			$folderObj->type = SYNC_FOLDER_TYPE_USER_APPOINTMENT;
		}
		//error_log(__METHOD__."('$id') folderObj=".array2string($folderObj));
		return $folderObj;
	}

	/**
	 * Return folder stats. This means you must return an associative array with the
	 * following properties:
	 *
	 * "id" => The server ID that will be used to identify the folder. It must be unique, and not too long
	 *		 How long exactly is not known, but try keeping it under 20 chars or so. It must be a string.
	 * "parent" => The server ID of the parent of the folder. Same restrictions as 'id' apply.
	 * "mod" => This is the modification signature. It is any arbitrary string which is constant as long as
	 *		  the folder has not changed. In practice this means that 'mod' can be equal to the folder name
	 *		  as this is the only thing that ever changes in folders. (the type is normally constant)
	 *
	 * @return array with values for keys 'id', 'mod' and 'parent'
	 */
	public function StatFolder($id)
	{
		$folder = $this->GetFolder($id);
		$this->backend->splitID($id, $type, $owner);

		$stat = array(
			'id'	 => $id,
			'mod'	=> $GLOBALS['egw']->accounts->id2name($owner,'account_fullname'),
			'parent' => '0',
		);

		return $stat;
	}

	/**
	 * Should return a list (array) of messages, each entry being an associative array
	 * with the same entries as StatMessage(). This function should return stable information; ie
	 * if nothing has changed, the items in the array must be exactly the same. The order of
	 * the items within the array is not important though.
	 *
	 * The cutoffdate is a date in the past, representing the date since which items should be shown.
	 * This cutoffdate is determined by the user's setting of getting 'Last 3 days' of e-mail, etc. If
	 * you ignore the cutoffdate, the user will not be able to select their own cutoffdate, but all
	 * will work OK apart from that.
	 *
	 * @param string $id folder id
	 * @param int $cutoffdate=null
	 * @return array
  	 */
	function GetMessageList($id, $cutoffdate=NULL)
	{
		if (!isset($this->calendar)) $this->calendar = new calendar_boupdate();

		debugLog (__METHOD__."('$id',$cutoffdate)");
		$this->backend->splitID($id,$type,$user);

		if (!$cutoffdate) $cutoffdate = $this->bo->now - 100*24*3600;	// default three month back -30 breaks all sync recurrences

		// todo return only etag relevant information
		$filter = array(
			'users' => $user,
			'start' => $cutoffdate,	// default one month back -30 breaks all sync recurrences
			'enum_recuring' => false,
			'daywise' => false,
			'date_format' => 'server',
			'filter' => 'default',	// not rejected
		);

		$messagelist = array();
		foreach ($this->calendar->search($filter) as $k => $event)
		{
			$messagelist[] = $this->StatMessage($id, $event);
		}
		return $messagelist;
	}

	/**
	 * Get specified item from specified folder.
	 *
	 * @param string $folderid
	 * @param string $id
	 * @param int $truncsize
	 * @param int $bodypreference
	 * @param bool $mimesupport
	 * @return $messageobject|boolean false on error
	*/
	public function GetMessage($folderid, $id, $truncsize, $bodypreference=false, $mimesupport = 0)
	{
		if (!isset($this->calendar)) $this->calendar = new calendar_boupdate();

		debugLog (__METHOD__."('$folderid', $id, truncsize=$truncsize, bodyprefence=$bodypreference, mimesupport=$mimesupport)");
		$this->backend->splitID($folderid, $type, $account);
		if ($type != 'calendar' || !($event = $this->calendar->read($id,null,'ts',false,$account)))
		{
			return false;
		}
		$message = new SyncAppointment();

		// set timezones (Todo: timestamps have to be in that timezone)
		//$message->timezone = base64_encode(self::_getSyncBlobFromTZ(self::tz2as($event['tzid'])));

		// copying timestamps
		foreach(array(
			'start' => 'starttime',
			'end'   => 'endtime',
			'created' => 'dtstamp',
			'modified' => 'dtstamp',
		) as $key => $attr)
		{
			if (!empty($event[$key])) $message->$attr = $event[$key];
		}
		// copying strings
		foreach(array(
			'title' => 'subject',
			'uid'   => 'uid',
			'location' => 'location',
		) as $key => $attr)
		{
			if (!empty($event[$key])) $message->$attr = $event[$key];
		}
		$message->organizername  = $GLOBALS['egw']->accounts->id2name($event['owner'],'account_fullname');
		$message->organizeremail = $GLOBALS['egw']->accounts->id2name($event['owner'],'account_email');

		$message->sensitivity = $event['public'] ? 0 : 2;	// 0=normal, 1=personal, 2=private, 3=confidential
		$message->alldayevent = (int)$this->calendar->isWholeDay($event);

		$message->attendees = array();
		foreach($event['participants'] as $uid => $status)
		{
			static $status2as = array(
				'u' => 0,	// unknown
				't' => 2,	// tentative
				'a' => 3,	// accepted
				'r' => 4,	// decline
				// 5 = not responded
			);
			static $role2as = array(
				'REQ-PARTICIPANT' => 1,	// required
				'CHAIR' => 1,			// required
				'OPT-PARTICIPANT' => 2,	// optional
				'NON-PARTICIPANT' => 2,
				// 3 = ressource
			);
			calendar_so::split_status($status, $quantity, $role);
			$attendee = new SyncAttendee();
			$attendee->status = (int)$status2as[$status];
			$attendee->type = (int)$role2as[$role];
			if (is_numeric($uid))
			{
				$attendee->name = $GLOBALS['egw']->accounts->id2name($uid,'account_fullname');
				$attendee->email = $GLOBALS['egw']->accounts->id2name($uid,'account_email');
			}
			else
			{
				// ToDo: ressources, eg. contacts
				continue;

				list($info) = $this->calendar->resources[$uid[0]]['info'] ?
					ExecMethod($this->resources[$uid[0]]['info'],substr($uid,1)) : array(false);
				if ($info)
				{
					if (!$info['email'] && $info['responsible'])
					{
						$info['email'] = $GLOBALS['egw']->accounts->id2name($info['responsible'],'account_email');
					}
					$attendee->name = empty($info['cn']) ? $info['name'] : $info['cn'];
					$attendee->email = $info['email'];
					if ($uid[0] == 'r') $attendee->type = 3;	// 3 = resource
				}
			}
			$message->attendees[] = $attendee;
		}
		$message->categories = array();
		foreach($event['catgory'] ? explode(',',$event['category']) : array() as $cat_id)
		{
			$message->categories[] = categories::id2name($cat_id);
		}

		// recurring information
		if ($event['recur_type'] != RECUR_NONE)
		{
			$message->recurrence = $recurrence = new SyncRecurrence();
			$rrule = calendar_rrule::event2rrule($event);
			static $recur_type2as = array(
				calendar_rrule::DAILY => 0,
				calendar_rrule::WEEKLY => 1,
				calendar_rrule::MONTHLY_MDAY => 2,	// monthly
				calendar_rrule::MONTHLY_WDAY => 3,	// monthly on nth day
				calendar_rrule::YEARLY => 5,
				// 6 = yearly on nth day
			);
			$recurrence->type = (int)$recur_type2as[$rrule->type];
			$recurrence->interval = $rrule->interval;
			switch ($rrule->type)
			{
				case calendar_rrule::MONTHLY_WDAY:
					$recurrence->weekofmonth = $rrule->monthly_byday_num >= 1 ?
						$rrule->monthly_byday_num : 5;	// 1..5=last week of month, not -1
					// fall throught
				case calendar_rrule::WEEKLY:
					$recurrence->dayofweek = $rrule->weekdays;	// 1=Su, 2=Mo, 4=Tu, .., 64=Sa
					break;
				case calendar_rrule::MONTHLY_MDAY:
					$recurrence->dayofmonth = $rrule->monthly_bymonthday >= 1 ?	// 1..31
						$rrule->monthly_bymonthday : 31;	// not -1 for last day of month!
					break;
				case calendar_rrule::YEARLY:
					$recurrence->dayofmonth = (int)$rrule->time->format('d');	// 1..31
					$recurrence->monthofyear = (int)$rrule->time->format('m');	// 1..12
					break;
			}
			if ($rrule->enddate) $recurrence->until = $rrule->enddate->format('ts');	// Timezone?

			if ($rrule->exceptions)
			{
				$message->exceptions = array();
				foreach($rrule->exceptions as $exception_time)
				{
					$exception = new SyncAppointment();	// exceptions seems to be full SyncAppointments, with only starttime required
					$exception->starttime = $exception_time->format('ts');	// Timezone?
					$message->exceptions[] = $exception;
				}
			}
		}
		//$message->busystatus;
		//$message->reminder;
		//$message->meetingstatus;
		//$message->deleted;
/*
		if (isset($protocolversion) && $protocolversion < 12.0) {
			$message->body;
			$message->bodytruncated;
			$message->rtf;
		}

		if(isset($protocolversion) && $protocolversion >= 12.0) {

		 	$message->airsyncbasebody;	// SYNC SyncAirSyncBaseBody
		}
*/
		return $message;
	}

	/**
	 * StatMessage should return message stats, analogous to the folder stats (StatFolder). Entries are:
	 * 'id'	 => Server unique identifier for the message. Again, try to keep this short (under 20 chars)
	 * 'flags'	 => simply '0' for unread, '1' for read
	 * 'mod'	=> modification signature. As soon as this signature changes, the item is assumed to be completely
	 *			 changed, and will be sent to the PDA as a whole. Normally you can use something like the modification
	 *			 time for this field, which will change as soon as the contents have changed.
	 *
	 * @param string $folderid
	 * @param int|array $id event id or array
	 * @return array
	 */
	public function StatMessage($folderid, $id)
	{
		if (!isset($this->calendar)) $this->calendar = new calendar_boupdate();

		if (!($etag = $this->calendar->get_etag($id)))
		{
			$stat = false;
			// error_log why access is denied (should nevery happen for everything returned by calendar_bo::search)
			$backup = $this->calendar->debug;
			$this->calendar->debug = 2;
			$this->check_perms(EGW_ACL_FREEBUSY, $id, 0, 'server');
			$this->calendar->debug = $backup;
		}
		else
		{
//list(,,$etag) = explode(':',$etag);
			$stat = array(
				'mod' => $etag,
				'id' => is_array($id) ? $id['id'] : $id,
				'flags' => 1,
			);
		}
		debugLog (__METHOD__."('$folderid',".array2string($id).") returning ".array2string($stat));

		return $stat;
	}

	/**
	 * Return a changes array
	 *
	 * if changes occurr default diff engine computes the actual changes
	 *
	 * @param string $folderid
	 * @param string &$syncstate on call old syncstate, on return new syncstate
	 * @return array|boolean false if $folderid not found, array() if no changes or array(array("type" => "fakeChange"))
	 */
	function AlterPingChanges($folderid, &$syncstate)
	{
		$this->backend->splitID($folderid, $type, $owner);

		if ($type != 'calendar') return false;

		if (!isset($this->calendar)) $this->calendar = new calendar_boupdate();
		$ctag = $this->calendar->get_ctag($owner);

		$changes = array();	// no change
		$syncstate_was = $syncstate;

		if ($ctag !== $syncstate)
		{
			$syncstate = $ctag;
			$changes = array(array('type' => 'fakeChange'));
		}
		//error_log(__METHOD__."('$folderid','$syncstate_was') syncstate='$syncstate' returning ".array2string($changes));
		return $changes;
	}

	/**
	 * Return AS timezone data from given timezone and time
	 *
	 * AS spezifies the timezone by the date it changes to dst and back and the offsets.
	 * Unfortunately this data is not available from PHP's DateTime(Zone) class.
	 * Just given the exact time of the next transition, which is available via DateTimeZone::getTransistions(),
	 * will fail for recurring events longer then a year, as the transition date/time changes!
	 *
	 * We could use the RRule given in the iCal timezone defintion available via calendar_timezones::tz2id($tz,'component').
	 *
	 * Not every timezone uses DST, in which case only bias matters and dstbias=0
	 * (probably all other values should be 0, as MapiMapping::_getGMTTZ() in backend/ics.php does it).
	 *
	 * @param string|DateTimeZone $tz
	 * @param int|string|DateTime $ts=null time for which active sync timezone data is requested, default current time
	 * @return array with values for keys:
	 * - "bias": timezone offset from UTC in minutes for NO DST
	 * - "dstendmonth", "dstendday", "dstendweek", "dstendhour", "dstendminute", "dstendsecond", "dstendmillis"
	 * - "stdbias": seems not to be used
	 * - "dststartmonth", "dststartday", "dststartweek", "dststarthour", "dststartminute", "dststartsecond", "dststartmillis"
	 * - "dstbias": offset in minutes for no DST --> DST, usually 1
	 *
	 * @link http://download.microsoft.com/download/5/D/D/5DD33FDF-91F5-496D-9884-0A0B0EE698BB/%5BMS-ASDTYPE%5D.pdf
	 */
	function tz2as($tz,$ts=null)
	{
/*
BEGIN:VTIMEZONE
TZID:Europe/Berlin
X-LIC-LOCATION:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
--> bias: 60 min
TZOFFSETTO:+0200
--> dstbias: +0200 - +0100 = +0100 = 60 min
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
--> dststart: month: 3, day: SU(0???), week: 5, hour: 2, minute, second, millis: 0
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
--> dstend: month: 10, day: SU(0???), week: -1|5, hour: 3, minute, second, millis: 0
END:STANDARD
END:VTIMEZONE
*/
		$data = array(
			'bias' => 0,
			'stdbias' => 0,
			'dstbias' => 0,
			'dststartmonth' => 0, 'dststartday' => 0, 'dststartweek' => 0,
			'dststarthour' => 0, 'dststartminute' => 0, 'dststartsecond' => 0, 'dststartmillis' => 0,
			'dstendmonth' => 0, 'dstendday' => 0, 'dstendweek' => 0,
			'dstendhour' => 0, 'dstendminute' => 0, 'dstendsecond' => 0, 'dstendmillis' => 0,
		);

		$name = is_a($tz,'DateTimeZone') ? $tz->getName() : $tz;
		$component = calendar_timezones::tz2id($name,'component');

		if (!preg_match("/BEGIN:STANDARD\nTZOFFSETFROM:([+-]?\d{4})\nTZOFFSETTO:([+-]?\d{4})\n(.*)\nEND:STANDARD\n/m",$component,$matches))
		{
			throw new egw_exception_assertion_failed("NO standard component for '$name' in '$component'!");
		}
		// get bias and dstbias, should be present in all tz
		$data['bias'] = 60 * substr($matches[2],0,-2) + substr($matches[2],-2);		// TZOFFSETTO
		$data['dstbias'] = 60 * substr($matches[1],0,-2) + substr($matches[1],-2);	// TZOFFSETFROM

		// check if we have a RRULE and a BEGIN:DAYLIGHT component
		if (($end=$matches[3]) &&
			preg_match("/BEGIN:DAYLIGHT\nTZOFFSETFROM:([+-]?\d{4})\nTZOFFSETTO:([+-]?\d{4})\n(.*)\nEND:DAYLIGHT\n/m",$component,$matches))
		{
			foreach(array('dststart' => $matches[3],'dstend' => $end) as $prefix => $comp)
			{
				if (pregmatch('/RRULE:FREQ=YEARLY;BYDAY=(.*);BYMONTH=(\d+)/',$comp,$matches))
				{
					$data[$prefix.'month'] = (int)$matches[2];
					$data[$prefix.'week'] = (int)$matches[1];
					static $day2int = array('SU'=>0,'MO'=>1,'TU'=>2,'WE'=>3,'TH'=>4,'FR'=>5,'SA'=>6);
					$data[$prefix.'day'] = (int)$day2int[substr($matches[1],-2)];
				}
				if (pregmatch('/DTSTART:\d{8}T(\d{6})/',$comp,$matches))
				{
					$data[$prefix.'hour'] = (int)substr($matches[1],0,2);
					$data[$prefix.'minute'] = (int)substr($matches[1],2,2);
					$data[$prefix.'second'] = (int)substr($matches[1],4,2);
				}
			}
		}
		error_log(__METHOD__."('$name') returning ".array2string($data));
		return $data;
	}

	/**
	 * Get timezone from AS timezone data
	 *
	 * Here we can only loop through all available timezones (possibly starting with the users timezone) and
	 * try to find a timezone matching the change data and offsets specified in $data.
	 * This conversation is not unique, as multiple timezones can match the given data or none!
	 *
	 * Maybe returning the users timezone, if no match found makes more sense.
	 *
	 * @param array $data
	 * @return string|boolean timezone name, eg. "Europe/Berlin" or false if no matching timezone found
	 */
	function as2tz(array $data)
	{
		return false;
	}

	/**
	 * Unpack timezone info from Sync
	 *
	 * copied from backend/ics.php
	 */
	static private function _getTZFromSyncBlob($data) {
		$tz = unpack(	"lbias/a64name/vdstendyear/vdstendmonth/vdstendday/vdstendweek/vdstendhour/vdstendminute/vdstendsecond/vdstendmillis/" .
						"lstdbias/a64name/vdststartyear/vdststartmonth/vdststartday/vdststartweek/vdststarthour/vdststartminute/vdststartsecond/vdststartmillis/" .
						"ldstbias", $data);

		return $tz;
	}

	/**
	 * Pack timezone info for Sync
	 *
	 * copied from backend/ics.php
	 */
	static private function _getSyncBlobFromTZ($tz) {
		$packed = pack("la64vvvvvvvv" . "la64vvvvvvvv" . "l",
				$tz["bias"], "", 0, $tz["dstendmonth"], $tz["dstendday"], $tz["dstendweek"], $tz["dstendhour"], $tz["dstendminute"], $tz["dstendsecond"], $tz["dstendmillis"],
				$tz["stdbias"], "", 0, $tz["dststartmonth"], $tz["dststartday"], $tz["dststartweek"], $tz["dststarthour"], $tz["dststartminute"], $tz["dststartsecond"], $tz["dststartmillis"],
				$tz["dstbias"]);

		return $packed;
	}
}
