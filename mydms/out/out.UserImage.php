<?
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.FileUtils.php");
include("../inc/inc.Authentication.php");

$myUser = getUser($userid); //es soll ja auch möglich sein, die bilder von anderen anzuzeigen

if (!$myUser->hasImage())
	die ("Kein Bild verfügbar");

$queryStr = "SELECT * FROM phpgw_mydms_UserImages WHERE userID = " . $userid;
$resArr = $db->getResultArray($queryStr);
if (is_bool($resArr) && $resArr == false)
	return false;

$resArr = $resArr[0];

header("ContentType: " . $resArr["mimeType"]);

print base64_decode($resArr["image"]);
exit;

?>