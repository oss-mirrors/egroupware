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

printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("add_document_link"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_READ)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("adding_document_link");
	
	$public = (isset($public) && $public == "true") ? true : false;
	if ($public && ($document->getAccessMode($user) == M_READ))
		$public = false;
	
	if (!is_numeric($docid))
		die("invalid document id");
	
	if ($document->addDocumentLink($docid, $user->getID(), $public))
	{
		$targetDoc = getDocument($docid);
		printMLText("op_finished");
		printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID()), 
						array($targetDoc->getName(), "../out/out.ViewDocument.php?documentid=".$targetDoc->getID())));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}


print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();

?>
