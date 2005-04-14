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

printHTMLHead( getMLText("my_account") );
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();

printStartBox(getMLText("my_account"));
print "<div class=\"standardText\">";

printMLText("editing_user_data");

$fullname = sanitizeString($fullname);
$email    = sanitizeString($email);
$comment  = sanitizeString($comment);

if (isset($pwd) && ($pwd != ""))
	$user->setPwd(md5($pwd));

if ($user->getFullName() != $fullname)
	$user->setFullName($fullname);

if ($user->getEmail() != $email)
	$user->setEmail($email);

if ($user->getComment() != $comment)
	$user->setComment($comment);

if (isset($userfile) && ($userfile != "") && ($userfile != "none"))
{
	$lastDotIndex = strrpos(basename($userfile_name), ".");
	$fileType = substr($userfile_name, $lastDotIndex);
	if ($fileType != ".jpg")
	{
		printMLText("error_occured");
		printMLText("only_jpg_user_images");
		printGoBack();
		print "</div>";
		printEndBox();
		printCenterEnd();
		printHTMLFoot();
		exit;
	}
	//verkleinern des Bildes, so dass es 150 Pixel hoch ist
	if(file_exists($userfile))
	{
		// Originalbild einlesen
		$origImg = imagecreatefromjpeg($userfile);
		$width = imagesx($origImg);
		$height = imagesy($origImg);
		// Thumbnail im Speicher erzeugen
		$newHeight = 150;
		$newWidth = ($width/$height) * $newHeight;
		$newImg = imagecreate($newWidth, $newHeight);
		// Verkleinern
		imagecopyresized($newImg, $origImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		// In File speichern 
		imagejpeg($newImg, $userfile);
		// Aufräumen
		imagedestroy($origImg);
		imagedestroy($newImg);
	}
	$user->setImage($userfile, $userfile_type);
}

printMLText("op_finished");
printGoto(array(
			array(getMLText("content"), "../out/out.ViewFolder.php?folderid=1"),
			array(getMLText("my_account"), "../out/out.EditUserData.php")
));

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();

?>
