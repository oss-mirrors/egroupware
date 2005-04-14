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

if ($document->getAccessMode($user) < M_ALL)
	die ("Access denied");


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );

printTitleBar($document->getFolder());
printDocumentPageStart($document);
printPageHeader(getMLText("rm_document") . ": " . $document->getName());

printStartBox(getMLText("rm_document"));
?>

<form action="../op/op.RemoveDocument.php" name="form1">
	<input type="Hidden" name="documentid" value="<?print $documentid;?>">
	<div class="standardText">
	<? printMLText("confirm_rm_document", array ("documentname" => $document->getName()));?>
	</div><br>
	<input type="Submit" value="<?printMLText("rm_document");?>">
</form>


<?

printEndBox();
printDocumentPageEnd($document);
printHTMLFoot();

?>