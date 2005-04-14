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

if (!$user->isAdmin())
	die ("access denied");

printHTMLHead( getMLText("admin_tools") );
?>

<style type="text/css">
.folderClass {
	list-style-image : url(<?printImgPath("folder_closed.gif");?>);
	list-style : url(<?printImgPath("folder_closed.gif");?>);
}

.documentClass {
	list-style-image : url(<?printImgPath("file.gif");?>);
	list-style : url(<?printImgPath("file.gif");?>);
}
</style>

<script language="JavaScript">

function showDocument(id) {
	url = "out.DetailedStatistic.php?documentid=" + id;
	alert(url);
}

function showFolder(id) {
	url = "out.DetailedStatistic.php?folderid=" + id;
	alert(url);
}

</script>

<?
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();

printStartBox(getMLText("folders_and_documents_statistic"));
	print "<div class=\"standardText\"><ul>";
	printFolder(getFolder($settings->_rootFolderID));
	print "</ul></div>";
printEndBox();

printCenterEnd();
printHTMLFoot();


function getAccessColor($mode)
{
	if ($mode == M_NONE)
		return "red";
	else if ($mode == M_READ)
		return "orange";
	else if ($mode == M_READWRITE)
		return "green";
	else // if ($mode == M_ALL)
		return "blue";
}


function printFolder($folder)
{
	$color = $folder->inheritsAccess() ? "black" : getAccessColor($folder->getDefaultAccess());
	
	print "<li class=\"folderClass\">";
	print "<a class=\"standardText\" style=\"color: $color\" href=\"out.ViewFolder.php?folderid=".$folder->getID()."\">".$folder->getName() ."</a>";
	
	if (! $folder->inheritsAccess())
		printAccessList($folder);
	
	print "</li>";
	
	$subFolders = $folder->getSubFolders();
	$documents = $folder->getDocuments();
	
	print "<ul>";
	foreach ($subFolders as $folder)
		printFolder($folder);
	
	foreach ($documents as $document)
		printDocument($document);
	print "</ul>";
}


function printDocument($document)
{
	$color = $document->inheritsAccess() ? "black" : getAccessColor($document->getDefaultAccess());
	print "<li class=\"documentClass\">";
	print "<a class=\"standardText\" style=\"color: $color\" href=\"out.ViewDocument.php?documentid=".$document->getID()."\">".$document->getName()."</a>";
	
	if (! $document->inheritsAccess())
		printAccessList($document);
	
	print "</li>";
}

function printAccessList($obj)
{
	$accessList = $obj->getAccessList();
	if (count($accessList["users"]) == 0 && count($accessList["groups"]) == 0)
		return;
	
	print " <span class=\"standardText\">(";
	
	for ($i = 0; $i < count($accessList["groups"]); $i++)
	{
		$group = $accessList["groups"][$i]->getGroup();
		$color = getAccessColor($accessList["groups"][$i]->getMode());
		print "<span style=\"color: $color\">".$group->getName()."</span>";
		if ($i+1 < count($accessList["groups"]) || count($accessList["users"]) > 0)
			print ", ";
	}
	for ($i = 0; $i < count($accessList["users"]); $i++)
	{
		$user = $accessList["users"][$i]->getUser();
		$color = getAccessColor($accessList["users"][$i]->getMode());
		print "<span style=\"color: $color\">".$user->getFullName()."</span>";
		if ($i+1 < count($accessList["users"]))
			print ", ";
	}
	print ")</span>";
}
?>