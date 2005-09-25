<?
include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
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

$folderid	= (int)$_POST['folderid'];
$folder		= getFolder($folderid);

// form data
$userfile	= $_FILES['userfile']['tmp_name'];
$fname		= (!empty($_POST['fname']) ? $_POST['fname'] : $_FILES['userfile']['name']);
$comment	= $_POST['comment'];
$keywords	= $_POST['keywords'];
$expires	= $_POST['expires'];
$expday		= $_POST['expday'];
$expmonth	= $_POST['expmonth'];
$expyear	= $_POST['expyear'];
$sequence	= $_POST['sequence'];

printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printCenterStart();

printStartBox(getMLText("add_document"));

print "<div class=\"standardText\">";


if ($folder->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	if (!isset($userfile) || ($userfile == "none") || ($userfile == ""))
	{
		printMLText("uploading_failed");
		printGoBack();
	}
	else
	{
		printMLText("adding_document", array("documentname" => $fname, "foldername" => $folder->getName()));
		
		$fname		= sanitizeString($fname);
		$comment	= sanitizeString($comment);
		$keywords	= sanitizeString($keywords);
		$userfile_type	= sanitizeString($_FILES['userfile']['type']);
		$userfile_name	= sanitizeString($_FILES['userfile']['name']);

		if (!is_numeric($sequence))
			die ("invalid sequence value");
		
		$lastDotIndex	= strrpos(basename($userfile_name), ".");
		if (is_bool($lastDotIndex) && !$lastDotIndex)
			$fileType = ".";
		else
			$fileType = substr($userfile_name, $lastDotIndex);
		
		$expires = ($expires == "true") ? mktime(0,0,0, $expmonth, $expday, $expyear) : false;
		
		$newDocument = $folder->addDocument($fname, $comment, $expires, $user, $keywords, $userfile, basename($userfile_name), $fileType, $userfile_type, $sequence);
		
		if (is_bool($newDocument) && !$newDocument)
		{
			printMLText("error_occured");
			printGoBack();
		}
		else
		{
			printMLText("op_finished");
			printGoto(array(array($folder->getName(), "../out/out.ViewFolder.php?folderid=".$folder->getID()), 
					array($newDocument->getName(), "../out/out.ViewDocument.php?documentid=".$newDocument->getID())));
		}
	}
}



print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>
