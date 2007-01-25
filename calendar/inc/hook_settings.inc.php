<?php
	/**************************************************************************\
	* eGroupWare - Calendar Preferences                                        *
	* http://www.egroupware.org                                                *
	* Based on Webcalendar by Craig Knudsen <cknudsen@radix.net>               *
	*          http://www.radix.net/~cknudsen                                  *
	* Modified by Mark Peters <skeeter@phpgroupware.org>                       *
	* Modified by Ralf Becker <ralfbecker@outdoor-training.de>                 *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$bocal =& CreateObject('calendar.bocal');
	$bocal->check_set_default_prefs();

	$default = array(
		'day'          => lang('Dayview'),
		'day4'         => lang('Four days view'),
		'week'         => lang('Weekview'),
		'month'        => lang('Monthview'),
		'planner_cat'  => lang('Planner by category'),
		'planner_user' => lang('Planner by user'),
		'listview'     => lang('Listview'),
	);
	$grid_views = array(
		'all' => lang('all'),
		'day_week' => lang('Dayview').', '.lang('Four days view').' &amp; '.lang('Weekview'),
		'day4' => lang('Dayview').' &amp; '.lang('Four days view'),
		'day' => lang('Dayview'),
	);
	/* Select list with number of day by week */
	$week_view = array(
		'5' => lang('Weekview without weekend'),
		'7' => lang('Weekview with weekend'),
	);

	$mainpage = array(
		'1' => lang('Yes'),
		'0' => lang('No'),
	);
/*
	$summary = array(
		'no'     => lang('Never'),
		'daily'  => lang('Daily'),
		'weekly' => lang('Weekly')
	);
	create_select_box('Receive summary of appointments','summary',$summary,
		'Do you want to receive a regulary summary of your appointsments via email?<br>The summary is sent to your standard email-address on the morning of that day or on Monday for weekly summarys.<br>It is only sent when you have any appointments on that day or week.');
*/
	$updates = array(
		'no'             => lang('Never'),
		'add_cancel'     => lang('on invitation / cancelation only'),
		'time_change_4h' => lang('on time change of more than 4 hours too'),
		'time_change'    => lang('on any time change too'),
		'modifications'  => lang('on all modification, but responses'),
		'responses'      => lang('on participant responses too')
	);
	$update_formats = array(
		'none'     => lang('None'),
		'extended' => lang('Extended'),
		'ical'     => lang('iCal / rfc2445')
	);
	$event_details = array(
		'to-fullname' => lang('Fullname of person to notify'),
		'to-firstname'=> lang('Firstname of person to notify'),
		'to-lastname' => lang('Lastname of person to notify'),
		'title'       => lang('Title of the event'),
		'description' => lang('Description'),
		'startdate'   => lang('Start Date/Time'),
		'enddate'     => lang('End Date/Time'),
		'olddate'     => lang('Old Startdate'),
		'category'    => lang('Category'),
		'location'    => lang('Location'),
		'priority'    => lang('Priority'),
		'participants'=> lang('Participants'),
		'owner'       => lang('Owner'),
		'repetition'  => lang('Repetitiondetails (or empty)'),
		'action'      => lang('Action that caused the notify: Added, Canceled, Accepted, Rejected, ...'),
		'link'        => lang('Link to view the event'),
		'disinvited'  => lang('Participants disinvited from an event'),
	);
	$weekdaystarts = array(
		'Monday'   => lang('Monday'),
		'Sunday'   => lang('Sunday'),
		'Saturday' => lang('Saturday')
	);

	for ($i=0; $i < 24; ++$i)
	{
		$times[$i] = $GLOBALS['egw']->common->formattime($i,'00');
	}

	$intervals = array(
		5	=> '5',
		10	=> '10',
		15	=> '15',
		20	=> '20',
		30	=> '30',
		45	=> '45',
		60	=> '60'
	);
	$groups = $GLOBALS['egw']->accounts->membership($GLOBALS['egw_info']['user']['account_id']);
	$options = array('0' => lang('none'));
	if (is_array($groups))
	{
		foreach($groups as $group)
		{
			$options[$group['account_id']] = $GLOBALS['egw']->common->grab_owner_name($group['account_id']);
		}
	}
	$defaultfilter = array(
		'all'     => lang('all'),
		'private' => lang('private only'),
//		'public'  => lang('global public only'),
//		'group'   => lang('group public only'),
//		'private+public' => lang('private and global public'),
//		'private+group'  => lang('private and group public'),
//		'public+group'   => lang('global public and group public')
	);
	$freebusy_url = $bocal->freebusy_url($GLOBALS['egw_info']['user']['account_lid'],$GLOBALS['egw_info']['user']['preferences']['calendar']['freebusy_pw']);
	$freebusy_help = lang('Should not loged in persons be able to see your freebusy information? You can set an extra password, different from your normal password, to protect this informations. The freebusy information is in iCal format and only include the times when you are busy. It does not include the event-name, description or locations. The URL to your freebusy information is %1.','<a href="'.$freebusy_url.'" target="_blank">'.$freebusy_url.'</a>');

	$GLOBALS['settings'] = array(
		'defaultcalendar' => array(
			'type'   => 'select',
			'label'  => 'default calendar view',
			'name'   => 'defaultcalendar',
			'values' => $default,
			'help'   => 'Which of calendar view do you want to see, when you start calendar ?',
			'xmlrpc' => True,
			'admin'  => False
		),
		'days_in_weekview' => array(
			'type'   => 'select',
			'label'  => 'default week view',
			'name'   => 'days_in_weekview',
			'values' => $week_view,
			'help'   => 'Do you want a weekview with or without weekend?',
			'xmlrpc' => True,
			'admin'  => False
		),
		'mainscreen_showevents' => array(
			'type'   => 'select',
			'label'  => 'show default view on main screen',
			'name'   => 'mainscreen_showevents',
			'values' => $mainpage,
			'help'   => 'Displays your default calendar view on the startpage (page you get when you enter eGroupWare or click on the homepage icon)?',
			'xmlrpc' => True,
			'admin'  => False
		),
		'show_rejected' => array(
			'type'   => 'check',
			'label'  => 'Show invitations you rejected',
			'name'   => 'show_rejected',
			'help'   => 'Should invitations you rejected still be shown in your calendar ?<br>You can only accept them later (eg. when your scheduling conflict is removed), if they are still shown in your calendar!'
		),
		'weekdaystarts' => array(
			'type'   => 'select',
			'label'  => 'weekday starts on',
			'name'   => 'weekdaystarts',
			'values' => $weekdaystarts,
			'help'   => 'This day is shown as first day in the week or month view.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'workdaystarts' => array(
			'type'   => 'select',
			'label'  => 'work day starts on',
			'name'   => 'workdaystarts',
			'values' => $times,
			'help'   => 'This defines the start of your dayview. Events before this time, are shown above the dayview.<br>This time is also used as a default starttime for new events.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'workdayends' => array(
			'type'   => 'select',
			'label'  => 'work day ends on',
			'name'   => 'workdayends',
			'values' => $times,
			'help'   => 'This defines the end of your dayview. Events after this time, are shown below the dayview.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'use_time_grid' => array(
			'type'   => 'select',
			'label'  => 'Views with fixed time intervals',
			'name'   => 'use_time_grid',
			'values' => $grid_views,
			'help'   => 'For which views should calendar show distinct lines with a fixed time interval.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'interval' => array(
			'type'   => 'select',
			'label'  => 'Length of the time interval',
			'name'   => 'interval',
			'values' => $intervals,
			'help'   => 'How many minutes should each interval last?',
			'xmlrpc' => True,
			'admin'  => False
		),
		'defaultlength' => array(
			'type'    => 'input',
			'label'   => 'default appointment length (in minutes)',
			'name'    => 'defaultlength',
			'help'    => 'Default length of newly created events. The length is in minutes, eg. 60 for 1 hour.',
			'default' => '',
			'size'    => 3,
			'xmlrpc' => True,
			'admin'  => False
		),
		'planner_start_with_group' => array(
			'type'   => 'select',
			'label'  => 'Preselected group for entering the planner',
			'name'   => 'planner_start_with_group',
			'values' => $options,
			'help'   => 'This group that is preselected when you enter the planner. You can change it in the planner anytime you want.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'default_private' => array(
			'type'  => 'check',
			'label' => 'Set new events to private',
			'name'  => 'default_private',
			'help'  => 'Should new events created as private by default ?',
			'xmlrpc' => True,
			'admin'  => False
		),
		'receive_updates' => array(
			'type'   => 'select',
			'label'  => 'Receive email updates',
			'name'   => 'receive_updates',
			'values' => $updates,
			'help'   => "Do you want to be notified about new or changed appointments? You be notified about changes you make yourself.<br>You can limit the notifications to certain changes only. Each item includes all the notification listed above it. All modifications include changes of title, description, participants, but no participant responses. If the owner of an event requested any notifcations, he will always get the participant responses like acceptions and rejections too.",
			'xmlrpc' => True,
			'admin'  => False
		),
		'update_format' => array(
			'type'   => 'select',
			'label'  => 'Format of event updates',
			'name'   => 'update_format',
			'values' => $update_formats,
			'help'   => 'Extended updates always include the complete event-details. iCal\'s can be imported by certain other calendar-applications.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'notifyAdded' => array(
			'type'   => 'notify',
			'label'  => 'Notification messages for added events ',
			'name'   => 'notifyAdded',
			'rows'   => 5,
			'cols'   => 50,
			'help'   => 'This message is sent to every participant of events you own, who has requested notifcations about new events.<br>You can use certain variables which get substituted with the data of the event. The first line is the subject of the email.',
			'default' => '',
			'values' => $event_details,
			'xmlrpc' => True,
			'admin'  => False
		),
		'notifyCanceled' => array(
			'type'   => 'notify',
			'label'  => 'Notification messages for canceled events ',
			'name'   => 'notifyCanceled',
			'rows'   => 5,
			'cols'   => 50,
			'help'   => 'This message is sent for canceled or deleted events.',
			'default' => '',
			'values' => $event_details,
			'subst_help' => False,
			'xmlrpc' => True,
			'admin'  => False
		),
		'notifyModified' => array(
			'type'   => 'notify',
			'label'  => 'Notification messages for modified events ',
			'name'   => 'notifyModified',
			'rows'   => 5,
			'cols'   => 50,
			'help'   => 'This message is sent for modified or moved events.',
			'default' => '',
			'values' => $event_details,
			'subst_help' => False,
			'xmlrpc' => True,
			'admin'  => False
		),
		'notifyDisinvited' => array(
			'type'   => 'notify',
			'label'  => 'Notification messages for disinvited participants ',
			'name'   => 'notifyDisinvited',
			'rows'   => 5,
			'cols'   => 50,
			'help'   => 'This message is sent to disinvited participants.',
			'default' => '',
			'values' => $event_details,
			'subst_help' => False,
			'xmlrpc' => True,
			'admin'  => False
		),
		'notifyResponse' => array(
			'type'   => 'notify',
			'label'  => 'Notification messages for your responses ',
			'name'   => 'notifyResponse',
			'rows'   => 5,
			'cols'   => 50,
			'help'   => 'This message is sent when you accept, tentative accept or reject an event.',
			'default' => '',
			'values' => $event_details,
			'subst_help' => False,
			'xmlrpc' => True,
			'admin'  => False
		),
		'notifyAlarm' => array(
			'type'   => 'notify',
			'label'  => 'Notification messages for your alarms',
			'name'   => 'notifyAlarm',
			'rows'   => 5,
			'cols'   => 50,
			'help'   => 'This message is sent when you set an Alarm for a certain event. Include all information you might need.',
			'default' => '',
			'values' => $event_details,
			'subst_help' => False,
			'xmlrpc' => True,
			'admin'  => False
		),
// disabled free/busy stuff til it gets rewritten with new Horde iCal classes -- RalfBecker 2006/03/03
		'freebusy' => array(
			'type'  => 'check',
			'label' => 'Make freebusy information available to not loged in persons?',
			'name'  => 'freebusy',
			'help'  => $freebusy_help,
			'run_lang' => false,
			'default' => '',
			'subst_help' => False,
			'xmlrpc' => True,
			'admin'  => False,
		),
		'freebusy_pw' => array(
			'type'  => 'input',
			'label' => 'Password for not loged in users to your freebusy information?',
			'name'  => 'freebusy_pw',
			'help'  => 'If you dont set a password here, the information is available to everyone, who knows the URL!!!',
			'xmlrpc' => True,
			'admin'  => False
		)
	);
