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

if ($document->getAccessMode($user) < M_READWRITE)
	die ("Access denied");


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printDocumentPageStart($document);
printPageHeader(getMLText("move_document") . ": " . $document->getName());

printStartBox(getMLText("move_document"));
?>

<form action="../op/op.MoveDocument.php" name="form1">
	<input type="Hidden" name="documentid" value="<?print $documentid;?>">
	<table>
		<tr>
			<td class="inputDescription"><?printMLText("choose_target_folder");?>:</td>
			<td><?printFolderChooser("form1", M_READWRITE, -1);?></td>
		</tr>
		<tr>
			<td colspan="2"><br><input type="Submit"></td>
		</tr>
	</table>
	</form>


<?

printEndBox();
printDocumentPageEnd($document);
printHTMLFoot();

?>