<?php
	/**************************************************************************\
	* eGroupWare - Preferences                                                 *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/* Setup some values to fill the array of this app's settings below */
	$templates = $GLOBALS['egw']->common->list_templates();
	foreach($templates as $var => $value)
	{
		$_templates[$var] = $templates[$var]['title'];
	}

	$themes = $GLOBALS['egw']->common->list_themes();
	foreach($themes as $value)
	{
		$_themes[$value] = $value;
	}

	$navbar_format = array(
		'icons'          => lang('Icons only'),
		'icons_and_text' => lang('Icons and text'),
		'text'           => lang('Text only')
	);

	$link_list_format = array(
		'icons'          => lang('Icons only'),
		'icons_and_text' => lang('Icons and text'),
		'text'           => lang('Text only')
	);

	$format = $GLOBALS['egw_info']['user']['preferences']['common']['dateformat'];
	$format = ($format ? $format : 'Y/m/d') . ', ';
	if($GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] == '12')
	{
		$format .= 'h:i a';
	}
	else
	{
		$format .= 'H:i';
	}
	for($i = -23.5; $i < 24.0; $i += 0.5)
	{
		$t = time() + round($i * 3600);
		$tz_offset[(string)$i] = sprintf('%3.1lf',$i) . ' ' . lang('hours').': ' . date($format,$t);
	}

	$date_formats = array(
		'm/d/Y' => 'm/d/Y',
		'm-d-Y' => 'm-d-Y',
		'm.d.Y' => 'm.d.Y',
		'Y/d/m' => 'Y/d/m',
		'Y-d-m' => 'Y-d-m',
		'Y.d.m' => 'Y.d.m',
		'Y/m/d' => 'Y/m/d',
		'Y-m-d' => 'Y-m-d',
		'Y.m.d' => 'Y.m.d',
		'd/m/Y' => 'd/m/Y',
		'd-m-Y' => 'd-m-Y',
		'd.m.Y' => 'd.m.Y',
		'd-M-Y' => 'd-M-Y'
	);

	$time_formats = array(
		'12' => lang('12 hour'),
		'24' => lang('24 hour')
	);

	$html_enter_mode = array(
		'p'		=> lang('p: Paragraph'),
		'div'	=> lang('div'),
		'br'	=> lang('br')
	);

	$rich_text_editor_skins = array(		
		'default'    => lang ('Default theme'),
		'office2003' => lang ('Office 2003 theme'),
		'silver'     => lang ('Silver theme')
	);

	$langs = $GLOBALS['egw']->translation->get_installed_langs();

	$user_apps = array();
	foreach($GLOBALS['egw_info']['user']['apps'] as $app => $data)
	{
		if($GLOBALS['egw_info']['apps'][$app]['status'] != 2 && $app)
		{
			$user_apps[$app] = $GLOBALS['egw_info']['apps'][$app]['title'] ? $GLOBALS['egw_info']['apps'][$app]['title'] : lang($app);
		}
	}

	$account_sels = array(
		'selectbox'     => lang('Selectbox'),
		'primary_group' => lang('Selectbox with primary group and search'),
		'popup'         => lang('Popup with search'),
		'groupmembers'  => lang('Selectbox with groupmembers'),
		'none'          => lang('No user-selection at all'),
	);

	$account_display = array(
		'firstname' => lang('Firstname'). ' '.lang('Lastname'),
		'lastname'  => lang('Lastname').', '.lang('Firstname'),
		'username'  => lang('username'),
		'firstall'  => lang('Firstname').' '.lang('Lastname').' ['.lang('username').']',
		'lastall'   => lang('Lastname').', '.lang('Firstname').' ['.lang('username').']',
		'all'       => '['.lang('username').'] '.lang('Lastname').', '.lang('Firstname')
	);

	/* Settings array for this app */
	$GLOBALS['settings'] = array(
		'maxmatchs' => array(
			'type'  => 'input',
			'label' => 'Max matches per page',
			'name'  => 'maxmatchs',
			'help'  => 'Any listing in eGW will show you this number of entries or lines per page.<br>To many slow down the page display, to less will cost you the overview.',
			'size'  => 3,
			'xmlrpc' => True,
			'admin'  => False
		),
		'template_set' => array(
			'type'   => 'select',
			'label'  => 'Interface/Template Selection',
			'name'   => 'template_set',
			'values' => $_templates,
			'help'   => 'A template defines the layout of eGroupWare and it contains icons for each application.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'theme' => array(
			'type'   => 'select',
			'label'  => 'Theme (colors/fonts) Selection',
			'name'   => 'theme',
			'values' => $_themes,
			'help'   => 'A theme defines the colors and fonts used by the template.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'navbar_format' => array(
			'type'   => 'select',
			'label'  => 'Show navigation bar as',
			'name'   => 'navbar_format',
			'values' => $navbar_format,
			'help'   => 'You can show the applications as icons only, icons with app-name or both.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'link_list_format' => array(
			'type'		=>	'select',
			'label'		=>	'Show links between eGroupWare aps as',
			'name'		=>	'link_list_format',
			'values'	=>	$link_list_format,
			'help'		=>	'You can show the linked entries with icons only, icons with app-name or both.',
			'xmlrpc'	=>	True,
			'admin'		=>	False
		),
		'link_list_thumbnail' => array(
			'type'		=>	'check',
			'label'		=>	'Display thumbnails for linked images',
			'name'		=>	'link_list_thumbnail',
			'help'		=>	'Images linked to an entry can be displayed as thumbnails.  You can turn this off to speed up page display.',
			'xmlrpc'	=>	True,
			'admin'		=>	False
		),
		'tz_offset' => array(
			'type'   => 'select',
			'label'  => 'Time zone offset',
			'name'   => 'tz_offset',
			'values' => $tz_offset,
			'help'   => 'How many hours are you in front or after the timezone of the server.<br>If you are in the same time zone as the server select 0 hours, else select your locale date and time.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'dateformat' => array(
			'type'   => 'select',
			'label'  => 'Date format',
			'name'   => 'dateformat',
			'values' => $date_formats,
			'help'   => 'How should eGroupWare display dates for you.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'timeformat' => array(
			'type'   => 'select',
			'label'  => 'Time format',
			'name'   => 'timeformat',
			'values' => $time_formats,
			'help'   => 'Do you prefer a 24 hour time format, or a 12 hour one with am/pm attached.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'country' => array(
			'type'   => 'select',
			'label'  => 'Country',
			'name'   => 'country',
			'values' => ExecMethod('phpgwapi.country.countries'),
			'help'   => 'In which country are you. This is used to set certain defaults for you.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'lang' => array(
			'type'   => 'select',
			'label'  => 'Language',
			'name'   => 'lang',
			'values' => $langs,
			'help'   => 'Select the language of texts and messages within eGroupWare.<br>Some languages may not contain all messages, in that case you will see an english message.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'spellchecker_lang' => array(
			'type'   => 'select',
			'label'  => 'Spellchecker language',
			'name'   => 'spellchecker_lang',
			'values' => $langs,
			'help'   => 'Select the language of the spellchecker integrated into the rich text editor.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'rte_enter_mode' => array(
			'type'   => 'select',
			'label'  => 'Rich text editor enter mode',
			'name'   => 'rte_enter_mode',
			'values' => $html_enter_mode,
			'help'   => 'Select how the rich text editor will generate the enter (linebreak) tag.',
			'xmlrpc' => True,
			'admin'  => False		
		),
		'rte_skin' => array(
			'type'   => 'select',
			'label'  => 'Rich text editor theme',
			'name'   => 'rte_skin',
			'values' => $rich_text_editor_skins,
			'help'   => 'Select the theme (visualization) of the rich text editor.',
			'xmlrpc' => True,
			'admin'  => False			
		),
		'show_currentusers' => array(
			'type'  => 'check',
			'label' => 'Show number of current users',
			'name'  => 'show_currentusers',
			'help'  => 'Should the number of active sessions be displayed for you all the time.',
			'xmlrpc' => False,
			'admin'  => True
		),
		'default_app' => array(
			'type'   => 'select',
			'label'  => 'Default application',
			'name'   => 'default_app',
			'values' => $user_apps,
			'help'   => "The default application will be started when you enter eGroupWare or click on the homepage icon.<br>You can also have more than one application showing up on the homepage, if you don't choose a specific application here (has to be configured in the preferences of each application).",
			'xmlrpc' => False,
			'admin'  => False
		),
		'currency' => array(
			'type'  => 'input',
			'label' => 'Currency',
			'name'  => 'currency',
			'help'  => 'Which currency symbol or name should be used in eGroupWare.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'account_selection' => array(
			'type'   => 'select',
			'label'  => 'How do you like to select accounts',
			'name'   => 'account_selection',
			'values' => $account_sels,
			'help'   => lang('The selectbox shows all available users (can be very slow on big installs with many users). The popup can search users by name or group.').' '.
				lang('The two last options limit the visibility of other users. Therefore they should be forced and apply NOT to administrators.'),
			'run_lang' => false,
			'xmlrpc' => True,
			'admin'  => False
		),
		'account_display' => array(
			'type'   => 'select',
			'label'  => 'How do you like to display accounts',
			'name'   => 'account_display',
			'values' => $account_display,
			'help'   => 'Set this to your convenience. For security reasons, you might not want to show your Loginname in public.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'show_help' => array(
			'type'   => 'check',
			'label'  => 'Show helpmessages by default',
			'name'   => 'show_help',
			'help'   => 'Should this help messages shown up always, when you enter the preferences or only on request.',
			'xmlrpc' => False,
			'admin'  => False
		),
		'enable_dragdrop' => array(
			'type'   => 'check',
			'label'  => 'Enable drag and drop functionality (experimental)',
			'name'   => 'enable_dragdrop',
			'help'   => 'Enables or disables drag and drop functions in all applications. If the browser does not support '.
				    'drag and drop, it will be disabled automatically. This feature is experimental at the moment.',
			'xmlrpc' => False,
			'admin'  => False
		),
		'csv_charset' => array(
			'type'   => 'select',
			'label'  => 'Charset for the CSV export',
			'name'   => 'csv_charset',
			'values' => $GLOBALS['egw']->translation->get_installed_charsets()+array('utf-8' => 'utf-8 (Unicode)'),		
			'help'   => 'Which charset should be used for the CSV export. The system default is the charset of this eGroupWare installation.',
			'xmlrpc' => True,
			'admin'  => false,
		),
	);
