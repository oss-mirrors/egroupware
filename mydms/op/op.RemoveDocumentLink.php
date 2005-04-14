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
$link = getDocumentLink($linkid);
$responsibleUser = $link->getUser();

printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("remove_document_link"));

print "<div class=\"standardText\">";

$accessMode = $document->getAccessMode($user);
if (
	($accessMode < M_READ)
	|| (($accessMode == M_READ) && ($responsibleUser->getID() != $user->getID()))
	|| (($accessMode > M_READ) && (!$user->isAdmin()) && ($responsibleUser->getID() != $user->getID()) && !$link->isPublic())
   )
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("removing_document_link");
	
	if (!is_numeric($linkid))
		die("invalid link id");
	
	if ($document->removeDocumentLink($linkid))
	{
		printMLText("op_finished");
		printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID())));
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
