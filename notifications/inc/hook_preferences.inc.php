<?php
	/**
	 * eGroupWare - Notifications
	 *
	 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	 * @package notifications
	 * @link http://www.egroupware.org
	 * @author Cornelius Weiss <nelius@cwtech.de>
	 * @version $Id$
	 */

	$title = $appname;
	
	$file = Array(	'Preferences' => $GLOBALS['egw']->link('/index.php',array('menuaction'=>'preferences.uisettings.index','appname'=>'notifications')),
								);
	display_section($appname,$title,$file);
?>
