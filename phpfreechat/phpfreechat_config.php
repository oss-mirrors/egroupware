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
$params["title"] = lang('eGroupware Chat');
// set timeout to 1 Minute, as we experienced problems with random disconnects when runnig phpfreechat within a farm on different
// machines. Timeout is usually set to 35000 (35s), and is used to determine if a user is still online or not (e.g.: by closing or crashing
// his browser, or the phpfreechat window (without phpfreechat recieving the /quit command))
$params["timeout"] = 60000;
/* Make the channel list configurable (via an admin interface?). */
$categories = new categories($GLOBALS['egw_info']['user']['account_id'],'phpfreechat');
$channels = array();
foreach((array)$categories->return_sorted_array(0,False,'','','',false) as $cat)
{
	if ($cat['appname']=='phpfreechat') $channels[] = $cat['name'];
}
$params["channels"] = (empty($channels)? array("eGroupware","eGroupware-".$GLOBALS['egw']->accounts->id2name($GLOBALS['egw_info']['user']['account_primary_group'])):$channels);
if ($GLOBALS['egw_info']['user']['apps']['admin'])
{
	$params["isadmin"] = true;
}
/* some configured params */
$config = config::read('phpfreechat');
$frozen_nick = true;
if (!empty($config['frozen_nick'])|| $config['frozen_nick']=='False')
{
	$frozen_nick = ($config['frozen_nick']=='False'?False:True);
}
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
	case 'si':	// using croation for slovenian for now
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
	// if phpFreeChat has not translation for users language, fall back to English
	if (!file_exists(EGW_SERVER_ROOT.'/phpfreechat/phpfreechat/i18n/'.$params['language']))
	{
		$params['language'] = 'en_US';
	}
	$params["nickmeta"] = array(
		'Full Name' => $GLOBALS['egw_info']['user']['account_fullname'],
	);
	/*
	 * A Frozen nick will not allow users to change their nick name
	 * and will also prohibit joining the chat multiple times (e.g. from
	 * different offices with the same login).
	*/
	$params["frozen_nick"] = $frozen_nick;

	// Maximum nick length
	$params['max_nick_len'] =($config['max_nick_len']? $config['max_nick_len']:64);
}
// if you use NFS you may want to use, since logging is enabled by default
$params['skip_proxies'] = array('log');
// if you want logging, consider using a different path than NFS
//$params['proxies_cfg']['log']['path'] = '/mp/phpfreechat/';
// params that are not cached:
$params["dyn_params"] = array(
	"channels",
	"nick",
	"frozen_nick",
	'max_nick_len',
	'language',
	"nickmeta",
	"data_public_path",
	"data_private_path",
	"data_public_url",
	"server_script_url",
 	"isadmin",
	'container_cfg_mysql_host','container_cfg_mysql_port','container_cfg_mysql_database','container_cfg_mysql_username','container_cfg_mysql_password',
);
/**
 * phpFreeChat caches all params - values (but the dyn_params) in $files_dir/phpfreechat/private/cache/default*.php
 * If you modifiy the config in EGroupware, you need to remove this file!
 */
$params["data_private_path"] = $GLOBALS['egw_info']['server']['files_dir'].'/phpfreechat/private';
$params["data_public_path"] = dirname(__FILE__).'/phpfreechat/data/public';
$params["data_public_url"] = $GLOBALS['egw_info']['server']['webserver_url']."/phpfreechat/phpfreechat/data/public";
$params["server_script_url"] = $GLOBALS['egw']->link('/phpfreechat/index.php');
$params['height'] = '400px';
// eGroupware install_id used as serverid
$params["serverid"] = $GLOBALS['egw_info']['server']['install_id'];
// mysql integration: note your serverid must fit the fieldlength of your database tables server column.
if (substr($GLOBALS['egw_info']['server']['db_type'],0,5)=='mysql')
{
/*
      container_cfg_mysql_host : the host of your Database. Default value is “localhost”
      container_cfg_mysql_port : the port of your database. default value is 3306
      container_cfg_mysql_database : your database's name. Default value is “phpfreechat”
      container_cfg_mysql_table : the table within your database. Default value is “phpfreechat”
      container_cfg_mysql_username : username to connect to your Database. Default value is “root”
      container_cfg_mysql_password : password to identify the username that connects to the database. Default value is ””
*/
	$params['container_type']='mysql';
	$params['container_cfg_mysql_host']=$GLOBALS['egw_info']['server']['db_host'];
	$params['container_cfg_mysql_port']=$GLOBALS['egw_info']['server']['db_port'];
	$params['container_cfg_mysql_database']=$GLOBALS['egw_info']['server']['db_name'];
	$params['container_cfg_mysql_table']='egw_phpfreechat';
	$params['container_cfg_mysql_username']=$GLOBALS['egw_info']['server']['db_user'];
	$params['container_cfg_mysql_password']=$GLOBALS['egw_info']['server']['db_pass'];
	// depending how long your server field is, you must trim the serverid accordingly
	$serverid_length = 32;
	$params["container_cfg_mysql_fieldtype_server"] = 'varchar('.$serverid_length.')';
	$params["serverid"] = substr($GLOBALS['egw_info']['server']['install_id'],0,$serverid_length); // used to identify the chat
}
