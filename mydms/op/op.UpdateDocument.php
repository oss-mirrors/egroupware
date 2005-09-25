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

$comment	= $_POST['comment'];
$documentid	= (int)$_POST['documentid'];
$expires	= $_POST['expires'];
$expday		= (int)$_POST['expday'];
$expmonth	= (int)$_POST['expmonth'];
$expyear	= (int)$_POST['expyear'];
$userfile	= $_FILES['userfile']['tmp_name'];


$document = getDocument($documentid);



printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("update_document"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_READWRITE)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	if ($document->isLocked())
	{
		$lockingUser = $document->getLockingUser();
		
		if (($lockingUser->getID() != $user->getID()) && ($document->getAccessMode($user) != M_ALL))
		{
			printMLText("update_locked_msg", array("username" => $lockingUser->getFullName(), "email" => $lockingUser->getEmail()));
			printMLText("no_update_cause_locked");
			printGoBack();
			
			print "</div>";
			printEndBox();
			printCenterEnd();
			printHTMLFoot();
			exit;
		}
		else
			$document->setLocked(false);
	}
	
	if (!isset($userfile) || ($userfile == "none") || ($userfile == ""))
	{
		printMLText("uploading_failed");
		printGoBack();
	}
	else
	{
		printMLText("updating_document");
		
		$comment  = sanitizeString($comment);
		$userfile_type = sanitizeString($userfile_type);
		$userfile_name = sanitizeString($userfile_name);
		
		$lastDotIndex = strrpos(basename($userfile_name), ".");
		if (is_bool($lastDotIndex) && !$lastDotIndex)
			$fileType = ".";
		else
			$fileType = substr($userfile_name, $lastDotIndex);
		
		if (!$document->addContent($comment, $user, $userfile, basename($userfile_name), $fileType, $userfile_type))
		{
			printMLText("error_occured");
			printGoBack();
		}
		else
		{
			$expires = ($expires == "true") ? mktime(0,0,0, $expmonth, $expday, $expyear) : false;
			
			if (!$document->setExpires($expires))
			{
				printMLText("error_occured");
				printGoBack();
			}
			else
			{
				printMLText("op_finished");
				printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID())));
			}
		}
	}
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>
