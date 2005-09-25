<?
include_once("inc.Utils.php");

switch($GLOBALS['phpgw_info']['user']['preferences']['common']['lang'])
{
	case 'zh':
	  $lang1 = 'Chinese';
	  break;
	case 'en':
          $lang1 = 'English';
          break;
        case 'de':
          $lang1 = 'German';
          break;
	default:
	  $lang1 = "English";
}

$user = getUser($GLOBALS['phpgw_info']['user']['account_id']);
$theme = 'English';
include $settings->_rootDir . "languages/".$lang1."/lang.inc";

?>
