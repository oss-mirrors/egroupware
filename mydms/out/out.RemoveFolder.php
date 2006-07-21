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

$folderid = (isset($_GET['folderid'])) ? (int) $_GET['folderid'] : NULL;
$folder = getFolder($folderid);

if ($folder->getAccessMode($user) < M_ALL)
	die ("Access denied");


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );

printTitleBar($folder);
printFolderPageStart($folder);
printPageHeader(getMLText("rm_folder") . ": " . $folder->getName());

printStartBox(getMLText("rm_folder"));
?>

<form action="../op/op.RemoveFolder.php" name="form1">
	<input type="Hidden" name="folderid" value="<?php print $folderid;?>">
	<div class="standardText">
	<?php 
		print lang('Do you really want to remove the folder "%1" and its content?<br>Be careful: This action cannot be undone.', $folder->getName());
	?>
	</div><br>
	<input type="Submit" value="<?php printMLText("rm_folder");?>">
</form>


<?php

printEndBox();
printFolderPageEnd($folder);
printHTMLFoot();

?>