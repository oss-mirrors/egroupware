<?
include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.DBAccess.php");

//Code when running PHP as Module -----------------------------------------------------------------

/*
setcookie("mydms_logged_out", "true", 0, $settings->_httpRoot);
header("Location: ../out/out.ViewFolder.php");
print "Logout successful";
*/

//Code when running PHP in CGI-Mode ---------------------------------------------------------------

//Delete from phpgw_mydms_Sessions

$dms_session = $HTTP_COOKIE_VARS["mydms_session"];
$dms_session = sanitizeString($dms_session);

$queryStr = "DELETE FROM phpgw_mydms_Sessions WHERE id = '$dms_session'";
if (!$db->getResult($queryStr))
	die ("Error while removing session from phpgw_mydms_Sessions: " . $db->getErrorMsg());

//Delete Cookie
setcookie("mydms_session", $HTTP_COOKIE_VARS["mydms_session"], time()-3600, $settings->_httpRoot);

//Forward to Login-page
header("Location: ../out/out.Login.php");
print "Logout successful";


?>