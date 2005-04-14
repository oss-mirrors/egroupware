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
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.Authentication.php");


$document = getDocument($documentid);
if (!$document) die("Error: could not get document");
$version  = $document->getContentByVersion($version);
if (!$version) die("error: could not get version $version");


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("rm_version"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_ALL)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("removing_version", array("version" => $version->getVersion()));
	
	if (!$version->remove())
	{
		printMLText("error_occured");
		printGoBack();
	}
	else
	{
		printMLText("op_finished");
		printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID())));
	}
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>