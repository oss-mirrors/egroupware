<?php
include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.DBAccess.php");


$login = sanitizeString($login);

if($GLOBALS['egw']->auth->authenticate($GLOBALS['egw_info']['user']['account_lid'], $GLOBALS['egw_info']['user']['passwd'], 'text')) {
}


$queryStr = "DELETE FROM phpgw_mydms_Sessions WHERE " . mktime() . " - lastAccess > 86400";
if (!$db->getResult($queryStr))
	die ("Error while removing old sessions from phpgw_mydms_Sessions: " . $db->getErrorMsg());

//Erstellen einer Sitzungs-ID

$id = "" . rand() . mktime() . rand() . "";
$id = md5($id);

$lang      = sanitizeString($lang);
$sesstheme = sanitizeString($sesstheme);

$userid = $GLOBALS['egw']->accounts->name2id($GLOBALS['egw_info']['user']['account_lid']);

//Einfügen eines neuen Datensatzes in phpgw_mydms_Sessions
$queryStr = "INSERT INTO phpgw_mydms_Sessions (id, userID, lastAccess, theme, language) VALUES ('$id', userid, ".mktime().", '$sesstheme', 'English')";
if (!$db->getResult($queryStr))
	die ("Error while adding Session to phpgw_mydms_Sessions: " . $db->getErrorMsg());

//Setzen des Sitzungs-Cookies
setcookie("mydms_session", $id, 0, $settings->_httpRoot);

//Weiterleiten zur Startseite
header("Location: ../out/out.ViewFolder.php?folderid=1");
print "Login successful";

?>
