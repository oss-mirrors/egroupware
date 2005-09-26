<?

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


$documentid = (int)$_GET['documentid'];
$version = (int)$_GET['version'];
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

if (in_array($content->getFileType(), $settings->_viewOnlineFileTypes))
	header("Content-Type: " . $content->getMimeType());

header("Content-Length: " . filesize($settings->_contentDir . $content->getDir() . $content->getFileName()));
header("Expires: 0");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

readfile($settings->_contentDir . $content->getDir() . $content->getFileName());
exit;

?>
