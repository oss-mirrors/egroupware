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

$document = getDocument($documentid);


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($document->getFolder());
printCenterStart();

printStartBox(getMLText("edit_document_access"));

print "<div class=\"standardText\">";


if ($document->getAccessMode($user) < M_ALL)
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
		
		$document->setOwner($newOwner);
	}

	//Änderung auf nicht erben ------------------------------------------------------------------------
	else if ($action == "notinherit")
	{
		if ($mode == "copy")
			printMLText("document_set_not_inherit_copy");
		else
			printMLText("document_set_not_inherit_empty");
		
		$defAccess = $document->getDefaultAccess();
		$document->setInheritAccess(false);
		$document->setDefaultAccess($defAccess);
		
		//copy ACL of parent folder
		if ($mode == "copy")
		{
			$folder = $document->getFolder();
			$accessList = $folder->getAccessList();
			
			foreach ($accessList["users"] as $userAccess)
				$document->addAccess($userAccess->getMode(), $userAccess->getUserID(), true);
			foreach ($accessList["groups"] as $groupAccess)
				$document->addAccess($groupAccess->getMode(), $groupAccess->getGroupID(), false);
		}
	}

	//Änderung auf erben ------------------------------------------------------------------------------
	else if ($action == "inherit")
	{
		printMLText("document_set_inherit");
		
		$document->clearAccessList();
		$document->setInheritAccess(true);
	}

	//Standardberechtigung setzen----------------------------------------------------------------------
	else if ($action == "setdefault")
	{
		printMLText("document_set_default_access");
		
		if (!is_numeric($mode))
			die ("invalid access mode");
		
		$document->setDefaultAccess($mode);
	}

	//Bestehende Berechtigung änndern -----------------------------------------------------------------
	else if ($action == "editaccess")
	{
		printMLText("document_edit_access");
		
		if (!is_numeric($mode))
			die ("invalid access mode");
		
		if (isset($userid)) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$document->changeAccess($mode, $userid, true);
		}
		else if (isset($groupid)) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$document->changeAccess($mode, $groupid, false);
		}
	}

	//Berechtigung löschen ----------------------------------------------------------------------------
	else if ($action == "delaccess")
	{
		printMLText("document_del_access");
		
		if (isset($userid)) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$document->removeAccess($userid, true);
		}
		else if (isset($groupid)) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$document->removeAccess($groupid, false);
		}
	}

	//Neue Berechtigung hinzufügen --------------------------------------------------------------------
	else if ($action == "addaccess")
	{
		printMLText("document_add_access");
		
		if (!is_numeric($mode))
			die ("invalid access mode");
		
		if ($userid != -1) {
			if (!is_numeric($userid))
				die ("invalid user id");
			$document->addAccess($mode, $userid, true);
		}
		if ($groupid != -1) {
			if (!is_numeric($groupid))
				die ("invalid group id");
			$document->addAccess($mode, $groupid, false);
		}
	}
	printMLText("op_finished");
	printGoto(array(array($document->getName(), "../out/out.ViewDocument.php?documentid=".$document->getID()), 
				array(getMLText("document_access_again"), "../out/out.DocumentAccess.php?documentid=".$document->getID())));

}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();
?>
