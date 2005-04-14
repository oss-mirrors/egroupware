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

printStartBox(getMLText("edit_folder_access"));

print "<div class=\"standardText\">";


if ($folder->getAccessMode($user) < M_ALL)
{
	printMLText("operation_not_allowed");
	printGoBack();
}
else
{
	//Ändern des Besitzers ----------------------------------------------------------------------------
	if ($action == "setowner")
	{
		if (!$user->isAdmin())
			die("Only an Administrator may set a new owner");
		$newOwner = getUser($ownerid);
		printMLText("setting_owner");
		
		$folder->setOwner($newOwner);
	}

	//Änderung auf nicht erben ------------------------------------------------------------------------
	else if ($action == "notinherit")
	{
		if ($mode == "copy")
			printMLText("folder_set_not_inherit_copy");
		else
			printMLText("folder_set_not_inherit_empty");
		
		$defAccess = $folder->getDefaultAccess();
		$folder->setInheritAccess(false);
		$folder->setDefaultAccess($defAccess);
		
		if ($mode == "copy")
		{
			$parent = $folder->getParent();
			$accessList = $parent->getAccessList();
			
			foreach ($accessList["users"] as $userAccess)
				$folder->addAccess($userAccess->getMode(), $userAccess->getUserID(), true);
			foreach ($accessList["groups"] as $groupAccess)
				$folder->addAccess($groupAccess->getMode(), $groupAccess->getGroupID(), false);
		}
	}

	//Änderung auf erben ------------------------------------------------------------------------------
	else if ($action == "inherit")
	{
		printMLText("folder_set_inherit");
		
		$folder->clearAccessList();
		$folder->setInheritAccess(true);
	}

	//Standardberechtigung setzen----------------------------------------------------------------------
	else if ($action == "setdefault")
	{
		if (!is_numeric($mode))
			die ("invalid access mode");
			
		printMLText("folder_set_default_access");
		$folder->setDefaultAccess($mode);
	}

	//Bestehende Berechtigung änndern -----------------------------------------------------------------
	else if ($action == "editaccess")
	{
		printMLText("folder_edit_access");
		
		if (!is_numeric($mode))
			die ("invalid access mode");
		
		if (isset($userid)) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$folder->changeAccess($mode, $userid, true);
		}
		else if (isset($groupid)) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$folder->changeAccess($mode, $groupid, false);
		}
	}

	//Berechtigung löschen ----------------------------------------------------------------------------
	else if ($action == "delaccess")
	{
		printMLText("folder_del_access");
		if (isset($userid)) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$folder->removeAccess($userid, true);
		}
		else if (isset($groupid)) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$folder->removeAccess($groupid, false);
		}
	}

	//Neue Berechtigung hinzufügen --------------------------------------------------------------------
	else if ($action == "addaccess")
	{
		printMLText("folder_add_access");
		
		if (!is_numeric($mode))
			die ("invalid access mode");
		
		if ($userid != -1) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$folder->addAccess($mode, $userid, true);
		}
		if ($groupid != -1) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$folder->addAccess($mode, $groupid, false);
		}
	}
	
	printMLText("op_finished");
	printGoto(array(array($folder->getName(), "../out/out.ViewFolder.php?folderid=".$folder->getID()), 
				array(getMLText("folder_access_again"), "../out/out.FolderAccess.php?folderid=".$folder->getID())));
}



print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>
