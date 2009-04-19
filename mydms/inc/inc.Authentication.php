<?php
include_once("inc.Utils.php");

switch($GLOBALS['egw_info']['user']['preferences']['common']['lang'])
{
	case 'zh':
		$lang1 = 'Chinese';
		$mydms_charset = 'utf-8';
		break;
	case 'en':
		$lang1 = 'English';
		break;
	case 'de':
		$lang1 = 'German';
		$mydms_charset = 'iso-8859-1';
		break;
	case 'ru':
		$lang1 = 'Russian';
		$mydms_charset = 'utf-8';
	break;
		default:
		$lang1 = "English";
}

$user = getUser($GLOBALS['egw_info']['user']['account_id']);
$theme = 'English';
include $settings->_rootDir . "languages/".$lang1."/lang.inc";

// translate original MyDMS phrases to eGW's charset
if (isset($mydms_charset) && $mydms_charset != $GLOBALS['egw']->translation->charset())
{
	$text = $GLOBALS['egw']->translation->convert($text,$mydms_charset);
	//echo "<p>converted MyDMS translations from $mydms_charset to ".$GLOBALS['egw']->translation->charset()."</p>\n";
}
