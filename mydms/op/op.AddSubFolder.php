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


$folderid	= (int)$_GET['folderid'];
$fname		= $_GET['fname'];
$comment	= $_GET['comment'];
$sequence	= $_GET['sequence'];

$folder = getFolder($folderid);



printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printCenterStart();

printStartBox(getMLText("add_subfolder"));

print "<div class=\"standardText\">";


if ($folder->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("adding_sub_folder", array("subfoldername" => $fname, "foldername" => $folder->getName()));

	$fname = sanitizeString($fname);
	$comment = sanitizeString($comment);
	if (!is_numeric($sequence))
		die ("invalid sequence value");
	

 	$subFolder = $folder->addSubFolder($fname, $comment, $user, $sequence);
	
	if (is_object($subFolder))
	{
		printMLText("op_finished");
		printGoto(array(array($folder->getName(), "../out/out.ViewFolder.php?folderid=".$folder->getID()), 
						array($subFolder->getName(), "../out/out.ViewFolder.php?folderid=".$subFolder->getID())));
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
