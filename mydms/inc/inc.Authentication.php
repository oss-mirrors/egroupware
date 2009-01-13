<?php
include_once("inc.Utils.php");

switch($GLOBALS['egw_info']['user']['preferences']['common']['lang'])
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
         //----
        case 'ru':
          $lang1 = 'Russian';
          break;
         //----
	default:
	  $lang1 = "English";
}

$user = getUser($GLOBALS['egw_info']['user']['account_id']);
$theme = 'English';
include $settings->_rootDir . "languages/".$lang1."/lang.inc";

?>
