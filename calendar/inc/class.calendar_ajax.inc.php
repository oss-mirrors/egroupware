<?php
/**
 * Calendar - ajax class
 *
 * @link http://www.egroupware.org
 * @author Christian Binder <christian.binder@freakmail.de>
 * @package calendar
 * @copyright (c) 2006 by Christian Binder <christian.binder@freakmail.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * General object of the calendar ajax class
 */
class calendar_ajax {

	/**
	 * calendar object to handle events
	 *
	 * @var calendar_boupdate
	 */
	var $calendar;

	function __construct()
	{
		$this->calendar = new calendar_boupdate();
	}

	/**
	 * moves an event to another date/time
	 *
	 * @param string $eventID id of the event which has to be moved
	 * @param string $calendarOwner the owner of the calendar the event is in
	 * @param string $targetDateTime the datetime where the event should be moved to, format: YYYYMMDD
	 * @param string $targetOwner the owner of the target calendar
	 * @param string $durationT the duration to support resizable calendar event
	 * @return string XML response if no error occurs
	 */
	function moveEvent($eventId,$calendarOwner,$targetDateTime,$targetOwner,$durationT=null)
	{
		// we do not allow dragging into another users calendar ATM
		if(!$calendarOwner == $targetOwner)
		{
			return false;
		}

		$old_event=$event=$this->calendar->read($eventId);
		if (!$durationT)
		{
			$duration=$event['end']-$event['start'];
		}
		else
		{
			$duration = $durationT;
		}

		$event['start'] = $this->calendar->date2ts($targetDateTime);
		$event['end'] = $event['start']+$duration;
		$status_reset_to_unknown = false;
		$sameday = (date('Ymd', $old_event['start']) == date('Ymd', $event['start']));
		foreach((array)$event['participants'] as $uid => $status)
		{
			calendar_so::split_status($status,$q,$r);
			if ($uid[0] != 'c' && $uid[0] != 'e' && $uid != $this->calendar->user && $status != 'U')
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

		$conflicts=$this->calendar->update($event);

		$response = new xajaxResponse();
		if(!is_array($conflicts))
		{
			$response->addRedirect('');
		}
		else
		{
			$response->addScriptCall(
				'egw_openWindowCentered2',
				$GLOBALS['egw_info']['server']['webserver_url'].'/index.php?menuaction=calendar.calendar_uiforms.edit
					&cal_id='.$event['id']
					.'&start='.$event['start']
					.'&end='.$event['end']
					.'&non_interactive=true'
					.'&cancel_needs_refresh=true',
				'',750,410);
		}
		if ($status_reset_to_unknown)
		{
			foreach((array)$event['participants'] as $uid => $status)
			{
				if ($uid[0] != 'c' && $uid[0] != 'e' && $uid != $this->calendar->user)
				{
					calendar_so::split_status($status,$q,$r);
					$status = calendar_so::combine_status('U',$q,$r);
					$this->calendar->set_status($event['id'], $uid, $status, 0, true);
				}
			}
		}

		return $response->getXML();
	}
}
