<?php
/**
 * TimeSheet - setup definitions
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package timesheet
 * @subpackage setup
 * @copyright (c) 2005/6 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$ 
 */

if (!defined('TIMESHEET_APP'))
{
	define('TIMESHEET_APP','timesheet');
}

$setup_info[TIMESHEET_APP]['name']      = TIMESHEET_APP;
$setup_info[TIMESHEET_APP]['version']   = '1.4';
$setup_info[TIMESHEET_APP]['app_order'] = 5;
$setup_info[TIMESHEET_APP]['tables']    = array('egw_timesheet', 'egw_timesheet_extra');
$setup_info[TIMESHEET_APP]['enable']    = 1;

$setup_info[TIMESHEET_APP]['author'] = 
$setup_info[TIMESHEET_APP]['maintainer'] = array(
	'name'  => 'Ralf Becker',
	'email' => 'RalfBecker@outdoor-training.de'
);
$setup_info[TIMESHEET_APP]['license']  = 'GPL';
$setup_info[TIMESHEET_APP]['description'] = 
'Tracking times and other activities for the Projectmanager.';
$setup_info[TIMESHEET_APP]['note'] = 
'The TimeSheet application is sponsored by:<ul>
<li> <a href="http://www.stylite.de" target="_blank">Stylite GmbH</a></li>
<li> <a href="http://www.outdoor-training.de" target="_blank">Outdoor Unlimited Training GmbH</a></li>
</ul>';

/* The hooks this app includes, needed for hooks registration */
$setup_info[TIMESHEET_APP]['hooks']['preferences'] = TIMESHEET_APP.'.ts_admin_prefs_sidebox_hooks.all_hooks';
$setup_info[TIMESHEET_APP]['hooks']['settings'] = TIMESHEET_APP.'.ts_admin_prefs_sidebox_hooks.settings';
$setup_info[TIMESHEET_APP]['hooks']['admin'] = TIMESHEET_APP.'.ts_admin_prefs_sidebox_hooks.all_hooks';
$setup_info[TIMESHEET_APP]['hooks']['sidebox_menu'] = TIMESHEET_APP.'.ts_admin_prefs_sidebox_hooks.all_hooks';
$setup_info[TIMESHEET_APP]['hooks']['search_link'] = TIMESHEET_APP.'.botimesheet.search_link';
$setup_info[TIMESHEET_APP]['hooks']['pm_cumulate'] = TIMESHEET_APP.'.botimesheet.cumulate';

/* Dependencies for this app to work */
$setup_info[TIMESHEET_APP]['depends'][] = array(
	 'appname' => 'phpgwapi',
	 'versions' => Array('1.3','1.4','1.5')
);
$setup_info[TIMESHEET_APP]['depends'][] = array(
	 'appname' => 'etemplate',
	 'versions' => Array('1.3','1.4','1.5')
);

