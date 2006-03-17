<?php
	/**************************************************************************\
	* eGroupWare - InfoLog Preferences                                         *
	* http://www.eGroupWare.org                                                *
	* Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Setup some values to fill the array of this app's settings below */
	$show_entries = array(
		0 => lang('No'),
		1 => lang('Yes'),
		2 => lang('Yes').' - '.lang('show list of upcoming entries'),
	);

	$ui =& CreateObject('infolog.uiinfolog');	// need some labels from
	foreach($ui->filters as $key => $label)
	{
		$filters[$key] = lang($label);
	}
	unset($ui);
	$show_links = array(
		'all'    => lang('all links and attachments'),
		'links'  => lang('only the links'),
		'attach' => lang('only the attachments'),
		'none'   => lang('no links or attachments'),
 		'no_describtion' => lang('no describtion, links or attachments'),
	);

	/* Settings array for this app */
	$GLOBALS['settings'] = array(
		'homeShowEvents' => array(
			'type'   => 'select',
			'label'  => 'Show open entries: Tasks/Calls/Notes on main screen',
			'name'   => 'homeShowEvents',
			'values' => $show_entries,
			'help'   => 'Should InfoLog display your open entries - not finished tasks, phonecalls or notes - on the main screen. Works only if you dont selected an application for the main screen (in your preferences).',
			'xmlrpc' => True,
			'admin'  => False
		),
		'mainscreen_maxshow' => array(
			'type'   => 'input',
			'label'  => 'Max number of entries to display on the main screen',
			'name'   => 'mainscreen_maxshow',
			'size'    => 3,
			'maxsize'    => 10,
			'help'   => 'Only up to this number of entries are displayed on the main screen.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'defaultFilter' => array(
			'type'   => 'select',
			'label'  => 'Default Filter for InfoLog',
			'name'   => 'defaultFilter',
			'values' => $filters,
			'help'   => 'This is the filter InfoLog uses when you enter the application. Filters limit the entries to show in the actual view. There are filters to show only finished, still open or futures entries of yourself or all users.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'listNoSubs' => array(
			'type'   => 'check',
			'label'  => 'List no Subs/Childs',
			'name'   => 'listNoSubs',
			'help'   => 'Should InfoLog show Subtasks, -calls or -notes in the normal view or not. You can always view the Subs via there parent.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'show_links' => array(
			'type'   => 'select',
			'label'  => 'Show in the InfoLog list',
			'name'   => 'show_links',
			'values' => $show_links,
			'help'   => 'Should InfoLog show the links to other applications and/or the file-attachments in the InfoLog list (normal view when you enter InfoLog).',
			'xmlrpc' => True,
			'admin'  => False
		),
		'never_hide' => array(
			'type'   => 'check',
			'label'  => 'Never hide search and filters',
			'name'   => 'never_hide',
			'help'   => 'If not set, the line with search and filters is hidden for less entries then "max matches per page" (as defined in your common preferences).',
			'xmlrpc' => True,
			'admin'  => False
		),
		'show_times' => array(
			'type'   => 'check',
			'label'  => 'Show times',
			'name'   => 'show_times',
			'help'   => 'Show a column for used and planned times in the list.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'set_start' => array(
			'type'   => 'select',
			'label'  => 'Startdate for new entries',
			'name'   => 'set_start',
			'values' => array(
				'date'     => lang('todays date'),
				'datetime' => lang('actual date and time'),
				'empty'    => lang('leave it empty'),
			),
			'help'   => 'To what should the startdate of new entries be set.',
			'xmlrpc' => True,
			'admin'  => False
		),
	);

	unset($show_entries);
	unset($filters);
	unset($show_links);
