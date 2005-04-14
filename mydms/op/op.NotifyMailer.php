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
include("../inc/inc.Utils.php");

function addExpiredMsg($document, $users)
{
	GLOBAL $msgs;
	
	$folder = $document->getFolder();
	$path = $folder->getPath();
	$pathStr = "";
	for ($i = 0; $i < count($path); $i++)
	{
		$pathStr .= $path[$i]->getName();
		if ($i+1 < count($path))
			$pathStr .= "/";
	}
	foreach ($users as $user)
	{
		if (!isset($msgs[$user->getEmail()]))
			$msgs[$user->getEmail()] = array();
		array_push(
			$msgs[$user->getEmail()],
			getMLText("msg_document_expired",
				array(
						"documentname" => $document->getName(),
						"path" => $pathStr,
						"documentid" => $document->getID(),
						"expires" => getReadableDate($document->getExpires())
				)
			)
		);
	}
}

function addChangedMsg($document, $users)
{
	GLOBAL $msgs;
	
	$latestContent = $document->getLatestContent();
	
	$folder = $document->getFolder();
	$path = $folder->getPath();
	$pathStr = "";
	for ($i = 0; $i < count($path); $i++)
	{
		$pathStr .= $path[$i]->getName();
		if ($i+1 < count($path))
			$pathStr .= "/";
	}
	
	foreach ($users as $user)
	{
		if (!isset($msgs[$user->getEmail()]))
			$msgs[$user->getEmail()] = array();
		
		array_push(
			$msgs[$user->getEmail()],
			getMLText("msg_document_updated",
				array(
					"documentname" => $document->getName(),
					"path" => $pathStr,
					"documentid" => $document->getID(), 
					"updated" => getLongReadableDate($latestContent->getDate())
				)
			)
		);
	}
}

function getUserOnlyNotifyList($obj, $oldList, $mode)
{
	$newList = $oldList;
	$listToAdd = $obj->getNotifyList();
	
	$tmpList = $listToAdd["users"];
	foreach ($listToAdd["groups"] as $group)
	{
		$members = $group->getUsers();
		foreach ($members as $member)
			array_push($tmpList, $member);
	}
	unset($listToAdd);
	foreach ($tmpList as $user)
	{
		$alreadyInList = false;
		foreach ($newList as $_user)
		{
			if ($_user->getID() == $user->getID())
			{
				$alreadyInList = true;
				break;
			}
		}
		if (!$alreadyInList)
			array_push($newList, $user);
	}
	return filterUsersByAccess($obj, $newList, $mode);
}

function notifyForDocument($document, $users)
{
	GLOBAl $settings;
	
	$newUsers = getUserOnlyNotifyList($document, $users, M_READ);
	
	//wenn das letzte update keine 24h (eher updateNotifyTime sek.) zurückliegt, werden
	//alle Benutzer, die in der alten Liste sowie in der Liste für diese Datei enthalten sind,
	//und mindestens Lese-Zugriff haben, benachrichtigt
	$latestContent = $document->getLatestContent();
	if (mktime() - $latestContent->getDate() < $settings->_updateNotifyTime)
		addChangedMsg($document, $newUsers);
	
	//über veraltete Inhalte werden nur solche Benutzer informiert, die auch noch über Schreib-Rechte verfügen
	if ($document->expires() && (mktime() > $document->getExpires()))
		addExpiredMsg($document, filterUsersByAccess($document, $newUsers, M_READWRITE));
}

function notifyForFolder($folder, $users)
{
	$newUsers = getUserOnlyNotifyList($folder, $users, M_READ);
	
	$documents = $folder->getDocuments();
	foreach ($documents as $document)
		notifyForDocument($document, $newUsers);
	
	$subFolders = $folder->getSubFolders();
	foreach ($subFolders as $subFolder)
		notifyForFolder($subFolder, $newUsers);
}

$rootFolder = getFolder($settings->_rootFolderID);
$msgs = array();
notifyForFolder($rootFolder, array());

print "<pre>";
print_r($msgs);
print "</pre>";

$receivers = array_keys($msgs);
foreach ($receivers as $receiver)
{
	$msg = implode("\n\n", $msgs[$receiver]);
	mail($receiver, getMLText("notify_subject"), $msg);
}
?>