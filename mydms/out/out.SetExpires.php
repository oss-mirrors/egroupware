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
printPageHeader(getMLText("expires") . ": " . $document->getName());

printStartBox(getMLText("expires"));
?>

<form action="../op/op.SetExpires.php">
	<input type="Hidden" name="documentid" value="<?print $documentid;?>">
	<table>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("expires");?>:</td>
			<td class="standardText">
				<input type="Radio" name="expires" value="false"<?if (!$document->expires()) print " checked";?>><?printMLText("does_not_expire");?><br>
				<input type="radio" name="expires" value="true"<?if ($document->expires()) print " checked";?>><? if ($document->expires()) printDateChooser($document->getExpires(), "exp"); else printDateChooser(-1, "exp"); ?>
			</td>
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