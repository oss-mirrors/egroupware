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

$folderid = (isset($folderid)) ? $folderid : 1;
$folder = getFolder($folderid);

if ($folder->getAccessMode($user) < M_READWRITE)
	die("access denied");

printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
?>

<script language="JavaScript">
function checkForm()
{
	msg = "";
	if (document.form1.userfile.value == "") msg += "<?printMLText("js_no_file");?>\n";
	if (document.form1.fname.value == "") msg += "<?printMLText("js_no_name");?>\n";
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
printTitleBar($folder);
printFolderPageStart($folder);
printPageHeader(getMLText("add_document") . ": " . $folder->getName());

printStartBox(getMLText("add_document"));
?>

<form action="../op/op.AddDocument.php" enctype="multipart/form-data" method="post" name="form1" onsubmit="return checkForm();">
	<input type="Hidden" name="folderid" value="<? print $folderid; ?>">
	<table>
		<tr>
			<td class="inputDescription"><?printMLText("local_file");?>:</td>
			<td><input type="File" name="userfile"></td>
		</tr>
		<tr>
			<td class="inputDescription"><?printMLText("name");?>:</td>
			<td><input name="fname"></td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("comment");?>:</td>
			<td><textarea name="comment" rows="4" cols="30"></textarea></td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("keywords");?>:</td>
			<td class="standardText">
				<textarea name="keywords" rows="4" cols="30"></textarea><br>
				<a href="javascript:chooseKeywords();"><?printMLText("use_default_keywords");?></a>
				<script language="JavaScript">
					var openDlg;
					
					function chooseKeywords() {
						openDlg = open("out.KeywordChooser.php", "openDlg", "width=500,height=400,scrollbars=yes,resizable=yes");
					}
				</script>
			</td>
		</tr>
		<tr>
			<td valign="top" class="inputDescription"><?printMLText("expires");?>:</td>
			<td class="standardText">
				<input type="Radio" name="expires" value="false" checked><?printMLText("does_not_expire");?><br>
				<input type="radio" name="expires" value="true"><?printDateChooser(-1, "exp");?>
			</td>
		</tr>
		<tr>
			<td class="inputDescription"><?printMLText("sequence");?>:</td>
			<td><?printSequenceChooser($folder->getDocuments());?></td>
		</tr>
		<tr>
			<td colspan="2"><br><input type="Submit"></td>
		</tr>
	</table>
</form>


<?

printEndBox();
printFolderPageEnd($folder);
printHTMLFoot();

?>