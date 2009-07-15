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
{
	// Only Modify the $file and $title variables.....
	$title = $appname;
	$file = array(
		'Preferences' => $GLOBALS['egw']->link('/index.php', 'menuaction=preferences.uisettings.index&appname=' . $appname),
		'Devices' => $GLOBALS['egw']->link('/index.php', 'menuaction=syncml.devices.listDevices'),
		'Documentation' => $GLOBALS['egw']->link('/'. $appname . '/index.php')
	);
	// Don't modify below this line
	display_section($appname,$title,$file);
}
