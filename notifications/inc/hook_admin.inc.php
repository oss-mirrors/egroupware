<?php
	/**
	 * eGroupWare - Notifications
	 *
	 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	 * @package notifications
	 * @link http://www.egroupware.org
	 * @author Christian Binder <christian@jaytraxx.de>
	 * @version $Id: hook_preferences.inc.php 22498 2006-09-25 10:20:46Z jaytraxx $
	 */

	$title = $appname;
	
	$file = Array(	'Site Configuration' => $GLOBALS['egw']->link('/index.php','menuaction=admin.uiconfig.index&appname=notifications'));
	display_section($appname,$title,$file);
?>
