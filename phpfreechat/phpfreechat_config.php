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

/* CONFIGURATION HERE. */
$params = array();
$params["serverid"] = $_REQUEST['domain'].md5(__FILE__); // used to identify the chat
$params["title"] = lang('eGroupware Chat');
/* Make the channel list configurable (via an admin interface?). */
$params["channels"] = array("eGroupware");
/* Pre-define some settings for known users (not from website) */
if ($GLOBALS['egw_info']['user']['account_lid'] != 'anonymous')
{
	$params["nick"] = $GLOBALS['egw_info']['user']['account_lid'];
	/* Can the language selection be done ins a more generic way? */
	switch ($GLOBALS['egw_info']['user']['preferences']['common']['lang'])
	{
	case 'ar':
		$params['language'] = 'ar_LB';
		break;
	case 'ba':
		$params['language'] = 'ba_BA';
		break;
	case 'be':
		$params['language'] = 'nl_BE';
		break;
	case 'bn':
		$params['language'] = 'bn_BD';
		break;
	case 'br':
	case 'pt-br':
		$params['language'] = 'pt_BR';
		break;
	case 'da':
		$params['language'] = 'da_DK';
		break;
	case 'de':
		$params['language'] = 'de_DE-formal';
		break;
	case 'el':
		$params['language'] = 'el_GR';
		break;
	case 'en':
		$params['language'] = 'en_US';
		break;
	case 'eo':
		$params['language'] = 'eo';
		break;
	case 'es-es':
		$params['language'] = 'es_ES';
		break;
	case 'gl':
		$params['language'] = 'gl_ES';
		break;
	case 'hr':
		$params['language'] = 'hr_HR';
		break;
	case 'hu':
		$params['language'] = 'hu_HU';
		break;
	case 'hy':
		$params['language'] = 'hy_AM';
		break;
	case 'id':
		$params['language'] = 'id_ID';
		break;
	case 'it':
		$params['language'] = 'it_IT';
		break;
	case 'ja':
		$params['language'] = 'ja_JP';
		break;
	case 'ko':
		$params['language'] = 'ko_KR';
		break;
	case 'nb':
		$params['language'] = 'nb_NO';
		break;
	case 'nn':
		$params['language'] = 'nn_NO';
		break;
	case 'oc':
		$params['language'] = 'oc_FR';
		break;
	case 'sr':
		$params['language'] = 'sr_CS';
		break;
	case 'sv':
		$params['language'] = 'sv_SE';
		break;
	case 'uk':
		$params['language'] = 'uk_RO';
		break;
	case 'vi':
		$params['language'] = 'vi_VN';
		break;
	case 'zh-tw':
		$params['language'] = 'zh_TW';
		break;
	default:
		$params['language'] = $GLOBALS['egw_info']['user']['preferences']['common']['lang'].'_'.
			strtoupper($GLOBALS['egw_info']['user']['preferences']['common']['lang']);
		break;
	}
	$params["nickmeta"] = array(
		'Full Name' => $GLOBALS['egw_info']['user']['account_fullname'],
	);
	/*
	 * A Frozen nick will not allow users to change their nick name
	 * and will also prohibit joining the chat multiple times (e.g. from
	 * different offices with the same login).
	$params["frozen_nick"] = true;
	*/
}
$params["dyn_params"] = array(
	"channels",
	"nick",
	'language',
	"nickmeta",
);
/**
 * phpFreeChat caches these values in $files_dir/phpfreechat/private/cache/default*.php
 * If you modifiy the config in EGroupware, you need to remove this file!
 */
$params["data_private_path"] = $GLOBALS['egw_info']['server']['files_dir'].'/phpfreechat/private';
$params["data_public_path"] = dirname(__FILE__).'/phpfreechat/data/public';
$params["data_public_url"] = $GLOBALS['egw_info']['server']['webserver_url']."/phpfreechat/phpfreechat/data/public";
$params["server_script_url"] = $GLOBALS['egw']->link('/phpfreechat/index.php');
$params['height'] = '400px';