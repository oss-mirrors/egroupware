<?php
/**
 * eGroupWare - Calendar setup
 *
 * @link http://www.egroupware.org
 * @package calendar
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$setup_info['calendar']['name']    = 'calendar';
$setup_info['calendar']['version'] = '1.7.011';
$setup_info['calendar']['app_order'] = 3;
$setup_info['calendar']['enable']  = 1;
$setup_info['calendar']['index']   = 'calendar.calendar_uiviews.index';

$setup_info['calendar']['license']  = 'GPL';
$setup_info['calendar']['description'] =
	'Powerful group calendar with meeting request system and ACL security.';
$setup_info['calendar']['note'] =
	'The calendar has been completly rewritten for eGroupWare 1.2.';
$setup_info['calendar']['author'] = $setup_info['calendar']['maintainer'] = array(
	'name'  => 'Ralf Becker',
	'email' => 'RalfBecker@outdoor-training.de'
);

$setup_info['calendar']['tables'][] = 'egw_cal';
$setup_info['calendar']['tables'][] = 'egw_cal_holidays';
$setup_info['calendar']['tables'][] = 'egw_cal_repeats';
$setup_info['calendar']['tables'][] = 'egw_cal_user';
$setup_info['calendar']['tables'][] = 'egw_cal_extra';
$setup_info['calendar']['tables'][] = 'egw_cal_dates';
$setup_info['calendar']['tables'][] = 'egw_cal_timezones';

/* The hooks this app includes, needed for hooks registration */
$setup_info['calendar']['hooks']['admin'] = 'calendar_hooks::admin';
$setup_info['calendar']['hooks']['deleteaccount'] = 'calendar.calendar_so.deleteaccount';
$setup_info['calendar']['hooks']['home'] = 'calendar_hooks::home';
$setup_info['calendar']['hooks']['preferences'] = 'calendar_hooks::preferences';
$setup_info['calendar']['hooks']['settings'] = 'calendar_hooks::settings';
$setup_info['calendar']['hooks']['sidebox_menu'] = 'calendar.calendar_ui.sidebox_menu';
$setup_info['calendar']['hooks']['search_link'] = 'calendar_hooks::search_link';
$setup_info['calendar']['hooks']['config_validate'] = 'calendar_hooks::config_validate';

/* Dependencies for this app to work */
$setup_info['calendar']['depends'][] = array(
	 'appname' => 'phpgwapi',
	 'versions' => Array('1.3','1.4','1.5','1.6','1.7')
);
$setup_info['calendar']['depends'][] = array(
	 'appname' => 'etemplate',
	 'versions' => Array('1.3','1.4','1.5','1.6','1.7')
);

// installation checks for calendar
$setup_info['calendar']['check_install'] = array(
	// check if PEAR is availible
	'' => array(
		'func' => 'pear_check',
		'from' => 'Calendar (iCal import+export)',
	),
	// check if PDO SQLite support is available
	'pdo_sqlite' => array(
		'func' => 'extension_check',
		'from' => 'Calendar',
	),
);


