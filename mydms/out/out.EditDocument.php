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
$folder = $document->getFolder();

if ($document->getAccessMode($user) < M_READWRITE)
	die ("Access denied");

printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
?>

<script language="JavaScript">
function checkForm()
{
	msg = "";
	if (document.form1.name.value == "") msg += "<?printMLText("js_no_name");?>\n";
	if (document.form1.comment.value == "") msg += "<?printMLText("js_no_comment");?>\n";
	if (document.form1.keywords.value == "") msg += "<?printMLText("js_no_keywords");?>\n";
	if (msg != "")
	{
		alert(msg);
		return false;
	}
	else
		return true;
}
</script>

<?
printTitleBar($document->getFolder());
printDocumentPageStart($document);
printPageHeader(getMLText("edit_document_props") . ": " . $document->getName());

printStartBox(getMLText("edit_document_props"));
?>

<form action="../op/op.EditDocument.php" name="form1" onsubmit="return checkForm();">
	<input type="Hidden" name="documentid" value="<?print $documentid;?>">
	<table cellpadding="3">
		<tr>
			<td class="inputDescription"><?printMLText("name");?>:</td>
			<td><input name="fname" value="<?print $document->getName();?>"></td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("comment");?>:</td>
			<td><textarea name="comment" rows="4" cols="30"><?print $document->getComment();?></textarea></td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("keywords");?>:</td>
			<td class="standardText">
				<textarea name="keywords" rows="4" cols="30"><?print $document->getKeywords();?></textarea><br>
				<a href="javascript:chooseKeywords();"><?printMLText("use_default_keywords");?></a>
				<script language="JavaScript">
					var openDlg;
					
					function chooseKeywords() {
						openDlg = open("out.KeywordChooser.php", "openDlg", "width=500,height=400,scrollbars=yes,resizable=yes");
					}
				</script>
			</td>
		</tr>
		<?
			if ($folder->getAccessMode($user) > M_READ)
			{
				print "<tr>";
				print "<td class=\"inputDescription\">" . getMLText("sequence") . ":</td>";
				print "<td>";
				printSequenceChooser($folder->getDocuments(), $document->getID());
				print "</td></tr>";
			}
		?>
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