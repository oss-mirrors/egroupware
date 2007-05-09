<?php
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

$documentid = (isset($_GET['documentid'])) ? (int) $_GET['documentid'] : NULL;
$version = (isset($_GET['version'])) ? (int) $_GET['version'] : NULL;

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
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: " . $content->getMimeType());
# Content-Length header does not work correctly on firefox and maybe other browser
# the right filesize gets send(like reported by ls on the filesystem)
# but the file stored by the firefox browser is about ~4000 ... 8000 bytes to small
# disabling Content-Length header solves this problem
#header("Content-Length: " . filesize($settings->_contentDir . $content->getPath() ) );
header("Content-Disposition: attachment; filename=\"" . $content->getOriginalFileName() . "\"");
header("Content-Transfer-Encoding: binary\n");

readfile($settings->_contentDir . $content->getPath());
exit();
?>