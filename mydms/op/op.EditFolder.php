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


$folder = getFolder($folderid);



printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printCenterStart();

printStartBox(getMLText("edit_folder_props"));

print "<div class=\"standardText\">";


if ($folder->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	printMLText("editing_folder_props");
	
	$fname =     sanitizeString($fname);

	$comment =  sanitizeString($comment);
	if (!is_numeric($sequence) && $sequence != "keep")
		die ("invalid sequence value");
	
	if (
			(($folder->getName() == $fname) || $folder->setName($fname))
			&& (($folder->getComment() == $comment) || $folder->setComment($comment))
			&& (($sequence == "keep") || $folder->setSequence($sequence))
		)
	{
		printMLText("op_finished");
		printGoto(array(array($folder->getName(), "../out/out.ViewFolder.php?folderid=".$folder->getID()), 
				array(getMLText("edit_folder_props_again"), "../out/out.EditFolder.php?folderid=".$folder->getID())));
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
