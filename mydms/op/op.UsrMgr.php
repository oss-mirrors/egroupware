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


function resizeImage($imageFile)
{
	// Originalbild einlesen
	$origImg = imagecreatefromjpeg($imageFile);
	$width = imagesx($origImg);
	$height = imagesy($origImg);
	// Thumbnail im Speicher erzeugen
	$newHeight = 150;
	$newWidth = ($width/$height) * $newHeight;
	$newImg = imagecreate($newWidth, $newHeight);
	// Verkleinern
	imagecopyresized($newImg, $origImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
	// In File speichern 
	imagejpeg($newImg, $imageFile);
	// Aufräumen
	imagedestroy($origImg);
	imagedestroy($newImg);
	
	return true;
}


printHTMLHead( getMLText("user_management") );
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();

if (!$user->isAdmin())
	die("Only an Administrator may use this feature");


//Neuen Benutzer anlegen --------------------------------------------------------------------------
if ($action == "adduser")
{
	printStartBox(getMLText("add_user"));
	print "<div class=\"standardText\">";
	printMLText("adding_user");
	
	$login   = sanitizeString($login);
	$name    = sanitizeString($name);
	$email   = sanitizeString($email);
	$comment = sanitizeString($comment);
	
	$newUser = addUser($login, md5($pwd), $name, $email, $comment);
	if ($newUser)
	{
		printMLText("op_finished");
		
		if (isset($userfile) && ($userfile != "") && ($userfile != "none"))
		{
			printMLText("setting_user_image");
			$lastDotIndex = strrpos(basename($userfile_name), ".");
			$fileType = substr($userfile_name, $lastDotIndex);
			if ($fileType != ".jpg")
			{
				printMLText("error_occured");
				printMLText("only_jpg_user_images");
			}
			else
			{
				resizeImage($userfile);
				$newUser->setImage($userfile, $userfile_type);
				printMLText("op_finished");
			}
		}
		printGoto(array(array(getMLText("user_management"), "../out/out.UsrMgr.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

//Benutzer löschen --------------------------------------------------------------------------------
else if ($action == "removeuser")
{
	printStartBox(getMLText("rm_user"));
	print "<div class=\"standardText\">";
	printMLText("removing_user");
	
	$userToRemove = getUser($userid);
	
	if ($userToRemove->remove())
	{
		printMLText("op_finished");
		printGoto(array(array(getMLText("user_management"), "../out/out.UsrMgr.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

//Benutzer bearbeiten -----------------------------------------------------------------------------
else if ($action == "edituser")
{
	$editedUser = getUser($userid);
	
	printStartBox(getMLText("edit_user", array("username" => $editedUser->getFullName())));
	print "<div class=\"standardText\">";
	printMLText("editing_user");
	
	$login   = sanitizeString($login);
	$name    = sanitizeString($name);
	$email   = sanitizeString($email);
	$comment = sanitizeString($comment);
	
	if ($editedUser->getLogin() != $login)
		$editedUser->setLogin($login);
	if (isset($pwd) && ($pwd != ""))
		$editedUser->setPwd(md5($pwd));
	if ($editedUser->getFullName() != $name)
		$editedUser->setFullName($name);
	if ($editedUser->getEmail() != $email)
		$editedUser->setEmail($email);
	if ($editedUser->getComment() != $comment)
		$editedUser->setComment($comment);
	printMLText("op_finished");
	
	if (isset($userfile) && ($userfile != "") && ($userfile != "none"))
	{
		printMLText("setting_user_image");
		$lastDotIndex = strrpos(basename($userfile_name), ".");
		$fileType = substr($userfile_name, $lastDotIndex);
		if ($fileType != ".jpg")
		{
			printMLText("error_occured");
			printMLText("only_jpg_user_images");
		}
		else
		{
			resizeImage($userfile);
			$editedUser->setImage($userfile, $userfile_type);
			printMLText("op_finished");
		}
	}
	printGoto(array(array(getMLText("user_management"), "../out/out.UsrMgr.php")));
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();

?>
