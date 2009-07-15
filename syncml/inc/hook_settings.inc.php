<?php
/**
 * eGroupWare - SyncML
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package syncml
 * @subpackage preferences
 * @author Joerg Lehrke <jlehrke@noc.de>
 * @copyright (c) 2009 by Joerg Lehrke <jlehrke@noc.de>
 * @version $Id$
 */
require_once(EGW_INCLUDE_ROOT.'/syncml/inc/class.devices.inc.php');

$show_entries = array(
	0 => lang('Client Wins'),
	1 => lang('Server Wins'),
	2 => lang('Merge Data'),
	3 => lang('Resolv with Duplicates'),
	4 => lang('Ignore Client'),
	5 => lang('Enforce Server'),
);

$selectYesNot = array(
	0 => lang('no'),
	1 => lang('yes')
);

$user = $GLOBALS['egw_info']['user']['account_id'];

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
$infolog_categories = $categories->return_array('app', 0, false, '', 'ASC', 'cat_name', truee);
$show_info_cats = array();
foreach ($infolog_categories as $cat)
{
	$show_info_cats[$cat['id']] = $cat['name'];
}

// maxEntries for the user's devices
$devices =& CreateObject('syncml.devices');
$user_devices = $devices->getAllUserDevices();
$devices_Entries = array();
$device_Entry = array();
foreach ($user_devices as $device)
{
	$label = '<b>'. lang('Settings for') . ' ' . $device['dev_manufacturer'] . ' ' . $device['dev_model'] . ' v' . $device['dev_swversion'] . '&nbsp;</b>';
	$intro_name = 'deviceExtension-' . $device['owner_deviceid'];
	$me_name = 'maxEntries-' . $device['owner_deviceid'];
	$ue_name = 'uidExtension-' . $device['owner_deviceid'];
	$nba_name = 'nonBlockingAllday-' . $device['owner_deviceid'];
	$device_Entry[$name] = array(
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
			'label'		=> 'Non Blocking Allday Envents',
			'name'		=> $nba_name,
			'values'    => $selectYesNo,
			'default'	=> 0,
			'xmlrpc'	=> True,
			'admin'		=> False,
		)
	);
	$devices_Entries += $device_Entry[$name];
}

/* Settings array for SyncML */
$GLOBALS['settings'] = array(
	'prefssection' => array(
		'type'  => 'section',
		'title' => lang('Preferences for the SyncML'),
		'xmlrpc' => False,
		'admin'  => False
	),
	'prefintro' => array(
		'type'  => 'subsection',
		'title' => lang('Preferences for the SyncML Conflict Handling<br/>and Server R/O Options'),
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
	'calendarhistoryintro' => array(
		'type'  => 'subsection',
		'title' => '<h3>' . lang('Calendar Synchronization Period') . '</h3>',
		'xmlrpc' => False,
		'admin'  => False
	),
	'calendar_past' => array (
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
		'The <b>UID Extension</b> enables the preservation of vCalandar UIDs by appending them to <i>Description</i> field for this device.'),
		'xmlrpc' => False,
		'admin'  => False
	),
) + $devices_Entries;
