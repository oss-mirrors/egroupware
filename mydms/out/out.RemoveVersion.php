<?php
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

//tim
$documentid = get_var('documentid','GET','');
//---

$document = getDocument($documentid);
if (!$document) die("Error: could not get document");
$version  = $document->getContentByVersion($version);
if (!$version) die("error: could not get version $version");


if ($document->getAccessMode($user) < M_ALL)
	die("Access denied");


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );

printTitleBar($document->getFolder());
printDocumentPageStart($document);
printPageHeader(getMLText("rm_version") . ": " . $document->getName());

printStartBox(getMLText("rm_version"));
?>

<form action="../op/op.RemoveVersion.php" name="form1">
	<input type="Hidden" name="documentid" value="<?php echo $documentid?>">
	<input type="Hidden" name="version" value="<?php echo $version->getVersion()?>">
	<div class="standardText">
	<?php printMLText("confirm_rm_version", array ("documentname" => $document->getName(), "version" => $version->getVersion()));?>
	</div><br>
	<input type="Submit" value="<?php printMLText("rm_version");?>">
</form>


<?php

printEndBox();
printDocumentPageEnd($document);
printHTMLFoot();

?>