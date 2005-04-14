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


printHTMLHead( getMLText("group_management") );
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();

if (!$user->isAdmin())
	die("Only an Administrator may use this feature");

//Neue Gruppe anlegen -----------------------------------------------------------------------------
if ($action == "addgroup")
{
	printStartBox(getMLText("add_group"));
	print "<div class=\"standardText\">";
	printMLText("adding_group");
	
	$name    = sanitizeString($name);
	$comment = sanitizeString($comment);
	
	$newGroup = addGroup($name, $comment);
	if ($newGroup)
	{
		printMLText("op_finished");
		printGoto(array(array(getMLText("group_management"), "../out/out.GroupMgr.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

//Gruppe löschen ----------------------------------------------------------------------------------
else if ($action == "removegroup")
{
	printStartBox(getMLText("rm_group"));
	print "<div class=\"standardText\">";
	printMLText("removing_group");
	
	$group = getGroup($groupid);
	
	if ($group->remove())
	{
		printMLText("op_finished");
		printGoto(array(array(getMLText("group_management"), "../out/out.GroupMgr.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

//Gruppe bearbeiten -------------------------------------------------------------------------------
else if ($action == "editgroup")
{
	$group = getGroup($groupid);
	
	printStartBox(getMLText("edit_group", array("groupname" => $group->getName())));
	print "<div class=\"standardText\">";
	printMLText("editing_group");
	
	$name    = sanitizeString($name);
	$comment = sanitizeString($comment);
	
	if ($group->getName() != $name)
		$group->setName($name);
	if ($group->getComment() != $comment)
		$group->setComment($comment);
	
	printMLText("op_finished");
	printGoto(array(array(getMLText("group_management"), "../out/out.GroupMgr.php")));
}

//Benutzer zu Gruppe hinzufügen -------------------------------------------------------------------
else if ($action == "addmember")
{
	printStartBox(getMLText("add_member"));
	print "<div class=\"standardText\">";
	printMLText("adding_member");
	
	$group = getGroup($groupid);
	$newMember = getUser($userid);
	
	$group->addUser($newMember);
	
	printMLText("op_finished");
	printGoto(array(array(getMLText("group_management"), "../out/out.GroupMgr.php")));
}

//Benutzer aus Gruppe entfernen -------------------------------------------------------------------
else if ($action == "rmmember")
{
	printStartBox(getMLText("remove_member"));
	print "<div class=\"standardText\">";
	printMLText("removing_member");
	
	$group = getGroup($groupid);
	$oldMember = getUser($userid);
	
	$group->removeUser($oldMember);
	
	printMLText("op_finished");
	printGoto(array(array(getMLText("group_management"), "../out/out.GroupMgr.php")));
}


print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();

?>
