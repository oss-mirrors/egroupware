<?php
/**
 * eGroupWare phpFreeChat integration
 *
 * @link http://www.egroupware.org
 * @link http://phpfreechat.sourceforge.net/
 * @package phpfreechat
 * @author Hans-Jürgen Tappe
 * @copyright 2009 by Hans-Jürgen Tappe
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$GLOBALS['egw_info'] = array(
	'flags' => array(
		'currentapp' => 'phpfreechat',
		'nonavbar' => true,
		'noheader' => true,
	),
);
include ('../header.inc.php');

// phpFreeChat need to creates it theme files
if (!file_exists(EGW_INCLUDE_ROOT.'/phpfreechat/phpfreechat/data/public/themes') &&
	!is_writable(EGW_INCLUDE_ROOT.'/phpfreechat/phpfreechat/data/public'))
{
	$GLOBALS['egw']->framework->render('<h3><b>'.lang('To complete the phpFreeChat installation you have to give the webserver write access to:').
		'<br />'.EGW_INCLUDE_ROOT.'/phpfreechat/phpfreechat/data/public'."</b></h3>\n<p>".
		lang(' You should remove the write access, once you see the chat!')."</p>\n");

	$GLOBALS['egw']->common->egw_exit();
}
require_once(EGW_INCLUDE_ROOT.'/phpfreechat/phpfreechat/src/phpfreechat.class.php');
include(EGW_INCLUDE_ROOT.'/phpfreechat/phpfreechat_config.php');
$chat = new phpFreeChat($params);

//echo "<html>\n";
//echo "<head>\n";
//echo "<title>EGroupware Chat</title>\n";
$GLOBALS['egw']->common->egw_header();
$chat->printJavaScript();
$chat->printStyle();
if (isset($_GET['referer'])) echo "<script>window.focus();</script>\n";
//echo "</head>\n";
//echo "<body>\n";
$chat->printChat();
echo "</body>\n";
//echo "</html>\n";
