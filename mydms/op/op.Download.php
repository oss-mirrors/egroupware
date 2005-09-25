<?
//session_cache_limiter('none');
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.Authentication.php");

$documentid	= (int)$_GET['documentid'];
$version 	= (int)$_GET['version'];

$document = getDocument($documentid);


if ($document->getAccessMode($user) < M_READ)
{
	printHTMLHead( getMLText("download") );
	printCenterStart();
	printStartBox(getMLText("download"));
	print "<div class=\"standardText\">";
	printMLText("operation_not_allowed");
	printGoBack();
	print "</div>";
	printEndBox();
	printCenterEnd();
	printHTMLFoot();
	exit;
}

$content = $document->getContentByVersion($version);

if (is_bool($content) && !$content)
	die("Version " . $version . " of Document \"" . $document->getName() . "\" not found");

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: private");


header("Content-Type: application/force-download; name=\"" . $content->getOriginalFileName() . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($settings->_contentDir . $content->getPath() ));
header("Content-Disposition: attachment; filename=\"" . $content->getOriginalFileName() . "\"");

header("Content-Type: " . $content->getMimeType());



readfile($settings->_contentDir . $content->getPath());
exit();
?>