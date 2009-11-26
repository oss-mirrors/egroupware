<?php
/**
 * EGroupware - SyncML
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package syncml
 * @subpackage preferences
 * @author Joerg Lehrke <jlehrke@noc.de>
 * @copyright (c) 2009 by Joerg Lehrke <jlehrke@noc.de>
 * @version $Id$
 */

class syncml_hooks
{
	/**
	 * Settings hook
	 *
	 * @param array|string $hook_data
	 * @return array
	 */
	static function settings($hook_data)
	{
		$show_entries = array(
			0 => lang('Client Wins'),
			1 => lang('Server Wins'),
			2 => lang('Merge Data'),
			3 => lang('Resolv with Duplicates'),
			4 => lang('Ignore Client'),
			5 => lang('Enforce Server'),
		);

		$selectYesNo = array(
			0 => lang('no'),
			1 => lang('yes')
		);

		$select_CalendarFilter = array(
			'default'     => lang('Not rejected'),
			'accepted'    => lang('Accepted'),
			'owner'       => lang('Owner too'),
			'all'         => lang('All incl. rejected'),
		);

		$devices_Entries = array();
		if (!$hook_data['setup'])
		{
			$tzs = array(0 => 'Use Event TZ');
			$tzs += egw_time::getTimezones();

			require_once(EGW_INCLUDE_ROOT.'/syncml/inc/class.devices.inc.php');

			$user = $GLOBALS['egw_info']['user']['account_id'];

			// list the distribution lists of this user
			$addressbook_bo = new addressbook_bo();
			$perms = EGW_ACL_READ | EGW_ACL_ADD | EGW_ACL_EDIT | EGW_ACL_DELETE;
			$show_addr_lists = $addressbook_bo->get_lists($perms,array('' => lang('none')));
			$show_addr_addr = $addressbook_bo->get_addressbooks($perms,lang('All'));
			unset($show_addr_addr[0]); // No Acounts

			// list the InfoLog filters
			$infolog_bo = new infolog_bo();
			$show_infolog_filters = $infolog_bo->filters;

			// list the calendars this user has access to
			$calendar_bo = new calendar_bo();
			$show_calendars = array();
			foreach($calendar_bo->list_cals() as $grant)
			{
				$show_calendars[$grant['grantor']] = $grant['name'];
			}

			// list the calendar categories of this user
			$categories = new categories($user, 'calendar');
			$calendar_categories = $categories->return_array('app', 0, false, '', 'ASC', 'cat_name', true);
			$show_cal_cats = array();
			foreach ($calendar_categories as $cat)
			{
				$show_cal_cats[$cat['id']] = $cat['name'];
			}
			// list the addressbook categories of this user
			$categories = new categories($user, 'addressbook');
			$addressbook_categories = $categories->return_array('app', 0, false, '', 'ASC', 'cat_name', true);
			$show_addr = array();
			foreach ($addressbook_categories as $cat)
			{
				$show_addr_cats[$cat['id']] = $cat['name'];
			}

			// list the infolog categories of this user
			$categories = new categories($user, 'infolog');
			$infolog_categories = $categories->return_array('app', 0, false, '', 'ASC', 'cat_name', true);
			$show_info_cats = array();
			foreach ($infolog_categories as $cat)
			{
				$show_info_cats[$cat['id']] = $cat['name'];
			}

			// Device specific settings
			$devices =& CreateObject('syncml.devices');
			$user_devices = $devices->getAllUserDevices();
			foreach ($user_devices as $device)
			{
				$label = '<b>'. lang('Settings for') . ' ' . $device['dev_manufacturer'] . ' ' . $device['dev_model'] . ' v' . $device['dev_swversion'] . '&nbsp;</b>';
				$intro_name = 'deviceExtension-' . $device['owner_deviceid'];
				$me_name = 'maxEntries-' . $device['owner_deviceid'];
				$ue_name = 'uidExtension-' . $device['owner_deviceid'];
				$nba_name = 'nonBlockingAllday-' . $device['owner_deviceid'];
				$tz_name = 'tzid-' . $device['owner_deviceid'];
				$device_Entry = array(
					$intro_name => array(
						'type'  => 'subsection',
						'title' =>  $label,
						'xmlrpc' => False,
						'admin'  => False
					),
					$me_name => array (
						'type'		=> 'input',
						'label'		=> 'Max Entries',
						'name'		=> $me_name,
						'size'		=> 3,
						'maxsize'	=> 10,
						'default'	=> 10,
						'xmlrpc'	=> True,
						'admin'		=> False,
					),
					$ue_name => array(
						'type'		=> 'check',
						'label'		=> 'UID Decription Extension',
						'name'		=> $ue_name,
						'values'    => $selectYesNo,
						'default'	=> 0,
						'xmlrpc'	=> True,
						'admin'		=> False,
					),
					$nba_name => array(
						'type'		=> 'check',
						'label'		=> 'Non Blocking Allday Events',
						'name'		=> $nba_name,
						'values'    => $selectYesNo,
						'default'	=> 0,
						'xmlrpc'	=> True,
						'admin'		=> False,
					),
					$tz_name => array(
						'type'   => 'select',
						'label'  => 'Time zone',
						'name'   => $tz_name,
						'values' => $tzs,
						'help'   => 'Please select the timezone of your device.',
						'xmlrpc' => True,
						'admin'  => False,
						'default'=> null,
					),
				);
				$devices_Entries += $device_Entry;
			}
		}
		/* Settings array for SyncML */
		return array(
			'prefssection' => array(
				'type'  => 'section',
				'title' => lang('Preferences for the SyncML'),
				'xmlrpc' => False,
				'admin'  => False
			),
			'prefintro' => array(
				'type'  => 'subsection',
				'title' => '<h3>' . lang('Preferences for the SyncML Conflict Handling<br/>and Server R/O Options') . '</h3>',
				'xmlrpc' => False,
				'admin'  => False
			),
			'./calendar' => array(
				'type'   => 'select',
				'label'  => './calendar',
				'name'   => './calendar',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'calendar' => array(
				'type'   => 'select',
				'label'  => 'calendar',
				'name'   => 'calendar',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./events' => array(
				'type'   => 'select',
				'label'  => './events',
				'name'   => './events',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'events' => array(
				'type'   => 'select',
				'label'  => 'events',
				'name'   => 'events',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./contacts' => array(
				'type'   => 'select',
				'label'  => './contacts',
				'name'   => './contacts',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'contacts' => array(
				'type'   => 'select',
				'label'  => 'contacts',
				'name'   => 'contacts',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./card' => array(
				'type'   => 'select',
				'label'  => './card',
				'name'   => './card',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'card' => array(
				'type'   => 'select',
				'label'  => 'card',
				'name'   => 'card',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./tasks' => array(
				'type'   => 'select',
				'label'  => './tasks',
				'name'   => './tasks',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'tasks' => array(
				'type'   => 'select',
				'label'  => 'tasks',
				'name'   => 'tasks',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./jobs' => array(
				'type'   => 'select',
				'label'  => './jobs',
				'name'   => './jobs',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'jobs' => array(
				'type'   => 'select',
				'label'  => 'jobs',
				'name'   => 'jobs',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./caltasks' => array(
				'type'   => 'select',
				'label'  => './caltasks',
				'name'   => './caltasks',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'caltasks' => array(
				'type'   => 'select',
				'label'  => 'caltasks',
				'name'   => 'caltasks',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./notes' => array(
				'type'   => 'select',
				'label'  => './notes',
				'name'   => './notes',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'notes' => array(
				'type'   => 'select',
				'label'  => 'notes',
				'name'   => 'notes',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./sifcalendar' => array(
				'type'   => 'select',
				'label'  => './sifcalendar',
				'name'   => './sifcalendar',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'sifcalendar' => array(
				'type'   => 'select',
				'label'  => 'sifcalendar',
				'name'   => 'sifcalendar',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./scal' => array(
				'type'   => 'select',
				'label'  => './scal',
				'name'   => './scal',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'scal' => array(
				'type'   => 'select',
				'label'  => 'scal',
				'name'   => 'scal',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./sifcontacts' => array(
				'type'   => 'select',
				'label'  => './sifcontacts',
				'name'   => './sifcontacts',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'sifcontacts' => array(
				'type'   => 'select',
				'label'  => 'sifcontacts',
				'name'   => 'sifcontacts',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./scard' => array(
				'type'   => 'select',
				'label'  => './scard',
				'name'   => './scard',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'scard' => array(
				'type'   => 'select',
				'label'  => 'scard',
				'name'   => 'scard',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./siftasks' => array(
				'type'   => 'select',
				'label'  => './siftasks',
				'name'   => './siftasks',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'siftasks' => array(
				'type'   => 'select',
				'label'  => 'siftasks',
				'name'   => 'siftasks',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./stask' => array(
				'type'   => 'select',
				'label'  => './stask',
				'name'   => './stask',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'stask' => array(
				'type'   => 'select',
				'label'  => 'stask',
				'name'   => 'stask',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./sifnotes' => array(
				'type'   => 'select',
				'label'  => './sifnotes',
				'name'   => './sifnotes',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'sifnotes' => array(
				'type'   => 'select',
				'label'  => 'sifnotes',
				'name'   => 'sifnotes',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'./snote' => array(
				'type'   => 'select',
				'label'  => './snote',
				'name'   => './snote',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'snote' => array(
				'type'   => 'select',
				'label'  => 'snote',
				'name'   => 'snote',
				'values' => $show_entries,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => 1
			),
			'uidintro' => array(
				'type'  => 'subsection',
				'title' => '<h3>' . lang('Minimum Accepted UID Length') . '</h3>',
				'xmlrpc' => False,
				'admin'  => False
			),
			'minimum_uid_length' => array (
				'type'		=> 'input',
				'label'		=> lang('Minimum UID Length'),
				'name'		=> 'minimum_uid_length',
				'size'		=> 2,
				'maxsize'	=> 3,
				'default'	=> 8,
				'xmlrpc'	=> True,
				'admin'		=> False,
			),
			'preffilter' => array(
				'type'  => 'subsection',
				'title' => '<h3>' . lang('Addressbook Synchronization Options') . '</h3>',
				'xmlrpc' => False,
				'admin'  => False
			),
			'filter_list' => array(
				'type'   => 'select',
				'label'  => 'Synchronize this list',
				'name'   => 'filter_list',
				'help'   => lang('This addressbook list of contacts will be synchronized.'),
				'values' => $show_addr_lists,
				'xmlrpc' => True,
				'admin'  => False,
			),
			'filter_addressbook' => array(
				'type'   => 'select',
				'label'  => 'Synchronize this addressbook',
				'name'   => 'filter_addressbook',
				'help'   => lang('Only this addressbook will be synchronized.'),
				'values' => $show_addr_addr,
				'xmlrpc' => True,
				'admin'  => False,
			),
			'calendarhistoryintro' => array(
				'type'  => 'subsection',
				'title' => '<h3>' . lang('Calendar Synchronization Options') . '</h3>',
				'xmlrpc' => False,
				'admin'  => False
			),
			'calendar_past' => array(
				'type'		=> 'input',
				'label'		=> lang('Calendar History Period'),
				'name'		=> 'calendar_past',
				'help'	    => lang('Your calendar will be synchronized up to this number of seconds in the past (2678400 seconds = 31 days).'),
				'size'		=> 8,
				'maxsize'	=> 9,
				'default'	=> 2678400,
				'xmlrpc'	=> True,
				'admin'		=> False,
			),
			'calendar_future' => array (
				'type'		=> 'input',
				'label'		=> lang('Calendar Future Period'),
				'name'		=> 'calendar_future',
				'help'	    => lang('Only events up to this number of seconds in the future will be synchonized (65000000 seconds > 2 years).'),
				'size'		=> 8,
				'maxsize'	=> 9,
				'default'	=> 65000000,
				'xmlrpc'	=> True,
				'admin'		=> False,
			),
			'calendar_filter' => array(
				'type'		=> 'select',
				'label' 	=> 'Calendar Filter',
				'name'		=> 'calendar_filter',
				'help'		=> lang('Only Events matching this filter criteria will be synchronized.'),
				'values'	=> $select_CalendarFilter,
				'default'	=> 'all',
				'xmlrpc'	 => True,
				'admin'  	=> False,
			),
			'calendar_owner' => array(
				'type'		=> 'select',
				'label' 	=> 'Syncronization Calendars',
				'name'		=> 'calendar_owner',
				'help'		=> lang('Events from all selected Calendars will be synchronized.'),
				'values'	=> $show_calendars,
				'default'	=> 'none',
				'xmlrpc'	 => True,
				'admin'  	=> False,
			),
			'taskoptionintro' => array(
				'type'  => 'subsection',
				'title' => '<h3>' . lang('Task Synchronization Options') . '</h3>',
				'xmlrpc' => False,
				'admin'  => False
			),
			'task_filter' => array(
				'type'   => 'select',
				'label'  => 'Synchronize this selection',
				'name'   => 'task_filter',
				'help'   => lang('Only Tasks matching this filter criteria will be synchronized.'),
				'values' => $show_infolog_filters,
				'xmlrpc' => True,
				'admin'  => False,
			),
			'noteoptionintro' => array(
				'type'  => 'subsection',
				'title' => '<h3>' . lang('Note Synchronization Options') . '</h3>',
				'xmlrpc' => False,
				'admin'  => False
			),
			'note_filter' => array(
				'type'   => 'select',
				'label'  => 'Synchronize this selection',
				'name'   => 'note_filter',
				'help'   => lang('Only Notes matching this filter criteria will be synchronized.'),
				'values' => $show_infolog_filters,
				'xmlrpc' => True,
				'admin'  => False,
			),
			'catintro' => array(
				'type'  => 'subsection',
				'title' => '<h3>' . lang('Categories for Conflict Duplicates') . '</h3>',
				'xmlrpc' => False,
				'admin'  => False
			),
			'calendar_conflict_category' => array(
				'type'   => 'select',
				'label'  => 'Calendar Conflict Category',
				'name'   => 'calendar_conflict_category',
				'help'   => lang('To this Calendar category a conflict duplicate will be added.'),
				'values' => $show_cal_cats,
				'xmlrpc' => True,
				'admin'  => False,
			),
			'adddressbook_conflict_category' => array(
				'type'   => 'select',
				'label'  => 'Addressbook Conflict Category',
				'name'   => 'addressbook_conflict_category',
				'help'   => lang('To this Addressbook category a conflict duplicate will be added.'),
				'values' => $show_addr_cats,
				'xmlrpc' => True,
				'admin'  => False,
			),
			'infolog_conflict_category' => array(
				'type'   => 'select',
				'label'  => 'InfoLog Conflict Category',
				'name'   => 'infolog_conflict_category',
				'help'   => lang('A duplicate infolog entry from a synchronization conflict will be assigned to this category.'),
				'values' => $show_info_cats,
				'xmlrpc' => True,
				'admin'  => False,
			),
			'max_entries' => array(
				'type'  => 'subsection',
				'title' => '<h2>' . lang('Device Specific Seetings')  . '</h2>' .
				lang('For <b>Max Entries</b> = 0 either <i>maxMsgSize</i> will be used or the default value 10.<br/>' .
					'With <b>Non Blocking Allday Events</b> set allday events will be nonblocking when imported from this device.<br/>' .
					'The <b>UID Extension</b> enables the preservation of vCalandar UIDs by appending them to <i>Description</i> field for this device.<br/>' .
				'The selected <b>Time zone</b> is used for calendar event syncronization with the device. If not set, the timezones of the events are used.'),
				'xmlrpc' => False,
				'admin'  => False
			),
		) + $devices_Entries;
	}

	/**
	 * Preferences hook
	 *
	 * @param array|string $hook_data
	 */
	static function preferences($hook_data)
	{
		// Only Modify the $file and $title variables.....
		$title = $appname = 'syncml';
		$file = array(
			'Preferences' => $GLOBALS['egw']->link('/index.php', 'menuaction=preferences.uisettings.index&appname=' . $appname),
			'Devices' => $GLOBALS['egw']->link('/index.php', 'menuaction=syncml.devices.listDevices'),
			'Documentation' => $GLOBALS['egw']->link('/'. $appname . '/index.php')
		);
		// Don't modify below this line
		display_section($appname,$title,$file);
	}
}
