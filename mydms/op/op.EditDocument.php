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


$documentid = (int)$_GET['documentid'];
$fname		= $_GET['fname'];
$comment	= $_GET['comment'];
$keywords	= $_GET['keywords'];
$sequence	= $_GET['sequence'];

$document = getDocument($documentid);


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("edit_document_props"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("editing_document_props");
	
	$fname =     sanitizeString($fname);
	$comment =  sanitizeString($comment);
	$keywords = sanitizeString($keywords);
	if (!is_numeric($sequence) && $sequence != "keep")
		die ("invalid sequence value");
	
	if (
			(($document->getName() == $fname) || $document->setName($fname))
			&& (($document->getComment() == $comment) || $document->setComment($comment))
			&& (($document->getKeywords() == $keywords) || $document->setKeywords($keywords))
			&& (($sequence == "keep") || $document->setSequence($sequence))
		)
	{
		printMLText("op_finished");
		printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID()), 
				array(getMLText("edit_document_props_again"), "../out/out.EditDocument.php?documentid=".$document->getID())));
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
