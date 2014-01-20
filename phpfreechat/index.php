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

// this section is introduced to enable reconnecting with the same nick, when not properly disconnected before
// initialize the global config object
$c =& pfcGlobalConfig::Instance( $params );
//error_log(__METHOD__.__LINE__.array2string($c));
// need to initiate the user config object here because it uses sessions
$u =& pfcUserConfig::Instance();
$channel2name = $name2channel =array();
foreach ((array)$u->channels as $key => $values)
{
	$channel2name[$values['recipient']] = $values['name'];
	$name2channel[$values['recipient']] = $values['name'];
}
//error_log(__METHOD__.__LINE__.array2string($u));
$pfcContainer =& pfcContainer::Instance();

$nick = $params['nick'];
$nickid = $pfcContainer->getNickId($nick);
$cmd = "notice";
//error_log(__METHOD__.__LINE__.array2string($nickid));
// get the current user's channels list
$channels = array();
$ret2 = $pfcContainer->getMeta("nickid-to-channelid",$nickid);
//error_log(__METHOD__.__LINE__.array2string($ret2));
foreach($ret2["value"] as $userchan)
{
	//error_log(__METHOD__.__LINE__.array2string($userchan));
	$userchan = $pfcContainer->decode($userchan);
	if ($userchan != 'SERVER')
	{
		// tell the others
		$param = lang("%1 is reconnecting to channel %2",$nick,(!empty($channel2name[$userchan])?$channel2name[$userchan]:$userchan));
		$pfcContainer->write($userchan, $nick, $cmd, $param);
		// disconnect the user from each joined channels
		$pfcContainer->removeNick($userchan, $nickid);
		$channels[] = $userchan;
	}
}
// now disconnect the user from the server
// (order is important because the SERVER channel has timestamp informations)
$userchan = 'SERVER';
$du = $pfcContainer->removeNick($userchan, $nickid);
// the above section is introduced to enable reconnecting with the same nick, when not properly disconnected before

// tell framework freechat needs eval and inline javascript :(
egw_ckeditor_config::set_csp_script_src_attrs();

//echo "<html>\n";
//echo "<head>\n";
//echo "<title>EGroupware Chat</title>\n";
$GLOBALS['egw']->common->egw_header();
$chat->printJavaScript();
$chat->printStyle();
if (isset($_GET['referer'])) echo '<script language="JavaScript">
window.focus();
</script>';
//echo "</head>\n";
//echo "<body>\n";
$chat->printChat();
echo "</body>\n";
//echo "</html>\n";
