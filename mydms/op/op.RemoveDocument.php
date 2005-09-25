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


$documentid = $_GET['documentid'];
$document = getDocument($documentid);


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("rm_document"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_ALL)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("removing_document");
	$folder = $document->getFolder();
	
	if (!$document->remove())
	{
		printMLText("error_occured");
		printGoBack();
	}
	else
	{
		printMLText("op_finished");
		printGoto(array(array($folder->getName(), "../out/out.ViewFolder.php?folderid=".$folder->getID())));
	}
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>